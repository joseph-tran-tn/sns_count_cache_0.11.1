<?php
/*
class-scc-share-twitter-strategy.php

Description: This class is abstract class of a data crawler
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

class SCC_Share_Twitter_Strategy extends SCC_Crawl_Strategy {

	/**
	 * SNS base url (widgetoon.js & count.jsoon)
	 * @var string
	 */
	const DEF_BASE_URL_JSOON = 'http://jsoon.digitiminimi.com/twitter/count.json';

	/**
	 * SNS base url (OpenShareCount)
	 * @var string
	 */
	const DEF_BASE_URL_OPENSHARECOUNT = 'http://opensharecount.com/count.json';

	/**
	 * SNS base url (TwitCount)
	 * @var string
	 */
	const DEF_BASE_URL_TWITCOUNT = 'http://counts.twitcount.com/counts.php';

	/**
	 * SNS base url (NewShareCounts)
	 * @var string
	 */
	const DEF_BASE_URL_NEWSHARECOUNTS = 'http://public.newsharecounts.com/count.json';

	/**
	 * Class constarctor
	 * Hook onto all of the actions and filters needed by the plugin.
	 *
	 */
	protected function __construct() {
		SCC_Common_Util::log('[' . __METHOD__ . '] (line='. __LINE__ . ')');
		$this->method = 'GET';
	}

	/**
	 * Initialization
	 * @since 0.9.0
	 * @param  array  $options Option for this strategy
	 * @return null There is no return.
	 */
	public function initialize( $options = array() ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		if ( isset( $options['url'] ) ) $this->url = $options['url'];
		if ( isset( $options['method'] ) ) $this->method = $options['method'];
		if ( isset( $options['parameters'] ) ) $this->parameters = $options['parameters'];
		if ( isset( $options['twitter_api'] ) ) $this->twitter_api = $options['twitter_api'];
	}

	/**
	 * Build header
	 * @since 0.9.0
	 * @return null no need to build header for Twitter share count retrieval.
	 */
	public function build_header() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		return null;
	}

	/**
	 * Build query URL
	 * @since 0.9.0
	 * @return string Query URL
	 */
	public function build_query_url() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$base_url = self::DEF_BASE_URL_JSOON;

		if ( isset( $this->twitter_api ) && $this->twitter_api ) {
			if ( $this->twitter_api === SNS_Count_Cache::OPT_SHARE_TWITTER_API_JSOON ) {
				$base_url = self::DEF_BASE_URL_JSOON;
			} elseif ( $this->twitter_api ===  SNS_Count_Cache::OPT_SHARE_TWITTER_API_OPENSHARECOUNT ) {
				$base_url = self::DEF_BASE_URL_OPENSHARECOUNT;
			} elseif( $this->twitter_api === SNS_Count_Cache::OPT_SHARE_TWITTER_API_TWITCOUNT ) {
				$base_url = self::DEF_BASE_URL_TWITCOUNT;
			} elseif( $this->twitter_api === SNS_Count_Cache::OPT_SHARE_TWITTER_API_NEWSHARECOUNTS ) {
				$base_url = self::DEF_BASE_URL_NEWSHARECOUNTS;
			}
		}

		$url = $base_url . '?' . http_build_query( $this->query_parameters , '' , '&' );

		return $url;
	}

	/**
	 * Extract count
	 * @since 0.9.0
	 * @param string  $content Target content
	 * @return int SNS count
	 */
	public function extract_count( $content ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$count = (int) -1;

		if ( isset( $content['data'] ) && empty( $content['error'] ) ) {
			$json = json_decode( $content['data'], true );

			if ( isset( $json['count'] ) && is_numeric( $json['count'] ) ) {
				$count = (int) $json['count'];
			} else {
				$count = (int) -1;
			}
		} else {
			$count = (int) -1;
		}

		return $count;
	}

	/**
	 * Setter of query parameters
	 * @param string $key key
	 * @param string $value value
	 */
	public function set_query_parameter( $key, $value ) {
		if ( isset( $this->twitter_api ) && $this->twitter_api ) {
			if ( $this->twitter_api === SNS_Count_Cache::OPT_SHARE_TWITTER_API_JSOON ) {
				if ( $key === 'url' ) {
					if ( $value ===  home_url( '/', 'http' ) || $value ===  home_url( '/', 'https' ) ) {
						$this->query_parameters[$key] = '"' . $value . '"';
					} else {
						$this->query_parameters[$key] = $value;
					}
				}
			} elseif ( $this->twitter_api ===  SNS_Count_Cache::OPT_SHARE_TWITTER_API_OPENSHARECOUNT ) {
				if ( $key === 'url' ) {
					$this->query_parameters[$key] = $value;
				}
			} elseif( $this->twitter_api === SNS_Count_Cache::OPT_SHARE_TWITTER_API_TWITCOUNT ) {
				if ( $key === 'url' ) {
					$this->query_parameters[$key] = $value;
				}
			} elseif ( $this->twitter_api === SNS_Count_Cache::OPT_SHARE_TWITTER_API_NEWSHARECOUNTS ) {
				if ( $key === 'url' ) {
					$this->query_parameters[$key] = $value;
				}
			}
		} else {
			if ( $key === 'url' ) {
				$this->query_parameters[$key] = $value;
			}
		}
	}

	/**
	 * Check if required paramters are included or not.
	 * @since 0.9.0
	 * @return boolean Check result
	 */
	public function check_configuration() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		if ( isset( $this->query_parameters['url'] ) && $this->query_parameters['url'] ) {
			return true;
		} else {
			return false;
		}
	}

}
