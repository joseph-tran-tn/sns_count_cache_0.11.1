<?php
/*
download.php

Description: Download page implementation
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

require_once( '../../../../wp-load.php' );

if ( ! defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

$abs_path = WP_PLUGIN_DIR . '/sns-count-cache/data/sns-count-cache-data.csv';

if ( current_user_can( 'manage_options' ) ) {
	if ( isset( $_POST['_wpnonce'] ) && $_POST['_wpnonce'] && check_admin_referer( 'download', '_wpnonce' ) ) {
		if ( isset( $_POST["download_data"] ) && $_POST["download_data"] === __( 'Download', SNS_Count_Cache::DOMAIN ) ) {
			if ( file_exists( $abs_path ) ) {
				$file_name = "sns-count-cache_data_" . date_i18n( 'YmdHis' ) . ".csv";

				$size = filesize( $abs_path );
				header( 'Content-Length: ' . $size );
				header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );
				header( 'Content-Transfer-Encoding: binary' );
				header( 'Content-Type: application/octet-stream' );

				while ( ob_get_level() > 0 ) {
					ob_end_clean();
				}

				ob_start();
				if ( $fp = fopen( $abs_path, 'rb' ) ) {
					while( ! feof( $fp ) && ( connection_status() == 0 ) ) {
						echo fread( $fp, 8192 );
						ob_flush();
					}

					ob_flush();
					fclose( $fp );
				}
				ob_end_clean();
			} else {
				echo 'There is no exported file.';
			}
		}
	} else {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
} else {
	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
}
die();

?>
