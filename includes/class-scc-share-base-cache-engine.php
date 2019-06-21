<?php
/*
class-scc-share-base-cache-engine.php

Description: This class is a data cache engine whitch get and cache data using wp-cron at regular intervals
Author: Daisuke Maruyama
Author URI: http://marubon.info/
License: GPL2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

/*
Copyright (C) 2014 - 2017 Daisuke Maruyama

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

class SCC_Share_Base_Cache_Engine extends SCC_Share_Cache_Engine {

	/**
	 * Prefix of cache ID
	 */
	const DEF_TRANSIENT_PREFIX = 'scc_share_count_';

	/**
	 * Cron name to schedule cache processing
	 */
	const DEF_PRIME_CRON = 'scc_share_basecache_prime';

	/**
	 * Cron name to execute cache processing
	 */
	const DEF_EXECUTE_CRON = 'scc_share_basecache_exec';

	/**
	 * Schedule name for cache processing
	 */
	const DEF_EVENT_SCHEDULE = 'share_base_cache_event';

	/**
	 * Schedule description for cache processing
	 */
 	const DEF_EVENT_DESCRIPTION = '[SCC] Share Base Cache Interval';

	/**
	 * Cache post types
	 */
	private $post_types = array( 'post', 'page' );

	/**
	 * Interval cheking and caching target data
	 */
	private $check_interval = 600;

	/**
	 * Number of posts to check at a time
	 */
	private $posts_per_check = 20;

	/**
	 * Offset suffix
	 */
	private $offset_suffix = 'base_offset';

	/**
	 * Initialization
	 *
	 * @since 0.1.1
	 */
	public function initialize( $options = array() ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$this->cache_prefix = self::DEF_TRANSIENT_PREFIX;
		$this->prime_cron = self::DEF_PRIME_CRON;
		$this->execute_cron = self::DEF_EXECUTE_CRON;
		$this->event_schedule = self::DEF_EVENT_SCHEDULE;
		$this->event_description = self::DEF_EVENT_DESCRIPTION;

		$this->load_ratio = 0.5;

		if ( isset( $options['delegate'] ) ) $this->delegate = $options['delegate'];
		if ( isset( $options['crawler'] ) ) $this->crawler = $options['crawler'];
		if ( isset( $options['target_sns'] ) ) $this->target_sns = $options['target_sns'];
		if ( isset( $options['check_interval'] ) ) $this->check_interval = $options['check_interval'];
		if ( isset( $options['posts_per_check'] ) ) $this->posts_per_check = $options['posts_per_check'];
		if ( isset( $options['cache_prefix'] ) ) $this->cache_prefix = $options['cache_prefix'];
		if ( isset( $options['prime_cron'] ) ) $this->prime_cron = $options['prime_cron'];
		if ( isset( $options['execute_cron'] ) ) $this->execute_cron = $options['execute_cron'];
		if ( isset( $options['event_schedule'] ) ) $this->event_schedule = $options['event_schedule'];
		if ( isset( $options['event_description'] ) ) $this->event_description = $options['event_description'];
		if ( isset( $options['post_types'] ) ) $this->post_types = $options['post_types'];
		if ( isset( $options['scheme_migration_mode'] ) ) $this->scheme_migration_mode = $options['scheme_migration_mode'];
		if ( isset( $options['scheme_migration_date'] ) ) $this->scheme_migration_date = $options['scheme_migration_date'];
		if ( isset( $options['scheme_migration_exclude_keys'] ) ) $this->scheme_migration_exclude_keys = $options['scheme_migration_exclude_keys'];
		if ( isset( $options['cache_retry'] ) ) $this->cache_retry = $options['cache_retry'];
		if ( isset( $options['retry_limit'] ) ) $this->retry_limit = $options['retry_limit'];
		if ( isset( $options['fault_tolerance'] ) ) $this->fault_tolerance = $options['fault_tolerance'];

		add_filter( 'cron_schedules', array( $this, 'schedule_check_interval' ) );
		add_action( $this->prime_cron, array( $this, 'prime_cache' ) );
		add_action( $this->execute_cron, array( $this, 'execute_cache' ), 10, 1 );
	}

	/**
	 * Register event schedule for this engine
	 *
	 * @since 0.1.0
	 */
	public function schedule_check_interval( $schedules ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$schedules[$this->event_schedule] = array(
			'interval' => $this->check_interval,
			'display' => $this->event_description
		);

		return $schedules;
	}

	/**
	 * Schedule data retrieval and cache processing
	 *
	 * @since 0.1.0
	 */
	public function prime_cache() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$next_exec_time = (int) current_time( 'timestamp', 1 ) + $this->check_interval;
		$posts_total = $this->get_posts_total();

		SCC_Common_Util::log( '[' . __METHOD__ . '] check_interval: ' . $this->check_interval );
		SCC_Common_Util::log( '[' . __METHOD__ . '] posts_per_check: ' . $this->posts_per_check );
		SCC_Common_Util::log( '[' . __METHOD__ . '] next_exec_time: ' . $next_exec_time );
		SCC_Common_Util::log( '[' . __METHOD__ . '] posts_total: ' . $posts_total );

		$option_key = $this->get_cache_key( $this->offset_suffix );

		if ( false === ( $posts_offset = get_option( $option_key ) ) ) {
			$posts_offset = 0;
		}

		SCC_Common_Util::log( '[' . __METHOD__ . '] posts_offset: ' . $posts_offset );

		wp_schedule_single_event( $next_exec_time, $this->execute_cron, array( (int) $posts_offset ) );

		$posts_offset = $posts_offset + $this->posts_per_check;

		if ( $posts_offset > $posts_total ) {
			$posts_offset = 0;
		}

		update_option( $option_key, $posts_offset );
	}

	/**
	 * Get and cache data of each published post and page
	 *
	 * @since 0.1.0
	 */
	public function execute_cache( $posts_offset ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		SCC_Common_Util::log( '[' . __METHOD__ . '] posts_offset: ' . $posts_offset );
		SCC_Common_Util::log( '[' . __METHOD__ . '] check_interval: ' . $this->check_interval );
		SCC_Common_Util::log( '[' . __METHOD__ . '] posts_per_check: ' . $this->posts_per_check );

		$cache_expiration = $this->get_cache_expiration();

		SCC_Common_Util::log( '[' . __METHOD__ . '] cache_expiration: ' . $cache_expiration );

		// Cache home url
		if ( $posts_offset == 0 ) {
			$post_ID = 'home';

			$transient_id = $this->get_cache_key( $post_ID );
			$url = home_url( '/' );
			$title = get_bloginfo( 'name' );

			$options = array(
				'cache_key' => $transient_id,
				'post_id' => $post_ID,
				'target_url' => $url,
				'target_sns' => $this->target_sns,
				'target_title' => $title,
				'publish_date' => NULL,
				'cache_expiration' => $cache_expiration
				);

			// Primary cache
			$this->cache( $options );

			// Secondary cache
			$this->delegate_order( SCC_Order::ORDER_DO_SECOND_CACHE, $options );
		}

		$query_args = array(
			'post_type' => $this->post_types,
			'post_status' => 'publish',
			'offset' => $posts_offset,
			'posts_per_page' => $this->posts_per_check,
			'no_found_rows' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false
			);

		$posts_query = new WP_Query( $query_args );

		if ( $posts_query->have_posts() ) {
			while ( $posts_query->have_posts() ) {
				$posts_query->the_post();

				$post_ID = get_the_ID();

				SCC_Common_Util::log( '[' . __METHOD__ . '] post_id: ' . $post_ID );

				$transient_id = $this->get_cache_key( $post_ID );

				$url = get_permalink( $post_ID );
				$title = get_the_title( $post_ID );

				$options = array(
					'cache_key' => $transient_id,
					'post_id' => $post_ID,
					'target_url' => $url,
					'target_sns' => $this->target_sns,
					'target_title' => $title,
					'publish_date' => get_the_date( 'Y/m/d' ),
					'cache_expiration' => $cache_expiration
					);

				// Primary cache
				$this->cache( $options );

				// Secondary cache
				$this->delegate_order( SCC_Order::ORDER_DO_SECOND_CACHE, $options );
			}
		}
		wp_reset_postdata();
	}

	/**
	 * Get and cache data for a given post
	 *
	 * @since 0.1.1
	 */
	public function direct_cache( $post_ID, $second_sync = false ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$cache_expiration = $this->get_cache_expiration();

		SCC_Common_Util::log( '[' . __METHOD__ . '] cache_expiration: ' . $cache_expiration );

		$transient_id = $this->get_cache_key( $post_ID );

		if ( $post_ID != 'home' ) {
			$url = get_permalink( $post_ID );
			$title = get_the_title( $post_ID );
			$publish_date = get_the_date( 'Y/m/d', $post_ID );
		} else {
			$url = home_url( '/' );
			$title = get_bloginfo( 'name' );
			$publish_date = NULL;
		}

		$options = array(
			'cache_key' => $transient_id,
			'post_id' => $post_ID,
			'target_url' => $url,
			'target_sns' => $this->target_sns,
			'target_title' => $title,
			'publish_date' => $publish_date,
			'cache_expiration' => $cache_expiration
			);

		// Primary cache
		$result = $this->cache( $options );

		if ( $second_sync ) {
			// Secondary cache
			$this->delegate_order( SCC_Order::ORDER_DO_SECOND_CACHE, $options );
		}

		return $result;
	}

	/**
	 * Get cache expiration based on current number of total post and page
	 *
	 * @since 0.1.1
	 */
	protected function get_cache_expiration() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$posts_total = $this->get_posts_total();

		SCC_Common_Util::log( '[' . __METHOD__ . '] posts_total: ' . $posts_total );

		return ceil( $posts_total / $this->posts_per_check ) * $this->check_interval * 3;
	}

	/**
	 * Initialize meta key for ranking
	 *
	 * @since 0.3.0
	 */
	public function initialize_cache() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$option_key = $this->get_cache_key( $this->offset_suffix );

		update_option( $option_key, 0 );
	}

	/**
	 * Clear meta key for ranking
	 *
	 * @since 0.3.0
	 */
	public function clear_cache() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$transient_id = $this->get_cache_key( 'home' );

		delete_transient( $transient_id );

		$query_args = array(
			'post_type' => $this->post_types,
			'post_status' => 'publish',
			'nopaging' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false
			);

		$posts_query = new WP_Query( $query_args );

		if ( $posts_query->have_posts() ) {
			while ( $posts_query->have_posts() ) {
				$posts_query->the_post();

				$post_ID = get_the_ID();

				$transient_id = $this->get_cache_key( $post_ID );

				delete_transient( $transient_id );
			}
		}
		wp_reset_postdata();

		$option_key = $this->get_cache_key( $this->offset_suffix );

		delete_option( $option_key );
	}


    /**
	 * Clear meta key for ranking
	 *
	 * @since 0.7.0
	 */
	public function clear_cache_by_post_id( $post_id ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$transient_id = $this->get_cache_key( $post_id );

		delete_transient( $transient_id );
	}

	/**
	 * Get total count of current published post and page
	 *
	 * @since 0.1.0
	 */
	private function get_posts_total() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$query_args = array(
			'post_type' => $this->post_types,
			'post_status' => 'publish',
			'nopaging' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false
			);

		$posts_query = new WP_Query( $query_args );

		return $posts_query->found_posts;
	}

	/**
	 * Get cache
	 *
	 * @since 0.9.3
	 */
	public function get_cache( $options = array() ) {
		$post_id = $options['post_id'];

		$transient_id = $this->get_cache_key( $post_id );

		$sns_counts = array();

		if ( false !== ( $sns_counts = get_transient( $transient_id ) ) ) {
			return $sns_counts;
		} else {
			return $this->delegate_order( SCC_Order::ORDER_GET_SECOND_CACHE, $options );
		}
	}

}

?>
