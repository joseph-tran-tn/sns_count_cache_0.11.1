<?php
/*
class-scc-common-util.php

Description: This class is a common utility
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

class SCC_Common_Util {

	/**
	 * Class constarctor
	 * Hook onto all of the actions and filters needed by the plugin.
	 *
	 */
	protected function __construct() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	}

	/**
	 * Output log message according to WP_DEBUG setting
	 *
	 * @since 0.1.0
	 */
	public static function log( $message ) {
		if ( WP_DEBUG === true ) {
			if ( is_array( $message ) || is_object( $message ) ) {
				error_log( print_r( $message, true ) );
			} else {
				error_log( $message );
			}
		}
	}

	/**
	 * Get short hash code
	 *
	 * @since 0.2.0
	 */
	public static function short_hash( $data, $algo = 'CRC32' ) {
		return strtr( rtrim( base64_encode( pack( 'H*', sprintf( '%u', $algo( $data ) ) ) ), '=' ), '+/', '-_' );
	}

	/**
	 * Get file size of given file
	 *
	 * @since 0.4.0
	 */
	public static function get_file_size( $file ) {

		if ( file_exists( $file ) && is_file( $file ) ) {
			$filesize = filesize( $file );
			$s = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB' );
			$e = floor( log( $filesize ) / log( 1024 ) );

			if( $e == 0 || $e == 1 ) {
				$format = '%d ';
			} else {
				$format = '%.1f ';
			}

 			$filesize = sprintf( $format . $s[$e], ( $filesize / pow( 1024, floor( $e ) ) ) );

			return $filesize;
		} else {
			return null;
		}
	}

	/**
	 * Get custom post types
	 *
	 * @since 0.4.0
	 */
	public static function get_custom_post_types() {

		global $wpdb;

		$custom_post_types = array();

		$builtin_post_types = get_post_types( array( '_builtin' => true ) );

		$exclude_post_types = "'";
		$exclude_post_types .= implode( "','", $builtin_post_types );
		$exclude_post_types .= "'";

		$sql = 'SELECT DISTINCT post_type FROM ' . $wpdb->posts . ' WHERE post_type NOT IN ( ' . $exclude_post_types . ' )';

		$results = $wpdb->get_results( $sql );

		foreach ( $results as $value ) {
			$custom_post_types[] = $value->post_type;
		}

		return $custom_post_types;
	}

	/**
	 * check if php-xml module is loaded or not
	 *
	 * @since 0.4.0
	 */
	public static function extension_loaded_php_xml() {
		if ( extension_loaded( 'xml' ) && extension_loaded( 'xmlreader' ) && extension_loaded( 'xmlwriter' ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * convert url based on http into url based on https
	 *
	 * @since 0.4.0
	 */
	public static function get_secure_url( $url ){
		$url = str_replace( 'http://', 'https://', $url );
		return $url;
	}

	/**
	 * convert url based on https into url based on http
	 *
	 * @since 0.4.0
	 */
	public static function get_normal_url( $url ){
		$url = str_replace( 'https://', 'http://', $url );
		return $url;
	}

	/**
	 * check if a given url is based on https or not.
	 *
	 * @since 0.4.0
	 */
	 public static function is_secure_url( $url ){
		if ( preg_match( '/^(https)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $url ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * check if a given url is based on http or not.
	 *
	 * @since 0.4.0
	 */
	public static function is_normal_url( $url ){
		if ( preg_match( '/^(http)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $url ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * get cout data from SNS
	 *
	 * @since 0.5.1
	 */
	public static function multi_remote_get( $urls, $headers, $timeout = 0, $sslverify = true, $curl = false ) {
		global $wp_version;

		$responses = array();

		if ( empty( $urls ) ) {
			return $responses;
		}

		if ( $curl ) {
			SCC_Common_Util::log( '[' . __METHOD__ . '] cURL: On' );

			$mh = curl_multi_init();
			$ch = array();

			foreach ( $urls as $sns => $url ) {
				$ch[$sns] = curl_init();

				curl_setopt( $ch[$sns], CURLOPT_URL, $url );
				curl_setopt( $ch[$sns], CURLOPT_USERAGENT, 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ) );
				curl_setopt( $ch[$sns], CURLOPT_FOLLOWLOCATION, true );
				curl_setopt( $ch[$sns], CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $ch[$sns], CURLOPT_ENCODING, 'gzip' );

				if ( ! empty( $headers[$sns] ) ) {
					//curl_setopt( $ch[$sns], CURLOPT_HEADER, true );

					$http_headers = array();

					foreach ( $headers[$sns] as $key => $value ) {
						$http_headers[] = $key . ': ' . $value;
					}

					curl_setopt( $ch[$sns], CURLOPT_HTTPHEADER, $http_headers );
				}

				if ( $sslverify ) {
					curl_setopt( $ch[$sns], CURLOPT_SSL_VERIFYPEER, true );
					curl_setopt( $ch[$sns], CURLOPT_SSL_VERIFYHOST, 2 );
				} else {
					curl_setopt( $ch[$sns], CURLOPT_SSL_VERIFYPEER, false );
					curl_setopt( $ch[$sns], CURLOPT_SSL_VERIFYHOST, 0 );
				}

				if ( $timeout > 0 ) {
					curl_setopt( $ch[$sns], CURLOPT_CONNECTTIMEOUT, $timeout );
					curl_setopt( $ch[$sns], CURLOPT_TIMEOUT, $timeout );
				}

				curl_multi_add_handle( $mh, $ch[$sns] );
			}

			$active = null;

			do {
				curl_multi_exec( $mh, $active );
				curl_multi_select( $mh );
			} while ( $active > 0 );

			foreach ( $urls as $sns => $url ) {
				$responses[$sns]['error'] = curl_error( $ch[$sns] );

				if ( ! empty( $responses[$sns]['error'] ) ) {
					$responses[$sns]['data']  = '';
				} else {
					$responses[$sns]['data']  = curl_multi_getcontent( $ch[$sns] );
				}

				curl_multi_remove_handle( $mh, $ch[$sns] );
				curl_close( $ch[$sns] );
			}

			curl_multi_close( $mh );

			return $responses;
		} else {

			SCC_Common_Util::log( '[' . __METHOD__ . '] cURL: Off' );

			foreach ( $urls as $sns => $url ) {

				$options = array(
					'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' )
					);

				if ( $sslverify ) {
					$options['sslverify'] = true;
				} else {
					$options['sslverify'] = false;
				}

				if ( $timeout > 0 ) {
					$options['timeout'] = $timeout;
				}

				if ( ! empty( $headers[$sns] ) ) {
					$options['headers'] = $headers[$sns];
				}

				$response = wp_remote_get( $url, $options );

				if ( ! is_wp_error( $response ) ) {
					if ( $response['response']['code'] === 200 ) {
						$responses[$sns]['data'] = $response['body'];
					} else {
						$responses[$sns]['data'] = '';
						$responses[$sns]['error'] = $response['response']['code'] . ': ' . $response['response']['message'];
					}
				} else {
					$responses[$sns]['data'] = '';
					$responses[$sns]['error'] = $response->get_error_message();
				}
			}

			return $responses;
		}
	}

	public static function serialize_base64_encode( $array ) {
		$data = serialize( $array );
		$data = base64_encode( $data );

		return $data;
	}

	public static function unserialize_base64_decode( $data ) {
		$data = base64_decode( $data );
		$array = unserialize( $data );

		return $array;
	}

	/**
	 * encrypt given data
	 *
	 * @since 0.9.0
	 */
	public static function encrypt_data( $input, $key = 'KEY_DEFAULT' ) {

		if ( extension_loaded( 'mcrypt' ) ) {
			self::log( 'mcrypt loaded' );

			$key = md5( $key );

			$td  = mcrypt_module_open( 'blowfish', '', 'ecb', '');
			$key = substr( $key, 0, mcrypt_enc_get_key_size( $td ) );
			$iv  = mcrypt_create_iv( mcrypt_enc_get_iv_size( $td ), MCRYPT_RAND );

			if ( mcrypt_generic_init( $td, $key, $iv ) < 0 ) {
				exit( 'error.' );
			}

			$encrypted_data = base64_encode( mcrypt_generic( $td, $input ) );

			mcrypt_generic_deinit( $td );
			mcrypt_module_close( $td );

			return $encrypted_data;
		} else {
			self::log( 'mcrypt not loaded' );
			$crypt = new Crypt_Blowfish( $key );
			return $crypt->encrypt( $input );
		}
	}

	/**
	 * decrypt given data
	 *
	 * @since 0.9.0
	 */
	public static function decrypt_data( $input, $key = 'KEY_DEFAULT' ) {

		if ( extension_loaded( 'mcrypt' ) ) {
			self::log( 'mcrypt loaded' );

			$key = md5( $key );

			$td  = mcrypt_module_open( 'blowfish', '', 'ecb', '' );
			$key = substr( $key, 0, mcrypt_enc_get_key_size( $td ) );
			$iv  = mcrypt_create_iv( mcrypt_enc_get_iv_size( $td ), MCRYPT_RAND );

			if ( mcrypt_generic_init( $td, $key, $iv ) < 0 ) {
				exit( 'error.' );
			}

			$data = mdecrypt_generic( $td, base64_decode( $input ) );

			mcrypt_generic_deinit( $td );
			mcrypt_module_close( $td );

			return $data;
		} else {
			self::log( 'mcrypt not loaded' );
			$crypt = new Crypt_Blowfish( $key );
			return $crypt->decrypt( $input );
		}
	}

	/**
	 * Get bearer token for Twitter
	 *
	 * @since 0.9.0
	 */
	public static function get_twitter_bearer_token( $cosumer_key, $consumer_secret, $sslverify = true, $timeout = 10 ) {
		global $wp_version;

		$bearer_token = '';
		$url = 'https://api.twitter.com/oauth2/token';

		$credential = base64_encode( $cosumer_key . ':' . $consumer_secret );

		$options = array(
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' )
			);

		if ( $sslverify ) {
			$options['sslverify'] = true;
		} else {
			$options['sslverify'] = false;
		}

		if ( $timeout > 0 ) {
			$options['timeout'] = $timeout;
		}

		$headers = array(
			'Authorization' => 'Basic ' .  $credential,
			'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8'
		);

		if ( ! empty( $headers ) ) {
			$options['headers'] = $headers;
		}

		$body = array(
			'grant_type' => 'client_credentials'
		);

		if ( ! empty( $body ) ) {
			$options['body'] = $body;
		}

		$response = wp_remote_post( $url, $options );

		//self::log( $response );

		if ( ! is_wp_error( $response ) ) {
			if ( $response['response']['code'] === 200 ) {
				$json = $response['body'];
				$content = json_decode( $json, true ) ;
				if ( isset( $content['token_type'] ) && $content['token_type'] && $content['token_type'] === 'bearer' ) {
					if ( isset( $content['access_token'] ) && $content['access_token'] ) {
						$bearer_token = $content['access_token'];
					}
				}
			} else {
				return new WP_Error( 'invalid_response_code', '[ERROR] Invalid response code: ' . $response['response']['code'] . ' ' . $response['response']['message'] );
			}
		} else {
			return $response;
		}

		return $bearer_token;
	}

	/**
	 * Get acess token for Instagram
	 *
	 * @since 0.9.0
	 */
	public static function get_instagram_access_token( $client_id, $client_secret, $redirect_uri, $code, $sslverify = true, $timeout = 10 ) {
		global $wp_version;

		$access_token = '';
		$url = 'https://api.instagram.com/oauth/access_token';

		$options = array(
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' )
			);

		if ( $sslverify ) {
			$options['sslverify'] = true;
		} else {
			$options['sslverify'] = false;
		}

		if ( $timeout > 0 ) {
			$options['timeout'] = $timeout;
		}

		$body = array(
			'client_id' => $client_id,
			'client_secret' => $client_secret,
			'grant_type' => 'authorization_code',
			'redirect_uri' => $redirect_uri,
			'code' => $code
		);

		if ( ! empty( $body ) ) {
			$options['body'] = $body;
		}

		$response = wp_remote_post( $url, $options );

		//self::log( $response );

		if ( ! is_wp_error( $response ) ) {
			if ( $response['response']['code'] === 200 ) {
				$json = $response['body'];
				$content = json_decode( $json, true ) ;
				if ( isset( $content['access_token'] ) && $content['access_token'] ) {
					$access_token = $content['access_token'];
				}
			} else {
				return new WP_Error( 'invalid_response_code', '[ERROR] Invalid response code: ' . $response['response']['code'] . ' ' . $response['response']['message'] );
			}
		} else {
			return $response;
		}

		return $access_token;
	}

	/**
	 * Get acess token for Facebook
	 *
	 * @since 0.9.0
	 */
	public static function get_facebook_access_token( $app_id, $app_secret, $redirect_uri, $code, $page_id, $sslverify = true, $timeout = 10 ) {
		global $wp_version;

		$original_access_token = '';
		$extended_access_token = '';
		$access_token = '';

		$url = 'https://graph.facebook.com/oauth/access_token';

		$options = array(
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' )
			);

		if ( $sslverify ) {
			$options['sslverify'] = true;
		} else {
			$options['sslverify'] = false;
		}

		if ( $timeout > 0 ) {
			$options['timeout'] = $timeout;
		}

		$query_parameters = array(
			'client_id' => $app_id,
			'client_secret' => $app_secret,
			'redirect_uri' => $redirect_uri,
			'code' => $code
			);

		$request_url = $url . '?' . http_build_query( $query_parameters , '' , '&' );

		$response = wp_remote_get( $request_url, $options );

		//self::log( $response );

		if ( ! is_wp_error( $response ) ) {
			if ( $response['response']['code'] === 200 ) {
				$json = $response['body'];

				parse_str( $response['body'], $data );
				if ( isset( $data['access_token'] ) && $data['access_token'] ) {
					$original_access_token = $data['access_token'];
				}
			} else {
				return new WP_Error( 'invalid_response_code', '[ERROR] Invalid response code: ' . $response['response']['code'] . ' ' . $response['response']['message'] );
			}
		} else {
			return $response;
		}

		if ( isset( $original_access_token ) && $original_access_token ) {
			$url = 'https://graph.facebook.com/oauth/access_token';

			$query_parameters = array(
				'client_id' => $app_id,
				'client_secret' => $app_secret,
				'grant_type' => 'fb_exchange_token',
				'fb_exchange_token' => $original_access_token
				);

			$request_url = $url . '?' . http_build_query( $query_parameters , '' , '&' );

			$response = wp_remote_get( $request_url, $options );

			//self::log( $response );

			$data = array();

			if ( ! is_wp_error( $response ) ) {
				if ( $response['response']['code'] === 200 ) {
					parse_str( $response['body'], $data );
					if ( isset( $data['access_token'] ) && $data['access_token'] ) {
						$extended_access_token = $data['access_token'];
					}
				} else {
					return new WP_Error( 'invalid_response_code', '[ERROR] Invalid response code: ' . $response['response']['code'] . ' ' . $response['response']['message'] );
				}
			} else {
				return $response;
			}
		}

		if ( isset( $extended_access_token ) && $extended_access_token ) {
			$url = 'https://graph.facebook.com/me/accounts';

			$query_parameters = array(
				'access_token' => $extended_access_token
				);

			$request_url = $url . '?' . http_build_query( $query_parameters , '' , '&' );

			$response = wp_remote_get( $request_url, $options );

			//self::log( $response );

			if ( ! is_wp_error( $response ) ) {
				if ( $response['response']['code'] === 200 ) {
					$json = $response['body'];
					$content = json_decode( $json, true ) ;

					$i = 0;

					while( true ) {
						if ( ! isset( $content['data'][$i] ) ) {
							break;
						}

						if ( isset( $content['data'][$i]['id'] ) && $content['data'][$i]['id'] === $page_id ) {
							if ( isset( $content['data'][$i]['access_token'] ) && $content['data'][$i]['access_token'] ) {
								$access_token = $content['data'][$i]['access_token'];
							}
							break;
						}

						++$i;
					}
				} else {
					return new WP_Error( 'invalid_response_code', '[ERROR] Invalid response code: ' . $response['response']['code'] . ' ' . $response['response']['message'] );
				}
			} else {
				return $response;
			}
		}

		return $access_token;
	}

	public static function sanitize( $str ) {
		$filtered = wp_check_invalid_utf8( $str );

		if ( strpos( $filtered, '<' ) !== false ) {
			$filtered = wp_pre_kses_less_than( $filtered );
			// This will strip extra whitespace for us.
			$filtered = wp_strip_all_tags( $filtered, true );
		} else {
			$filtered = trim( preg_replace( '/[\r\n\t ]+/', ' ', $filtered ) );
		}

		$filtered = trim( preg_replace('/ +/', ' ', $filtered) );

		return $filtered;
	}

	public static function sanitize_cache_key( $cache_key ) {
		$cache_key = strtolower( $cache_key );
		$cache_key = preg_replace( '/[^a-z0-9_\-\+]/', '', $cache_key );
		return $cache_key;
	}

}

?>
