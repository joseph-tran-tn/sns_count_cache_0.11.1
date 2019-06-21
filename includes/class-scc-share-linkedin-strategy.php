<?php
/*
class-scc-share-linkedin-strategy.php

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

class SCC_Share_Linkedin_Strategy extends SCC_Crawl_Strategy {

	/**
	 * SNS base url
	 * @var string
	 */
	const DEF_BASE_URL = 'https://www.linkedin.com/countserv/count/share';

	/**
	 * Class constarctor
	 * Hook onto all of the actions and filters needed by the plugin.
	 *
	 */
	protected function __construct() {
		SCC_Common_Util::log('[' . __METHOD__ . '] (line='. __LINE__ . ')');

		$this->method = 'GET';
		$this->query_parameters['format'] = 'json';
	}

	/**
	 * Initialization
	 * @since 0.11.0
	 * @param  array  $options Option for this strategy
	 * @return null There is no return.
	 */
	public function initialize( $options = array() ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		if ( isset( $options['url'] ) ) $this->url = $options['url'];
		if ( isset( $options['method'] ) ) $this->method = $options['method'];
		if ( isset( $options['parameters'] ) ) $this->parameters = $options['parameters'];
	}

	/**
	 * Build header
	 * @since 0.11.0
	 * @return null no need to build header for Twitter share count retrieval.
	 */
	public function build_header() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		return null;
	}

	/**
	 * Build query URL
	 * @since 0.11.0
	 * @return string Query URL
	 */
	public function build_query_url() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$url = self::DEF_BASE_URL . '?' . http_build_query( $this->query_parameters , '' , '&' );

		return $url;
	}

	/**
	 * Extract count
	 * @since 0.11.0
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
		if ( $key === 'url' ) {
			$this->query_parameters[$key] = $value;
		}
	}

	/**
	 * Check if required paramters are included or not.
	 * @since 0.11.0
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
