<?php
/*
class-scc-follow-twitter-strategy.php

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

class SCC_Follow_Twitter_Strategy extends SCC_Crawl_Strategy {

	/**
	 * SNS base url
	 */
	const DEF_BASE_URL = 'https://api.twitter.com/1.1/users/show.json';

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
	 *
	 * @since 0.9.0
	 */
	public function initialize( $options = array() ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		if ( isset( $options['url'] ) ) $this->url = $options['url'];
		if ( isset( $options['method'] ) ) $this->method = $options['method'];
		if ( isset( $options['parameters'] ) ) $this->parameters = $options['parameters'];
	}

  	/**
	 * Build header
	 *
	 * @since 0.9.0
	 */
	public function build_header() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$headers = array();

		/*
		// vesion using user auth
		$oauth_parameters = array(
			'oauth_consumer_key' => $this->parameters['consumer_key'],
			'oauth_token' => $this->parameters['access_token'],
			'oauth_nonce' => microtime(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp' => time(),
			'oauth_version' => '1.0'
			);

		$signature_key = rawurlencode( $this->parameters['consumer_secret'] ) . '&' . rawurlencode( $this->parameters['access_token_secret'] );

		$oauth_parameters = array_merge( $oauth_parameters, $this->query_parameters );

		ksort( $oauth_parameters );

		$signature_parameters = str_replace( array( '+' , '%7E' ) , array( '%20' , '~' ) , http_build_query( $oauth_parameters , '' , '&' ) );

		$signature_data = rawurlencode( $this->method ) . '&' . rawurlencode( self::DEF_BASE_URL ) . '&' . rawurlencode( $signature_parameters );

		$signature = base64_encode( hash_hmac( 'sha1' , $signature_data , $signature_key , true ) );

		$oauth_parameters['oauth_signature'] = $signature;

		$header_parameters = http_build_query( $oauth_parameters, '', ',' );

		$headers['Authorization'] = 'OAuth ' . $header_parameters;
		*/

		//version using application-only auth
		$headers['Authorization'] = 'Bearer ' . $this->parameters['bearer_token'];

		return $headers;
	}

	/**
	 * Build query url
	 *
	 * @since 0.9.0
	 */
	public function build_query_url() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$url = self::DEF_BASE_URL . '?' . http_build_query( $this->query_parameters , '' , '&' );

		return $url;
	}

	/**
	 * Extract count
	 *
	 * @since 0.9.0
	 */
	public function extract_count( $content ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$count = (int) -1;

		if ( isset( $content['data'] ) && empty( $content['error'] ) ) {
			$json = json_decode( $content['data'], true );

			if ( isset( $json['followers_count'] ) && is_numeric( $json['followers_count'] ) ) {
				$count = (int) $json['followers_count'];
			} else {
				$count = (int) -1;
			}
		} else {
			$count = (int) -1;
		}

		return $count;
	}

	public function set_query_parameter( $key, $value ) {
		if ( $key === 'screen_name' ) {
			$this->query_parameters[$key] = $value;
		}
	}

	/**
	 * Check if required paramters are included or not.
	 *
	 * @since 0.9.0
	 */
	public function check_configuration() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		/*
		// vesion using user auth
		if ( isset( $this->query_parameters['screen_name'] ) && $this->query_parameters['screen_name'] &&
			isset( $this->parameters['consumer_key'] ) && $this->parameters['consumer_key'] &&
			isset( $this->parameters['consumer_secret'] ) && $this->parameters['consumer_secret'] &&
			isset( $this->parameters['access_token'] ) && $this->parameters['access_token'] &&
			isset( $this->parameters['access_token_secret'] ) && $this->parameters['access_token_secret']
		) {
			return true;
		} else {
			return false;
		}
		*/

		//version using application-only auth
		if ( isset( $this->query_parameters['screen_name'] ) && $this->query_parameters['screen_name'] &&
			isset( $this->parameters['consumer_key'] ) && $this->parameters['consumer_key'] &&
			isset( $this->parameters['consumer_secret'] ) && $this->parameters['consumer_secret'] &&
			isset( $this->parameters['bearer_token'] ) && $this->parameters['bearer_token']
		) {
			return true;
		} else {
			return false;
		}

	}

}
