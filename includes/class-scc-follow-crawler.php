<?php
/*
class-scc-follow-crawler.php

Description: This class is a data crawler whitch get share count using given API and cURL
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

class SCC_Follow_Crawler extends SCC_Crawler {

	/**
	 * Initialization
	 *
	 * @since 0.5.1
	 */
	public function initialize( $options = array() ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		//$this->throttle = new Sleep_Throttle( 0.9 );

		if ( isset( $options['target_sns'] ) ) $this->target_sns = $options['target_sns'];
		if ( isset( $options['crawl_method'] ) ) $this->crawl_method = $options['crawl_method'];
		if ( isset( $options['timeout'] ) ) $this->timeout = $options['timeout'];
		if ( isset( $options['ssl_verification'] ) ) $this->ssl_verification = $options['ssl_verification'];
		if ( isset( $options['crawl_retry'] ) ) $this->crawl_retry = $options['crawl_retry'];
		if ( isset( $options['retry_limit'] ) ) $this->retry_limit = $options['retry_limit'];

		$target_sns = $this->target_sns;

		unset( $target_sns[SNS_Count_Cache::REF_CRAWL_DATE] );

		foreach ( $target_sns as $sns => $active ) {
			if ( $active ) {
				$this->crawl_strategies[$sns] = SCC_Follow_Crawl_Strategy_Factory::create_crawl_strategy( $sns );
			}
		}

	}

	/**
	 * Check configuration
	 *
	 * @since 0.9.0
	 */
	private function check_configurations( $target_sns ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		//$checked_target_sns = array();

		unset( $target_sns[SNS_Count_Cache::REF_CRAWL_DATE] );

		SCC_Common_Util::log( $target_sns );

		foreach ( $target_sns as $sns => $active ) {
			if ( $active ) {
				$target_sns[$sns] = $this->crawl_strategies[$sns]->check_configuration();
			}
		}

		$target_sns[SNS_Count_Cache::REF_CRAWL_DATE] = true;

		return $target_sns;

	}

	/**
	 * Implementation of abstract method. this method gets each share count
	 *
	 * @since 0.1.1
	 */
	public function get_data( $target_sns, $options ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		SCC_Common_Util::log( $target_sns );

		$valid_target_sns = $this->check_configurations( $target_sns );

		SCC_Common_Util::log( $valid_target_sns );

		$query_urls = $this->build_query_urls( $valid_target_sns );

		SCC_Common_Util::log( $query_urls );

		$query_headers = $this->build_query_headers( $valid_target_sns );

		SCC_Common_Util::log( $query_headers );

		$data = array();

		$throttle = new SCC_Sleep_Throttle( 0.9 );

		$throttle->reset();
		$throttle->start();

		if ( $this->crawl_method === SNS_Count_Cache::OPT_COMMON_CRAWLER_METHOD_CURL ) {
			$data = SCC_Common_Util::multi_remote_get( $query_urls, $query_headers, $this->timeout, $this->ssl_verification, true );
		} else {
			$data = SCC_Common_Util::multi_remote_get( $query_urls, $query_headers, $this->timeout, $this->ssl_verification, false );
		}

		$throttle->stop();

		$retry_count = 0;

		while( true ) {
			$target_sns_retry = array();

			$tmp_count = $this->extract_count( $valid_target_sns, $data );

			foreach ( $valid_target_sns as $sns => $active ){
				if ( $active ) {
					if ( $tmp_count[$sns] === -1 ) {
						$target_sns_retry[$sns] = true;
					}
				}
			}

			if ( empty( $target_sns_retry ) ) {
				break;
			} else {
				SCC_Common_Util::log( '[' . __METHOD__ . '] crawl failure' );
				SCC_Common_Util::log( $target_sns_retry );

				if ( $retry_count < $this->retry_limit ) {

					SCC_Common_Util::log( '[' . __METHOD__ . '] sleep before crawl retry: ' . $throttle->get_sleep_time() . ' sec.' );

					$throttle->sleep();

					++$retry_count;

					SCC_Common_Util::log( '[' . __METHOD__ . '] count of crawl retry: ' . $retry_count );

					$query_urls_retry = $this->build_query_urls( $target_sns_retry );

					$query_headers_retry = $this->build_query_headers( $target_sns_retry );

					$data_retry = array();

					$throttle->reset();
					$throttle->start();

					if ( $this->crawl_method === SNS_Count_Cache::OPT_COMMON_CRAWLER_METHOD_CURL ) {
						$data_retry = SCC_Common_Util::multi_remote_get( $query_urls_retry, $query_headers_retry, $this->timeout, $this->ssl_verification, true );
					} else {
						$data_retry = SCC_Common_Util::multi_remote_get( $query_urls_retry, $query_headers_retry, $this->timeout, $this->ssl_verification, false );
					}

					$throttle->stop();

					$data = array_merge( $data, $data_retry );
				} else {
					SCC_Common_Util::log( '[' . __METHOD__ . '] crawling: retry failed' );
					break;
				}
			}
		}

		return $this->extract_count( $target_sns, $data );
	}

	/**
	 * build query
	 *
	 * @since 0.5.1
	 */
	private function build_query_urls( $target_sns ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$query_urls = array();

		unset( $target_sns[SNS_Count_Cache::REF_CRAWL_DATE] );

		SCC_Common_Util::log( $target_sns );


		foreach ( $target_sns as $sns => $active ) {
			if ( $active ) {
				$query_urls[$sns] = $this->crawl_strategies[$sns]->build_query_url();
			}
		}

		return $query_urls;
	}

	/**
	 * build query
	 *
	 * @since 0.5.1
	 */
	private function build_query_headers( $target_sns ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$query_headers = array();

		unset( $target_sns[SNS_Count_Cache::REF_CRAWL_DATE] );

		SCC_Common_Util::log( $target_sns );

		foreach ( $target_sns as $sns => $active ) {
			if ( $active ) {
				$query_headers[$sns] = $this->crawl_strategies[$sns]->build_header();
			}
		}

		return $query_headers;
	}

	/**
	 * extract count data from retrieved content
	 *
	 * @since 0.5.1
	 */
	private function extract_count( $target_sns, $contents ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$sns_counts = array();

		$extract_date = date_i18n( 'Y/m/d H:i:s' );

		SCC_Common_Util::log( $contents );

		foreach ( $target_sns as $sns => $active ) {
			if ( $active ) {
				if ( isset( $contents[$sns] ) ) {
					$sns_counts[$sns] = $this->crawl_strategies[$sns]->extract_count( $contents[$sns] );
				} else {
					$sns_counts[$sns] = (int) -1;
				}
			}
		}

		if ( isset( $target_sns[SNS_Count_Cache::REF_CRAWL_DATE] ) && $target_sns[SNS_Count_Cache::REF_CRAWL_DATE] ) {
			$sns_counts[SNS_Count_Cache::REF_CRAWL_DATE] = $extract_date;
		} else {
			$sns_counts[SNS_Count_Cache::REF_CRAWL_DATE] = '';
		}

		return $sns_counts;
	}

}

?>
