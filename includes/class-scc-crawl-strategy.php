<?php
/*
class-scc-crawl-strategy.php

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

abstract class SCC_Crawl_Strategy {

	/**
	 * TODO Auto-generated comment.
	 */
	protected $url;

	/**
	 * TODO Auto-generated comment.
	 */
	protected $method;

	/**
	 * TODO Auto-generated comment.
	 */
	protected $query_parameters = array();

	/**
	 * TODO Auto-generated comment.
	 */
	protected $parameters = array();

	/**
	 * Instance
	 */
	private static $instance = array();

	/**
	 * Class constarctor
	 * Hook onto all of the actions and filters needed by the plugin.
	 *
	 */
	protected function __construct() {
		SCC_Common_Util::log('[' . __METHOD__ . '] (line='. __LINE__ . ')');
		//$this->get_object_id();
	}

	/**
	 * Get instance
	 *
	 * @since 0.1.1
	 */
	public static function get_instance() {
		$class_name = get_called_class();

		if ( ! isset( self::$instance[$class_name] ) ) {
			self::$instance[$class_name] = new $class_name();
		}

		return self::$instance[$class_name];
	}

	/**
	 * Return object ID
	 *
	 * @since 0.6.0
	 */
	public function get_object_id() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$object_id = spl_object_hash( $this );

		SCC_Common_Util::log( '[' . __METHOD__ . '] object ID: ' . $object_id );

		return $object_id;
	}

	/**
	 * Inhibit clone
	 *
	 * @since 0.6.0
	 */
	 final public function __clone() {
		 throw new Exception('Clone is not allowed against' . get_class( $this ) );
	 }

	/**
	 * Initialization
	 *
	 * @since 0.6.0
	 */
	abstract public function initialize( $options = array() );

	/**
	 * TODO Auto-generated comment.
	 */
	public function get_url() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		return $url;
	}

	/**
	 * TODO Auto-generated comment.
	 */
	public function set_url( $url) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		$this->url = $url;
	}

	/**
	 * TODO Auto-generated comment.
	 */
	public function get_parameters() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		return $this->parameters;
	}

	public function get_parameter( $key ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		return $this->parameters[$key];
	}

	/**
	 * TODO Auto-generated comment.
	 */
	public function set_parameters( $parameters = array() ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		//$this->parameters = array_merge( $this->parameters, $parameters );
		foreach ( $parameters as $key => $value ) {
			$this->set_parameter( $key, $value );
		}
	}

	/**
	 * TODO Auto-generated comment.
	 */
	public function set_parameter( $key, $value ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		$this->parameters[$key] = $value;
	}

	/**
	 * TODO Auto-generated comment.
	 */
	public function get_query_parameters() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		return $this->query_parameters;
	}

	public function get_query_parameter( $key ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		return $this->query_parameters[$key];
	}

	/**
	 * TODO Auto-generated comment.
	 */
	public function set_query_parameters( $query_parameters = array() ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		//$this->query_parameters = array_merge( $this->query_parameters, $query_parameters );
		foreach( $query_parameters as $key => $value ) {
			$this->set_query_parameter( $key, $value );
		}
	}

	/**
	 * TODO Auto-generated comment.
	 */
	public function set_query_parameter( $key, $value ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		$this->query_parameters[$key] = $value;
	}

	/**
	 * TODO Auto-generated comment.
	 */
	public function get_method() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		return $method;
	}

	/**
	 * TODO Auto-generated comment.
	 */
	public function set_method( $method) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		$this->method = $method;
	}

	/**
	 * TODO Auto-generated comment.
	 */
	abstract public function build_query_url();

	/**
	 * TODO Auto-generated comment.
	 */
	abstract public function extract_count( $content );

	/**
	 * TODO Auto-generated comment.
	 */
	abstract public function build_header();

	/**
	 * TODO Auto-generated comment.
	 */
	abstract public function check_configuration();

}
