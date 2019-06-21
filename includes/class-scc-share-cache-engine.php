<?php
/*
class-scc-share-cache-engine.php

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

abstract class SCC_Share_Cache_Engine extends SCC_Cache_Engine {

	/**
	 * Crawler instance
	 */
	protected $crawler = NULL;

	/**
	 * Cache target
	 */
	protected $target_sns = array();

	/**
	 * migration mode from http to https
	 */
	protected $scheme_migration_mode = false;

	/**
	 * migration date from http to https
	 */
	protected $scheme_migration_date = NULL;

	/**
	 * excluded keys in scheme migration
	 */
	protected $scheme_migration_exclude_keys = array();

	/**
	 * load ratio for throttle
	 */
	protected $load_ratio = 0.5;

 	/**
	 * Get and cache data for a given post
	 *
	 * @since 0.1.1
	 */
	public function cache( $options = array() ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$cache_key = $options['cache_key'];
		$target_url = $options['target_url'];
		$target_sns = $options['target_sns'];
		$cache_expiration = $options['cache_expiration'];
		$publish_date = $options['publish_date'];
		$target_title = $options['target_title'];

		SCC_Common_Util::log( '[' . __METHOD__ . '] current memory usage: ' . round( memory_get_usage( true )/1024/1024, 2 ) . ' MB' );
		SCC_Common_Util::log( '[' . __METHOD__ . '] target url: ' . $target_url );

		$target_option = array(
			'target_url' =>  $target_url,
			'target_title' => $target_title
		);

		$data = $this->get_data( $target_sns, $target_option );

		SCC_Common_Util::log( $data );
		SCC_Common_Util::log( '[' . __METHOD__ . '] scheme migration date: ' . $this->scheme_migration_date );
		SCC_Common_Util::log( '[' . __METHOD__ . '] publish date: ' . $publish_date );

		if ( $this->scheme_migration_mode && SCC_Common_Util::is_secure_url( $target_url ) ) {
			if ( ! isset( $this->scheme_migration_date ) ) {
				$target_normal_url = SCC_Common_Util::get_normal_url( $target_url );
				$target_sns_migrated = $target_sns;

				foreach ( $this->scheme_migration_exclude_keys as $sns ) {
					unset( $target_sns_migrated[$sns] );
				}

				SCC_Common_Util::log( '[' . __METHOD__ . '] target url: ' . $target_normal_url );

				$target_option = array(
					'target_url' =>  $target_normal_url,
					'target_title' => $target_title
				);

				$migrated_data = $this->get_data( $target_sns_migrated, $target_option );

				SCC_Common_Util::log( $migrated_data );

				foreach ( $target_sns_migrated as $sns => $active ) {
					if ( $active && isset( $migrated_data[$sns] ) && is_numeric( $migrated_data[$sns] ) && $migrated_data[$sns] > 0 ){
						$data[$sns] = $data[$sns] + $migrated_data[$sns];
					}
				}

			} else {
				if ( isset( $publish_date ) ) {
					if ( strtotime( $publish_date ) <= strtotime( $this->scheme_migration_date ) ) {
						$target_normal_url = SCC_Common_Util::get_normal_url( $target_url );
						$target_sns_migrated = $target_sns;

						foreach ( $this->scheme_migration_exclude_keys as $sns ) {
							unset( $target_sns_migrated[$sns] );
						}

						SCC_Common_Util::log( '[' . __METHOD__ . '] target url: ' . $target_normal_url );

						$target_option = array(
							'target_url' =>  $target_normal_url,
							'target_title' => $target_title
						);

						$migrated_data = $this->get_data( $target_sns_migrated, $target_option );

						SCC_Common_Util::log( $migrated_data );

						foreach ( $target_sns_migrated as $sns => $active ) {
							if ( $active && isset( $migrated_data[$sns] ) && is_numeric( $migrated_data[$sns] ) && $migrated_data[$sns] > 0 ){
								$data[$sns] = $data[$sns] + $migrated_data[$sns];
							}
						}
					}
				} else {
					$target_normal_url = SCC_Common_Util::get_normal_url( $target_url );
					$target_sns_migrated = $target_sns;

					foreach ( $this->scheme_migration_exclude_keys as $sns ) {
						unset( $target_sns_migrated[$sns] );
					}

					SCC_Common_Util::log( '[' . __METHOD__ . '] target url: ' . $target_url );

					$target_option = array(
						'target_url' =>  $target_url,
						'target_title' => $target_title
					);

					$migrated_data = $this->get_data( $target_sns_migrated, $target_option );

					SCC_Common_Util::log( $migrated_data );

					foreach ( $target_sns_migrated as $sns => $active ) {
						if ( $active && isset( $migrated_data[$sns] ) && is_numeric( $migrated_data[$sns] ) && $migrated_data[$sns] > 0 ){
							$data[$sns] = $data[$sns] + $migrated_data[$sns];
						}
					}
				}
			}

			$target_sns_param = $target_sns;

			unset( $target_sns_param[SNS_Count_Cache::REF_CRAWL_DATE] );
			unset( $target_sns_param[SNS_Count_Cache::REF_SHARE_TOTAL] );

			$query_parameters = array(
				'url' => $target_url
				);

			foreach ( $target_sns_param as $sns => $active ) {
				if ( $active ) {
					$this->crawler->set_crawl_strategy_query_parameters( $sns, $query_parameters );
				}
			}
		}

		if ( $this->fault_tolerance === SNS_Count_Cache::OPT_COMMON_FAULT_TOLERANCE_ON ) {

			$target_sns_tmp = $target_sns;
			unset( $target_sns_tmp[SNS_Count_Cache::REF_CRAWL_DATE] );
			unset( $target_sns_tmp[SNS_Count_Cache::REF_SHARE_TOTAL] );

			$target_sns_tolerance  = array();

			foreach ( $target_sns_tmp as $sns => $active ){
				if ( $active ) {
					if ( ! isset( $data[$sns] ) || $data[$sns] === -1 ) {
						$target_sns_tolerance[$sns] = true;
					}
				}
			}

			if ( ! empty( $target_sns_tolerance) ) {
				$current_data = $this->get_cache( $options );

				foreach ( $target_sns_tolerance as $sns => $active ){
					if ( $active ) {
						if ( isset( $current_data[$sns] ) && $current_data[$sns] >= 0 ) {
							$data[$sns] = $current_data[$sns];
						}
					}
				}

				$total = 0;

				foreach ( $target_sns_tmp as $sns => $active ){
					if ( $active ) {
						if ( isset( $data[$sns] ) &&  $data[$sns] >= 0 ) {
							$total = $total + $data[$sns];
						}
					}
				}

				$data[SNS_Count_Cache::REF_SHARE_TOTAL] = $total;
			}
		}

		SCC_Common_Util::log( $data );

		if ( $data ) {
			$throttle = new SCC_Sleep_Throttle( $this->load_ratio );

			$throttle->reset();
			$throttle->start();

			$result = set_transient( $cache_key, $data, $cache_expiration );
			$throttle->stop();

			$retry_count = 0;

			while ( true ) {

				SCC_Common_Util::log( '[' . __METHOD__ . '] set_transient result (' . $cache_key . '): ' . $result );

				if ( $result ) {
					break;
				} else {
					if ( $retry_count < $this->retry_limit ) {
						SCC_Common_Util::log( '[' . __METHOD__ . '] sleep before set_transient retry (' . $cache_key . '): ' . $throttle->get_sleep_time() . ' sec.' );

						$throttle->sleep();
						++$retry_count;

						SCC_Common_Util::log( '[' . __METHOD__ . '] count of set_transient retry (' . $cache_key . '): ' . $retry_count );

						$throttle->reset();
						$throttle->start();

						$result = set_transient( $cache_key, $data, $cache_expiration );

						$throttle->stop();
					} else {
						SCC_Common_Util::log( '[' . __METHOD__ . '] set_transient result (' . $cache_key . '): retry failed' );
						break;
					}
				}
			}
		}

		SCC_Common_Util::log( '[' . __METHOD__ . '] current memory usage: ' . round( memory_get_usage( true )/1024/1024, 2 ) . ' MB' );
		SCC_Common_Util::log( '[' . __METHOD__ . '] max memory usage: ' . round( memory_get_peak_usage( true )/1024/1024, 2 ) . ' MB' );

		return $data;
	}

	/**
	 * Get data
	 *
	 * @since 0.9.0
	 */
	private function get_data( $target_sns, $target_option ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$target_sns_base = $target_sns;

		$target_url = $target_option['target_url'];
		$target_title = $target_option['target_title'];

		unset( $target_sns_base[SNS_Count_Cache::REF_CRAWL_DATE] );
		unset( $target_sns_base[SNS_Count_Cache::REF_SHARE_TOTAL] );

		$query_parameters = array(
			'url' => $target_url,
			'title' => $target_title
		);

		foreach ( $target_sns_base as $sns => $active ) {
			if ( $active ) {
				$this->crawler->set_crawl_strategy_query_parameters( $sns, $query_parameters );
			}
		}

		return $this->crawler->get_data( $target_sns, NULL );
	}

}

?>
