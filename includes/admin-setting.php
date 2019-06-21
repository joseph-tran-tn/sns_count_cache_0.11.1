<?php
/*
admin-setting.php

Description: Option page implementation
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

	if ( ! defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

	$tmp_instagram_access_token = '';
	$tmp_facebook_access_token = '';
	$tmp_twitter_bearer_token = '';

	if ( isset( $_POST['_wpnonce'] ) && $_POST['_wpnonce'] && check_admin_referer( __FILE__, '_wpnonce' ) ) {
		if ( isset( $_POST["update_all_options"] ) && $_POST["update_all_options"] === __( 'Update All Options', self::DOMAIN ) ) {

			$wp_error = new WP_Error();

			//$settings = array();
			$settings = get_option( self::DB_SETTINGS );

			$share_base_cache_target = array();
			$follow_base_cache_target = array();

			if ( isset( $_POST["share_base_custom_post_types"] ) && $_POST["share_base_custom_post_types"] ) {
				$settings[self::DB_SHARE_CUSTOM_POST_TYPES] = explode( ',', sanitize_text_field( $_POST["share_base_custom_post_types"] ) );
			} else {
				$settings[self::DB_SHARE_CUSTOM_POST_TYPES] = array();
			}

			if ( isset( $_POST["share_base_check_interval"] ) && $_POST["share_base_check_interval"] ) {
				$tmp_share_base_check_interval = sanitize_text_field( $_POST["share_base_check_interval"] );
				if ( is_numeric( $tmp_share_base_check_interval ) ) {
					$settings[self::DB_SHARE_BASE_CHECK_INTERVAL] = $tmp_share_base_check_interval;
				}
			}

			if ( isset( $_POST["share_base_posts_per_check"] ) && $_POST["share_base_posts_per_check"] ) {
				$tmp_share_base_posts_per_check = sanitize_text_field( $_POST["share_base_posts_per_check"] );
				if ( is_numeric( $tmp_share_base_posts_per_check ) ) {
					$settings[self::DB_SHARE_BASE_POSTS_PER_CHECK] = $tmp_share_base_posts_per_check;
				}
			}

			if ( isset( $_POST["dynamic_cache_mode"] ) && $_POST["dynamic_cache_mode"] ) {
				$settings[self::DB_COMMON_DYNAMIC_CACHE_MODE] = sanitize_text_field( $_POST["dynamic_cache_mode"] );
			}

			if ( isset( $_POST["fault_tolerance_mode"] ) && $_POST["fault_tolerance_mode"] ) {
				$settings[self::DB_COMMON_FAULT_TOLERANCE_MODE] = sanitize_text_field( $_POST["fault_tolerance_mode"] );
			}

			if ( isset( $_POST["share_variation_analysis_mode"] ) && $_POST["share_variation_analysis_mode"] ) {
				$settings[self::DB_SHARE_VARIATION_ANALYSIS_MODE] = sanitize_text_field( $_POST["share_variation_analysis_mode"] );
			}

			if ( isset( $_POST["follow_variation_analysis_mode"] ) && $_POST["follow_variation_analysis_mode"] ) {
				$settings[self::DB_FOLLOW_VARIATION_ANALYSIS_MODE] = sanitize_text_field( $_POST["follow_variation_analysis_mode"] );
			}

			if ( isset( $_POST["share_rush_new_content_term"] ) && $_POST["share_rush_new_content_term"] ) {
				$tmp_share_rush_new_content_term = sanitize_text_field( $_POST["share_rush_new_content_term"] );
				if ( is_numeric( $tmp_share_rush_new_content_term ) ) {
					$settings[self::DB_SHARE_RUSH_NEW_CONTENT_TERM] = $tmp_share_rush_new_content_term;
				}
			}

			if ( isset( $_POST["share_rush_check_interval"] ) && $_POST["share_rush_check_interval"] ) {
				$tmp_share_rush_check_interval = sanitize_text_field( $_POST["share_rush_check_interval"] );
				if ( is_numeric( $tmp_share_rush_check_interval ) ) {
					$settings[self::DB_SHARE_RUSH_CHECK_INTERVAL] = $tmp_share_rush_check_interval;
				}
			}

			if ( isset( $_POST["share_rush_posts_per_check"] ) && $_POST["share_rush_posts_per_check"] ) {
				$tmp_share_rush_posts_per_check = sanitize_text_field( $_POST["share_rush_posts_per_check"] );
				if ( is_numeric( $tmp_share_rush_posts_per_check ) ) {
					$settings[self::DB_SHARE_RUSH_POSTS_PER_CHECK] = $tmp_share_rush_posts_per_check;
				}
			}

			if ( isset( $_POST["data_export_mode"] ) && $_POST["data_export_mode"] ) {
				$settings[self::DB_COMMON_DATA_EXPORT_MODE] = sanitize_text_field( $_POST["data_export_mode"] );
			}

			if ( isset( $_POST["data_export_interval"] ) && $_POST["data_export_interval"] ) {
				$tmp_data_export_interval  = sanitize_text_field( $_POST["data_export_interval"] );
				if ( is_numeric( $tmp_data_export_interval ) ) {
					$settings[self::DB_COMMON_DATA_EXPORT_INTERVAL] = $tmp_data_export_interval;
				}
			}

			if ( isset( $_POST["common_data_crawler_retry_limit"] ) && $_POST["common_data_crawler_retry_limit"] ) {
				$tmp_common_data_crawler_retry_limit  = sanitize_text_field( $_POST["common_data_crawler_retry_limit"] );
				if ( is_numeric( $tmp_common_data_crawler_retry_limit ) ) {
					$settings[self::DB_COMMON_CRAWLER_RETRY_LIMIT] = $tmp_common_data_crawler_retry_limit;
				}
			}

			if ( isset( $_POST["share_base_cache_target_twitter"] ) && $_POST["share_base_cache_target_twitter"] ) {
				$share_base_cache_target[self::REF_SHARE_TWITTER] = true;
			} else {
				$share_base_cache_target[self::REF_SHARE_TWITTER] = false;
			}

			if ( isset( $_POST["share_alternative_twitter_api"] ) && $_POST["share_alternative_twitter_api"] ) {
				$settings[self::DB_SHARE_BASE_TWITTER_API] = sanitize_text_field( $_POST["share_alternative_twitter_api"] );
			}

			if ( isset( $_POST["share_base_cache_target_facebook"] ) && $_POST["share_base_cache_target_facebook"] ) {
				$share_base_cache_target[self::REF_SHARE_FACEBOOK] = true;
			} else {
				$share_base_cache_target[self::REF_SHARE_FACEBOOK] = false;
			}

			if ( isset( $_POST["share_base_cache_target_gplus"] ) && $_POST["share_base_cache_target_gplus"] ) {
				$share_base_cache_target[self::REF_SHARE_GPLUS] = true;
			} else {
				$share_base_cache_target[self::REF_SHARE_GPLUS] = false;
			}

			if ( isset( $_POST["share_base_cache_target_pocket"] ) && $_POST["share_base_cache_target_pocket"] ) {
				$share_base_cache_target[self::REF_SHARE_POCKET] = true;
			} else {
				$share_base_cache_target[self::REF_SHARE_POCKET] = false;
			}

			if ( isset( $_POST["share_base_cache_target_hatebu"] ) && $_POST["share_base_cache_target_hatebu"] ) {
				$share_base_cache_target[self::REF_SHARE_HATEBU] = true;
			} else {
				$share_base_cache_target[self::REF_SHARE_HATEBU] = false;
			}

			if ( isset( $_POST["share_base_cache_target_pinterest"] ) && $_POST["share_base_cache_target_pinterest"] ) {
				$share_base_cache_target[self::REF_SHARE_PINTEREST] = true;
			} else {
				$share_base_cache_target[self::REF_SHARE_PINTEREST] = false;
			}

			if ( isset( $_POST["share_base_cache_target_linkedin"] ) && $_POST["share_base_cache_target_linkedin"] ) {
				$share_base_cache_target[self::REF_SHARE_LINKEDIN] = true;
			} else {
				$share_base_cache_target[self::REF_SHARE_LINKEDIN] = false;
			}

			if ( ! empty( $share_base_cache_target ) ) {
				$settings[self::DB_SHARE_CACHE_TARGET] = $share_base_cache_target;
			}

			if ( isset( $_POST["follow_base_check_interval"] ) && $_POST["follow_base_check_interval"] ) {
				$tmp_follow_base_check_interval = sanitize_text_field( $_POST["follow_base_check_interval"] );
				if ( is_numeric( $tmp_follow_base_check_interval ) ) {
					if ( $tmp_follow_base_check_interval >= self::OPT_FOLLOW_BASE_CHECK_INTERVAL_MIN ) {
						$settings[self::DB_FOLLOW_CHECK_INTERVAL] = $tmp_follow_base_check_interval;
					} else {
						$settings[self::DB_FOLLOW_CHECK_INTERVAL] = self::OPT_FOLLOW_BASE_CHECK_INTERVAL_MIN;
					}
				}
			}

			if ( isset( $_POST["follow_base_cache_target_feedly"] ) && $_POST["follow_base_cache_target_feedly"] ) {
				$follow_base_cache_target[self::REF_FOLLOW_FEEDLY] = true;
			} else {
				$follow_base_cache_target[self::REF_FOLLOW_FEEDLY] = false;
			}

			if ( isset( $_POST["follow_base_cache_target_twitter"] ) && $_POST["follow_base_cache_target_twitter"] ) {
				$follow_base_cache_target[self::REF_FOLLOW_TWITTER] = true;
			} else {
				$follow_base_cache_target[self::REF_FOLLOW_TWITTER] = false;
			}

			if ( isset( $_POST["follow_base_cache_target_facebook"] ) && $_POST["follow_base_cache_target_facebook"] ) {
				$follow_base_cache_target[self::REF_FOLLOW_FACEBOOK] = true;
			} else {
				$follow_base_cache_target[self::REF_FOLLOW_FACEBOOK] = false;
			}

			if ( isset( $_POST["follow_base_cache_target_push7"] ) && $_POST["follow_base_cache_target_push7"] ) {
				$follow_base_cache_target[self::REF_FOLLOW_PUSH7] = true;
			} else {
				$follow_base_cache_target[self::REF_FOLLOW_PUSH7] = false;
			}

			if ( isset( $_POST["follow_base_cache_target_instagram"] ) && $_POST["follow_base_cache_target_instagram"] ) {
				$follow_base_cache_target[self::REF_FOLLOW_INSTAGRAM] = true;
			} else {
				$follow_base_cache_target[self::REF_FOLLOW_INSTAGRAM] = false;
			}

			if ( ! empty( $follow_base_cache_target ) ) {
				$settings[self::DB_FOLLOW_CACHE_TARGET] = $follow_base_cache_target;
			}

			$follow_twitter_preparation_flag = true;

			if ( isset( $_POST["share_facebook_app_id"] ) && $_POST["share_facebook_app_id"] ) {
				$settings[self::DB_SHARE_FACEBOOK_APP_ID] = SCC_Common_Util::encrypt_data( SCC_Common_Util::sanitize( $_POST["share_facebook_app_id"] ), AUTH_KEY );
			}

			if ( isset( $_POST["share_facebook_app_secret"] ) && $_POST["share_facebook_app_secret"] ) {
				$settings[self::DB_SHARE_FACEBOOK_APP_SECRET] = SCC_Common_Util::encrypt_data( SCC_Common_Util::sanitize( $_POST["share_facebook_app_secret"] ), AUTH_KEY );
			}

			if ( isset( $_POST["follow_twitter_screen_name"] ) && $_POST["follow_twitter_screen_name"] ) {
				$settings[self::DB_FOLLOW_TWITTER_SCREEN_NAME] = sanitize_text_field( $_POST["follow_twitter_screen_name"] );
			}

			if ( isset( $_POST["follow_twitter_consumer_key"] ) && $_POST["follow_twitter_consumer_key"] ) {
				$settings[self::DB_FOLLOW_TWITTER_CONSUMER_KEY] = SCC_Common_Util::encrypt_data( SCC_Common_Util::sanitize( $_POST["follow_twitter_consumer_key"] ), AUTH_KEY );
			} else {
				$follow_twitter_preparation_flag = false;
			}

			if ( isset( $_POST["follow_twitter_consumer_secret"] ) && $_POST["follow_twitter_consumer_secret"] ) {
				$settings[self::DB_FOLLOW_TWITTER_CONSUMER_SECRET] = SCC_Common_Util::encrypt_data( SCC_Common_Util::sanitize( $_POST["follow_twitter_consumer_secret"] ), AUTH_KEY );
			} else {
				$follow_twitter_preparation_flag = false;
			}

			if ( isset( $_POST["follow_twitter_bearer_token"] ) && $_POST["follow_twitter_bearer_token"] ) {
				$settings[self::DB_FOLLOW_TWITTER_BEARER_TOKEN] = SCC_Common_Util::encrypt_data( SCC_Common_Util::sanitize( $_POST["follow_twitter_bearer_token"] ), AUTH_KEY );
			}

			/*
			if ( isset( $_POST["follow_twitter_access_token"] ) && $_POST["follow_twitter_access_token"] ) {
				$settings[self::DB_FOLLOW_TWITTER_ACCESS_TOKEN] = SCC_Common_Util::encrypt_data( $_POST["follow_twitter_access_token"], AUTH_KEY );
			}

			if ( isset( $_POST["follow_twitter_access_token_secret"] ) && $_POST["follow_twitter_access_token_secret"] ) {
				$settings[self::DB_FOLLOW_TWITTER_ACCESS_TOKEN_SECRET] = SCC_Common_Util::encrypt_data( $_POST["follow_twitter_access_token_secret"], AUTH_KEY );
			}
			*/

			if ( isset( $_POST["follow_facebook_page_id"] ) && $_POST["follow_facebook_page_id"] ) {
				$settings[self::DB_FOLLOW_FACEBOOK_PAGE_ID] = sanitize_text_field( $_POST["follow_facebook_page_id"] );
			}

			if ( isset( $_POST["follow_facebook_app_id"] ) && $_POST["follow_facebook_app_id"] ) {
				$settings[self::DB_FOLLOW_FACEBOOK_APP_ID] = SCC_Common_Util::encrypt_data( SCC_Common_Util::sanitize( $_POST["follow_facebook_app_id"] ), AUTH_KEY );
			}

			if ( isset( $_POST["follow_facebook_app_secret"] ) && $_POST["follow_facebook_app_secret"] ) {
				$settings[self::DB_FOLLOW_FACEBOOK_APP_SECRET] = SCC_Common_Util::encrypt_data( SCC_Common_Util::sanitize( $_POST["follow_facebook_app_secret"] ), AUTH_KEY );
			}

			if ( isset( $_POST["follow_facebook_access_token"] ) && $_POST["follow_facebook_access_token"] ) {
				$settings[self::DB_FOLLOW_FACEBOOK_ACCESS_TOKEN] = SCC_Common_Util::encrypt_data( SCC_Common_Util::sanitize( $_POST["follow_facebook_access_token"] ), AUTH_KEY );
			}

			if ( isset( $_POST["follow_push7_appno"] ) && $_POST["follow_push7_appno"] ) {
				$settings[self::DB_FOLLOW_PUSH7_APPNO] = SCC_Common_Util::encrypt_data( sanitize_text_field( $_POST["follow_push7_appno"] ), AUTH_KEY );
			}

			if ( isset( $_POST["follow_instagram_access_token"] ) && $_POST["follow_instagram_access_token"] ) {
				$settings[self::DB_FOLLOW_INSTAGRAM_ACCESS_TOKEN] = SCC_Common_Util::encrypt_data( SCC_Common_Util::sanitize( $_POST["follow_instagram_access_token"] ), AUTH_KEY );
			}

			if ( isset( $_POST["follow_instagram_client_id"] ) && $_POST["follow_instagram_client_id"] ) {
				$settings[self::DB_FOLLOW_INSTAGRAM_CLIENT_ID] = SCC_Common_Util::encrypt_data( SCC_Common_Util::sanitize( $_POST["follow_instagram_client_id"] ), AUTH_KEY );
			}

			if ( isset( $_POST["follow_instagram_client_secret"] ) && $_POST["follow_instagram_client_secret"] ) {
				$settings[self::DB_FOLLOW_INSTAGRAM_CLIENT_SECRET] = SCC_Common_Util::encrypt_data( SCC_Common_Util::sanitize( $_POST["follow_instagram_client_secret"] ), AUTH_KEY );
			}

			if ( isset( $_POST["follow_feed_type"] ) && $_POST["follow_feed_type"] ) {
				switch ( $_POST["follow_feed_type"] ) {
					case 'default':
						$settings[self::DB_FOLLOW_FEED_TYPE] = self::OPT_FEED_TYPE_DEFAULT;
						break;
					case 'rss2':
						$settings[self::DB_FOLLOW_FEED_TYPE] = self::OPT_FEED_TYPE_RSS2;
						break;
					case 'rss':
						$settings[self::DB_FOLLOW_FEED_TYPE] = self::OPT_FEED_TYPE_RSS;
						break;
					case 'rdf':
						$settings[self::DB_FOLLOW_FEED_TYPE] = self::OPT_FEED_TYPE_RDF;
						break;
					case 'atom':
						$settings[self::DB_FOLLOW_FEED_TYPE] = self::OPT_FEED_TYPE_ATOM;
						break;
					default:
						$settings[self::DB_FOLLOW_FEED_TYPE] = self::OPT_FEED_TYPE_DEFAULT;
				}
			} else {
				$settings[self::DB_FOLLOW_FEED_TYPE] = self::OPT_FEED_TYPE_DEFAULT;
			}

			if ( isset( $_POST["scheme_migration_mode"] ) && $_POST["scheme_migration_mode"] ) {
				$settings[self::DB_COMMON_SCHEME_MIGRATION_MODE] = self::OPT_COMMON_SCHEME_MIGRATION_MODE_ON;
			} else {
				$settings[self::DB_COMMON_SCHEME_MIGRATION_MODE] = self::OPT_COMMON_SCHEME_MIGRATION_MODE_OFF;
			}

			if ( isset( $_POST["scheme_migration_date"] ) && $_POST["scheme_migration_date"] ) {
				$tmp_scheme_migration_date = sanitize_text_field( $_POST["scheme_migration_date"] );
				if ( strptime( $tmp_scheme_migration_date, '%Y/%m/%d' ) ) {
					$settings[self::DB_COMMON_SCHEME_MIGRATION_DATE] = $tmp_scheme_migration_date;
				}
			}

			if ( isset( $_POST["crawler_ssl_verification"] ) && $_POST["crawler_ssl_verification"] ) {
				$settings[self::DB_COMMON_CRAWLER_SSL_VERIFICATION] = self::OPT_COMMON_CRAWLER_SSL_VERIFY_ON;
			} else {
				$settings[self::DB_COMMON_CRAWLER_SSL_VERIFICATION] = self::OPT_COMMON_CRAWLER_SSL_VERIFY_OFF;
			}

			if ( isset( $_POST['a_cronbtype'] ) && $_POST['a_cronbtype'] === 'mon' ) {
				$settings[self::DB_SHARE_VARIATION_ANALYSIS_SCHEDULE] = $_POST['a_moncronminutes'] . ' ' . $_POST['a_moncronhours'] . ' ' . $_POST['a_moncronmday'] . ' * *';
			}

			if ( isset( $_POST['a_cronbtype'] ) && $_POST['a_cronbtype'] === 'week' ) {
				$settings[self::DB_SHARE_VARIATION_ANALYSIS_SCHEDULE] = $_POST['a_weekcronminutes'] . ' ' . $_POST['a_weekcronhours'] . ' * * ' . $_POST['a_weekcronwday'];
			}

			if ( isset( $_POST['a_cronbtype'] ) && $_POST['a_cronbtype'] === 'day' ) {
				$settings[self::DB_SHARE_VARIATION_ANALYSIS_SCHEDULE] = $_POST['a_daycronminutes'] . ' ' . $_POST['a_daycronhours'] . ' * * *';
			}

			if ( isset( $_POST['a_cronbtype'] ) && $_POST['a_cronbtype'] === 'hour' ) {
				$settings[self::DB_SHARE_VARIATION_ANALYSIS_SCHEDULE] = $_POST['a_hourcronminutes'] . ' * * * *';
			}

			if ( isset( $_POST['b_cronbtype'] ) && $_POST['b_cronbtype'] === 'mon' ) {
				$settings[self::DB_FOLLOW_VARIATION_ANALYSIS_SCHEDULE] = $_POST['b_moncronminutes'] . ' ' . $_POST['b_moncronhours'] . ' ' . $_POST['b_moncronmday'] . ' * *';
			}

			if ( isset( $_POST['b_cronbtype'] ) && $_POST['b_cronbtype'] === 'week' ) {
				$settings[self::DB_FOLLOW_VARIATION_ANALYSIS_SCHEDULE] = $_POST['b_weekcronminutes'] . ' ' . $_POST['b_weekcronhours'] . ' * * ' . $_POST['b_weekcronwday'];
			}

			if ( isset( $_POST['b_cronbtype'] ) && $_POST['b_cronbtype'] === 'day' ) {
				$settings[self::DB_FOLLOW_VARIATION_ANALYSIS_SCHEDULE] = $_POST['b_daycronminutes'] . ' ' . $_POST['b_daycronhours'] . ' * * *';
			}

			if ( isset( $_POST['b_cronbtype'] ) && $_POST['b_cronbtype'] === 'hour' ) {
				$settings[self::DB_FOLLOW_VARIATION_ANALYSIS_SCHEDULE] = $_POST['b_hourcronminutes'] . ' * * * *';
			}

			if ( isset( $_POST['e_cronbtype'] ) && $_POST['e_cronbtype'] === 'mon' ) {
				$settings[self::DB_COMMON_DATA_EXPORT_SCHEDULE] = $_POST['e_moncronminutes'] . ' ' . $_POST['e_moncronhours'] . ' ' . $_POST['e_moncronmday'] . ' * *';
			}

			if ( isset( $_POST['e_cronbtype'] ) && $_POST['e_cronbtype'] === 'week' ) {
				$settings[self::DB_COMMON_DATA_EXPORT_SCHEDULE] = $_POST['e_weekcronminutes'] . ' ' . $_POST['e_weekcronhours'] . ' * * ' . $_POST['e_weekcronwday'];
			}

			if ( isset( $_POST['e_cronbtype'] ) && $_POST['e_cronbtype'] === 'day' ) {
				$settings[self::DB_COMMON_DATA_EXPORT_SCHEDULE] = $_POST['e_daycronminutes'] . ' ' . $_POST['e_daycronhours'] . ' * * *';
			}

			if ( isset( $_POST['e_cronbtype'] ) && $_POST['e_cronbtype'] === 'hour' ) {
				$settings[self::DB_COMMON_DATA_EXPORT_SCHEDULE] = $_POST['e_hourcronminutes'] . ' * * * *';
			}

			update_option( self::DB_SETTINGS, $settings );

			$this->reactivate_plugin();

			set_transient( self::OPT_COMMON_ERROR_MESSAGE, $wp_error->get_error_messages(), 10 );

			//wp_safe_redirect( menu_page_url( 'scc-setting', false ) );
		}

		if ( isset( $_POST["reset_data"] ) && $_POST["reset_data"] === __( 'Reset', self::DOMAIN ) ) {
			SCC_Common_Util::log( '[' . __METHOD__ . '] reset' );

			$this->export_engines[self::REF_COMMON_EXPORT]->reset_export();

			//wp_safe_redirect( menu_page_url( 'scc-setting', false ) );
		}

		if ( isset( $_POST["export_data"] ) && $_POST["export_data"] === __( 'Export', self::DOMAIN ) ) {
			SCC_Common_Util::log( '[' . __METHOD__ . '] export' );

			set_time_limit( $this->extended_max_execution_time );

			$this->export_engines[self::REF_COMMON_EXPORT]->execute_export( NULL );

			set_time_limit( $this->original_max_execution_time );

			//wp_safe_redirect( menu_page_url('scc-setting', false ) );
		}

		if ( isset( $_POST["update_share_comparison_base"] ) && $_POST["update_share_comparison_base"] === __( 'Update Basis of Comparison', self::DOMAIN ) ) {
			SCC_Common_Util::log( '[' . __METHOD__ . '] base' );

			set_time_limit( $this->extended_max_execution_time );

			$this->analytical_engines[self::REF_SHARE_ANALYSIS]->execute_base( NULL );

			set_time_limit( $this->original_max_execution_time );

			//wp_safe_redirect( menu_page_url( 'scc-setting', false ) );
		}

		if ( isset( $_POST["update_follow_comparison_base"] ) && $_POST["update_follow_comparison_base"] === __( 'Update Basis of Comparison', self::DOMAIN ) ) {
			SCC_Common_Util::log( '[' . __METHOD__ . '] base' );

			set_time_limit( $this->extended_max_execution_time );

			$this->analytical_engines[self::REF_FOLLOW_ANALYSIS]->execute_base( NULL );

			set_time_limit( $this->original_max_execution_time );

			//wp_safe_redirect( menu_page_url( 'scc-setting', false ) );
		}

		if ( isset( $_POST["clear_share_base_cache"] ) && $_POST["clear_share_base_cache"] === __( 'Clear Cache', self::DOMAIN ) ) {
			SCC_Common_Util::log( '[' . __METHOD__ . '] clear cache' );

			set_time_limit( $this->extended_max_execution_time );

			$this->cache_engines[self::REF_SHARE_BASE]->clear_cache();
			//$this->cache_engines[self::REF_SHARE_2ND]->clear_cache();
			$this->cache_engines[self::REF_SHARE_2ND]->initialize_cache();
			$this->analytical_engines[self::REF_SHARE_ANALYSIS]->clear_base();

			set_time_limit( $this->original_max_execution_time );

			//wp_safe_redirect( menu_page_url('scc-setting', false ) );
		}

		if ( isset( $_POST["clear_follow_base_cache"] ) && $_POST["clear_follow_base_cache"] === __( 'Clear Cache', self::DOMAIN ) ) {
			SCC_Common_Util::log( '[' . __METHOD__ . '] clear cache' );

			set_time_limit( $this->extended_max_execution_time );

			$this->cache_engines[self::REF_FOLLOW_BASE]->clear_cache();
			//$this->cache_engines[self::REF_FOLLOW_2ND]->clear_cache();
			$this->cache_engines[self::REF_FOLLOW_2ND]->initialize_cache();

			set_time_limit( $this->original_max_execution_time );

			//wp_safe_redirect( menu_page_url('scc-setting', false ) );
		}

		if ( isset( $_POST["direct_follow_base_cache"] ) && $_POST["direct_follow_base_cache"] === __( 'Cache', self::DOMAIN ) ) {
			SCC_Common_Util::log( '[' . __METHOD__ . '] cache' );

			set_time_limit( $this->extended_max_execution_time );

			$this->cache_engines[self::REF_FOLLOW_BASE]->direct_cache( true );

			set_time_limit( $this->original_max_execution_time );

			//wp_safe_redirect( menu_page_url('scc-setting', false ) );
		}

		if ( isset( $_POST["get_tiwtter_bearer_token"] ) && $_POST["get_tiwtter_bearer_token"] === __( 'Get Bearer Token', self::DOMAIN ) ) {
			$tmp_twitter_bearer_token = SCC_Common_Util::get_twitter_bearer_token( $this->follow_twitter_consumer_key, $this->follow_twitter_consumer_secret );
		}

	} elseif( isset( $_GET['_wpnonce'] ) && $_GET['_wpnonce'] && isset( $_GET['action'] ) && $_GET['action'] && isset( $_GET['code'] ) && $_GET['code'] ) {
		if ( $_GET['action'] === 'instagram-auth' ) {
			if (  check_admin_referer( 'instagram-auth', '_wpnonce' )  ) {
				if ( current_user_can( self::OPT_COMMON_CAPABILITY ) ) {
					$redirect_uri = plugins_url() . '/sns-count-cache/?action=instagram-auth';
					$tmp_instagram_access_token = SCC_Common_Util::get_instagram_access_token( $this->follow_instagram_client_id, $this->follow_instagram_client_secret, $redirect_uri, $_GET['code'] );
				}
			}
		} elseif ( $_GET['action'] === 'facebook-auth' ) {
			if (  check_admin_referer( 'facebook-auth', '_wpnonce' )  ) {
				if ( current_user_can( self::OPT_COMMON_CAPABILITY ) ) {
					$redirect_uri = plugins_url() . '/sns-count-cache/?action=facebook-auth';
					$tmp_facebook_access_token = SCC_Common_Util::get_facebook_access_token( $this->follow_facebook_app_id, $this->follow_facebook_app_secret, $redirect_uri, $_GET['code'], $this->follow_facebook_page_id );
				}
			}
		}
	}
?>
	<div class="wrap">
		<h2><a href="admin.php?page=scc-setting"><?php _e( 'SNS Count Cache', self::DOMAIN ); ?></a></h2>
		<?php
			if ( $messages = get_transient( self::OPT_COMMON_ERROR_MESSAGE  ) ) {
		?>
		<div class="error">
			<ul>
			<?php
				foreach( $messages as $message ) {
			?>
				<li><?php echo esc_html( $message ); ?></li>
			<?php
				}
			?>
			</ul>
		</div>
		<?php
				delete_transient( self::OPT_COMMON_ERROR_MESSAGE );
			}
		?>

		<?php
			$status = SCC_WP_Cron_Util::test_cron_spawn();

			if ( is_wp_error( $status ) ) {
				echo '<div class="notice notice-error"><p>';
				_e( 'There was a problem spawning a call to the WP-Cron system on your site. This means WP-Cron jobs on your site may not work. The problem was: ', self::DOMAIN );
				echo '<br><strong>' . esc_html( $status->get_error_message() ) . '</strong>';
				echo '.</p></div>';
			}

			if ( isset( $this->share_base_cache_target[self::REF_SHARE_FACEBOOK] ) && $this->share_base_cache_target[self::REF_SHARE_FACEBOOK] ) {
				$configuration_check = $this->crawlers[self::REF_SHARE]->check_crawl_strategy_configurations( self::REF_SHARE_FACEBOOK );

				if ( ! $configuration_check ) {
					echo '<div class="notice notice-warning"><p>';
					_e( 'Configuratin is not enough to get share count. Please set required parameters at ', self::DOMAIN );
					echo '<a href="#share-base-cache-facebook">' . __( 'Share Base Cache - Facebook', self::DOMAIN ) . '</a>';
					echo '.</p></div>';
				}
			}

			if ( isset( $this->follow_base_cache_target[self::REF_FOLLOW_INSTAGRAM] ) && $this->follow_base_cache_target[self::REF_FOLLOW_INSTAGRAM] ) {
				$configuration_check = $this->crawlers[self::REF_FOLLOW]->check_crawl_strategy_configurations( self::REF_FOLLOW_INSTAGRAM);

				if ( ! $configuration_check ) {
					echo '<div class="notice notice-warning"><p>';
					_e( 'Configuratin is not enough to get follower count. Please set required parameters at ', self::DOMAIN );
					echo '<a href="#follow-base-cache-instagram">' . __( 'Follow Base Cache - Instagram', self::DOMAIN ) . '</a>';
					echo '.</p></div>';
				}
			}

			if ( isset( $this->follow_base_cache_target[self::REF_FOLLOW_FACEBOOK] ) && $this->follow_base_cache_target[self::REF_FOLLOW_FACEBOOK] ) {
				$configuration_check = $this->crawlers[self::REF_FOLLOW]->check_crawl_strategy_configurations( self::REF_FOLLOW_FACEBOOK );

				if ( ! $configuration_check ) {
					echo '<div class="notice notice-warning"><p>';
					_e( 'Configuratin is not enough to get follower count. Please set required parameters at ', self::DOMAIN );
					echo '<a href="#follow-base-cache-facebook">' . __( 'Follow Base Cache - Facebook', self::DOMAIN ) . '</a>';
					echo '.</p></div>';
				}
			}

			if ( isset( $this->follow_base_cache_target[self::REF_FOLLOW_PUSH7] ) && $this->follow_base_cache_target[self::REF_FOLLOW_PUSH7] ) {
				$configuration_check = $this->crawlers[self::REF_FOLLOW]->check_crawl_strategy_configurations( self::REF_FOLLOW_PUSH7 );

				if ( ! $configuration_check ) {
					echo '<div class="notice notice-warning"><p>';
					_e( 'Configuratin is not enough to get follower count. Please set required parameters at ', self::DOMAIN );
					echo '<a href="#follow-base-cache-push7">' . __( 'Follow Base Cache - Push7', self::DOMAIN ) . '</a>';
					echo '.</p></div>';
				}
			}

			if ( isset( $this->follow_base_cache_target[self::REF_FOLLOW_TWITTER] ) && $this->follow_base_cache_target[self::REF_FOLLOW_TWITTER] ) {
				$configuration_check = $this->crawlers[self::REF_FOLLOW]->check_crawl_strategy_configurations( self::REF_FOLLOW_TWITTER );

				if ( ! $configuration_check ) {
					echo '<div class="notice notice-warning"><p>';
					_e( 'Configuratin is not enough to get follower count. Please set required parameters at ', self::DOMAIN );
					echo '<a href="#follow-base-cache-twitter">' . __( 'Follow Base Cache - Twitter', self::DOMAIN ) . '</a>';
					echo '.</p></div>';
				}
			}

		?>

		<div class="sns-cnt-cache">
			<h3 class="nav-tab-wrapper">
				<a class="nav-tab" href="admin.php?page=scc-dashboard"><?php _e( 'Dashboard', self::DOMAIN ); ?></a>
				<a class="nav-tab" href="admin.php?page=scc-cache-status"><?php _e( 'Cache Status', self::DOMAIN ); ?></a>
				<a class="nav-tab" href="admin.php?page=scc-share-count"><?php _e( 'Share Count', self::DOMAIN ); ?></a>
			<?php if ( $this->share_variation_analysis_mode !== self::OPT_SHARE_VARIATION_ANALYSIS_NONE ) { ?>
				<a class="nav-tab" href="admin.php?page=scc-hot-content"><?php _e( 'Hot Content', self::DOMAIN ); ?></a>
			<?php } ?>
				<a class="nav-tab nav-tab-active" href="admin.php?page=scc-setting"><?php _e( 'Setting', self::DOMAIN ); ?></a>
				<a class="nav-tab" href="admin.php?page=scc-help"><?php _e( 'Help', self::DOMAIN ); ?></a>
			</h3>
			<p id="options-menu">
				<a href="#current-parameter"><?php _e( 'Current Setting', self::DOMAIN ); ?></a> | <a href="#share-base-cache"><?php _e( 'Share Base Cache', self::DOMAIN ); ?></a> | <a href="#share-rush-cache"><?php _e( 'Share Rush Cache', self::DOMAIN ); ?></a> | <a href="#share-variation-analysis"><?php _e( 'Share Variation Analysis', self::DOMAIN ); ?></a> | <a href="#follow-base-cache"><?php _e( 'Follow Base Cache', self::DOMAIN ); ?></a> | <a href="#follow-variation-analysis"><?php _e( 'Follow Variation Analysis', self::DOMAIN ); ?></a> | <a href="#common-dynamic-cache"><?php _e( 'Dynamic Cache', self::DOMAIN ); ?></a> | <a href="#common-fault-tolerance"><?php _e( 'Fault Tolerance', self::DOMAIN ); ?></a> | <a href="#common-data-crawler"><?php _e( 'Data Crawler', self::DOMAIN ); ?></a> | <a href="#common-data-export"><?php _e( 'Data Export', self::DOMAIN ); ?></a> | <a href="#common-exported-file"><?php _e( 'Exported File', self::DOMAIN ); ?></a>
			</p>
			<div class="metabox-holder">
				<div id="current-parameter" class="postbox">
					<div class="handlediv" title="Click to toggle"><br></div>
					<h3 class="hndle"><span><?php _e( 'Current Setting', self::DOMAIN ); ?></span></h3>
					<div class="inside">
						<table class="view-table">
							<thead>
								<tr>
									<th><?php _e( 'Capability', self::DOMAIN ); ?></th>
									<th><?php _e( 'Parameter', self::DOMAIN ); ?></th>
									<th><?php _e( 'Value', self::DOMAIN ); ?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><?php _e( 'Share Base Cache', self::DOMAIN); ?></td>
									<td><?php _e( 'Target SNS', self::DOMAIN ); ?></td>
									<td>
										<?php
											$target_sns = array();

											if ( isset( $this->share_base_cache_target[self::REF_SHARE_FACEBOOK] ) && $this->share_base_cache_target[self::REF_SHARE_FACEBOOK] ) {
												$target_sns[] = __( 'Facebook', self::DOMAIN );
											}
											if ( isset( $this->share_base_cache_target[self::REF_SHARE_GPLUS] ) && $this->share_base_cache_target[self::REF_SHARE_GPLUS] ) {
												$target_sns[] = __( 'Google+', self::DOMAIN );
											}
											if ( isset( $this->share_base_cache_target[self::REF_SHARE_HATEBU] ) && $this->share_base_cache_target[self::REF_SHARE_HATEBU] ) {
												$target_sns[] = __( 'Hatena Bookmark', self::DOMAIN );
											}
											if ( isset( $this->share_base_cache_target[self::REF_SHARE_LINKEDIN] ) && $this->share_base_cache_target[self::REF_SHARE_LINKEDIN] ) {
												$target_sns[] = __( 'Linkedin', self::DOMAIN );
											}
											if ( isset( $this->share_base_cache_target[self::REF_SHARE_PINTEREST] ) && $this->share_base_cache_target[self::REF_SHARE_PINTEREST] ) {
												$target_sns[] = __( 'Pinterest', self::DOMAIN );
											}
											if ( isset( $this->share_base_cache_target[self::REF_SHARE_POCKET] ) && $this->share_base_cache_target[self::REF_SHARE_POCKET] ) {
												$target_sns[] = __( 'Pocket', self::DOMAIN );
											}
											if ( isset( $this->share_base_cache_target[self::REF_SHARE_TWITTER] ) && $this->share_base_cache_target[self::REF_SHARE_TWITTER] ) {
												$target_sns[] = __( 'Twitter', self::DOMAIN );
											}
											echo esc_html( implode( ", ", $target_sns ) );
										?>
									</td>
								</tr>
								<tr>
									<td><?php _e( 'Share Base Cache', self::DOMAIN ); ?></td>
									<td><?php _e( 'Custom post types', self::DOMAIN ); ?></td>
									<td>
										<?php
												if ( ! empty( $this->share_base_custom_post_types ) && $this->share_base_custom_post_types ) {
													echo esc_html( implode( ',', $this->share_base_custom_post_types ) );
												} else {
													_e( 'N/A', self::DOMAIN );
												}
										?>
									</td>
								</tr>
								<tr>
									<td><?php _e( 'Share Base Cache', self::DOMAIN ); ?></td>
									<td><?php _e( 'Interval cheking share count (sec)', self::DOMAIN ); ?></td>
									<td><?php echo esc_html( $this->share_base_check_interval ) . ' ' . __( 'seconds', self::DOMAIN ); ?></td>
								</tr>
								<tr>
									<td><?php _e( 'Share Base Cache', self::DOMAIN ); ?></td>
									<td><?php _e( 'Number of contents to check at a time', self::DOMAIN ) ?></td>
									<td><?php echo esc_html( $this->share_base_posts_per_check ) . ' ' . __( 'contents', self::DOMAIN ); ?></td>
								</tr>
								<tr>
									<td><?php _e( 'Share Base Cache', self::DOMAIN ); ?></td>
									<td><?php _e( 'Scheme migration mode from http to https', self::DOMAIN ); ?></td>
									<td>
										<?php
											if ( $this->scheme_migration_mode ) {
												_e( 'On', self::DOMAIN );
											} else {
												_e( 'Off', self::DOMAIN );
											}
										?>
									</td>
								</tr>
							<?php if ( $this->scheme_migration_mode ) { ?>
								<tr>
									<td><?php _e( 'Share Base Cache', self::DOMAIN ); ?></td>
									<td><?php _e( 'Scheme migration date from http to https', self::DOMAIN ); ?></td>
									<td>
										<?php
											if ( isset( $this->scheme_migration_date ) ) {
												echo esc_html( $this->scheme_migration_date );
											} else {
												_e( 'N/A', self::DOMAIN );
											}
										?>
									</td>
								</tr>
							<?php } ?>

							<?php if ( isset( $this->share_base_cache_target[self::REF_SHARE_TWITTER] ) && $this->share_base_cache_target[self::REF_SHARE_TWITTER] ) { ?>
								<tr>
									<td><?php _e( 'Share Base Cache - Twitter', self::DOMAIN ); ?></td>
									<td><?php _e( 'Alternative Twitter API', self::DOMAIN ); ?></td>
									<td>
									<?php
										if ( $this->share_base_twitter_api === self::OPT_SHARE_TWITTER_API_JSOON ) {
											echo '<a href="' . esc_url( 'https://jsoon.digitiminimi.com/' ) . '" target="_blank">' . esc_html( 'widgetoon.js & count.jsoon' ) . '</a>';
										} elseif ( $this->share_base_twitter_api === self::OPT_SHARE_TWITTER_API_OPENSHARECOUNT ) {
											echo '<a href="' . esc_url( 'https://opensharecount.com/' ) . '" target="_blank">' . esc_html( 'OpenShareCount' ) . '</a>';
										} elseif (  $this->share_base_twitter_api === self::OPT_SHARE_TWITTER_API_TWITCOUNT ) {
											echo '<a href="' . esc_url( 'http://twitcount.com/' ) . '" target="_blank">' . esc_html( 'TwitCount' ) . '</a>';
										} elseif( $this->share_base_twitter_api === self::OPT_SHARE_TWITTER_API_NEWSHARECOUNTS ) {
											echo '<a href="' . esc_url( 'http://newsharecounts.com/' ) . '" target="_blank">' . esc_html( 'NewShareCounts' ) . '</a>';
										}
									?>
									</td>
								</tr>
							<?php } ?>
								<tr>
									<td><?php _e( 'Share Rush Cache', self::DOMAIN ); ?></td>
									<td><?php _e( 'Term considering posted content as new content', self::DOMAIN ); ?></td>
									<td>
										<?php
											if ( $this->share_rush_new_content_term == 1 ) {
												echo esc_html( $this->share_rush_new_content_term ) . ' ' . __( 'day', self::DOMAIN );
											} elseif ( $this->share_rush_new_content_term > 1 ) {
												echo esc_html( $this->share_rush_new_content_term ) . ' ' . __( 'days', self::DOMAIN );
											}
										?>
									</td>
								</tr>
								<tr>
									<td><?php _e( 'Share Rush Cache', self::DOMAIN ); ?></td>
									<td><?php _e( 'Interval cheking share count (sec)', self::DOMAIN ); ?></td>
									<td>
										<?php
											echo esc_html( $this->share_rush_check_interval ) . ' ' . __( 'seconds', self::DOMAIN );
										?>
									</td>
									<tr>
										<td><?php _e( 'Share Rush Cache', self::DOMAIN ); ?></td>
										<td><?php _e( 'Number of contents to check at a time', self::DOMAIN ) ?></td>
										<td><?php echo esc_html( $this->share_rush_posts_per_check ) . ' ' . __( 'contents', self::DOMAIN ); ?></td>
									</tr>
								</tr>
								<tr>
									<td><?php _e( 'Share Variation Analysis', self::DOMAIN ); ?></td>
									<td><?php _e( 'Method to update basis of comparison', self::DOMAIN ); ?></td><td>
										<?php
											switch ( $this->share_variation_analysis_mode ) {
												case self::OPT_SHARE_VARIATION_ANALYSIS_NONE:
													_e( 'Disabled (None)', self::DOMAIN );
													break;
												case self::OPT_SHARE_VARIATION_ANALYSIS_MANUAL:
													_e( 'Enabled (Manual)', self::DOMAIN );
													break;
												case self::OPT_SHARE_VARIATION_ANALYSIS_SCHEDULER:
													_e( 'Enabled (Scheduler)', self::DOMAIN );
													break;
												default:
													_e( 'Disabled (None)', self::DOMAIN );
											}
										?>
									</td>
								</tr>
							<?php
								if ( $this->share_variation_analysis_mode === self::OPT_SHARE_VARIATION_ANALYSIS_SCHEDULER ) {
							?>
								<tr>
									<td><?php _e( 'Share Variation Analysis', self::DOMAIN ); ?></td>
									<td><?php _e( 'Schedule', self::DOMAIN ); ?></td>
									<td><?php echo esc_html( $this->share_variation_analysis_schedule ); ?></td>
								</tr>
							<?php
								}
							?>
								<tr>
									<td><?php _e( 'Follow Base Cache', self::DOMAIN); ?></td>
									<td><?php _e( 'Target SNS', self::DOMAIN ); ?></td>
									<td>
										<?php
											$target_sns = array();
											if ( isset( $this->follow_base_cache_target[self::REF_FOLLOW_FACEBOOK] ) && $this->follow_base_cache_target[self::REF_FOLLOW_FACEBOOK] ) {
												$target_sns[] = 'Facebook';
											}
											if ( isset( $this->follow_base_cache_target[self::REF_FOLLOW_FEEDLY] ) && $this->follow_base_cache_target[self::REF_FOLLOW_FEEDLY] ) {
												$target_sns[] = 'Feedly';
											}
											if ( isset( $this->follow_base_cache_target[self::REF_FOLLOW_INSTAGRAM] ) && $this->follow_base_cache_target[self::REF_FOLLOW_INSTAGRAM] ) {
												$target_sns[] = 'Instagram';
											}
											if ( isset( $this->follow_base_cache_target[self::REF_FOLLOW_PUSH7] ) && $this->follow_base_cache_target[self::REF_FOLLOW_PUSH7] ) {
												$target_sns[] = 'Push7';
											}
											if ( isset( $this->follow_base_cache_target[self::REF_FOLLOW_TWITTER] ) && $this->follow_base_cache_target[self::REF_FOLLOW_TWITTER] ) {
												$target_sns[] = 'Twitter';
											}
											echo esc_html( implode( ', ', $target_sns ) );
										?>
									</td>
								</tr>
								<tr>
									<td><?php _e( 'Follow Base Cache', self::DOMAIN ); ?></td>
									<td><?php _e( 'Interval cheking follower count (sec)', self::DOMAIN ); ?></td>
									<td><?php echo esc_html( $this->follow_base_check_interval ) . ' ' . __( 'seconds', self::DOMAIN ); ?></td>
								</tr>
							<?php if ( $this->follow_base_cache_target[self::REF_FOLLOW_FEEDLY] ) { ?>
								<tr>
									<td><?php _e( 'Follow Base Cache - Feedly', self::DOMAIN ); ?></td>
									<td><?php _e( 'Target feed type', self::DOMAIN ); ?></td>
									<td>
										<?php
											switch ( $this->follow_feed_type ) {
												case self::OPT_FEED_TYPE_DEFAULT:
													_e( 'Default', self::DOMAIN );
													break;
												case self::OPT_FEED_TYPE_RSS:
													_e( 'RSS', self::DOMAIN );
													break;
												case self::OPT_FEED_TYPE_RSS2:
													_e( 'RSS2', self::DOMAIN );
													break;
												case self::OPT_FEED_TYPE_RDF:
													_e( 'RDF', self::DOMAIN );
													break;
												case self::OPT_FEED_TYPE_ATOM:
													_e( 'ATOM', self::DOMAIN );
													break;
												default:
													_e( 'Default', self::DOMAIN );
											}
										?>
									</td>
								</tr>
								<tr>
									<td><?php _e( 'Follow Base Cache - Feedly', self::DOMAIN ); ?></td>
									<td><?php _e( 'Target feed', self::DOMAIN ); ?></td>
									<td><a href="<?php echo esc_url( get_feed_link( $this->follow_feed_type ) ); ?>" target="_blank"><?php echo esc_html( get_feed_link( $this->follow_feed_type ) ); ?></a></td>
								</tr>
							<?php } ?>
							<?php if ( isset( $this->follow_base_cache_target[self::REF_FOLLOW_TWITTER] ) && $this->follow_base_cache_target[self::REF_FOLLOW_TWITTER] ) { ?>
								<tr>
									<td><?php _e( 'Follow Base Cache - Twitter', self::DOMAIN ); ?></td>
									<td><?php _e( 'Target screen name', self::DOMAIN ); ?></td>
									<td>
										<?php
											if ( isset( $this->follow_twitter_screen_name ) && $this->follow_twitter_screen_name ) {
												echo '@' . esc_html( $this->follow_twitter_screen_name );
											} else {
												_e( 'N/A', self::DOMAIN );
											}
										?>
									</td>
								</tr>
							<?php } ?>
							<?php if ( isset( $this->follow_base_cache_target[self::REF_FOLLOW_FACEBOOK] ) && $this->follow_base_cache_target[self::REF_FOLLOW_FACEBOOK] ) { ?>
								<tr>
									<td><?php _e( 'Follow Base Cache - Facebook', self::DOMAIN ); ?></td>
									<td><?php _e( 'Page ID', self::DOMAIN ); ?></td>
									<td>
										<?php
											if ( isset( $this->follow_facebook_page_id ) ) {
												echo esc_html( $this->follow_facebook_page_id );
											} else {
												_e( 'N/A', self::DOMAIN );
											}
										?>
									</td>
								</tr>
							<?php } ?>
								<tr>
									<td><?php _e( 'Follow Variation Analysis', self::DOMAIN ); ?></td>
									<td><?php _e( 'Method to update basis of comparison', self::DOMAIN ); ?></td><td>
										<?php
											switch ( $this->follow_variation_analysis_mode ) {
												case self::OPT_FOLLOW_VARIATION_ANALYSIS_NONE:
													_e( 'Disabled (None)', self::DOMAIN );
													break;
												case self::OPT_FOLLOW_VARIATION_ANALYSIS_MANUAL:
													_e( 'Enabled (Manual)', self::DOMAIN );
													break;
												case self::OPT_FOLLOW_VARIATION_ANALYSIS_SCHEDULER:
													_e( 'Enabled (Scheduler)', self::DOMAIN );
													break;
												default:
													_e( 'Disabled (None)', self::DOMAIN );
											}
										?>
									</td>
								</tr>
							<?php
								if ( $this->follow_variation_analysis_mode === self::OPT_FOLLOW_VARIATION_ANALYSIS_SCHEDULER ) {
							?>
								<tr>
									<td><?php _e( 'Follow Variation Analysis', self::DOMAIN ); ?></td>
									<td><?php _e( 'Schedule', self::DOMAIN ); ?></td>
									<td><?php echo esc_html( $this->follow_variation_analysis_schedule ); ?></td>
								</tr>
							<?php
								}
							?>
								<tr>
									<td><?php _e( 'Dynamic Cache', self::DOMAIN); ?></td>
									<td><?php _e( 'Dynamic caching based on user access', self::DOMAIN ); ?></td><td>
										<?php
											switch ( $this->dynamic_cache_mode ) {
												case self::OPT_COMMON_ACCESS_BASED_CACHE_OFF:
													_e( 'Disabled', self::DOMAIN );
													break;
												case self::OPT_COMMON_ACCESS_BASED_CACHE_ON:
													_e( 'Enabled', self::DOMAIN );
													break;
												default:
													_e( 'Disabled', self::DOMAIN );
											}
										?>
									</td>
								</tr>
								<tr>
									<td><?php _e( 'Fault Tolerance', self::DOMAIN); ?></td>
									<td><?php _e( 'Fault tolerance of count retrieval', self::DOMAIN ); ?></td><td>
										<?php
											switch ( $this->fault_tolerance_mode ) {
												case self::OPT_COMMON_FAULT_TOLERANCE_OFF:
													_e( 'Disabled', self::DOMAIN );
													break;
												case self::OPT_COMMON_FAULT_TOLERANCE_ON:
													_e( 'Enabled', self::DOMAIN );
													break;
												default:
													_e( 'Disabled', self::DOMAIN );
											}
										?>
									</td>
								</tr>
								<tr>
									<td><?php _e( 'Data Crawler', self::DOMAIN ); ?></td>
									<td><?php _e( 'Crawl method', self::DOMAIN ); ?></td>
									<td>
										<?php
											switch ( $this->crawler_method ) {
												case self::OPT_COMMON_CRAWLER_METHOD_NORMAL:
													_e( 'Normal (Sequential Retrieval)', self::DOMAIN );
													break;
												case self::OPT_COMMON_CRAWLER_METHOD_CURL:
													_e( 'Extended (Parallel Retrieval)', self::DOMAIN );
													break;
											}
										?>
									</td>
								</tr>
								<tr>
									<td><?php _e( 'Data Crawler', self::DOMAIN ); ?></td>
									<td><?php _e( 'Crawl retry limit', self::DOMAIN ); ?></td>
									<td>
										<?php
											switch ( $this->crawler_retry_limit ) {
												case -1:
													_e( 'no retry', self::DOMAIN );
													break;
												case 1:
													_e( '1 time', self::DOMAIN );
													break;
												case 2:
													_e( '2 times', self::DOMAIN );
													break;
												case 3:
													_e( '3 times', self::DOMAIN );
													break;
												case 4:
													_e( '2 times', self::DOMAIN );
													break;
												case 5:
													_e( '5 times', self::DOMAIN );
													break;
											}
										?>
									</td>
								</tr>
								<tr>
									<td><?php _e( 'Data Crawler', self::DOMAIN ); ?></td>
									<td><?php _e( 'SSL verification', self::DOMAIN ); ?></td>
									<td>
										<?php
											if ( $this->crawler_ssl_verification ) {
												_e( 'On', self::DOMAIN );
											} else {
												_e( 'Off', self::DOMAIN );
											}
										?>
									</td>
								</tr>
								<tr>
									<td><?php _e( 'Data Export', self::DOMAIN ); ?></td>
									<td><?php _e( 'Method of data export', self::DOMAIN ); ?></td><td>
										<?php
											switch ( $this->data_export_mode ) {
												case self::OPT_COMMON_DATA_EXPORT_MANUAL:
													_e( 'Manual', self::DOMAIN );
													break;
												case self::OPT_COMMON_DATA_EXPORT_SCHEDULER:
													_e( 'Scheduler', self::DOMAIN );
													break;
											}
										?>
									</td>
								</tr>
							<?php
								if ( $this->data_export_mode === self::OPT_COMMON_DATA_EXPORT_SCHEDULER ) {
							?>
								<tr>
									<td><?php _e( 'Data Export', self::DOMAIN ); ?></td>
									<td><?php _e( 'Interval exporting share count to a csv file', self::DOMAIN ); ?></td>
									<td><?php echo esc_html( $this->data_export_interval / 3600 ) . ' ' . __( 'hours', self::DOMAIN ); ?></td>
								</tr>
							<?php
								}
							?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="metabox-holder">
				<form action="admin.php?page=scc-setting" method="post">
					<?php wp_nonce_field( __FILE__, '_wpnonce' ); ?>
					<div id="share-base-cache" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php _e('Share Base Cache', self::DOMAIN); ?></span></h3>
						<div class="inside">
							<table class="form-table">
								<tr>
									<th><label><?php _e( 'Target SNS', self::DOMAIN ); ?></label></th>
									<td>
										<div class="sns-check">
											<input type="checkbox" value="1" id="share_base_cache_target_facebook" name="share_base_cache_target_facebook"<?php if ( isset( $this->share_base_cache_target[self::REF_SHARE_FACEBOOK] ) && $this->share_base_cache_target[self::REF_SHARE_FACEBOOK] ) echo ' checked="checked"'; ?> />
											<label for="share_base_cache_target_facebook"><?php _e( 'Facebook', self::DOMAIN ); ?></label>
										</div>
										<div class="sns-check">
											<input type="checkbox" value="1" id="share_base_cache_target_gplus" name="share_base_cache_target_gplus"<?php if ( isset( $this->share_base_cache_target[self::REF_SHARE_GPLUS] ) && $this->share_base_cache_target[self::REF_SHARE_GPLUS] ) echo ' checked="checked"'; ?> />
											<label for="share_base_cache_target_gplus"><?php _e( 'Google+', self::DOMAIN ); ?></label>
										</div>
										<div class="sns-check">
											<input type="checkbox" value="1" id="share_base_cache_target_hatebu" name="share_base_cache_target_hatebu"<?php if ( isset( $this->share_base_cache_target[self::REF_SHARE_HATEBU] ) && $this->share_base_cache_target[self::REF_SHARE_HATEBU] ) echo ' checked="checked"'; ?> />
											<label for="share_base_cache_target_hatebu"><?php _e( 'Hatena Bookmark', self::DOMAIN ); ?></label>
										</div>
										<div class="sns-check">
											<input type="checkbox" value="1" id="share_base_cache_target_linkedin" name="share_base_cache_target_linkedin"<?php if ( isset( $this->share_base_cache_target[self::REF_SHARE_LINKEDIN] ) && $this->share_base_cache_target[self::REF_SHARE_LINKEDIN] ) echo ' checked="checked"'; ?> />
											<label for="share_base_cache_target_linkedin"><?php _e( 'Linkedin', self::DOMAIN ); ?></label>
										</div>
										<div class="sns-check">
											<input type="checkbox" value="1" id="share_base_cache_target_pinterest" name="share_base_cache_target_pinterest"<?php if ( isset( $this->share_base_cache_target[self::REF_SHARE_PINTEREST] ) && $this->share_base_cache_target[self::REF_SHARE_PINTEREST] ) echo ' checked="checked"'; ?> />
											<label for="share_base_cache_target_pinterest"><?php _e( 'Pinterest', self::DOMAIN ); ?></label>
										</div>
										<div class="sns-check">
											<input type="checkbox" value="1" id="share_base_cache_target_pocket" name="share_base_cache_target_pocket"<?php if ( isset( $this->share_base_cache_target[self::REF_SHARE_POCKET] ) && $this->share_base_cache_target[self::REF_SHARE_POCKET] ) echo ' checked="checked"'; ?> />
											<label for="share_base_cache_target_pocket"><?php _e( 'Pocket', self::DOMAIN ); ?></label>
										</div>
										<div class="sns-check">
											<input type="checkbox" value="1" id="share_base_cache_target_twitter" name="share_base_cache_target_twitter"<?php if ( isset( $this->share_base_cache_target[self::REF_SHARE_TWITTER] ) && $this->share_base_cache_target[self::REF_SHARE_TWITTER] ) echo ' checked="checked"'; ?> />
											<label for="share_base_cache_target_twitter"><?php _e( 'Twitter', self::DOMAIN ); ?></label>
										</div>
									</td>
								</tr>
								<tr>
									<th><label for="share_base_custom_post_types"><?php _e( 'Custom post types', self::DOMAIN ); ?></label></th>
									<td>
										<input type="text" class="text" id="share_base_custom_post_types" name="share_base_custom_post_types" size="60" value="<?php echo esc_attr( implode( ',', $this->share_base_custom_post_types ) );  ?>" />
										<br>
										<span class="description"><?php _e( 'e.g. aaa, bbb, ccc (comma-delimited)', self::DOMAIN ); ?></span>
									</td>
								</tr>
								<tr>
									<th><label for="share_base_check_interval"><?php _e( 'Interval cheking share count (sec)', self::DOMAIN ); ?></label></th>
									<td>
										<input type="text" class="text" id="share_base_check_interval" name="share_base_check_interval" size="20" value="<?php echo esc_attr( $this->share_base_check_interval ); ?>" />
										<span class="description"><?php _e( 'Default: 600', self::DOMAIN ); ?></span>
									</td>
								</tr>
								<tr>
									<th><label for="share_base_posts_per_check"><?php _e( 'Number of contents to check at a time', self::DOMAIN ); ?></label></th>
									<td>
										<input type="text" class="text" id="share_base_posts_per_check" name="share_base_posts_per_check" size="20" value="<?php echo esc_attr( $this->share_base_posts_per_check ); ?>" />
										<span class="description"><?php _e( 'Default: 20', self::DOMAIN ); ?></span>
									</td>
								</tr>
								<tr>
									<th><label for="scheme_migration_mode"><?php _e( 'Scheme migration mode from http to https', self::DOMAIN ); ?></label></th>
									<td>
										<select id="scheme_migration_mode" name="scheme_migration_mode">
											<option value="0"<?php if ( $this->scheme_migration_mode === self::OPT_COMMON_SCHEME_MIGRATION_MODE_OFF ) echo ' selected="selected"'; ?>><?php _e( 'Off', self::DOMAIN ); ?></option>
											<option value="1"<?php if ( $this->scheme_migration_mode === self::OPT_COMMON_SCHEME_MIGRATION_MODE_ON ) echo ' selected="selected"'; ?>><?php _e( 'On', self::DOMAIN ); ?></option>
										</select>
										<span class="description"><?php _e('Default: Off', self::DOMAIN ); ?></span>
									</td>
								</tr>
							<?php if ( $this->scheme_migration_mode ) { ?>
								<tr>
									<th><label for="scheme-migration-date"><?php _e( 'Scheme migration date from http to https', self::DOMAIN ); ?></label></th>
									<td>
										<input id="scheme-migration-date" type="text" class="text" name="scheme_migration_date" size="20" value="<?php echo esc_attr( $this->scheme_migration_date ); ?>" />
										<span class="description"><?php _e( 'Default: N/A', self::DOMAIN ); ?></span>
										<script>
											jQuery(document).ready(function() {
												jQuery('#scheme-migration-date').datepicker({
													dateFormat : 'yy/mm/dd'
												});
											});
										</script>
									</td>
								</tr>
							<?php } ?>
							</table>
							<div class="submit-button">
								<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />
								<input type="submit" class="button button-secondary" name="clear_share_base_cache" value="<?php _e( 'Clear Cache', self::DOMAIN ); ?>">
							</div>
						</div>
					</div>
				<?php if ( isset( $this->share_base_cache_target[self::REF_SHARE_FACEBOOK] ) && $this->share_base_cache_target[self::REF_SHARE_FACEBOOK] ) { ?>
					<div id="share-base-cache-facebook" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php _e( 'Share Base Cache - Facebook', self::DOMAIN ); ?></span></h3>
						<div class="inside">
							<table class="form-table">
								<tr>
									<th><label><?php _e( 'Developer page', self::DOMAIN ); ?></label></th>
									<td>
										<a href="https://developers.facebook.com/" target="_blank">https://developers.facebook.com/</a>
										<br>
										<span class="description"><?php _e( 'Register a application in Facebook developer page to get the following App ID and App Secret.', self::DOMAIN ); ?></span>
									</td>
								</tr>
								<tr>
									<th><label for="share_facebook_app_id"><?php _e( 'App ID (Client ID)', self::DOMAIN ); ?></label></th>
									<td>
										<input type="password" id="share_facebook_app_id" class="text" name="share_facebook_app_id" size="60" value="<?php echo esc_attr( $this->share_facebook_app_id ); ?>" />
										<br>
										<span class="description"><?php _e( 'App ID for Facebook API', self::DOMAIN ); ?></span>
									</td>
								</tr>
								<tr>
									<th><label for="share_facebook_app_secret"><?php _e( 'App Secret (Client Secret)', self::DOMAIN ); ?></label></th>
									<td>
										<input type="password" id="share_facebook_app_secret" class="text" name="share_facebook_app_secret" size="60" value="<?php echo esc_attr( $this->share_facebook_app_secret ); ?>" />
										<br>
										<span class="description"><?php _e( 'App Secret for Facebook API', self::DOMAIN ); ?></span>
									</td>
								</tr>
								<?php if ( isset( $this->share_facebook_app_id ) && $this->share_facebook_app_id && isset( $this->share_facebook_app_secret ) && $this->share_facebook_app_secret ) { ?>
									<tr>
										<th><label for="tmp_share_facebook_access_token"><?php _e( 'Access Token', self::DOMAIN ); ?></label></th>
										<td>
											<input type="password" id="tmp_share_facebook_access_token" class="text" name="tmp_share_facebook_access_token" size="60" value="<?php echo esc_attr( $this->share_facebook_access_token ); ?>" readonly />
											<br>
											<span class="description"><?php _e( 'Access Token for Facebook API', self::DOMAIN ); ?></span>
										</td>
									</tr>
								<?php } ?>
							</table>
							<div class="submit-button">
								<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />
							</div>
						</div>
					</div>
				<?php } ?>
				<?php if ( isset( $this->share_base_cache_target[self::REF_SHARE_TWITTER] ) && $this->share_base_cache_target[self::REF_SHARE_TWITTER] ) { ?>
					<div id="share-base-cache-twitter" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php _e( 'Share Base Cache - Twitter', self::DOMAIN ); ?></span></h3>
						<div class="inside">
							<table class="form-table">
								<tr>
									<th><label for="share_alternative_twitter_api"><?php _e( 'Alternative Twitter API', self::DOMAIN ); ?></label></th>
									<td>
										<select id="share_alternative_twitter_api" name="share_alternative_twitter_api">
											<option value="1"<?php if ( $this->share_base_twitter_api === self::OPT_SHARE_TWITTER_API_JSOON ) echo ' selected="selected"'; ?>><?php echo esc_html( 'widgetoon.js & count.jsoon' ); ?></option>
											<option value="2"<?php if ( $this->share_base_twitter_api === self::OPT_SHARE_TWITTER_API_OPENSHARECOUNT ) echo ' selected="selected"'; ?>><?php echo esc_html( 'OpenShareCount' ); ?></option>
											<option value="3"<?php if ( $this->share_base_twitter_api === self::OPT_SHARE_TWITTER_API_TWITCOUNT ) echo ' selected="selected"'; ?>><?php echo esc_html( 'TwitCount' ); ?></option>
											<option value="4"<?php if ( $this->share_base_twitter_api === self::OPT_SHARE_TWITTER_API_NEWSHARECOUNTS ) echo ' selected="selected"'; ?>><?php echo esc_html( 'NewShareCounts' ); ?></option>
										</select>
										<span class="description"><?php _e( 'Default: ', self::DOMAIN ); echo esc_html( 'widgetoon.js & count.jsoon' ); ?></span>
									</td>
								</tr>
								<tr>
									<th><label><?php _e( 'Registration destination', self::DOMAIN ); ?></label></th>
									<td>
										<?php
											if ( $this->share_base_twitter_api === self::OPT_SHARE_TWITTER_API_JSOON ) {
												echo '<a href="' . esc_url( 'https://jsoon.digitiminimi.com/' ) . '" target="_blank">' . esc_html( 'https://jsoon.digitiminimi.com/' ) . '</a>';
											} elseif ( $this->share_base_twitter_api === self::OPT_SHARE_TWITTER_API_OPENSHARECOUNT ) {
												echo '<a href="' . esc_url( 'https://opensharecount.com/' ) . '" target="_blank">' . esc_html( 'https://opensharecount.com/' ) . '</a>';
											} elseif (  $this->share_base_twitter_api === self::OPT_SHARE_TWITTER_API_TWITCOUNT ) {
												echo '<a href="' . esc_url( 'http://twitcount.com/' ) . '" target="_blank">' . esc_html( 'http://twitcount.com/' ) . '</a>';
											} elseif( $this->share_base_twitter_api === self::OPT_SHARE_TWITTER_API_NEWSHARECOUNTS ) {
												echo '<a href="' . esc_url( 'http://newsharecounts.com/' ) . '" target="_blank">' . esc_html( 'http://newsharecounts.com/' ) . '</a>';
											}
										?>
										<br />
										<span class="description"><?php _e( 'You need to register information such as your domain with the above site in order to start counting.', self::DOMAIN ); ?></span>
									</td>
								</tr>
							</table>
							<div class="submit-button">
								<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />
							</div>
						</div>
					</div>
				<?php } ?>
					<div id="share-rush-cache" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php _e( 'Share Rush Cache', self::DOMAIN); ?></span></h3>
						<div class="inside">
							<table class="form-table">
								<tr>
									<th><label for="share_rush_new_content_term"><?php _e( 'Term considering posted content as new content', self::DOMAIN ); ?></label></th>
									<td>
										<select id="share_rush_new_content_term" name="share_rush_new_content_term">
											<option value="1"<?php if ( $this->share_rush_new_content_term === 1 ) echo ' selected="selected"'; ?>><?php _e( '1 day', self::DOMAIN ); ?></option>
											<option value="2"<?php if ( $this->share_rush_new_content_term === 2 ) echo ' selected="selected"'; ?>><?php _e( '2 days', self::DOMAIN ); ?></option>
											<option value="3"<?php if ( $this->share_rush_new_content_term === 3 ) echo ' selected="selected"'; ?>><?php _e( '3 days', self::DOMAIN ); ?></option>
											<option value="4"<?php if ( $this->share_rush_new_content_term === 4 ) echo ' selected="selected"'; ?>><?php _e( '4 days', self::DOMAIN ); ?></option>
											<option value="5"<?php if ( $this->share_rush_new_content_term === 5 ) echo ' selected="selected"'; ?>><?php _e( '5 days', self::DOMAIN ); ?></option>
											<option value="6"<?php if ( $this->share_rush_new_content_term === 6 ) echo ' selected="selected"'; ?>><?php _e( '6 days', self::DOMAIN ); ?></option>
											<option value="7"<?php if ( $this->share_rush_new_content_term === 7 ) echo ' selected="selected"'; ?>><?php _e( '7 days', self::DOMAIN ); ?></option>
											<option value="8"<?php if ( $this->share_rush_new_content_term === 8 ) echo ' selected="selected"'; ?>><?php _e( '8 days', self::DOMAIN ); ?></option>
											<option value="9"<?php if ( $this->share_rush_new_content_term === 9 ) echo ' selected="selected"'; ?>><?php _e( '9 days', self::DOMAIN ); ?></option>
											<option value="10"<?php if ( $this->share_rush_new_content_term === 10 ) echo ' selected="selected"'; ?>><?php _e( '10 days', self::DOMAIN ); ?></option>
											<option value="11"<?php if ( $this->share_rush_new_content_term === 11 ) echo ' selected="selected"'; ?>><?php _e( '11 days', self::DOMAIN ); ?></option>
											<option value="12"<?php if ( $this->share_rush_new_content_term === 12 ) echo ' selected="selected"'; ?>><?php _e( '12 days', self::DOMAIN ); ?></option>
											<option value="13"<?php if ( $this->share_rush_new_content_term === 13 ) echo ' selected="selected"'; ?>><?php _e( '13 days', self::DOMAIN ); ?></option>
											<option value="14"<?php if ( $this->share_rush_new_content_term === 14 ) echo ' selected="selected"'; ?>><?php _e( '14 days', self::DOMAIN ); ?></option>
										</select>
										<span class="description"><?php _e( 'Default: 3 days', self::DOMAIN ); ?></span>
									</td>
								</tr>
								<tr>
									<th><label for="share_rush_check_interval"><?php _e( 'Interval cheking share count (sec)', self::DOMAIN ); ?></label></th>
									<td>
										<input type="text" id="share_rush_check_interval" class="text" name="share_rush_check_interval" size="20" value="<?php echo esc_attr( $this->share_rush_check_interval ); ?>" />
										<span class="description"><?php _e( 'Default: 600', self::DOMAIN ); ?></span>
									</td>
								</tr>
								<tr>
									<th><label for="share_rush_posts_per_check"><?php _e( 'Number of contents to check at a time', self::DOMAIN ); ?></label></th>
									<td>
										<input type="text" id="share_rush_posts_per_check" class="text" name="share_rush_posts_per_check" size="20" value="<?php echo esc_attr( $this->share_rush_posts_per_check ); ?>" />
										<span class="description"><?php _e( 'Default: 20', self::DOMAIN ); ?></span>
									</td>
								</tr>
							</table>
							<div class="submit-button">
								<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />
							</div>
						</div>
					</div>
					<div id="share-variation-analysis" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php _e( 'Share Variation Analysis', self::DOMAIN ); ?></span></h3>
						<div class="inside">
							<table class="form-table">
								<tr>
									<th><label for="share_variation_analysis_mode"><?php _e( 'Method to update basis of comparison', self::DOMAIN ); ?></label></th>
									<td>
										<select id="share_variation_analysis_mode" name="share_variation_analysis_mode">
											<option value="1"<?php if ( $this->share_variation_analysis_mode === self::OPT_SHARE_VARIATION_ANALYSIS_NONE ) echo ' selected="selected"'; ?>><?php _e( 'Disabled (None)', self::DOMAIN ); ?></option>
											<option value="2"<?php if ( $this->share_variation_analysis_mode === self::OPT_SHARE_VARIATION_ANALYSIS_MANUAL ) echo ' selected="selected"'; ?>><?php _e( 'Enabled (Manual)', self::DOMAIN ); ?></option>
											<option value="3"<?php if ( $this->share_variation_analysis_mode === self::OPT_SHARE_VARIATION_ANALYSIS_SCHEDULER ) echo ' selected="selected"'; ?>><?php _e( 'Enabled (Scheduler)', self::DOMAIN ); ?></option>
										</select>
										<span class="description"><?php _e( 'Disabled (None)', self::DOMAIN ); ?></span>
									</td>
								</tr>
							<?php
								if ( $this->share_variation_analysis_mode === self::OPT_SHARE_VARIATION_ANALYSIS_SCHEDULER ) {
									list( $cronstr['minutes'], $cronstr['hours'], $cronstr['mday'], $cronstr['mon'], $cronstr['wday'] ) = explode( ' ', $this->share_variation_analysis_schedule, 5 );
									if ( strstr( $cronstr['minutes'], '*/' ) ) {
										$minutes = explode( '/', $cronstr['minutes'] );
									} else {
										$minutes = explode( ',', $cronstr['minutes'] );
									}
									if ( strstr( $cronstr['hours'], '*/' ) ) {
										$hours = explode( '/', $cronstr['hours'] );
									} else {
										$hours = explode( ',', $cronstr['hours'] );
									}
									if ( strstr( $cronstr['mday'], '*/' ) ) {
										$mday = explode( '/', $cronstr['mday'] );
									} else {
										$mday = explode( ',', $cronstr['mday'] );
									}
									if ( strstr( $cronstr['mon'], '*/' ) ) {
										$mon = explode( '/', $cronstr['mon'] );
									} else {
										$mon = explode( ',', $cronstr['mon'] );
									}
									if ( strstr( $cronstr['wday'], '*/' ) ) {
										$wday = explode( '/', $cronstr['wday'] );
									} else {
										$wday = explode( ',', $cronstr['wday'] );
									}
							?>
								<tr class="a_wpcron">
									<th scope="row"><?php _e( 'Scheduler', self::DOMAIN ); ?></th>
									<td>
										<table class="wpcron">
											<tr>
												<th>
													<?php _e( 'Type', self::DOMAIN ); ?>
												</th>
												<th>
												</th>
												<th>
													<?php _e( 'Hour', self::DOMAIN ); ?>
												</th>
												<th>
													<?php _e( 'Minute', self::DOMAIN ); ?>
												</th>
											</tr>
											<tr>
												<td>
													<label for="idcronbtype-mon">
														<?php echo '<input class="radio" type="radio"' . checked( TRUE, is_numeric( $mday[0] ), FALSE ) . ' name="a_cronbtype" value="mon" /> ' . __( 'monthly', self::DOMAIN ); ?>
													</label>
												</td>
												<td>
													<select name="a_moncronmday">
														<?php
															for ( $i = 1; $i <= 31; $i ++ ) {
																$on_day = '';

																switch ( $i ) {
																	case 1:
																		$on_day = __( 'on 1.', self::DOMAIN );
																		break;
																	case 2:
																		$on_day = __( 'on 2.', self::DOMAIN );
																		break;
																	case 3:
																		$on_day = __( 'on 3.', self::DOMAIN );
																		break;
																	case 4:
																		$on_day = __( 'on 4.', self::DOMAIN );
																		break;
																	case 5:
																		$on_day = __( 'on 5.', self::DOMAIN );
																		break;
																	case 6:
																		$on_day = __( 'on 6.', self::DOMAIN );
																		break;
																	case 7:
																		$on_day = __( 'on 7.', self::DOMAIN );
																		break;
																	case 8:
																		$on_day = __( 'on 8.', self::DOMAIN );
																		break;
																	case 9:
																		$on_day = __( 'on 9.', self::DOMAIN );
																		break;
																	case 10:
																		$on_day = __( 'on 10.', self::DOMAIN );
																		break;
																	case 11:
																		$on_day = __( 'on 11.', self::DOMAIN );
																		break;
																	case 12:
																		$on_day = __( 'on 12.', self::DOMAIN );
																		break;
																	case 13:
																		$on_day = __( 'on 13.', self::DOMAIN );
																		break;
																	case 14:
																		$on_day = __( 'on 14.', self::DOMAIN );
																		break;
																	case 15:
																		$on_day = __( 'on 15.', self::DOMAIN );
																		break;
																	case 16:
																		$on_day = __( 'on 16.', self::DOMAIN );
																		break;
																	case 17:
																		$on_day = __( 'on 17.', self::DOMAIN );
																		break;
																	case 18:
																		$on_day = __( 'on 18.', self::DOMAIN );
																		break;
																	case 19:
																		$on_day = __( 'on 19.', self::DOMAIN );
																		break;
																	case 20:
																		$on_day = __( 'on 20.', self::DOMAIN );
																		break;
																	case 21:
																		$on_day = __( 'on 21.', self::DOMAIN );
																		break;
																	case 22:
																		$on_day = __( 'on 22.', self::DOMAIN );
																		break;
																	case 23:
																		$on_day = __( 'on 23.', self::DOMAIN );
																		break;
																	case 24:
																		$on_day = __( 'on 24.', self::DOMAIN );
																		break;
																	case 25:
																		$on_day = __( 'on 25.', self::DOMAIN );
																		break;
																	case 26:
																		$on_day = __( 'on 26.', self::DOMAIN );
																		break;
																	case 27:
																		$on_day = __( 'on 27.', self::DOMAIN );
																		break;
																	case 28:
																		$on_day = __( 'on 28.', self::DOMAIN );
																		break;
																	case 29:
																		$on_day = __( 'on 29.', self::DOMAIN );
																		break;
																	case 30:
																		$on_day = __( 'on 30.', self::DOMAIN );
																		break;
																	case 31:
																		$on_day = __( 'on 31.', self::DOMAIN );
																		break;
																}

																echo '<option ' . selected( in_array( "$i", $mday, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $on_day . '</option>';
															}
														?>
													</select>
												</td>
												<td>
													<select name="a_moncronhours">
														<?php for ( $i = 0; $i < 24; $i ++ ) {
															echo '<option ' . selected( in_array( "$i", $hours, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
														} ?>
													</select>
												</td>
												<td>
													<select name="a_moncronminutes">
														<?php for ( $i = 0; $i < 60; $i = $i + 5 ) {
															echo '<option ' . selected( in_array( "$i", $minutes, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
														} ?>
													</select>
												</td>
											</tr>
											<tr>
												<td>
													<label for="idcronbtype-week">
														<?php echo '<input class="radio" type="radio"' . checked( TRUE, is_numeric( $wday[0] ), FALSE ) . ' name="a_cronbtype" value="week" /> ' . __( 'weekly', self::DOMAIN ); ?>
													</label>
												</td>
												<td>
													<select name="a_weekcronwday">
														<?php
															echo '<option ' . selected( in_array( '0', $wday, TRUE ), TRUE, FALSE ) . '  value="0" />' . __( 'Sunday', self::DOMAIN ) . '</option>';
															echo '<option ' . selected( in_array( '1', $wday, TRUE ), TRUE, FALSE ) . '  value="1" />' . __( 'Monday', self::DOMAIN ) . '</option>';
															echo '<option ' . selected( in_array( '2', $wday, TRUE ), TRUE, FALSE ) . '  value="2" />' . __( 'Tuesday', self::DOMAIN ) . '</option>';
															echo '<option ' . selected( in_array( '3', $wday, TRUE ), TRUE, FALSE ) . '  value="3" />' . __( 'Wednesday', self::DOMAIN ) . '</option>';
															echo '<option ' . selected( in_array( '4', $wday, TRUE ), TRUE, FALSE ) . '  value="4" />' . __( 'Thursday', self::DOMAIN ) . '</option>';
															echo '<option ' . selected( in_array( '5', $wday, TRUE ), TRUE, FALSE ) . '  value="5" />' . __( 'Friday', self::DOMAIN ) . '</option>';
															echo '<option ' . selected( in_array( '6', $wday, TRUE ), TRUE, FALSE ) . '  value="6" />' . __( 'Saturday', self::DOMAIN ) . '</option>';
														?>
													</select>
												</td>
												<td>
													<select name="a_weekcronhours">
														<?php for ( $i = 0; $i < 24; $i ++ ) {
															echo '<option ' . selected( in_array( "$i", $hours, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
														} ?>
													</select>
												</td>
												<td>
													<select name="a_weekcronminutes">
														<?php for ( $i = 0; $i < 60; $i = $i + 5 ) {
															echo '<option ' . selected( in_array( "$i", $minutes, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
														} ?>
													</select>
												</td>
											</tr>
											<tr>
												<td>
													<label for="idcronbtype-day">
														<?php echo '<input class="radio" type="radio"' . checked( "**", $mday[0] . $wday[0], FALSE ) . ' name="a_cronbtype" value="day" /> ' . __( 'daily', self::DOMAIN ); ?>
													</label>
												</td>
												<td>
												</td>
												<td>
													<select name="a_daycronhours">
														<?php
															for ( $i = 0; $i < 24; $i ++ ) {
																echo '<option ' . selected( in_array( "$i", $hours, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
															}
														?>
													</select>
												</td>
												<td>
													<select name="a_daycronminutes">
														<?php for ( $i = 0; $i < 60; $i = $i + 5 ) {
															echo '<option ' . selected( in_array( "$i", $minutes, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
														} ?>
													</select>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							<?php
								}
							?>
							</table>
							<div class="submit-button">
								<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />
								<input type="submit" class="button button-secondary" name="update_share_comparison_base" value="<?php _e( 'Update Basis of Comparison', self::DOMAIN ); ?>" />
							</div>
						</div>
					</div>
					<div id="follow-base-cache" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php _e( 'Follow Base Cache', self::DOMAIN ); ?></span></h3>
						<div class="inside">
							<table class="form-table">
								<tr>
									<th><label><?php _e( 'Target SNS', self::DOMAIN ); ?></label></th>
									<td>
										<div class="sns-check">
											<input type="checkbox" value="1" id="follow_base_cache_target_facebook" name="follow_base_cache_target_facebook"<?php if ( isset( $this->follow_base_cache_target[self::REF_FOLLOW_FACEBOOK] ) && $this->follow_base_cache_target[self::REF_FOLLOW_FACEBOOK] ) echo ' checked="checked"'; ?> />
											<label for="follow_base_cache_target_facebook"><?php _e( 'Facebook', self::DOMAIN ); ?></label>
										</div>
										<div class="sns-check">
											<input type="checkbox" value="1" id="follow_base_cache_target_feedly" name="follow_base_cache_target_feedly"<?php if ( isset( $this->follow_base_cache_target[self::REF_FOLLOW_FEEDLY] ) && $this->follow_base_cache_target[self::REF_FOLLOW_FEEDLY] ) echo ' checked="checked"'; ?> />
											<label for="follow_base_cache_target_feedly"><?php _e( 'Feedly', self::DOMAIN ); ?></label>
										</div>
										<div class="sns-check">
											<input type="checkbox" value="1" id="follow_base_cache_target_instagram" name="follow_base_cache_target_instagram"<?php if ( isset( $this->follow_base_cache_target[self::REF_FOLLOW_INSTAGRAM] ) && $this->follow_base_cache_target[self::REF_FOLLOW_INSTAGRAM] ) echo ' checked="checked"'; ?> />
											<label for="follow_base_cache_target_instagram"><?php _e( 'Instagram', self::DOMAIN ); ?></label>
										</div>
										<div class="sns-check">
											<input type="checkbox" value="1" id="follow_base_cache_target_push7" name="follow_base_cache_target_push7"<?php if ( isset( $this->follow_base_cache_target[self::REF_FOLLOW_PUSH7] ) && $this->follow_base_cache_target[self::REF_FOLLOW_PUSH7] ) echo ' checked="checked"'; ?> />
											<label for="follow_base_cache_target_push7"><?php _e( 'Push7', self::DOMAIN ); ?></label>
										</div>
										<div class="sns-check">
											<input type="checkbox" value="1" id="follow_base_cache_target_twitter" name="follow_base_cache_target_twitter"<?php if ( isset( $this->follow_base_cache_target[self::REF_FOLLOW_TWITTER] ) && $this->follow_base_cache_target[self::REF_FOLLOW_TWITTER] ) echo ' checked="checked"'; ?> />
											<label for="follow_base_cache_target_twitter"><?php _e( 'Twitter', self::DOMAIN ); ?></label>
										</div>
									</td>
								</tr>
								<tr>
									<th><label for="follow_base_check_interval"><?php _e( 'Interval cheking follower count (sec)', self::DOMAIN ); ?></label></th>
									<td>
										<input type="text" class="text" id="follow_base_check_interval" name="follow_base_check_interval" size="20" value="<?php echo esc_attr( $this->follow_base_check_interval); ?>" />
										<span class="description"><?php _e( 'Default: 86400 Minimum: 3600', self::DOMAIN ); ?></span>
									</td>
								</tr>
							</table>
							<div class="submit-button">
								<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />
								<input type="submit" class="button button-secondary" name="direct_follow_base_cache" value="<?php _e( 'Cache', self::DOMAIN ); ?>">
								<input type="submit" class="button button-secondary" name="clear_follow_base_cache" value="<?php _e( 'Clear Cache', self::DOMAIN ); ?>">
							</div>
						</div>
					</div>
				<?php if ( isset( $this->follow_base_cache_target[self::REF_FOLLOW_FACEBOOK] ) && $this->follow_base_cache_target[self::REF_FOLLOW_FACEBOOK] ) { ?>
					<div id="follow-base-cache-facebook" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php _e( 'Follow Base Cache - Facebook', self::DOMAIN ); ?></span></h3>
						<div class="inside">
							<table class="form-table">
								<tr>
									<th><label for="follow_facebook_page_id"><?php _e( 'Page ID', self::DOMAIN ); ?></label></th>
									<td>
										<input type="text" class="text" id="follow_facebook_page_id" name="follow_facebook_page_id" size="30" value="<?php echo esc_attr( $this->follow_facebook_page_id ); ?>" />
										<br>
										<span class="description"><?php _e( 'Facebook page ID that you want to get follower count', self::DOMAIN ); ?></span>
									</td>
								</tr>
								<tr>
									<th><label><?php _e( 'Developer page', self::DOMAIN ); ?></label></th>
									<td>
										<a href="https://developers.facebook.com/" target="_blank">https://developers.facebook.com/</a>
										<br>
										<span class="description"><?php _e( 'Register a application in Facebook developer page to get the following App ID and App Secret.', self::DOMAIN ); ?></span>
									</td>
								</tr>
								<tr>
									<th><label for="follow_facebook_app_id"><?php _e( 'App ID (Client ID)', self::DOMAIN ); ?></label></th>
									<td>
										<input type="password" id="follow_facebook_app_id" class="text" name="follow_facebook_app_id" size="60" value="<?php echo esc_attr( $this->follow_facebook_app_id ); ?>" />
										<br>
										<span class="description"><?php _e( 'App ID for Facebook API', self::DOMAIN ); ?></span>
									</td>
								</tr>
								<tr>
									<th><label for="follow_facebook_app_secret"><?php _e( 'App Secret (Client Secret)', self::DOMAIN ); ?></label></th>
									<td>
										<input type="password" id="follow_facebook_app_secret" class="text" name="follow_facebook_app_secret" size="60" value="<?php echo esc_attr( $this->follow_facebook_app_secret ); ?>" />
										<br>
										<span class="description"><?php _e( 'App Secret for Facebook API', self::DOMAIN ); ?></span>
									</td>
								</tr>
								<tr>
									<th><label for="tmp_follow_facebook_redirect_uri"><?php _e( 'Redirect URI', self::DOMAIN ); ?></label></th>
									<td>
										<input type="text" id="tmp_follow_facebook_redirect_uri" class="text" name="tmp_follow_facebook_redirect_uri" size="60" value="<?php echo esc_url( plugins_url() . '/sns-count-cache/' ); ?>" onclick="this.focus();this.select()" title="<?php _e( 'To copy, click the field then press Ctrl + C (PC) or Cmd + C (Mac).', self::DOMAIN );  ?>" readonly />
										<br>
										<span class="description"><?php  _e( 'Copy and set this to the field of "Valid OAuth redirect URIs" in application management page of Facebook developer page.',  self::DOMAIN ); ?></span>
									</td>
								</tr>
							<?php if ( isset( $_GET['action'] ) && $_GET['action'] && $_GET['action'] === 'facebook-auth' ) { ?>
								<tr>
									<th><label for="tmp_follow_facebook_access_token"><?php _e( 'Access Token', self::DOMAIN ); ?></label></th>
									<td>
										<?php if ( ! is_wp_error( $tmp_facebook_access_token ) ) {  ?>
											<input type="text" id="tmp_follow_facebook_access_token" class="text" name="tmp_follow_facebook_access_token" size="60" value="<?php echo esc_attr( $tmp_facebook_access_token ); ?>" onclick="this.focus();this.select()" title="<?php _e( 'To copy, click the field then press Ctrl + C (PC) or Cmd + C (Mac).', self::DOMAIN );  ?>" readonly />
											<br>
											<span class="description"><?php  _e( 'Copy and pase this into the fields below.',  self::DOMAIN ); ?></span>
										<?php } else { ?>
											<span class="update-message notice-error"><p><?php echo esc_html( $tmp_facebook_access_token->get_error_message() ); ?></p></span>
										<?php } ?>
									</td>
								</tr>
							<?php } ?>
							<?php if ( isset( $this->follow_facebook_page_id ) && $this->follow_facebook_page_id && isset( $this->follow_facebook_app_id ) && $this->follow_facebook_app_id && isset( $this->follow_facebook_app_secret ) && $this->follow_facebook_app_secret ) { ?>
								<tr>
									<th><label for="follow_facebook_access_token"><?php _e( 'Access Token', self::DOMAIN ); ?></label></th>
									<td>
										<input type="password" id="follow_facebook_access_token" class="text" name="follow_facebook_access_token" size="60" value="<?php echo esc_attr( $this->follow_facebook_access_token ); ?>" />
										<br>
										<span class="description"><?php _e( 'Access Token for Facebook API', self::DOMAIN ); ?></span>
									</td>
								</tr>
							<?php } ?>
							</table>
							<div class="submit-button">
								<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />
							<?php if ( isset( $this->follow_facebook_page_id ) && $this->follow_facebook_page_id && isset( $this->follow_facebook_app_id ) && $this->follow_facebook_app_id && isset( $this->follow_facebook_app_secret ) && $this->follow_facebook_app_secret ) { ?>
								<a href="https://www.facebook.com/dialog/oauth?client_id=<?php echo esc_attr( $this->follow_facebook_app_id ); ?>&scope=manage_pages,read_insights&state=<?php echo wp_create_nonce( 'facebook-auth' ); ?>&redirect_uri=<?php echo plugins_url() . '/sns-count-cache/'; ?>?action=facebook-auth" class="button button-secondary"><?php _e( 'Get Access Token', self::DOMAIN ); ?></a>
							<?php } ?>
							</div>
						</div>
					</div>
				<?php } ?>
				<?php if ( isset( $this->follow_base_cache_target[self::REF_FOLLOW_FEEDLY] ) && $this->follow_base_cache_target[self::REF_FOLLOW_FEEDLY] ) { ?>
					<div id="follow-base-cache-feedly" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php _e( 'Follow Base Cache - Feedly', self::DOMAIN ); ?></span></h3>
						<div class="inside">
							<table class="form-table">
								<tr>
									<th><label for="follow_feed_type"><?php _e( 'Target feed type', self::DOMAIN ); ?></label></th>
									<td>
										<select id="follow_feed_type" name="follow_feed_type">
											<option value="default"<?php if ( $this->follow_feed_type === self::OPT_FEED_TYPE_DEFAULT ) echo ' selected="selected"'; ?>><?php _e( 'Default', self::DOMAIN ) ?></option>
											<option value="rss"<?php if ( $this->follow_feed_type === self::OPT_FEED_TYPE_RSS ) echo ' selected="selected"'; ?>><?php _e( 'RSS', self::DOMAIN ); ?></option>
											<option value="rss2"<?php if ( $this->follow_feed_type === self::OPT_FEED_TYPE_RSS2 ) echo ' selected="selected"'; ?>><?php _e( 'RSS2', self::DOMAIN ); ?></option>
											<option value="rdf"<?php if ( $this->follow_feed_type === self::OPT_FEED_TYPE_RDF ) echo ' selected="selected"'; ?>><?php _e( 'RDF', self::DOMAIN ); ?></option>
											<option value="atom"<?php if ( $this->follow_feed_type === self::OPT_FEED_TYPE_ATOM ) echo ' selected="selected"'; ?>><?php _e( 'ATOM', self::DOMAIN ); ?></option>
										</select>
										<span class="description"><?php _e( 'Default: Default', self::DOMAIN ); ?></span>
									</td>
								</tr>
								<tr>
									<th><label><?php _e( 'Target feed', self::DOMAIN ); ?></label></th>
									<td><a href="<?php echo esc_url( get_feed_link( $this->follow_feed_type ) ); ?>" target="_blank"><?php echo esc_html( get_feed_link( $this->follow_feed_type ) ); ?></a></td>
								</tr>
							</table>
							<div class="submit-button">
								<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />
							</div>
						</div>
					</div>
				<?php } ?>
				<?php if ( isset( $this->follow_base_cache_target[self::REF_FOLLOW_INSTAGRAM] ) && $this->follow_base_cache_target[self::REF_FOLLOW_INSTAGRAM] ) { ?>
					<div id="follow-base-cache-instagram" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php _e( 'Follow Base Cache - Instagram', self::DOMAIN ); ?></span></h3>
						<div class="inside">
							<table class="form-table">
								<tr>
									<th><label><?php _e( 'Developer page', self::DOMAIN ); ?></label></th>
									<td>
										<a href="https://www.instagram.com/developer/" target="_blank">https://www.instagram.com/developer/</a>
										<br>
										<span class="description"><?php _e( 'Register a client in Instagram developer page to get the following Client ID and Client Secret.', self::DOMAIN ); ?></span>
									</td>
								</tr>
								<tr>
									<th><label for="follow_instagram_client_id"><?php _e( 'Client ID', self::DOMAIN ); ?></label></th>
									<td>
										<input type="password" id="follow_instagram_client_id" class="text" name="follow_instagram_client_id" size="60" value="<?php echo esc_attr( $this->follow_instagram_client_id ); ?>" />
										<br>
										<span class="description"><?php _e( 'Client ID for Instagram API', self::DOMAIN ); ?></span>
									</td>
								</tr>
								<tr>
									<th><label for="follow_instagram_client_secret"><?php _e( 'Client Secret', self::DOMAIN ); ?></label></th>
									<td>
										<input type="password" id="follow_instagram_client_secret" class="text" name="follow_instagram_client_secret" size="60" value="<?php echo esc_attr( $this->follow_instagram_client_secret ); ?>" />
										<br>
										<span class="description"><?php _e( 'Client Secret for Instagram API', self::DOMAIN ); ?></span>
									</td>
								</tr>
								<tr>
									<th><label for="tmp_follow_instagram_redirect_uri"><?php _e( 'Redirect URI', self::DOMAIN ); ?></label></th>
									<td>
										<input type="text" id="tmp_follow_instagram_redirect_uri" class="text" name="tmp_follow_instagram_redirect_uri" size="60" value="<?php echo esc_url( plugins_url() . '/sns-count-cache/' ); ?>" onclick="this.focus();this.select()" title="<?php _e( 'To copy, click the field then press Ctrl + C (PC) or Cmd + C (Mac).', self::DOMAIN );  ?>" readonly />
										<br>
										<span class="description"><?php  _e( 'Copy and set this to the field of "Valid redirect URIs" in the client management page of Instagram developer page.',  self::DOMAIN ); ?></span>
									</td>
								</tr>
							<?php if ( isset( $_GET['action'] ) && $_GET['action'] && $_GET['action'] === 'instagram-auth' ) { ?>
								<tr>
									<th><label for="tmp_follow_instagram_access_token"><?php _e( 'Access Token', self::DOMAIN ); ?></label></th>
									<td>
										<?php if ( ! is_wp_error( $tmp_instagram_access_token ) ) {  ?>
											<input type="text" id="tmp_follow_instagram_access_token" class="text" name="tmp_follow_instagram_access_token" size="60" value="<?php echo esc_attr( $tmp_instagram_access_token ); ?>" onclick="this.focus();this.select()" title="<?php _e( 'To copy, click the field then press Ctrl + C (PC) or Cmd + C (Mac).', self::DOMAIN );  ?>" readonly />
											<br>
											<span class="description"><?php  _e( 'Copy and pase this into the fields below.',  self::DOMAIN ); ?></span>
										<?php } else { ?>
											<span class="update-message notice-error"><p><?php echo esc_html( $tmp_instagram_access_token->get_error_message() ); ?></p></span>
										<?php } ?>
									</td>
								</tr>
							<?php } ?>
							<?php if ( isset( $this->follow_instagram_client_id ) && $this->follow_instagram_client_id && isset( $this->follow_instagram_client_secret ) && $this->follow_instagram_client_secret ) { ?>
								<tr>
									<th><label for="follow_instagram_access_token"><?php _e( 'Access Token', self::DOMAIN ); ?></label></th>
									<td>
										<input type="password" id="follow_instagram_access_token" class="text" name="follow_instagram_access_token" size="60" value="<?php echo esc_attr( $this->follow_instagram_access_token ); ?>" />
										<br>
										<span class="description"><?php _e( 'Access Token for Instagram API', self::DOMAIN ); ?></span>
									</td>
								</tr>
							<?php } ?>
							</table>
							<div class="submit-button">
								<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />
							<?php if ( isset( $this->follow_instagram_client_id ) && $this->follow_instagram_client_id && isset( $this->follow_instagram_client_secret ) && $this->follow_instagram_client_secret ) { ?>
								<a href="https://api.instagram.com/oauth/authorize/?client_id=<?php echo esc_attr( $this->follow_instagram_client_id ); ?>&response_type=code&state=<?php echo wp_create_nonce( 'instagram-auth' ); ?>&redirect_uri=<?php echo plugins_url() . '/sns-count-cache/'; ?>?action=instagram-auth" class="button button-secondary"><?php _e( 'Get Access Token', self::DOMAIN ); ?></a>
							<?php } ?>
							</div>
						</div>
					</div>
				<?php } ?>
				<?php if ( isset( $this->follow_base_cache_target[self::REF_FOLLOW_PUSH7] ) && $this->follow_base_cache_target[self::REF_FOLLOW_PUSH7] ) { ?>
					<div id="follow-base-cache-push7" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php _e( 'Follow Base Cache - Push7', self::DOMAIN ); ?></span></h3>
						<div class="inside">
							<table class="form-table">
								<tr>
									<th><label><?php _e( 'Push7 page', self::DOMAIN ); ?></label></th>
									<td>
										<a href="https://push7.jp/" target="_blank">https://push7.jp/</a>
										<br>
										<span class="description"><?php _e( 'Register a application in Push7 page to get the following AppNo.', self::DOMAIN ); ?></span>
									</td>
								</tr>
								<tr>
									<th><label for="follow_push7_appno"><?php _e( 'AppNo', self::DOMAIN ); ?></label></th>
									<td>
										<input type="password" id="follow_push7_appno" class="text" name="follow_push7_appno" size="60" value="<?php echo esc_attr( $this->follow_push7_appno ); ?>" />
										<br>
										<span class="description"><?php _e( 'AppNo for Push7 API', self::DOMAIN ); ?></span>
									</td>
								</tr>
							</table>
							<div class="submit-button">
								<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />
							</div>
						</div>
					</div>
				<?php } ?>
				<?php if ( isset( $this->follow_base_cache_target[self::REF_FOLLOW_TWITTER] ) && $this->follow_base_cache_target[self::REF_FOLLOW_TWITTER] ) { ?>
					<div id="follow-base-cache-twitter" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php _e( 'Follow Base Cache - Twitter', self::DOMAIN ); ?></span></h3>
						<div class="inside">
							<table class="form-table">
								<tr>
									<th><label for="follow_twitter_screen_name"><?php _e( 'Screen Name', self::DOMAIN ); ?></label></th>
									<td>
										<span class="at-mark">@</span>
										<input type="text" id="follow_twitter_screen_name" class="text" name="follow_twitter_screen_name" size="30" value="<?php echo esc_attr( $this->follow_twitter_screen_name ); ?>" />
										<br>
										<span class="description"><?php _e( 'Twitter screen name that you want to get follower count', self::DOMAIN ); ?></span>
									</td>
								</tr>
								<tr>
									<th><label><?php _e( 'Developer page', self::DOMAIN ); ?></label></th>
									<td>
										<a href="https://dev.twitter.com/" target="_blank">https://dev.twitter.com/</a>
										<br>
										<span class="description"><?php _e( 'Register a application in Twitter developer page to get the following Consumer Key and Consumer Secret.', self::DOMAIN ); ?></span>
									</td>
								</tr>
								<tr>
									<th><label for="follow_twitter_consumer_key"><?php _e( 'Consumer Key (API Key)', self::DOMAIN ); ?></label></th>
									<td>
										<input type="password" id="follow_twitter_consumer_key" class="text" name="follow_twitter_consumer_key" size="60" value="<?php echo esc_attr( $this->follow_twitter_consumer_key ); ?>" />
										<br>
										<span class="description"><?php _e( 'Cconsumer Key for Twitter API', self::DOMAIN ); ?></span>
									</td>
								</tr>
								<tr>
									<th><label for="follow_twitter_consumer_secret"><?php _e( 'Consumer Secret (API Secret)', self::DOMAIN ); ?></label></th>
									<td>
										<input type="password" id="follow_twitter_consumer_secret" class="text" name="follow_twitter_consumer_secret" size="60" value="<?php echo esc_attr( $this->follow_twitter_consumer_secret ); ?>" />
										<br>
										<span class="description"><?php _e( 'Consumer Secret for Twitter API', self::DOMAIN ); ?></span>
									</td>
								</tr>
							<?php if ( isset( $_POST["get_tiwtter_bearer_token"] ) && $_POST["get_tiwtter_bearer_token"] === __( 'Get Bearer Token', self::DOMAIN ) ) { ?>
								<tr>
									<th><label for="tmp_follow_twitter_bearer_token"><?php _e( 'Bearer Token', self::DOMAIN ); ?></label></th>
									<td>
										<?php if ( ! is_wp_error( $tmp_twitter_bearer_token ) ) {  ?>
											<input type="text" id="tmp_follow_twitter_bearer_token" class="text" name="tmp_follow_twitter_bearer_token" size="60" value="<?php echo esc_attr( $tmp_twitter_bearer_token ); ?>" onclick="this.focus();this.select()" title="<?php _e( 'To copy, click the field then press Ctrl + C (PC) or Cmd + C (Mac).', self::DOMAIN );  ?>" readonly />
											<br>
											<span class="description"><?php  _e( 'Copy and pase this into the fields below.',  self::DOMAIN ); ?></span>
										<?php } else { ?>
											<span class="update-message notice-error"><p><?php echo esc_html( $tmp_twitter_bearer_token->get_error_message() ); ?></p></span>
										<?php } ?>
									</td>
								</tr>
							<?php } ?>
							<?php if ( isset( $this->follow_twitter_consumer_key ) && $this->follow_twitter_consumer_key && isset( $this->follow_twitter_consumer_secret ) && $this->follow_twitter_consumer_secret ) { ?>
								<tr>
									<th><label for="follow_twitter_bearer_token"><?php _e( 'Bearer Token', self::DOMAIN ); ?></label></th>
									<td>
										<input type="password" id="follow_twitter_bearer_token" class="text" name="follow_twitter_bearer_token" size="60" value="<?php echo esc_attr( $this->follow_twitter_bearer_token ); ?>" />
										<br>
										<span class="description"><?php _e( 'Bearer Token for Twitter API', self::DOMAIN ); ?></span>
									</td>
								</tr>
							<?php } ?>
							</table>
							<div class="submit-button">
								<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />
							<?php if ( isset( $this->follow_twitter_consumer_key ) && $this->follow_twitter_consumer_key && isset( $this->follow_twitter_consumer_secret ) && $this->follow_twitter_consumer_secret ) { ?>
								<input type="submit" class="button button-secondary" name="get_tiwtter_bearer_token" value="<?php _e( 'Get Bearer Token', self::DOMAIN ); ?>" />
							<?php } ?>
							</div>
						</div>
					</div>
				<?php } ?>
					<div id="follow-variation-analysis" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php _e( 'Follow Variation Analysis', self::DOMAIN ); ?></span></h3>
						<div class="inside">
							<table class="form-table">
								<tr>
									<th><label for="follow_variation_analysis_mode"><?php _e( 'Method to update basis of comparison', self::DOMAIN ); ?></label></th>
									<td>
										<select id="follow_variation_analysis_mode" name="follow_variation_analysis_mode">
											<option value="1"<?php if ( $this->follow_variation_analysis_mode === self::OPT_FOLLOW_VARIATION_ANALYSIS_NONE ) echo ' selected="selected"'; ?>><?php _e( 'Disabled (None)', self::DOMAIN ); ?></option>
											<option value="2"<?php if ( $this->follow_variation_analysis_mode === self::OPT_FOLLOW_VARIATION_ANALYSIS_MANUAL ) echo ' selected="selected"'; ?>><?php _e( 'Enabled (Manual)', self::DOMAIN ); ?></option>
											<option value="3"<?php if ( $this->follow_variation_analysis_mode === self::OPT_FOLLOW_VARIATION_ANALYSIS_SCHEDULER ) echo ' selected="selected"'; ?>><?php _e( 'Enabled (Scheduler)', self::DOMAIN ); ?></option>
										</select>
										<span class="description"><?php _e( 'Disabled (None)', self::DOMAIN ); ?></span>
									</td>
								</tr>
							<?php
								if ( $this->follow_variation_analysis_mode === self::OPT_FOLLOW_VARIATION_ANALYSIS_SCHEDULER ) {
									list( $cronstr['minutes'], $cronstr['hours'], $cronstr['mday'], $cronstr['mon'], $cronstr['wday'] ) = explode( ' ', $this->follow_variation_analysis_schedule, 5 );
									if ( strstr( $cronstr['minutes'], '*/' ) ) {
										$minutes = explode( '/', $cronstr['minutes'] );
									} else {
										$minutes = explode( ',', $cronstr['minutes'] );
									}
									if ( strstr( $cronstr['hours'], '*/' ) ) {
										$hours = explode( '/', $cronstr['hours'] );
									} else {
										$hours = explode( ',', $cronstr['hours'] );
									}
									if ( strstr( $cronstr['mday'], '*/' ) ) {
										$mday = explode( '/', $cronstr['mday'] );
									} else {
										$mday = explode( ',', $cronstr['mday'] );
									}
									if ( strstr( $cronstr['mon'], '*/' ) ) {
										$mon = explode( '/', $cronstr['mon'] );
									} else {
										$mon = explode( ',', $cronstr['mon'] );
									}
									if ( strstr( $cronstr['wday'], '*/' ) ) {
										$wday = explode( '/', $cronstr['wday'] );
									} else {
										$wday = explode( ',', $cronstr['wday'] );
									}
							?>
								<tr class="a_wpcron">
									<th scope="row"><?php _e( 'Scheduler', self::DOMAIN ); ?></th>
									<td>
										<table class="wpcron">
											<tr>
												<th>
													<?php _e( 'Type', self::DOMAIN ); ?>
												</th>
												<th>
												</th>
												<th>
													<?php _e( 'Hour', self::DOMAIN ); ?>
												</th>
												<th>
													<?php _e( 'Minute', self::DOMAIN ); ?>
												</th>
											</tr>
											<tr>
												<td>
													<label for="idcronbtype-mon">
														<?php echo '<input class="radio" type="radio"' . checked( TRUE, is_numeric( $mday[0] ), FALSE ) . ' name="b_cronbtype" value="mon" /> ' . __( 'monthly', self::DOMAIN ); ?>
													</label>
												</td>
												<td>
													<select name="b_moncronmday">
														<?php
															for ( $i = 1; $i <= 31; $i ++ ) {
																$on_day = '';

																switch ( $i ) {
																	case 1:
																		$on_day = __( 'on 1.', self::DOMAIN );
																		break;
																	case 2:
																		$on_day = __( 'on 2.', self::DOMAIN );
																		break;
																	case 3:
																		$on_day = __( 'on 3.', self::DOMAIN );
																		break;
																	case 4:
																		$on_day = __( 'on 4.', self::DOMAIN );
																		break;
																	case 5:
																		$on_day = __( 'on 5.', self::DOMAIN );
																		break;
																	case 6:
																		$on_day = __( 'on 6.', self::DOMAIN );
																		break;
																	case 7:
																		$on_day = __( 'on 7.', self::DOMAIN );
																		break;
																	case 8:
																		$on_day = __( 'on 8.', self::DOMAIN );
																		break;
																	case 9:
																		$on_day = __( 'on 9.', self::DOMAIN );
																		break;
																	case 10:
																		$on_day = __( 'on 10.', self::DOMAIN );
																		break;
																	case 11:
																		$on_day = __( 'on 11.', self::DOMAIN );
																		break;
																	case 12:
																		$on_day = __( 'on 12.', self::DOMAIN );
																		break;
																	case 13:
																		$on_day = __( 'on 13.', self::DOMAIN );
																		break;
																	case 14:
																		$on_day = __( 'on 14.', self::DOMAIN );
																		break;
																	case 15:
																		$on_day = __( 'on 15.', self::DOMAIN );
																		break;
																	case 16:
																		$on_day = __( 'on 16.', self::DOMAIN );
																		break;
																	case 17:
																		$on_day = __( 'on 17.', self::DOMAIN );
																		break;
																	case 18:
																		$on_day = __( 'on 18.', self::DOMAIN );
																		break;
																	case 19:
																		$on_day = __( 'on 19.', self::DOMAIN );
																		break;
																	case 20:
																		$on_day = __( 'on 20.', self::DOMAIN );
																		break;
																	case 21:
																		$on_day = __( 'on 21.', self::DOMAIN );
																		break;
																	case 22:
																		$on_day = __( 'on 22.', self::DOMAIN );
																		break;
																	case 23:
																		$on_day = __( 'on 23.', self::DOMAIN );
																		break;
																	case 24:
																		$on_day = __( 'on 24.', self::DOMAIN );
																		break;
																	case 25:
																		$on_day = __( 'on 25.', self::DOMAIN );
																		break;
																	case 26:
																		$on_day = __( 'on 26.', self::DOMAIN );
																		break;
																	case 27:
																		$on_day = __( 'on 27.', self::DOMAIN );
																		break;
																	case 28:
																		$on_day = __( 'on 28.', self::DOMAIN );
																		break;
																	case 29:
																		$on_day = __( 'on 29.', self::DOMAIN );
																		break;
																	case 30:
																		$on_day = __( 'on 30.', self::DOMAIN );
																		break;
																	case 31:
																		$on_day = __( 'on 31.', self::DOMAIN );
																		break;
																}

																echo '<option ' . selected( in_array( "$i", $mday, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $on_day . '</option>';
															}
														?>
													</select>
												</td>
												<td>
													<select name="b_moncronhours">
														<?php for ( $i = 0; $i < 24; $i ++ ) {
															echo '<option ' . selected( in_array( "$i", $hours, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
														} ?>
													</select>
												</td>
												<td>
													<select name="b_moncronminutes">
														<?php for ( $i = 0; $i < 60; $i = $i + 5 ) {
															echo '<option ' . selected( in_array( "$i", $minutes, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
														} ?>
													</select>
												</td>
											</tr>
											<tr>
												<td>
													<label for="idcronbtype-week">
														<?php echo '<input class="radio" type="radio"' . checked( TRUE, is_numeric( $wday[0] ), FALSE ) . ' name="a_cronbtype" value="week" /> ' . __( 'weekly', self::DOMAIN ); ?>
													</label>
												</td>
												<td>
													<select name="b_weekcronwday">
														<?php
															echo '<option ' . selected( in_array( '0', $wday, TRUE ), TRUE, FALSE ) . '  value="0" />' . __( 'Sunday', self::DOMAIN ) . '</option>';
															echo '<option ' . selected( in_array( '1', $wday, TRUE ), TRUE, FALSE ) . '  value="1" />' . __( 'Monday', self::DOMAIN ) . '</option>';
															echo '<option ' . selected( in_array( '2', $wday, TRUE ), TRUE, FALSE ) . '  value="2" />' . __( 'Tuesday', self::DOMAIN ) . '</option>';
															echo '<option ' . selected( in_array( '3', $wday, TRUE ), TRUE, FALSE ) . '  value="3" />' . __( 'Wednesday', self::DOMAIN ) . '</option>';
															echo '<option ' . selected( in_array( '4', $wday, TRUE ), TRUE, FALSE ) . '  value="4" />' . __( 'Thursday', self::DOMAIN ) . '</option>';
															echo '<option ' . selected( in_array( '5', $wday, TRUE ), TRUE, FALSE ) . '  value="5" />' . __( 'Friday', self::DOMAIN ) . '</option>';
															echo '<option ' . selected( in_array( '6', $wday, TRUE ), TRUE, FALSE ) . '  value="6" />' . __( 'Saturday', self::DOMAIN ) . '</option>';
														?>
													</select>
												</td>
												<td>
													<select name="b_weekcronhours">
														<?php for ( $i = 0; $i < 24; $i ++ ) {
															echo '<option ' . selected( in_array( "$i", $hours, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
														} ?>
													</select>
												</td>
												<td>
													<select name="b_weekcronminutes">
														<?php for ( $i = 0; $i < 60; $i = $i + 5 ) {
															echo '<option ' . selected( in_array( "$i", $minutes, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
														} ?>
													</select>
												</td>
											</tr>
											<tr>
												<td>
													<label for="idcronbtype-day">
														<?php echo '<input class="radio" type="radio"' . checked( "**", $mday[0] . $wday[0], FALSE ) . ' name="b_cronbtype" value="day" /> ' . __( 'daily', self::DOMAIN ); ?>
													</label>
												</td>
												<td>
												</td>
												<td>
													<select name="b_daycronhours">
														<?php
															for ( $i = 0; $i < 24; $i ++ ) {
																echo '<option ' . selected( in_array( "$i", $hours, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
															}
														?>
													</select>
												</td>
												<td>
													<select name="b_daycronminutes">
														<?php for ( $i = 0; $i < 60; $i = $i + 5 ) {
															echo '<option ' . selected( in_array( "$i", $minutes, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
														} ?>
													</select>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							<?php
								}
							?>
							</table>
							<div class="submit-button">
								<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />
								<input type="submit" class="button button-secondary" name="update_follow_comparison_base" value="<?php _e( 'Update Basis of Comparison', self::DOMAIN ); ?>" />
							</div>
						</div>
					</div>
					<div id="common-dynamic-cache" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php _e( 'Dynamic Cache', self::DOMAIN ); ?></span></h3>
						<div class="inside">
							<table class="form-table">
								<tr>
									<th><label for="dynamic_cache_mode"><?php _e( 'Dynamic caching based on user access', self::DOMAIN ); ?></label></th>
									<td>
										<select id="dynamic_cache_mode" name="dynamic_cache_mode">
											<option value="1"<?php if ( $this->dynamic_cache_mode === self::OPT_COMMON_ACCESS_BASED_CACHE_OFF ) echo ' selected="selected"'; ?>><?php _e( 'Disabled', self::DOMAIN ); ?></option>
											<option value="5"<?php if ( $this->dynamic_cache_mode === self::OPT_COMMON_ACCESS_BASED_CACHE_ON ) echo ' selected="selected"'; ?>><?php _e( 'Enabled', self::DOMAIN ); ?></option>
										</select>
										<span class="description"><?php _e( 'Default: Disabled', self::DOMAIN ); ?></span>
									</td>
								</tr>
							</table>
							<div class="submit-button">
								<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />
							</div>
						</div>
					</div>
					<div id="common-fault-tolerance" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php _e( 'Fault Tolerance', self::DOMAIN ); ?></span></h3>
						<div class="inside">
							<table class="form-table">
								<tr>
									<th><label for="fault_tolerance_mode"><?php _e( 'Fault tolerant mode of count retrieval', self::DOMAIN ); ?></label></th>
									<td>
										<select id="fault_tolerance_mode" name="fault_tolerance_mode">
											<option value="1"<?php if ( $this->fault_tolerance_mode === self::OPT_COMMON_FAULT_TOLERANCE_OFF ) echo ' selected="selected"'; ?>><?php _e( 'Disabled', self::DOMAIN ); ?></option>
											<option value="2"<?php if ( $this->fault_tolerance_mode === self::OPT_COMMON_FAULT_TOLERANCE_ON ) echo ' selected="selected"'; ?>><?php _e( 'Enabled', self::DOMAIN ); ?></option>
										</select>
										<span class="description"><?php _e( 'Default: Disabled', self::DOMAIN ); ?></span>
									</td>
								</tr>
							</table>
							<div class="submit-button">
								<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />
							</div>
						</div>
					</div>
					<div id="common-data-crawler" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php _e( 'Data Crawler', self::DOMAIN ); ?></span></h3>
						<div class="inside">
							<table class="form-table">
								<tr>
									<th><label><?php _e( 'Crawl method', self::DOMAIN ); ?></label></th>
									<td>
										<?php
											switch ( $this->crawler_method ) {
												case self::OPT_COMMON_CRAWLER_METHOD_NORMAL:
													_e( 'Normal (Sequential Retrieval)', self::DOMAIN );
													break;
												case self::OPT_COMMON_CRAWLER_METHOD_CURL:
													_e( 'Extended (Parallel Retrieval)', self::DOMAIN );
													break;
											}
										?>
									</td>
								</tr>
								<tr>
									<th><label for="common_data_crawler_retry_limit"><?php _e( 'Crawl retry limit', self::DOMAIN ); ?></label></th>
									<td>
										<select id="common_data_crawler_retry_limit" name="common_data_crawler_retry_limit">
											<option value="-1"<?php if ( $this->crawler_retry_limit === -1 ) echo ' selected="selected"'; ?>><?php _e( 'no retry', self::DOMAIN ); ?></option>
											<option value="1"<?php if ( $this->crawler_retry_limit === 1 ) echo ' selected="selected"'; ?>><?php _e( '1 time', self::DOMAIN ); ?></option>
											<option value="2"<?php if ( $this->crawler_retry_limit === 2 ) echo ' selected="selected"'; ?>><?php _e( '2 times', self::DOMAIN ); ?></option>
											<option value="3"<?php if ( $this->crawler_retry_limit === 3 ) echo ' selected="selected"'; ?>><?php _e( '3 times', self::DOMAIN ); ?></option>
											<option value="4"<?php if ( $this->crawler_retry_limit === 4 ) echo ' selected="selected"'; ?>><?php _e( '4 times', self::DOMAIN ); ?></option>
											<option value="5"<?php if ( $this->crawler_retry_limit === 5 ) echo ' selected="selected"'; ?>><?php _e( '5 times', self::DOMAIN ); ?></option>
										</select>
										<span class="description"><?php _e( 'Default: 1 time', self::DOMAIN ); ?></span>
									</td>
								</tr>
								<tr>
									<th><label for="crawler_ssl_verification"><?php _e( 'SSL verification', self::DOMAIN ); ?></label></th>
									<td>
										<select id="crawler_ssl_verification" name="crawler_ssl_verification">
											<option value="0"<?php if ( $this->crawler_ssl_verification === self::OPT_COMMON_CRAWLER_SSL_VERIFY_OFF ) echo ' selected="selected"'; ?>><?php _e( 'Off', self::DOMAIN ); ?></option>
											<option value="1"<?php if ( $this->crawler_ssl_verification === self::OPT_COMMON_CRAWLER_SSL_VERIFY_ON ) echo ' selected="selected"'; ?>><?php _e( 'On', self::DOMAIN ); ?></option>
										</select>
										<span class="description"><?php _e( 'Default: On', self::DOMAIN ); ?></span>
									</td>
								</tr>
							</table>
							<div class="submit-button">
								<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />
							</div>
						</div>
					</div>
					<div id="common-data-export" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php _e( 'Data Export', self::DOMAIN ); ?></span></h3>
						<div class="inside">
							<table class="form-table">
								<tr>
									<th><label for="data_export_mode"><?php _e( 'Method of data export', self::DOMAIN ); ?></label></th>
									<td>
										<select id="data_export_mode" name="data_export_mode">
											<option value="1"<?php if ( $this->data_export_mode === self::OPT_COMMON_DATA_EXPORT_MANUAL ) echo ' selected="selected"'; ?>><?php _e( 'Manual', self::DOMAIN ); ?></option>
											<option value="2"<?php if ( $this->data_export_mode === self::OPT_COMMON_DATA_EXPORT_SCHEDULER ) echo ' selected="selected"'; ?> disabled="disabled"><?php _e( 'Scheduler', self::DOMAIN ); ?></option>
										</select>
										<span class="description"><?php _e( 'Default: Manual', self::DOMAIN ); ?></span>
									</td>
								</tr>
							<?php
								if ( $this->data_export_mode === self::OPT_COMMON_DATA_EXPORT_SCHEDULER ) {
									list( $cronstr[ 'minutes' ], $cronstr[ 'hours' ], $cronstr[ 'mday' ], $cronstr[ 'mon' ], $cronstr[ 'wday' ] ) = explode( ' ', $this->data_export_schedule, 5 );
									if ( strstr( $cronstr[ 'minutes' ], '*/' ) ) {
										$minutes = explode( '/', $cronstr[ 'minutes' ] );
									} else {
										$minutes = explode( ',', $cronstr[ 'minutes' ] );
									}
									if ( strstr( $cronstr[ 'hours' ], '*/' ) ) {
										$hours = explode( '/', $cronstr[ 'hours' ] );
									} else {
										$hours = explode( ',', $cronstr[ 'hours' ] );
									}
									if ( strstr( $cronstr[ 'mday' ], '*/' ) ) {
										$mday = explode( '/', $cronstr[ 'mday' ] );
									} else {
										$mday = explode( ',', $cronstr[ 'mday' ] );
									}
									if ( strstr( $cronstr[ 'mon' ], '*/' ) ) {
										$mon = explode( '/', $cronstr[ 'mon' ] );
									} else {
										$mon = explode( ',', $cronstr[ 'mon' ] );
									}
									if ( strstr( $cronstr[ 'wday' ], '*/' ) ) {
										$wday = explode( '/', $cronstr[ 'wday' ] );
									} else {
										$wday = explode( ',', $cronstr[ 'wday' ] );
									}
							?>
								<tr class="e_wpcron">
									<th scope="row"><?php _e( 'Scheduler', self::DOMAIN ); ?></th>
									<td>
										<table class="wpcron">
											<tr>
												<th>
													<?php _e( 'Type', self::DOMAIN ); ?>
												</th>
												<th>
												</th>
												<th>
													<?php _e( 'Hour', self::DOMAIN ); ?>
												</th>
												<th>
													<?php _e( 'Minute', self::DOMAIN ); ?>
												</th>
											</tr>
											<tr>
												<td>
													<label for="idcronbtype-mon">
														<?php echo '<input class="radio" type="radio"' . checked( TRUE, is_numeric( $mday[0] ), FALSE ) . ' name="e_cronbtype" value="mon" /> ' . __( 'monthly', self::DOMAIN ); ?>
													</label>
												</td>
												<td>
													<select name="e_moncronmday">
														<?php
															for ( $i = 1; $i <= 31; $i ++ ) {
																$on_day = '';

																switch ( $i ) {
																	case 1:
																		$on_day = __( 'on 1.', self::DOMAIN );
																		break;
																	case 2:
																		$on_day = __( 'on 2.', self::DOMAIN );
																		break;
																	case 3:
																		$on_day = __( 'on 3.', self::DOMAIN );
																		break;
																	case 4:
																		$on_day = __( 'on 4.', self::DOMAIN );
																		break;
																	case 5:
																		$on_day = __( 'on 5.', self::DOMAIN );
																		break;
																	case 6:
																		$on_day = __( 'on 6.', self::DOMAIN );
																		break;
																	case 7:
																		$on_day = __( 'on 7.', self::DOMAIN );
																		break;
																	case 8:
																		$on_day = __( 'on 8.', self::DOMAIN );
																		break;
																	case 9:
																		$on_day = __( 'on 9.', self::DOMAIN );
																		break;
																	case 10:
																		$on_day = __( 'on 10.', self::DOMAIN );
																		break;
																	case 11:
																		$on_day = __( 'on 11.', self::DOMAIN );
																		break;
																	case 12:
																		$on_day = __( 'on 12.', self::DOMAIN );
																		break;
																	case 13:
																		$on_day = __( 'on 13.', self::DOMAIN );
																		break;
																	case 14:
																		$on_day = __( 'on 14.', self::DOMAIN );
																		break;
																	case 15:
																		$on_day = __( 'on 15.', self::DOMAIN );
																		break;
																	case 16:
																		$on_day = __( 'on 16.', self::DOMAIN );
																		break;
																	case 17:
																		$on_day = __( 'on 17.', self::DOMAIN );
																		break;
																	case 18:
																		$on_day = __( 'on 18.', self::DOMAIN );
																		break;
																	case 19:
																		$on_day = __( 'on 19.', self::DOMAIN );
																		break;
																	case 20:
																		$on_day = __( 'on 20.', self::DOMAIN );
																		break;
																	case 21:
																		$on_day = __( 'on 21.', self::DOMAIN );
																		break;
																	case 22:
																		$on_day = __( 'on 22.', self::DOMAIN );
																		break;
																	case 23:
																		$on_day = __( 'on 23.', self::DOMAIN );
																		break;
																	case 24:
																		$on_day = __( 'on 24.', self::DOMAIN );
																		break;
																	case 25:
																		$on_day = __( 'on 25.', self::DOMAIN );
																		break;
																	case 26:
																		$on_day = __( 'on 26.', self::DOMAIN );
																		break;
																	case 27:
																		$on_day = __( 'on 27.', self::DOMAIN );
																		break;
																	case 28:
																		$on_day = __( 'on 28.', self::DOMAIN );
																		break;
																	case 29:
																		$on_day = __( 'on 29.', self::DOMAIN );
																		break;
																	case 30:
																		$on_day = __( 'on 30.', self::DOMAIN );
																		break;
																	case 31:
																		$on_day = __( 'on 31.', self::DOMAIN );
																		break;
																}

																echo '<option ' . selected( in_array( "$i", $mday, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $on_day . '</option>';
															}
														?>
													</select>
												</td>
												<td>
													<select name="e_moncronhours">
														<?php
															for ( $i = 0; $i < 24; $i ++ ) {
																echo '<option ' . selected( in_array( "$i", $hours, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
															}
														?>
													</select>
												</td>
												<td>
													<select name="e_moncronminutes">
														<?php
															for ( $i = 0; $i < 60; $i = $i + 5 ) {
																echo '<option ' . selected( in_array( "$i", $minutes, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
															}
														?>
													</select>
												</td>
											</tr>
											<tr>
												<td>
													<label for="idcronbtype-week">
														<?php echo '<input class="radio" type="radio"' . checked( TRUE, is_numeric( $wday[0] ), FALSE ) . ' name="e_cronbtype" value="week" /> ' . __( 'weekly', self::DOMAIN ); ?>
													</label>
												</td>
												<td>
													<select name="e_weekcronwday">
														<?php
															echo '<option ' . selected( in_array( '0', $wday, TRUE ), TRUE, FALSE ) . '  value="0" />' . __( 'Sunday', self::DOMAIN ) . '</option>';
															echo '<option ' . selected( in_array( '1', $wday, TRUE ), TRUE, FALSE ) . '  value="1" />' . __( 'Monday', self::DOMAIN ) . '</option>';
															echo '<option ' . selected( in_array( '2', $wday, TRUE ), TRUE, FALSE ) . '  value="2" />' . __( 'Tuesday', self::DOMAIN ) . '</option>';
															echo '<option ' . selected( in_array( '3', $wday, TRUE ), TRUE, FALSE ) . '  value="3" />' . __( 'Wednesday', self::DOMAIN ) . '</option>';
															echo '<option ' . selected( in_array( '4', $wday, TRUE ), TRUE, FALSE ) . '  value="4" />' . __( 'Thursday', self::DOMAIN ) . '</option>';
															echo '<option ' . selected( in_array( '5', $wday, TRUE ), TRUE, FALSE ) . '  value="5" />' . __( 'Friday', self::DOMAIN ) . '</option>';
															echo '<option ' . selected( in_array( '6', $wday, TRUE ), TRUE, FALSE ) . '  value="6" />' . __( 'Saturday', self::DOMAIN ) . '</option>';
														?>
													</select>
												</td>
												<td>
													<select name="e_weekcronhours">
														<?php
															for ( $i = 0; $i < 24; $i ++ ) {
																echo '<option ' . selected( in_array( "$i", $hours, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
															}
														?>
													</select>
												</td>
												<td>
													<select name="e_weekcronminutes">
														<?php
															for ( $i = 0; $i < 60; $i = $i + 5 ) {
																echo '<option ' . selected( in_array( "$i", $minutes, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
															}
														?>
													</select>
												</td>
											</tr>
											<tr>
												<td>
													<label for="idcronbtype-day">
														<?php echo '<input class="radio" type="radio"' . checked( "**", $mday[0] . $wday[0], FALSE ) . ' name="e_cronbtype" value="day" /> ' . __( 'daily', self::DOMAIN ); ?>
													</label>
												</td>
												<td>
												</td>
												<td>
													<select name="e_daycronhours">
														<?php
															for ( $i = 0; $i < 24; $i ++ ) {
																echo '<option ' . selected( in_array( "$i", $hours, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
															}
														?>
													</select>
												</td>
												<td>
													<select name="e_daycronminutes">
														<?php
															for ( $i = 0; $i < 60; $i = $i + 5 ) {
																echo '<option ' . selected( in_array( "$i", $minutes, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
															}
														?>
													</select>
												</td>
											</tr>
											<tr>
												<td>
													<label for="idcronbtype-hour">
														<?php echo '<input class="radio" type="radio"' . checked( "*", $hours[0], FALSE, FALSE ) . ' name="e_cronbtype" value="hour" /> ' . __( 'hourly', self::DOMAIN ); ?>
													</label>
												</td>
												<td></td>
												<td></td>
												<td>
													<select name="e_hourcronminutes">
														<?php
															for ( $i = 0; $i < 60; $i = $i + 5 ) {
																echo '<option ' . selected( in_array( "$i", $minutes, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
															}
														?>
													</select>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							<?php
								}
							?>
							</table>
							<div class="submit-button">
								<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="metabox-holder">
				<div id="common-exported-file" class="postbox">
					<div class="handlediv" title="Click to toggle"><br></div>
					<h3 class="hndle"><span><?php _e( 'Exported File', self::DOMAIN ); ?></span></h3>
					<div class="inside">
						<table class="form-table">
							<tbody>
								<tr>
									<th><?php _e( 'Disk usage of exported file', self::DOMAIN ); ?></th>
									<td>
										<?php
											$abs_path = WP_PLUGIN_DIR . '/sns-count-cache/data/sns-count-cache-data.csv';
											$file_size = SCC_Common_Util::get_file_size( $abs_path );

											if ( isset( $file_size ) ) {
												echo $file_size;
											} else {
												_e( 'No exported file', self::DOMAIN );
											}
										?>
									</td>
								</tr>
							</tbody>
						</table>
						<form action="admin.php?page=scc-setting" method="post">
							<?php wp_nonce_field( __FILE__, '_wpnonce' ); ?>
							<table class="form-table">
								<tbody>
									<tr>
										<th><?php _e( 'Manual export', self::DOMAIN ); ?></th>
										<td>
											<input type="submit" class="button button-secondary" name="export_data" value="<?php _e( 'Export', self::DOMAIN ); ?>" />
											<br>
											<span class="description"><?php _e( 'Export share count to a csv file.', self::DOMAIN ); ?></span>
										</td>
									</tr>
								</tbody>
							</table>
						</form>
					<?php
						if ( file_exists( $abs_path ) ) {
					?>
						<form action="admin.php?page=scc-setting" method="post">
							<?php wp_nonce_field( __FILE__, '_wpnonce' ); ?>
							<table class="form-table">
								<tbody>
									<tr>
										<th><?php _e( 'Reset of exported file', self::DOMAIN ); ?></th>
										<td>
											<input type="submit" class="button button-secondary" name="reset_data" value="<?php _e( 'Reset', self::DOMAIN ); ?>" />
											<br>
											<span class="description"><?php _e( 'Clear exported csv file.', self::DOMAIN ); ?></span>
										</td>
									</tr>
								</tbody>
							</table>
						</form>
						<form action="<?php echo plugins_url(); ?>/sns-count-cache/includes/download.php" method="post">
							<?php wp_nonce_field( 'download', '_wpnonce' ); ?>
							<table class="form-table">
								<tbody>
									<tr>
										<th><?php _e( 'Download of exported file', self::DOMAIN ); ?></th>
										<td>
											<input type="submit" class="button button-secondary" name="download_data" value="<?php _e( 'Download', self::DOMAIN ); ?>" />
											<br>
											<span class="description"><?php _e( 'Download the exported csv file.', self::DOMAIN ); ?></span>
										</td>
									</tr>
								</tbody>
							</table>
						</form>
					<?php
						}
					?>
					</div>
				</div>
			</div>
		</div>
	</div>
