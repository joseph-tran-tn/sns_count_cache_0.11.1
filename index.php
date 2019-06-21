<?php
/*
index.php

Description:
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

require_once( '../../../wp-load.php' );

if ( ! defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

if ( current_user_can( 'manage_options' ) ) {
	if ( isset( $_GET['state'] ) && $_GET['state'] && isset( $_GET['action'] ) && $_GET['action'] && isset( $_GET['code'] ) && $_GET['code'] ) {
		if ( $_GET['action'] === 'instagram-auth' ) {
			if ( check_admin_referer( 'instagram-auth', 'state' ) ) {
				$redirect_url = admin_url( 'admin.php?page=scc-setting' ) . '&action=instagram-auth&code=' . $_GET['code'] .'&_wpnonce=' . $_GET['state'];
				wp_safe_redirect( $redirect_url );
				exit;
			} else {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}
		} elseif ( $_GET['action'] === 'facebook-auth' ) {
			if ( check_admin_referer( 'facebook-auth', 'state' ) ) {
				$redirect_url = admin_url( 'admin.php?page=scc-setting' ) . '&action=facebook-auth&code=' . $_GET['code'] .'&_wpnonce=' . $_GET['state'];
				wp_safe_redirect( $redirect_url );
				exit;
			} else {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}
		} else {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
	} else {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
} else {
	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
}

?>
