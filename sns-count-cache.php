<?php
/*
Plugin Name: SNS Count Cache
Description: SNS Count Cache gets share count for Twitter and Facebook, Google Plus, Pocket, Pinterest, Linkedin, Hatena Bookmark and caches these count in the background. This plugin may help you to shorten page loading time because the share count can be retrieved not through network but through the cache using given functions.
Version: 0.11.1
Plugin URI: https://wordpress.org/plugins/sns-count-cache/
Author: Daisuke Maruyama
Author URI: https://marubon.info/
License: GPL2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain: sns-count-cache
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

require_once ( dirname( __FILE__ ) . '/includes/class-scc-common-util.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-wp-cron-util.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-sleep-throttle.php' );

require_once ( dirname( __FILE__ ) . '/includes/interface-scc-order.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-scc-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-cache-engine.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-scc-share-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-share-base-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-share-rush-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-share-lazy-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-share-second-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-share-restore-cache-engine.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-scc-follow-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-follow-base-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-follow-lazy-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-follow-second-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-follow-restore-cache-engine.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-scc-export-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-common-data-export-engine.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-scc-common-job-reset-engine.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-scc-crawler.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-share-crawler.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-follow-crawler.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-scc-analytical-engline.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-share-analytical-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-follow-analytical-engine.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-scc-crawl-strategy.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-share-crawl-strategy-factory.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-follow-crawl-strategy-factory.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-scc-share-facebook-strategy.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-share-twitter-strategy.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-share-pocket-strategy.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-share-google-strategy.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-share-hatebu-strategy.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-share-pinterest-strategy.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-share-linkedin-strategy.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-scc-follow-twitter-strategy.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-follow-feedly-strategy.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-follow-facebook-strategy.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-follow-push7-strategy.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-scc-follow-instagram-strategy.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-crypt-blowfish.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-default-key.php' );

if ( ! defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

if ( ! class_exists( 'SNS_Count_Cache' ) ) {

final class SNS_Count_Cache implements SCC_Order {

	/**
	 * Prefix of share cache ID
	 */
	const OPT_SHARE_BASE_TRANSIENT_PREFIX = 'scc_share_count_';

	/**
	 * Meta key for share second cache
	 */
	const OPT_SHARE_2ND_META_KEY_PREFIX = 'scc_share_count_';

	/**
	 * Prefix of follow cache ID
	 */
	const OPT_FOLLOW_BASE_TRANSIENT_PREFIX = 'scc_follow_count_';

	/**
	 * Meta key for follow second cache
	 */
	const OPT_FOLLOW_2ND_META_KEY_PREFIX = 'scc_follow_count_';

	/**
	 * Interval cheking and caching share count for share base cache
	 */
	const OPT_SHARE_BASE_CHECK_INTERVAL = 600;

	/**
	 * Number of posts to check at a time for share base cache
	 */
	const OPT_SHARE_BASE_POSTS_PER_CHECK = 20;

	/**
	 * Interval cheking and caching share count for share rush cache
	 */
	const OPT_SHARE_RUSH_CHECK_INTERVAL = 600;

	/**
	 * Number of posts to check at a time for share rush cache
	 */
	const OPT_SHARE_RUSH_POSTS_PER_CHECK = 20;

	/**
	 * Term that a content is considered as new content in share rush cache
	 */
	const OPT_SHARE_RUSH_NEW_CONTENT_TERM = 3;

	/**
	 * Interval for share second cache
	 */
	const OPT_SHARE_2ND_CHECK_INTERVAL = 600;

	/**
	 * Twitter alternative API (widgetoon.js & count.jsoon)
	 */
	const OPT_SHARE_TWITTER_API_JSOON = 1;

	/**
	 * Twitter alternative API (OpenShareCount)
	 */
	const OPT_SHARE_TWITTER_API_OPENSHARECOUNT = 2;

	/**
	 * Twitter alternative API (TwitCount)
	 */
	const OPT_SHARE_TWITTER_API_TWITCOUNT = 3;

	/**
	 * Twitter alternative API (NewShareCounts)
	 */
	const OPT_SHARE_TWITTER_API_NEWSHARECOUNTS = 4;

	/**
	 * Interval cheking and caching share count for follow base cache
	 */
	const OPT_FOLLOW_BASE_CHECK_INTERVAL = 86400;
	const OPT_FOLLOW_BASE_CHECK_INTERVAL_MIN = 3600;

	/**
	 * Interval for follow second cache
	 */
	const OPT_FOLLOW_2ND_CHECK_INTERVAL = 600;

	/**
	 * Type of data export
	 */
	const OPT_COMMON_DATA_EXPORT_MANUAL = 1;

	/**
	 * Type of data export
	 */
	const OPT_COMMON_DATA_EXPORT_SCHEDULER = 2;

	/**
	 * Type of share analysis
	 */
	const OPT_SHARE_VARIATION_ANALYSIS_NONE = 1;

	/**
	 * Type of share analysis
	 */
	const OPT_SHARE_VARIATION_ANALYSIS_MANUAL = 2;

	/**
	 * Type of share analysis
	 */
	const OPT_SHARE_VARIATION_ANALYSIS_SCHEDULER = 3;

	/**
	 * Type of share analysis
	 */
	const OPT_SHARE_VARIATION_ANALYSIS_SCHEDULE = '0 0 * * *';

	/**
	 * Type of follow analysis
	 */
	const OPT_FOLLOW_VARIATION_ANALYSIS_NONE = 1;

	/**
	 * Type of follow analysis
	 */
	const OPT_FOLLOW_VARIATION_ANALYSIS_MANUAL = 2;

	/**
	 * Type of follow analysis
	 */
	const OPT_FOLLOW_VARIATION_ANALYSIS_SCHEDULER = 3;

	/**
	 * Type of follow analysis
	 */
	const OPT_FOLLOW_VARIATION_ANALYSIS_SCHEDULE = '0 0 * * *';

	/**
	 * File name of data export
	 */
	const OPT_COMMON_DATA_EXPORT_FILE_NAME = 'sns-count-cache-data.csv';

	/**
	 * Data export schedule
	 */
	const OPT_COMMON_DATA_EXPORT_SCHEDULE = '0 0 * * *';

	/**
	 * Type of dynamic cache processing
	 */
	const OPT_COMMON_ACCESS_BASED_CACHE_OFF = 1;

	/**
	 * Type of dynamic cache processing
	 */
	const OPT_COMMON_ACCESS_BASED_CACHE_ON = 5;

	/**
	 * Type of fault tolerance processing
	 */
	const OPT_COMMON_FAULT_TOLERANCE_OFF = 1;

	/**
	 * Type of fault tolerance processing
	 */
	const OPT_COMMON_FAULT_TOLERANCE_ON = 2;

	/**
	 * Type of scheme migration mode
	 */
	const OPT_COMMON_SCHEME_MIGRATION_MODE_OFF = false;

	/**
	 * Type of scheme migration mode
	 */
	const OPT_COMMON_SCHEME_MIGRATION_MODE_ON = true;

    /**
	 * Error message
	 */
	const OPT_COMMON_ERROR_MESSAGE = 'scc_error_message';

    /**
	 * Update message
	 */
	const OPT_COMMON_UPDATE_MESSAGE = 'scc_update_message';

	/**
	 * Type of crawl method
	 */
	const OPT_COMMON_CRAWLER_METHOD_NORMAL = 1;

	/**
	 * Type of crawl method
	 */
	const OPT_COMMON_CRAWLER_METHOD_CURL = 2;

	/**
	 * Type of crawl ssl verification
	 */
	const OPT_COMMON_CRAWLER_SSL_VERIFY_ON = true;

	/**
	 * Type of crawl ssl verification
	 */
	const OPT_COMMON_CRAWLER_SSL_VERIFY_OFF = false;

	/**
	 * crawler timeout
	 */
	const OPT_COMMON_CRAWLER_TIMEOUT = 10;

	/**
	 * crawler retry limit
	 */
	const OPT_COMMON_CRAWLER_RETRY_LIMIT = 1;

	/**
	 * Type of feed
	 */
	const OPT_FEED_TYPE_DEFAULT = '';

	/**
	 * Type of feed
	 */
	const OPT_FEED_TYPE_RSS = 'rss';

	/**
	 * Type of feed
	 */
	const OPT_FEED_TYPE_RSS2 = 'rss2';

	/**
	 * Type of feed
	 */
	const OPT_FEED_TYPE_RDF = 'rdf';

	/**
	 * Type of feed
	 */
	const OPT_FEED_TYPE_ATOM = 'atom';

	/**
	 * Capability for admin
	 */
	const OPT_COMMON_CAPABILITY = 'manage_options';

	/**
	 * Option key for custom post types for share base cache
	 */
	const DB_SHARE_CUSTOM_POST_TYPES = 'share_custom_post_types';

	/**
	 * Option key for check interval of share base cache
	 */
	const DB_SHARE_BASE_CHECK_INTERVAL = 'share_base_check_interval';

	/**
	 * Option key for twitter alternative api
	 */
	const DB_SHARE_BASE_TWITTER_API = 'share_base_twitter_api';

	/**
	 * Option key for number of posts to check at a time for share base cache
	 */
	const DB_SHARE_BASE_POSTS_PER_CHECK = 'share_posts_per_check';

	/**
	 * Option key for dynamic cache
	 */
	const DB_COMMON_DYNAMIC_CACHE_MODE = 'common_dynamic_cache_mode';

	/**
	 * Option key for fault tolerance
	 */
	const DB_COMMON_FAULT_TOLERANCE_MODE = 'common_fault_tolerance_mode';

	/**
	 * Option key for new content term for share rush cache
	 */
	const DB_SHARE_RUSH_NEW_CONTENT_TERM = 'share_new_content_term';

	/**
	 * Option key for check interval of share rush cache
	 */
	const DB_SHARE_RUSH_CHECK_INTERVAL = 'share_rush_check_interval';

	/**
	 * Option key for number of posts to check at a time for share rush cache
	 */
	const DB_SHARE_RUSH_POSTS_PER_CHECK = 'share_rush_posts_per_check';

	/**
	 * Option key of cache target for share base cache
	 */
	const DB_SHARE_CACHE_TARGET = 'share_cache_target';

	/**
	 * Option key of cache target for follow base cache
	 */
	const DB_FOLLOW_CACHE_TARGET = 'follow_cache_target';

	/**
	 * Option key of cache target for follow base cache
	 */
	const DB_FOLLOW_FEED_TYPE = '';

	/**
	 * Option key of checking interval for follow base cache
	 */
	const DB_FOLLOW_CHECK_INTERVAL = 'follow_check_interval';

	/**
	 * Option key of data export
	 */
	const DB_COMMON_DATA_EXPORT_MODE = 'common_data_export_mode';

	/**
	 * Option key of data export schedule
	 */
	const DB_COMMON_DATA_EXPORT_SCHEDULE = 'common_data_export_schedule';

	/**
	 * Option key of http migration
	 */
	const DB_COMMON_SCHEME_MIGRATION_MODE = 'common_scheme_migration_mode';

	/**
	 * Option key of http migration
	 */
	const DB_COMMON_SCHEME_MIGRATION_DATE = 'common_scheme_migration_date';

	/**
	 * Option key of crawl ssl verification
	 */
	const DB_COMMON_CRAWLER_SSL_VERIFICATION = 'common_crawler_ssl_verification';

	/**
	 * Option key of crawl ssl verification
	 */
	const DB_COMMON_CRAWLER_RETRY_LIMIT = 'common_crawler_retry_limit';

	/**
	 * Option key of share variation analysis
	 */
	const DB_SHARE_VARIATION_ANALYSIS_MODE = 'share_variation_analysis_mode';

	/**
	 * Option key of share variation analysis
	 */
	const DB_SHARE_VARIATION_ANALYSIS_SCHEDULE = 'share_variation_analysis_schedule';

	/**
	 * Option key of follow variation analysis
	 */
	const DB_FOLLOW_VARIATION_ANALYSIS_MODE = 'follow_variation_analysis_mode';

	/**
	 * Option key of follow variation analysis
	 */
	const DB_FOLLOW_VARIATION_ANALYSIS_SCHEDULE = 'follow_variation_analysis_schedule';

	/**
	 * Option key of twitter consumer key
	 */
	const DB_FOLLOW_TWITTER_SCREEN_NAME = 'follow_twitter_screen_name';

	/**
	 * Option key of twitter consumer key
	 */
	const DB_FOLLOW_TWITTER_CONSUMER_KEY = 'follow_twitter_consumer_key';

	/**
	 * Option key of twitter consumer secret
	 */
	const DB_FOLLOW_TWITTER_CONSUMER_SECRET = 'follow_twitter_consumer_secret';

	/**
	 * Option key of twitter bearer token
	 */
	const DB_FOLLOW_TWITTER_BEARER_TOKEN = 'follow_twitter_bearer_token';

	/**
	 * Option key of twitter access token
	 */
	const DB_FOLLOW_TWITTER_ACCESS_TOKEN = 'follow_twitter_access_token';

	/**
	 * Option key of twitter access token secret
	 */
	const DB_FOLLOW_TWITTER_ACCESS_TOKEN_SECRET = 'follow_twitter_access_token_secret';

	/**
	 * Option key of facebook page ID
	 */
	const DB_FOLLOW_FACEBOOK_PAGE_ID = 'follow_facebook_page_id';

	/**
	 * Option key of facebook app ID
	 */
	const DB_FOLLOW_FACEBOOK_APP_ID = 'follow_facebook_app_id';

	/**
	 * Option key of facebook app secret
	 */
	const DB_FOLLOW_FACEBOOK_APP_SECRET = 'follow_facebook_app_secret';

	/**
	 * Option key of facebook access token
	 */
	const DB_FOLLOW_FACEBOOK_ACCESS_TOKEN = 'follow_facebook_access_token';

	/**
	 * Option key of facebook app ID
	 */
	const DB_SHARE_FACEBOOK_APP_ID = 'share_facebook_app_id';

	/**
	 * Option key of facebook app secret
	 */
	const DB_SHARE_FACEBOOK_APP_SECRET = 'share_facebook_app_secret';

	/**
	 * Option key of facebook access token
	 */
	const DB_SHARE_FACEBOOK_ACCESS_TOKEN = 'share_facebook_access_token';

	/**
	 * Option key of push7 appno
	 */
	const DB_FOLLOW_PUSH7_APPNO = 'follow_push7_appno';

	/**
	 * Option key of instagram client id
	 */
	const DB_FOLLOW_INSTAGRAM_CLIENT_ID = 'follow_instagram_client_id';

	/**
	 * Option key of instagram client secret
	 */
	const DB_FOLLOW_INSTAGRAM_CLIENT_SECRET = 'follow_instagram_client_secret';

	/**
	 * Option key of instagram access token
	 */
	const DB_FOLLOW_INSTAGRAM_ACCESS_TOKEN = 'follow_instagram_access_token';

	/**
	 * Option key of setting
	 */
	const DB_SETTINGS = 'scc_settings';

	/**
	 * Slug of the plugin
	 */
	const DOMAIN = 'sns-count-cache';

	/**
	 * ID of share base cache
	 */
	const REF_SHARE_BASE = 'share-base';

	/**
	 * ID of share rush cache
	 */
	const REF_SHARE_RUSH = 'share-rush';

	/**
	 * ID of share lazy cache
	 */
	const REF_SHARE_LAZY = 'share-lazy';

	/**
	 * ID of share second cache
	 */
	const REF_SHARE_2ND = 'share-second';

	/**
	 * ID of share analysis cache
	 */
	const REF_SHARE_ANALYSIS = 'share-analysis';

	/**
	 * ID of share restore cache
	 */
	const REF_SHARE_RESTORE = 'share-restore';

	/**
	 * ID of follow base cache
	 */
	const REF_FOLLOW_BASE = 'follow-base';

	/**
	 * ID of follow lazy cache
	 */
	const REF_FOLLOW_LAZY = 'follow-lazy';

	/**
	 * ID of follow second cache
	 */
	const REF_FOLLOW_2ND = 'follow-second';

	/**
	 * ID of follow analysis cache
	 */
	const REF_FOLLOW_ANALYSIS = 'follow-analysis';

	/**
	 * ID of share restore cache
	 */
	const REF_FOLLOW_RESTORE = 'follow-restore';

	/**
	 * ID of common data export
	 */
	const REF_COMMON_EXPORT = 'common-export';

	/**
	 * ID of common data export
	 */
	const REF_COMMON_CONTROL = 'common-control';

	/**
	 * ID of share
	 */
	const REF_SHARE = 'share';

	/**
	 * ID of follow
	 */
	const REF_FOLLOW = 'follow';

	/**
	 * ID of share count (Twitter)
	 */
	const REF_SHARE_TWITTER = 'Twitter';

	/**
	 * ID of share count (Facebook)
	 */
	const REF_SHARE_FACEBOOK = 'Facebook';

	/**
	 * ID of share count (Google Plus)
	 */
	const REF_SHARE_GPLUS = 'Google+';

	/**
	 * ID of share count (Hatena Bookmark)
	 */
	const REF_SHARE_HATEBU = 'Hatebu';

	/**
	 * ID of share count (Pocket)
	 */
	const REF_SHARE_POCKET = 'Pocket';

	/**
	 * ID of share count (Pinterest)
	 */
	const REF_SHARE_PINTEREST = 'Pinterest';

	/**
	 * ID of share count (LinkedIn)
	 */
	const REF_SHARE_LINKEDIN = 'Linkedin';

	/**
	 * ID of share count (Total)
	 */
	const REF_SHARE_TOTAL = 'Total';

	/**
	 * ID of follow count (Feedly)
	 */
	const REF_FOLLOW_FEEDLY = 'Feedly';

	/**
	 * ID of follow count (Feedly)
	 */
	const REF_FOLLOW_TWITTER = 'Twitter';

	/**
	 * ID of follow count (Feedly)
	 */
	const REF_FOLLOW_FACEBOOK = 'Facebook';

	/**
	 * ID of follow count (Push7)
	 */
	const REF_FOLLOW_PUSH7 = 'Push7';

	/**
	 * ID of follow count (Instagram)
	 */
	const REF_FOLLOW_INSTAGRAM = 'Instagram';

	/**
	 * ID of crawl date
	 */
	const REF_CRAWL_DATE = 'CrawlDate';

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 */
	private $version = '0.11.1';

	/**
	 * Instances of crawler
	 */
	private $crawlers = array();

	/**
	 * Instance of cache engine
	 */
	private $cache_engines = array();

	/**
	 * Instance of export engine
	 */
	private $export_engines = array();

	/**
	 * Instance of control engine
	 */
	private $control_engines = array();

	/**
	 * Instance of analytical engine
	 */
	private $analytical_engines = array();

	/**
	 * Slug of the plugin screen
	 */
	private $plugin_screen_hook_suffix = array();

	/**
	 * Cache target for share base cache
	 */
	private $share_base_cache_target = array();

	/**
	 * Post types to be cached
	 */
	private $share_base_cache_post_types = array( 'post', 'page' );

	/**
	 * Post types to be cached
	 */
	private $share_base_custom_post_types = array();

	/**
	 * Check interval for share base cahce
	 */
	private $share_base_check_interval = 600;

	/**
	 * Post per check for share base cache
	 */
	private $share_base_posts_per_check = 20;

	/**
	 * Post per check for share base cache
	 */
	private $share_base_twitter_api = '';

	/**
	 * Term considering content as new one
	 */
	private $share_rush_new_content_term = 3;

	/**
	 *Check interval for share rush cahce
	 */
	private $share_rush_check_interval = 600;

	/**
	 * Post per check for share rush cache
	 */
	private $share_rush_posts_per_check = 20;

	/**
	 * Facebook app ID
	 */
	private $share_facebook_app_id = '';

	/**
	 * Facebook app ID
	 */
	private $share_facebook_app_secret = '';

	/**
	 * Facebook access token
	 */
	private $share_facebook_access_token = '';

	/**
	 * Cache target for follow base cache
	 */
	private $follow_base_cache_target = array();

	/**
	 * Check interval for follow base cache
	 */
	private $follow_base_check_interval = 1800;

	/**
	 * Feed type to be followed
	 */
	private $follow_feed_type = '';

	/**
	 * Twitter consumer key
	 */
	private $follow_twitter_consumer_key = '';

	/**
	 * Twitter consumer secret
	 */
	private $follow_twitter_consumer_secret = '';

	/**
	 * Twitter access token
	 */
	private $follow_twitter_access_token = '';

	/**
	 * Twitter bearer token
	 */
	private $follow_twitter_bearer_token = '';

	/**
	 * Twitter access token secret
	 */
	private $follow_twitter_access_token_secret = '';

	 /**
	 * Twitter screen name
	 */
	private $follow_twitter_screen_name = '';

	/**
	 * Facebook page ID
	 */
	private $follow_facebook_page_id = '';

	/**
	 * Facebook app ID
	 */
	private $follow_facebook_app_id = '';

	/**
	 * Facebook app ID
	 */
	private $follow_facebook_app_secret = '';

	/**
	 * Facebook access token
	 */
	private $follow_facebook_access_token = '';

	/**
	 * Push7 appno
	 */
	private $follow_push7_appno = '';

	/**
	 * Instagram client id
	 */
	private $follow_instagram_client_id = '';

	/**
	 * Instagram client secret
	 */
	private $follow_instagram_client_secret = '';

	/**
	 * Instagram access token
	 */
	private $follow_instagram_access_token = '';

	/**
	 * Dynamic cache mode
	 */
	private $dynamic_cache_mode = 1;

	/**
	 * Fault tolerance mode
	 */
	private $fault_tolerance_mode = 1;

	/**
	 * Data export mode
	 */
	private $data_export_mode = 1;

	/**
	 * Data export schedule
	 */
	private $data_export_schedule  = '0 0 * * *';

	/**
	 * Share variation analysis mode
	 */
	private $share_variation_analysis_mode = 1;

	/**
	 * Share variation analysis schedule
	 */
	private $share_variation_analysis_schedule  = '0 0 * * *';

	/**
	 * Follow variation analysis mode
	 */
	private $follow_variation_analysis_mode = 1;

	/**
	 * Follow variation analysis schedule
	 */
	private $follow_variation_analysis_schedule  = '0 0 * * *';

	/**
	 * Migration mode from http to https
	 */
	private $scheme_migration_mode = false;

	/**
	 * Migration date from http to https
	 */
	private $scheme_migration_date = NULL;

	/**
	 * Excluded key in migration from http to https
	 */
	private $scheme_migration_exclude_keys = array();

	/**
	 * Max execution time
	 */
	private $original_max_execution_time = 0;

	/**
	 * Extended max execution time
	 */
	private $extended_max_execution_time = 600;

	/**
	 * URL of loding image
	 */
	private $loading_img_url = '';

	/**
	 * ajax action
	 */
	private $ajax_action = 'scc_cache_info';

	/**
	 * Cralwer method
	 */
	private $crawler_method = 1;

	/**
	 * Cralwer method
	 */
	private $crawler_retry_limit = 1;

	/**
	 * Cralwer SSL verification
	 */
	private $crawler_ssl_verification = true;

	/**
	 * Instance
	 */
	private static $instance = NULL;

	/**
	 * Class constarctor
	 * Hook onto all of the actions and filters needed by the plugin.
	 */
	private function __construct() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		load_plugin_textdomain( self::DOMAIN, false, basename( dirname( __FILE__ ) ) . '/languages' );

		add_action( 'init', array( $this, 'initialize' ) );

		register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate_plugin' ) );

		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );
		//add_action( 'admin_notices', array( $this, 'notice_page' ) );
		add_action( 'wp_ajax_' . $this->ajax_action, array( $this, 'get_cache_info' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'add_wp_dashboard_widget' ) );
		add_action( 'deleted_post' , array( $this, 'clear_cache_deleted_post' ) );

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_plugin_action_links' ), 10, 4 );
	}

	/**
	 * Get instance
	 *
	 * @since 0.1.1
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
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
	 * @since 0.1.1
	 */
	public function initialize() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$settings = get_option( self::DB_SETTINGS );

		if ( isset( $settings[self::DB_SHARE_BASE_CHECK_INTERVAL] ) && $settings[self::DB_SHARE_BASE_CHECK_INTERVAL] ) {
			$this->share_base_check_interval = (int) $settings[self::DB_SHARE_BASE_CHECK_INTERVAL];
		} else {
			$this->share_base_check_interval = self::OPT_SHARE_BASE_CHECK_INTERVAL;
		}

		if ( isset( $settings[self::DB_SHARE_BASE_POSTS_PER_CHECK] ) && $settings[self::DB_SHARE_BASE_POSTS_PER_CHECK] ) {
			$this->share_base_posts_per_check = (int) $settings[self::DB_SHARE_BASE_POSTS_PER_CHECK];
		} else {
			$this->share_base_posts_per_check = self::OPT_SHARE_BASE_POSTS_PER_CHECK;
		}

		if ( isset( $settings[self::DB_FOLLOW_CHECK_INTERVAL] ) && $settings[self::DB_FOLLOW_CHECK_INTERVAL] ) {
			$this->follow_base_check_interval = (int) $settings[self::DB_FOLLOW_CHECK_INTERVAL];
			if ( $this->follow_base_check_interval < self::OPT_FOLLOW_BASE_CHECK_INTERVAL_MIN ) {
				$this->follow_base_check_interval = self::OPT_FOLLOW_BASE_CHECK_INTERVAL_MIN;
			}
		} else {
			$this->follow_base_check_interval = self::OPT_FOLLOW_BASE_CHECK_INTERVAL;
		}

		if ( isset( $settings[self::DB_COMMON_DYNAMIC_CACHE_MODE] ) && $settings[self::DB_COMMON_DYNAMIC_CACHE_MODE] ) {
			$this->dynamic_cache_mode = (int) $settings[self::DB_COMMON_DYNAMIC_CACHE_MODE];
		} else {
			$this->dynamic_cache_mode = self::OPT_COMMON_ACCESS_BASED_CACHE_OFF;
		}

		if ( isset( $settings[self::DB_COMMON_FAULT_TOLERANCE_MODE] ) && $settings[self::DB_COMMON_FAULT_TOLERANCE_MODE] ) {
			$this->fault_tolerance_mode = (int) $settings[self::DB_COMMON_FAULT_TOLERANCE_MODE];
		} else {
			$this->fault_tolerance_mode = self::OPT_COMMON_FAULT_TOLERANCE_OFF;
		}

		if ( isset( $settings[self::DB_SHARE_RUSH_CHECK_INTERVAL] ) && $settings[self::DB_SHARE_RUSH_CHECK_INTERVAL] ) {
			$this->share_rush_check_interval = (int) $settings[self::DB_SHARE_RUSH_CHECK_INTERVAL];
		} else {
			$this->share_rush_check_interval = self::OPT_SHARE_RUSH_CHECK_INTERVAL;
		}

		if ( isset( $settings[self::DB_SHARE_RUSH_POSTS_PER_CHECK] ) && $settings[self::DB_SHARE_RUSH_POSTS_PER_CHECK] ) {
			$this->share_rush_posts_per_check = (int) $settings[self::DB_SHARE_RUSH_POSTS_PER_CHECK];
		} else {
			$this->share_rush_posts_per_check = self::OPT_SHARE_RUSH_POSTS_PER_CHECK;
		}

		if ( isset( $settings[self::DB_SHARE_RUSH_NEW_CONTENT_TERM] ) && $settings[self::DB_SHARE_RUSH_NEW_CONTENT_TERM] ) {
			$this->share_rush_new_content_term = (int) $settings[self::DB_SHARE_RUSH_NEW_CONTENT_TERM];
		} else {
			$this->share_rush_new_content_term = self::OPT_SHARE_RUSH_NEW_CONTENT_TERM;
		}

		if ( isset( $settings[self::DB_COMMON_DATA_EXPORT_MODE] ) && $settings[self::DB_COMMON_DATA_EXPORT_MODE] ) {
			$this->data_export_mode = (int) $settings[self::DB_COMMON_DATA_EXPORT_MODE];
		} else {
			$this->data_export_mode = self::OPT_COMMON_DATA_EXPORT_MANUAL;
		}

		if ( isset( $settings[self::DB_COMMON_DATA_EXPORT_SCHEDULE] ) && $settings[self::DB_COMMON_DATA_EXPORT_SCHEDULE] ) {
			$this->data_export_schedule = $settings[self::DB_COMMON_DATA_EXPORT_SCHEDULE];
		} else {
			$this->data_export_schedule = self::OPT_COMMON_DATA_EXPORT_SCHEDULE;
		}

		if ( isset( $settings[self::DB_SHARE_VARIATION_ANALYSIS_MODE] ) && $settings[self::DB_SHARE_VARIATION_ANALYSIS_MODE] ) {
			$this->share_variation_analysis_mode = (int) $settings[self::DB_SHARE_VARIATION_ANALYSIS_MODE];
		} else {
			$this->share_variation_analysis_mode = self::OPT_SHARE_VARIATION_ANALYSIS_NONE;
		}

		if ( isset( $settings[self::DB_SHARE_VARIATION_ANALYSIS_SCHEDULE] ) && $settings[self::DB_SHARE_VARIATION_ANALYSIS_SCHEDULE] ) {
			$this->share_variation_analysis_schedule = $settings[self::DB_SHARE_VARIATION_ANALYSIS_SCHEDULE];
		} else {
			$this->share_variation_analysis_schedule = self::OPT_SHARE_VARIATION_ANALYSIS_SCHEDULE;
		}

		if ( isset( $settings[self::DB_FOLLOW_VARIATION_ANALYSIS_MODE] ) && $settings[self::DB_FOLLOW_VARIATION_ANALYSIS_MODE] ) {
			$this->follow_variation_analysis_mode = (int) $settings[self::DB_FOLLOW_VARIATION_ANALYSIS_MODE];
		} else {
			$this->follow_variation_analysis_mode = self::OPT_FOLLOW_VARIATION_ANALYSIS_NONE;
		}

		if ( isset( $settings[self::DB_FOLLOW_VARIATION_ANALYSIS_SCHEDULE] ) && $settings[self::DB_FOLLOW_VARIATION_ANALYSIS_SCHEDULE] ) {
			$this->follow_variation_analysis_schedule = $settings[self::DB_FOLLOW_VARIATION_ANALYSIS_SCHEDULE];
		} else {
			$this->follow_variation_analysis_schedule = self::OPT_FOLLOW_VARIATION_ANALYSIS_SCHEDULE;
		}

		if ( isset( $settings[self::DB_COMMON_SCHEME_MIGRATION_MODE] ) ) {
			$this->scheme_migration_mode = $settings[self::DB_COMMON_SCHEME_MIGRATION_MODE];
		} else {
			$this->scheme_migration_mode = self::OPT_COMMON_SCHEME_MIGRATION_MODE_OFF;
		}

		if ( isset( $settings[self::DB_COMMON_SCHEME_MIGRATION_DATE] ) ) {
			$this->scheme_migration_date = $settings[self::DB_COMMON_SCHEME_MIGRATION_DATE];
		}

		if ( isset( $settings[self::DB_SHARE_CACHE_TARGET] ) && $settings[self::DB_SHARE_CACHE_TARGET] ) {
			$this->share_base_cache_target = $settings[self::DB_SHARE_CACHE_TARGET];
		} else {
			$this->share_base_cache_target[self::REF_SHARE_GPLUS] = true;
			$this->share_base_cache_target[self::REF_SHARE_FACEBOOK] = true;
			$this->share_base_cache_target[self::REF_SHARE_POCKET] = true;
			$this->share_base_cache_target[self::REF_SHARE_HATEBU] = true;
		}

		$this->share_base_cache_target[self::REF_CRAWL_DATE] = true;
		$this->share_base_cache_target[self::REF_SHARE_TOTAL] = true;

		if ( isset( $settings[self::DB_SHARE_BASE_TWITTER_API] ) ) {
			$this->share_base_twitter_api = (int) $settings[self::DB_SHARE_BASE_TWITTER_API];
		} else {
			$this->share_base_twitter_api = self::OPT_SHARE_TWITTER_API_JSOON;
		}

		// Pocket and Google+, Linkedin are excluded from migration target because they are migrated automatically.
		$this->scheme_migration_exclude_keys = array(
			self::REF_SHARE_POCKET,
			self::REF_SHARE_GPLUS,
			self::REF_SHARE_LINKEDIN,
			self::REF_FOLLOW_TWITTER,
			self::REF_FOLLOW_FACEBOOK,
			self::REF_FOLLOW_PUSH7,
			self::REF_FOLLOW_INSTAGRAM
		);

		if ( $this->share_base_twitter_api === self::OPT_SHARE_TWITTER_API_JSOON ) {
			$this->scheme_migration_exclude_keys[] = self::REF_SHARE_TWITTER;
		}

		if ( isset( $settings[self::DB_FOLLOW_CACHE_TARGET] ) && $settings[self::DB_FOLLOW_CACHE_TARGET] ) {
			$this->follow_base_cache_target = $settings[self::DB_FOLLOW_CACHE_TARGET];
		} else {
			$this->follow_base_cache_target[self::REF_FOLLOW_FEEDLY] = true;
		}

		$this->follow_base_cache_target[self::REF_CRAWL_DATE] = true;

		if ( isset( $settings[self::DB_FOLLOW_FEED_TYPE] ) && $settings[self::DB_FOLLOW_FEED_TYPE] ) {
			$this->follow_feed_type = $settings[self::DB_FOLLOW_FEED_TYPE];
		} else {
			$this->follow_feed_type = self::OPT_FEED_TYPE_DEFAULT;
		}

		if ( isset( $settings[self::DB_SHARE_CUSTOM_POST_TYPES] ) && $settings[self::DB_SHARE_CUSTOM_POST_TYPES] ) {
			$this->share_base_custom_post_types = $settings[self::DB_SHARE_CUSTOM_POST_TYPES];
		} else {
			$this->share_base_custom_post_types = array();
		}

		$this->share_base_cache_post_types = array_merge( $this->share_base_cache_post_types, $this->share_base_custom_post_types );

		if ( extension_loaded( 'curl' ) ) {
			$this->crawler_method = self::OPT_COMMON_CRAWLER_METHOD_CURL;
		} else {
			$this->crawler_method = self::OPT_COMMON_CRAWLER_METHOD_NORMAL;
		}

		if ( isset( $settings[self::DB_COMMON_CRAWLER_SSL_VERIFICATION] ) ) {
			$this->crawler_ssl_verification = $settings[self::DB_COMMON_CRAWLER_SSL_VERIFICATION];
		} else {
			$this->crawler_ssl_verification = self::OPT_COMMON_CRAWLER_SSL_VERIFY_ON;
		}

		if ( isset( $settings[self::DB_COMMON_CRAWLER_RETRY_LIMIT] ) && $settings[self::DB_COMMON_CRAWLER_RETRY_LIMIT] ) {
			$this->crawler_retry_limit = (int) $settings[self::DB_COMMON_CRAWLER_RETRY_LIMIT];
		} else {
			$this->crawler_retry_limit = self::OPT_COMMON_CRAWLER_RETRY_LIMIT;
		}

		if ( isset( $settings[self::DB_FOLLOW_TWITTER_SCREEN_NAME] ) && $settings[self::DB_FOLLOW_TWITTER_SCREEN_NAME] ) {
			$this->follow_twitter_screen_name = $settings[self::DB_FOLLOW_TWITTER_SCREEN_NAME];
		}

		if ( isset( $settings[self::DB_FOLLOW_TWITTER_CONSUMER_KEY] ) && $settings[self::DB_FOLLOW_TWITTER_CONSUMER_KEY] ) {
			$this->follow_twitter_consumer_key = trim( SCC_Common_Util::decrypt_data( $settings[self::DB_FOLLOW_TWITTER_CONSUMER_KEY], AUTH_KEY ) );
		}

		if ( isset( $settings[self::DB_FOLLOW_TWITTER_CONSUMER_SECRET] ) && $settings[self::DB_FOLLOW_TWITTER_CONSUMER_SECRET] ) {
			$this->follow_twitter_consumer_secret = trim( SCC_Common_Util::decrypt_data( $settings[self::DB_FOLLOW_TWITTER_CONSUMER_SECRET], AUTH_KEY ) );
		}

		if ( isset( $settings[self::DB_FOLLOW_TWITTER_BEARER_TOKEN] ) && $settings[self::DB_FOLLOW_TWITTER_BEARER_TOKEN] ) {
			$this->follow_twitter_bearer_token = trim( SCC_Common_Util::decrypt_data( $settings[self::DB_FOLLOW_TWITTER_BEARER_TOKEN], AUTH_KEY ) );
		}

		/*
		if ( isset( $settings[self::DB_FOLLOW_TWITTER_ACCESS_TOKEN] ) && $settings[self::DB_FOLLOW_TWITTER_ACCESS_TOKEN] ) {
			$this->follow_twitter_access_token = trim( SCC_Common_Util::decrypt_data( $settings[self::DB_FOLLOW_TWITTER_ACCESS_TOKEN], AUTH_KEY ) );
		}

		if ( isset( $settings[self::DB_FOLLOW_TWITTER_ACCESS_TOKEN_SECRET] ) && $settings[self::DB_FOLLOW_TWITTER_ACCESS_TOKEN_SECRET] ) {
			$this->follow_twitter_access_token_secret = trim( SCC_Common_Util::decrypt_data( $settings[self::DB_FOLLOW_TWITTER_ACCESS_TOKEN_SECRET], AUTH_KEY ) );
		}
		*/

		if ( isset( $settings[self::DB_SHARE_FACEBOOK_APP_ID] ) && $settings[self::DB_SHARE_FACEBOOK_APP_ID] ) {
			$this->share_facebook_app_id = trim( SCC_Common_Util::decrypt_data( $settings[self::DB_SHARE_FACEBOOK_APP_ID], AUTH_KEY ) );
		}

		if ( isset( $settings[self::DB_SHARE_FACEBOOK_APP_SECRET] ) && $settings[self::DB_SHARE_FACEBOOK_APP_SECRET] ) {
			$this->share_facebook_app_secret = trim( SCC_Common_Util::decrypt_data( $settings[self::DB_SHARE_FACEBOOK_APP_SECRET], AUTH_KEY ) );
		}

		if ( isset( $this->share_facebook_app_id ) && $this->share_facebook_app_id && isset( $this->share_facebook_app_secret ) && $this->share_facebook_app_secret ) {
			$this->share_facebook_access_token = $this->share_facebook_app_id . '|' . $this->share_facebook_app_secret;
		} else {
			$this->share_facebook_access_token = '';
		}

		if ( isset( $settings[self::DB_FOLLOW_FACEBOOK_PAGE_ID] ) && $settings[self::DB_FOLLOW_FACEBOOK_PAGE_ID] ) {
			$this->follow_facebook_page_id = $settings[self::DB_FOLLOW_FACEBOOK_PAGE_ID];
		}

		if ( isset( $settings[self::DB_FOLLOW_FACEBOOK_APP_ID] ) && $settings[self::DB_FOLLOW_FACEBOOK_APP_ID] ) {
			$this->follow_facebook_app_id = trim( SCC_Common_Util::decrypt_data( $settings[self::DB_FOLLOW_FACEBOOK_APP_ID], AUTH_KEY ) );
		}

		if ( isset( $settings[self::DB_FOLLOW_FACEBOOK_APP_SECRET] ) && $settings[self::DB_FOLLOW_FACEBOOK_APP_SECRET] ) {
			$this->follow_facebook_app_secret = trim( SCC_Common_Util::decrypt_data( $settings[self::DB_FOLLOW_FACEBOOK_APP_SECRET], AUTH_KEY ) );
		}

		if ( isset( $settings[self::DB_FOLLOW_FACEBOOK_ACCESS_TOKEN] ) && $settings[self::DB_FOLLOW_FACEBOOK_ACCESS_TOKEN] ) {
			$this->follow_facebook_access_token = trim( SCC_Common_Util::decrypt_data( $settings[self::DB_FOLLOW_FACEBOOK_ACCESS_TOKEN], AUTH_KEY ) );
		}

		if ( isset( $settings[self::DB_FOLLOW_PUSH7_APPNO] ) && $settings[self::DB_FOLLOW_PUSH7_APPNO] ) {
			$this->follow_push7_appno = trim( SCC_Common_Util::decrypt_data( $settings[self::DB_FOLLOW_PUSH7_APPNO], AUTH_KEY ) );
		}

		if ( isset( $settings[self::DB_FOLLOW_INSTAGRAM_CLIENT_ID] ) && $settings[self::DB_FOLLOW_INSTAGRAM_CLIENT_ID] ) {
			$this->follow_instagram_client_id = trim( SCC_Common_Util::decrypt_data( $settings[self::DB_FOLLOW_INSTAGRAM_CLIENT_ID], AUTH_KEY ) );
		}

		if ( isset( $settings[self::DB_FOLLOW_INSTAGRAM_CLIENT_SECRET] ) && $settings[self::DB_FOLLOW_INSTAGRAM_CLIENT_SECRET] ) {
			$this->follow_instagram_client_secret = trim( SCC_Common_Util::decrypt_data( $settings[self::DB_FOLLOW_INSTAGRAM_CLIENT_SECRET], AUTH_KEY ) );
		}

		if ( isset( $settings[self::DB_FOLLOW_INSTAGRAM_ACCESS_TOKEN] ) && $settings[self::DB_FOLLOW_INSTAGRAM_ACCESS_TOKEN] ) {
			$this->follow_instagram_access_token = trim( SCC_Common_Util::decrypt_data( $settings[self::DB_FOLLOW_INSTAGRAM_ACCESS_TOKEN], AUTH_KEY ) );
		}

		// Share Crawler
		$options = array(
			'target_sns' => $this->share_base_cache_target,
			'crawl_method' => $this->crawler_method,
			'timeout' => self::OPT_COMMON_CRAWLER_TIMEOUT,
			'retry_limit' => $this->crawler_retry_limit,
			'ssl_verification' => $this->crawler_ssl_verification,
			);

		$this->crawlers[self::REF_SHARE] = SCC_Share_Crawler::get_instance();
		$this->crawlers[self::REF_SHARE]->initialize( $options );

		// Share Twitter Crawl Strategy
		$options = array(
			'twitter_api' => $this->share_base_twitter_api
			);

		$this->crawlers[self::REF_SHARE]->initialize_crawl_strategy( self::REF_SHARE_TWITTER, $options );

		// Share Facebook Crawl Strategy
		$parameters = array(
			'app_id' => $this->share_facebook_app_id,
			'app_secret' => $this->share_facebook_app_secret
			);

		$query_parameters = array(
			'access_token' => $this->share_facebook_access_token
			);

		$this->crawlers[self::REF_SHARE]->set_crawl_strategy_query_parameters( self::REF_SHARE_FACEBOOK, $query_parameters );
		$this->crawlers[self::REF_SHARE]->set_crawl_strategy_parameters( self::REF_SHARE_FACEBOOK, $parameters );

		// Follow Crawler
		$options = array(
			'target_sns' => $this->follow_base_cache_target,
			'crawl_method' => $this->crawler_method,
			'timeout' => self::OPT_COMMON_CRAWLER_TIMEOUT,
			'retry_limit' => $this->crawler_retry_limit,
			'ssl_verification' => $this->crawler_ssl_verification
			);

		$this->crawlers[self::REF_FOLLOW] = SCC_Follow_Crawler::get_instance();
		$this->crawlers[self::REF_FOLLOW]->initialize( $options );

		// Follow Twitter Crawl Strategy
		$query_parameters = array(
			'screen_name' => $this->follow_twitter_screen_name
			);

		/*
		// version using user auth
		$parameters = array(
			'consumer_key' => $this->follow_twitter_consumer_key,
			'consumer_secret' => $this->follow_twitter_consumer_secret,
			'access_token' => $this->follow_twitter_access_token,
			'access_token_secret' => $this->follow_twitter_access_token_secret
			);
		*/

		// version using application-only auth
		$parameters = array(
			'consumer_key' => $this->follow_twitter_consumer_key,
			'consumer_secret' => $this->follow_twitter_consumer_secret,
			'bearer_token' => $this->follow_twitter_bearer_token
			);

		$this->crawlers[self::REF_FOLLOW]->set_crawl_strategy_query_parameters( self::REF_FOLLOW_TWITTER, $query_parameters );
		$this->crawlers[self::REF_FOLLOW]->set_crawl_strategy_parameters( self::REF_FOLLOW_TWITTER, $parameters );

		// Follow Feedly Crawl Strategy
		$query_parameters = array(
			'url' => get_feed_link( $this->follow_feed_type )
			);

		$this->crawlers[self::REF_FOLLOW]->set_crawl_strategy_query_parameters( self::REF_FOLLOW_FEEDLY, $query_parameters );

		// Follow Facebook Crawl Strategy
		$query_parameters = array(
			'access_token' => $this->follow_facebook_access_token
			);

		$parameters = array(
			'page_id' => $this->follow_facebook_page_id,
			'client_id' => $this->follow_facebook_app_id,
			'client_secret' => $this->follow_facebook_app_secret
			);

		$this->crawlers[self::REF_FOLLOW]->set_crawl_strategy_query_parameters( self::REF_FOLLOW_FACEBOOK, $query_parameters );
		$this->crawlers[self::REF_FOLLOW]->set_crawl_strategy_parameters( self::REF_FOLLOW_FACEBOOK, $parameters );

		// Follow Push7 Crawl Strategy
		$parameters = array(
			'appno' => $this->follow_push7_appno
			);

		//$this->crawlers[self::REF_FOLLOW]->set_crawl_strategy_query_parameters( self::REF_FOLLOW_PUSH7, $query_parameters );
		$this->crawlers[self::REF_FOLLOW]->set_crawl_strategy_parameters( self::REF_FOLLOW_PUSH7, $parameters );

		// Follow Instagram Crawl Strategy
		$query_parameters = array(
			'access_token' => $this->follow_instagram_access_token
			);

		$parameters = array(
			'client_id' => $this->follow_instagram_client_id,
			'client_secret' => $this->follow_instagram_client_secret
			);

		$this->crawlers[self::REF_FOLLOW]->set_crawl_strategy_query_parameters( self::REF_FOLLOW_INSTAGRAM, $query_parameters );
		$this->crawlers[self::REF_FOLLOW]->set_crawl_strategy_parameters( self::REF_FOLLOW_INSTAGRAM, $parameters );

		// Share base cache engine
		$options = array(
			'delegate' => $this,
			'crawler' => $this->crawlers[self::REF_SHARE],
			'target_sns' => $this->share_base_cache_target,
			'check_interval' => $this->share_base_check_interval,
			'posts_per_check' => $this->share_base_posts_per_check,
			'post_types' => $this->share_base_cache_post_types,
			'scheme_migration_mode' => $this->scheme_migration_mode,
			'scheme_migration_date' => $this->scheme_migration_date,
			'scheme_migration_exclude_keys' => $this->scheme_migration_exclude_keys,
			'fault_tolerance' => $this->fault_tolerance_mode
			);

		$this->cache_engines[self::REF_SHARE_BASE] = SCC_Share_Base_Cache_Engine::get_instance();
		$this->cache_engines[self::REF_SHARE_BASE]->initialize( $options );

		// Share rush cache engine
		$options = array(
			'delegate' => $this,
			'crawler' => $this->crawlers[self::REF_SHARE],
			'target_sns' => $this->share_base_cache_target,
			'check_interval' => $this->share_rush_check_interval,
			'posts_per_check' => $this->share_rush_posts_per_check,
			'new_content_term' => $this->share_rush_new_content_term,
			'post_types' => $this->share_base_cache_post_types,
			'scheme_migration_mode' => $this->scheme_migration_mode,
			'scheme_migration_date' => $this->scheme_migration_date,
			'scheme_migration_exclude_keys' => $this->scheme_migration_exclude_keys,
			'fault_tolerance' => $this->fault_tolerance_mode
			);

		$this->cache_engines[self::REF_SHARE_RUSH] = SCC_Share_Rush_Cache_Engine::get_instance();
		$this->cache_engines[self::REF_SHARE_RUSH]->initialize( $options );

		// Share lazy cache engine
		$options = array(
			'delegate' => $this,
			'crawler' => $this->crawlers[self::REF_SHARE],
			'target_sns' => $this->share_base_cache_target,
			'check_interval' => $this->share_base_check_interval,
			'posts_per_check' => $this->share_base_posts_per_check,
			'post_types' => $this->share_base_cache_post_types,
			'scheme_migration_mode' => $this->scheme_migration_mode,
			'scheme_migration_date' => $this->scheme_migration_date,
			'scheme_migration_exclude_keys' => $this->scheme_migration_exclude_keys,
			'fault_tolerance' => $this->fault_tolerance_mode
			);

		$this->cache_engines[self::REF_SHARE_LAZY] = SCC_Share_Lazy_Cache_Engine::get_instance();
		$this->cache_engines[self::REF_SHARE_LAZY]->initialize( $options );

		// Share second cache engine
		$options = array(
			'delegate' => $this,
			'target_sns' => $this->share_base_cache_target,
			'check_interval' => self::OPT_SHARE_2ND_CHECK_INTERVAL,
			'post_types' => $this->share_base_cache_post_types,
			'cache_prefix' => self::OPT_SHARE_2ND_META_KEY_PREFIX,
			'crawl_date_key' => self::REF_CRAWL_DATE
			);

		$this->cache_engines[self::REF_SHARE_2ND] = SCC_Share_Second_Cache_Engine::get_instance();
		$this->cache_engines[self::REF_SHARE_2ND]->initialize( $options );

		// Share restore cache engine
		$options = array(
			'target_sns' => $this->share_base_cache_target,
			'check_interval' => $this->share_base_check_interval,
			'posts_per_check' => $this->share_base_posts_per_check,
			'post_types' => $this->share_base_cache_post_types,
			);

		$this->cache_engines[self::REF_SHARE_RESTORE] = SCC_Share_Restore_Cache_Engine::get_instance();
		$this->cache_engines[self::REF_SHARE_RESTORE]->initialize( $options );

		// Follow base cache engine
		$options = array(
			'delegate' => $this,
			'crawler' => $this->crawlers[self::REF_FOLLOW],
			'target_sns' => $this->follow_base_cache_target,
			'check_interval' => $this->follow_base_check_interval,
			'scheme_migration_mode' => $this->scheme_migration_mode,
			'scheme_migration_exclude_keys' => $this->scheme_migration_exclude_keys,
			'feed_type' => $this->follow_feed_type
			);

		$this->cache_engines[self::REF_FOLLOW_BASE] = SCC_Follow_Base_Cache_Engine::get_instance();
		$this->cache_engines[self::REF_FOLLOW_BASE]->initialize( $options );

		// Follow lazy cache engine
		$options = array(
			'delegate' => $this,
			'crawler' => $this->crawlers[self::REF_FOLLOW],
			'target_sns' => $this->follow_base_cache_target,
			'check_interval' => $this->follow_base_check_interval,
			'scheme_migration_mode' => $this->scheme_migration_mode,
			'scheme_migration_exclude_keys' => $this->scheme_migration_exclude_keys,
			'feed_type' => $this->follow_feed_type
			);

		$this->cache_engines[self::REF_FOLLOW_LAZY] = SCC_Follow_Lazy_Cache_Engine::get_instance();
		$this->cache_engines[self::REF_FOLLOW_LAZY]->initialize( $options );

		// Follow second cache engine
		$options = array(
			'delegate' => $this,
			'target_sns' => $this->follow_base_cache_target,
			'check_interval' => self::OPT_FOLLOW_2ND_CHECK_INTERVAL,
			'cache_prefix' => self::OPT_FOLLOW_2ND_META_KEY_PREFIX
			);

		$this->cache_engines[self::REF_FOLLOW_2ND] = SCC_Follow_Second_Cache_Engine::get_instance();
		$this->cache_engines[self::REF_FOLLOW_2ND]->initialize( $options );

		// Follow restore cache engine
		$options = array(
			'target_sns' => $this->follow_base_cache_target,
			'check_interval' => $this->follow_base_check_interval,
			);

		$this->cache_engines[self::REF_FOLLOW_RESTORE] = SCC_Follow_Restore_Cache_Engine::get_instance();
		$this->cache_engines[self::REF_FOLLOW_RESTORE]->initialize( $options );

		// Data export engine
		$options = array(
			'export_activation' => $this->data_export_mode,
			'export_schedule' => $this->data_export_schedule,
			'share_target_sns' => $this->share_base_cache_target,
			'follow_target_sns' => $this->follow_base_cache_target,
			'export_file_name' => self::OPT_COMMON_DATA_EXPORT_FILE_NAME,
			'export_exclude_keys' => array( self::REF_SHARE_TOTAL, self::REF_CRAWL_DATE ),
			'post_types' => $this->share_base_cache_post_types
			);

		$this->export_engines[self::REF_COMMON_EXPORT] = SCC_Common_Data_Export_Engine::get_instance();
		$this->export_engines[self::REF_COMMON_EXPORT]->initialize( $options );

		// Share analytical engine
		$options = array(
			'delegate' => $this,
			'target_sns' => $this->share_base_cache_target,
			'check_interval' => $this->share_base_check_interval,
			'post_types' => $this->share_base_cache_post_types,
			'base_schedule' => $this->share_variation_analysis_schedule,
			'crawl_date_key' => self::REF_CRAWL_DATE
			);

		$this->analytical_engines[self::REF_SHARE_ANALYSIS] = SCC_Share_Analytical_Engine::get_instance();
		$this->analytical_engines[self::REF_SHARE_ANALYSIS]->initialize( $options );

		// Follow analytical engine
		$options = array(
			'delegate' => $this,
			'target_sns' => $this->follow_base_cache_target,
			'check_interval' => $this->follow_base_check_interval,
			'base_schedule' => $this->follow_variation_analysis_schedule,
			'crawl_date_key' => self::REF_CRAWL_DATE
			);

		$this->analytical_engines[self::REF_FOLLOW_ANALYSIS] = SCC_Follow_Analytical_Engine::get_instance();
		$this->analytical_engines[self::REF_FOLLOW_ANALYSIS]->initialize( $options );

		// Job reset engine
		$target_crons = array();

		foreach ( $this->cache_engines as $key => $cache_engine ) {
			$target_crons[] = $cache_engine->get_excute_cron();
		}

		foreach ( $this->control_engines as $key => $control_engine ) {
			$target_crons[] = $control_engine->get_excute_cron();
		}

		if ( $this->data_export_mode === self::OPT_COMMON_DATA_EXPORT_SCHEDULER ) {
			$target_crons[] = $this->export_engines[self::REF_COMMON_EXPORT]->get_excute_cron();
		}

		if ( $this->share_variation_analysis_mode === self::OPT_SHARE_VARIATION_ANALYSIS_SCHEDULER ) {
			$target_crons[] = $this->analytical_engines[self::REF_SHARE_ANALYSIS]->get_excute_cron();
		}

		if ( $this->follow_variation_analysis_mode === self::OPT_FOLLOW_VARIATION_ANALYSIS_SCHEDULER ) {
			$target_crons[] = $this->analytical_engines[self::REF_FOLLOW_ANALYSIS]->get_excute_cron();
		}

		$options = array(
			'delegate' => $this,
			'check_interval' => 600,
			'expiration_time ' => 1800,
			'target_cron' => $target_crons
			);

		$this->control_engines[self::REF_COMMON_CONTROL] = SCC_Common_Job_Reset_Engine::get_instance();
		$this->control_engines[self::REF_COMMON_CONTROL]->initialize( $options );

		$tmp_max_execution_time = ini_get( 'max_execution_time' );

		if ( isset( $tmp_max_execution_time ) && $tmp_max_execution_time > 0 ) {
			$this->original_max_execution_time = $tmp_max_execution_time;
		} else {
			$this->original_max_execution_time = 30;
		}

		$this->loading_img_url = plugins_url( '/images/loading.gif', __FILE__ );

	}

	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 * @since 0.1.0
	 */
	public function register_admin_styles() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( in_array( $screen->id, $this->plugin_screen_hook_suffix ) ) {
			wp_enqueue_style( self::DOMAIN .'-admin-style-1', plugins_url( ltrim( '/css/sns-count-cache.css', '/' ), __FILE__ ) );
			wp_enqueue_style( self::DOMAIN .'-admin-style-2', plugins_url( ltrim( '/css/monokai.css', '/' ), __FILE__ ) );
			wp_enqueue_style( 'jquery-ui-datepicker-style' , '//ajax.googleapis.com/ajax/libs/jqueryui/1/themes/smoothness/jquery-ui.css' );
		}
	}

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 *
	 * @since 0.1.0
	 */
	public function register_admin_scripts() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( in_array( $screen->id, $this->plugin_screen_hook_suffix ) ) {
			wp_enqueue_script( self::DOMAIN . '-admin-script-1', plugins_url( ltrim( '/js/highlight.pack.js', '/' ) , __FILE__ ), array( 'jquery' ) );

			wp_enqueue_script( self::DOMAIN . '-admin-script-2', plugins_url( ltrim( '/js/jquery.scc-cache-info.min.js', '/' ), __FILE__ ), array( 'jquery' ) );
			wp_localize_script( self::DOMAIN . '-admin-script-2', 'scc', array( 'endpoint' => admin_url( 'admin-ajax.php' ), 'action' => $this->ajax_action, 'nonce' => wp_create_nonce( $this->ajax_action ) ) );

			wp_enqueue_script( 'jquery-ui-datepicker' );
		}
	}

	/**
	 * Activate cache engine (schedule cron)
	 *
	 * @since 0.1.1
	 */
	public function activate_plugin() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$this->initialize();

		set_time_limit( $this->extended_max_execution_time );

		foreach ( $this->cache_engines as $key => $cache_engine ) {
			switch ( $key ) {
				case self::REF_SHARE_2ND:
					$cache_engine->initialize_cache();
					break;
				case self::REF_FOLLOW_2ND:
					$cache_engine->initialize_cache();
					break;
				default:
					$cache_engine->initialize_cache();
					$cache_engine->register_schedule();
			}
		}

		foreach ( $this->control_engines as $key => $control_engine ) {
			$control_engine->register_schedule();
		}

		if ( $this->share_variation_analysis_mode === self::OPT_SHARE_VARIATION_ANALYSIS_SCHEDULER ) {
			$this->analytical_engines[self::REF_SHARE_ANALYSIS]->register_schedule();
		}

		if ( $this->follow_variation_analysis_mode === self::OPT_FOLLOW_VARIATION_ANALYSIS_SCHEDULER ) {
			$this->analytical_engines[self::REF_FOLLOW_ANALYSIS]->register_schedule();
		}

		if ( $this->data_export_mode === self::OPT_COMMON_DATA_EXPORT_SCHEDULER ) {
			$this->export_engines[self::REF_COMMON_EXPORT]->register_schedule();
		}

		set_time_limit( $this->original_max_execution_time  );
	}

	/**
	 * Deactivate cache engine (schedule cron)
	 *
	 * @since 0.1.1
	 */
	public function deactivate_plugin() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		set_time_limit( $this->extended_max_execution_time );

		foreach ( $this->cache_engines as $key => $cache_engine ) {
			$cache_engine->unregister_schedule();
			$cache_engine->clear_cache();
		}

		foreach ( $this->control_engines as $key => $control_engine ) {
			$control_engine->unregister_schedule();
		}

		$this->analytical_engines[self::REF_SHARE_ANALYSIS]->unregister_schedule();
		$this->analytical_engines[self::REF_SHARE_ANALYSIS]->clear_base();

		$this->analytical_engines[self::REF_FOLLOW_ANALYSIS]->unregister_schedule();
		$this->analytical_engines[self::REF_FOLLOW_ANALYSIS]->clear_base();

		$this->export_engines[self::REF_COMMON_EXPORT]->unregister_schedule();

		set_time_limit( $this->original_max_execution_time );
	}

	/**
	 * Reactivate cache engine
	 *
	 * @since 0.1.1
	 */
	function reactivate_plugin() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$this->deactivate_plugin();
		$this->activate_plugin();
	}

	/**
	 * Delete related cache when the post was deleted.
	 *
	 * @since 0.7.0
	 */
	public function clear_cache_deleted_post( $post_id ) {
		if ( isset( $post_id ) && $post_id ) {
			$this->cache_engines[self::REF_SHARE_BASE]->clear_cache_by_post_id( $post_id );
			$this->cache_engines[self::REF_SHARE_2ND]->clear_cache_by_post_id( $post_id );
			$this->analytical_engines[self::REF_SHARE_ANALYSIS]->clear_base_by_post_id( $post_id );
		}
	}

	/**
	 * Adds options & management pages to the admin menu.
	 *
	 * Run using the 'admin_menu' action.
	 *
	 * @since 0.1.0
	 */
	public function register_admin_menu() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$this->plugin_screen_hook_suffix[] = 'dashboard';
		$this->plugin_screen_hook_suffix[] = add_menu_page( __( 'SNS Count Cache', self::DOMAIN ), __( 'SNS Count Cache', self::DOMAIN ), self::OPT_COMMON_CAPABILITY, 'scc-dashboard', array( $this, 'dashboard_page' ), 'dashicons-share' );
		$this->plugin_screen_hook_suffix[] = add_submenu_page( 'scc-dashboard', __( 'Dashboard | SNS Count Cache', self::DOMAIN ), __( 'Dashboard', self::DOMAIN ), self::OPT_COMMON_CAPABILITY, 'scc-dashboard', array( $this, 'dashboard_page' ) );
		$this->plugin_screen_hook_suffix[] = add_submenu_page( 'scc-dashboard', __( 'Cache Status | SNS Count Cache', self::DOMAIN ), __( 'Cache Status', self::DOMAIN ), self::OPT_COMMON_CAPABILITY, 'scc-cache-status', array( $this, 'cache_status_page' ) );
		$this->plugin_screen_hook_suffix[] = add_submenu_page( 'scc-dashboard', __( 'Share Count | SNS Count Cache', self::DOMAIN ), __( 'Share Count', self::DOMAIN ), self::OPT_COMMON_CAPABILITY, 'scc-share-count', array( $this, 'share_count_page' ) );

		if ( $this->share_variation_analysis_mode !== self::OPT_SHARE_VARIATION_ANALYSIS_NONE ) {
			$this->plugin_screen_hook_suffix[] = add_submenu_page( 'scc-dashboard', __( 'Hot Content | SNS Count Cache', self::DOMAIN ), __( 'Hot Content', self::DOMAIN ), self::OPT_COMMON_CAPABILITY, 'scc-hot-content', array( $this, 'hot_content_page' ) );
		}

		$this->plugin_screen_hook_suffix[] = add_submenu_page( 'scc-dashboard', __( 'Setting | SNS Count Cache', self::DOMAIN ), __( 'Setting', self::DOMAIN ), self::OPT_COMMON_CAPABILITY, 'scc-setting', array( $this, 'setting_page' ) );
		$this->plugin_screen_hook_suffix[] = add_submenu_page( 'scc-dashboard', __( 'Help | SNS Count Cache', self::DOMAIN ), __( 'Help', self::DOMAIN ), self::OPT_COMMON_CAPABILITY, 'scc-help', array( $this, 'help_page' ) );
    }

	/**
	 * Add plugin action links
	 * @since 0.10.1
	 */
	public function add_plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {

		$actions['scc-setting'] = '<a href="' . admin_url( 'admin.php?page=scc-setting' ). '">' . __( 'Setting', self::DOMAIN ) . '</a>';

		return $actions;
	}

	/**
	 * Add widget to wordpress dashboard
	 *
	 * @since 0.5.1
	 */
	public function add_wp_dashboard_widget() {
		if ( ! current_user_can( self::OPT_COMMON_CAPABILITY ) ) {
			return false;
		}
		wp_add_dashboard_widget( 'scc_dashboard', 'SNS Count Cache', array( $this, 'wp_dashboard_page' ) );
	}

	/**
	 * Option page implementation
	 *
	 * @since 0.5.1
	 */
	public function wp_dashboard_page() {
		if ( ! current_user_can( self::OPT_COMMON_CAPABILITY ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once( dirname( __FILE__ ) . '/includes/admin-dashboard-widget.php' );
	}


	/**
	 * Option page implementation
	 *
	 * @since 0.1.0
	 */
	public function dashboard_page() {
		if ( ! current_user_can( self::OPT_COMMON_CAPABILITY ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once( dirname( __FILE__ ) . '/includes/admin-dashboard.php' );
	}

	/**
	 * Option page implementation
	 *
	 * @since 0.1.0
	 */
	public function cache_status_page() {
		if ( ! current_user_can( self::OPT_COMMON_CAPABILITY ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once( dirname( __FILE__ ) . '/includes/admin-cache-status.php' );
	}

	/**
	 * Option page implementation
	 *
	 * @since 0.1.0
	 */
	public function share_count_page() {
		if ( ! current_user_can( self::OPT_COMMON_CAPABILITY ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once( dirname( __FILE__ ) . '/includes/admin-share-count.php' );
  	}

	/**
	 * Option page implementation
	 *
	 * @since 0.1.0
	 */
	public function setting_page() {
		if ( ! current_user_can( self::OPT_COMMON_CAPABILITY ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once( dirname( __FILE__ ) . '/includes/admin-setting.php' );
	}

	/**
	 * Option page implementation
	 *
	 * @since 0.1.0
	 */
	public function help_page() {
		if ( ! current_user_can( self::OPT_COMMON_CAPABILITY ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once( dirname( __FILE__ ) . '/includes/admin-help.php' );
	}

	/**
	 * Option page implementation
	 *
	 * @since 0.5.1
	 */
	public function notice_page() {
		if ( ! current_user_can( self::OPT_COMMON_CAPABILITY ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once( dirname( __FILE__ ) . '/includes/admin-notice.php' );
	}

	/**
	 * Option page implementation
	 *
	 * @since 0.6.1
	 */
	public function hot_content_page() {
		if ( ! current_user_can( self::OPT_COMMON_CAPABILITY ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once( dirname( __FILE__ ) . '/includes/admin-hot-content.php' );
	}

	/**
	 * Method call between one cache engine and another
	 *
	 * @since 0.9.3
	 */
	public function order( SCC_Cache_Engine $engine, $order, $options = array() ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		switch ( get_class( $engine ) ) {
			case 'SCC_Share_Lazy_Cache_Engine':
				if ( $order === SCC_Order::ORDER_DO_SECOND_CACHE ) {
					$this->cache_engines[self::REF_SHARE_2ND]->cache( $options );
				} elseif ( $order === SCC_Order::ORDER_GET_SECOND_CACHE ) {
					return $this->cache_engines[self::REF_SHARE_2ND]->get_cache( $options );
				}
				break;
			case 'SCC_Share_Base_Cache_Engine':
				if ( $order === SCC_Order::ORDER_DO_SECOND_CACHE) {
					$this->cache_engines[self::REF_SHARE_2ND]->cache( $options );
				} elseif ( $order === SCC_Order::ORDER_GET_SECOND_CACHE ) {
					return $this->cache_engines[self::REF_SHARE_2ND]->get_cache( $options );
				}
				break;
			case 'SCC_Share_Second_Cache_Engine':
				if ( $order === SCC_Order::ORDER_DO_ANALYSIS ) {
					if ( $this->share_variation_analysis_mode !== self::OPT_SHARE_VARIATION_ANALYSIS_NONE ) {
						$this->analytical_engines[self::REF_SHARE_ANALYSIS]->analyze( $options );
					}
				}
				break;
			case 'SCC_Share_Rush_Cache_Engine':
				if ( $order === SCC_Order::ORDER_DO_SECOND_CACHE) {
					$this->cache_engines[self::REF_SHARE_2ND]->cache( $options );
				} elseif ( $order === SCC_Order::ORDER_GET_SECOND_CACHE ) {
					return $this->cache_engines[self::REF_SHARE_2ND]->get_cache( $options );
				}
				break;
			case 'SCC_Follow_Lazy_Cache_Engine':
				if ( $order === SCC_Order::ORDER_DO_SECOND_CACHE) {
					$this->cache_engines[self::REF_FOLLOW_2ND]->cache( $options );
				}
				break;
			case 'SCC_Follow_Base_Cache_Engine':
				if ( $order === SCC_Order::ORDER_DO_SECOND_CACHE) {
					$this->cache_engines[self::REF_FOLLOW_2ND]->cache( $options );
				}
				break;
			case 'SCC_Follow_Second_Cache_Engine':
				if ( $order === SCC_Order::ORDER_DO_ANALYSIS ) {
					if ( $this->follow_variation_analysis_mode !== self::OPT_FOLLOW_VARIATION_ANALYSIS_NONE ) {
						$this->analytical_engines[self::REF_FOLLOW_ANALYSIS]->analyze( $options );
					}
				}
				break;
		}
  	}

	/**
	 * Return pagination
	 *
	 * @since 0.4.0
	 */
	private function pagination( $numpages = '', $pagerange = '', $paged='', $inherit_param = true ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		if ( empty( $pagerange ) ) {
			$pagerange = 2;
		}

		if ( $paged === '' ) {
			global $paged;

			if ( empty( $paged ) ) {
				$paged = 1;
			}
		}

		if ( $numpages === '' ) {
			global $wp_query;

			$numpages = $wp_query->max_num_pages;

			if( ! $numpages ) {
				$numpages = 1;
			}
		}

		$pagination_args = array();

		$url = parse_url( get_pagenum_link(1) );
		$base_url = $url['scheme'] . '://' . $url['host'] . $url['path'];

		parse_str ( $url['query'], $query );

		$base_url = $base_url . '?page=' . $query['page'];

		SCC_Common_Util::log( '[' . __METHOD__ . '] base url: ' . $base_url );

		$pagination_args = array(
			'base' => $base_url . '%_%',
			'format' => '&paged=%#%',
			'total' => $numpages,
			'current' => $paged,
			'show_all' => false,
			'end_size' => 1,
			'mid_size' => $pagerange,
			'prev_next' => true,
			'prev_text' => '&laquo;',
			'next_text' => '&raquo;',
			'type' => 'plain',
			'add_args' => '',
			'add_fragment' => ''
			);

		$paginate_links = paginate_links( $pagination_args );

		if ( $inherit_param ) {
			SCC_Common_Util::log( '[' . __METHOD__ . '] inherit param: true' );
		} else {
			SCC_Common_Util::log( '[' . __METHOD__ . '] inherit param: false' );

			$pattern = '/(?:&#038;action=cache&#038;post_id=[0-9]+&#038;_wpnonce=.{10})/';
			$paginate_links = preg_replace( $pattern, '', $paginate_links );
		}

		if ( $paginate_links ) {
			echo "<nav class='pagination'>";
			echo "<span class='page-numbers page-num'>Page " . $paged . " of " . $numpages . "</span> ";
			echo $paginate_links;
			echo "</nav>";
		}
	}

	/**
	 * Return cache information through ajax interface
	 *
	 * @since 0.5.1
	 */
	public function get_cache_info() {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		if ( isset( $_REQUEST['nonce'] ) && wp_verify_nonce( $_REQUEST['nonce'], $this->ajax_action ) ) {
			if ( current_user_can( self::OPT_COMMON_CAPABILITY ) ) {

				$share_base_cache_target = $this->share_base_cache_target;

				unset( $share_base_cache_target[self::REF_CRAWL_DATE] );

				$posts_count = 0;
				$primary_full_cache_count = 0;
				$primary_partial_cache_count = 0;
				$primary_no_cache_count = 0;

				$secondary_full_cache_count = 0;
				$secondary_partial_cache_count = 0;
				$secondary_no_cache_count = 0;

				$sum = array();
				$delta = array();
				$return = array();

				foreach ( $share_base_cache_target as $sns => $active ) {
					if ( $active ) {
						$sum[$sns] = 0;
						$delta[$sns] = 0;
					}
				}

				$query_args = array(
					'post_type' => $this->share_base_cache_post_types,
					'post_status' => 'publish',
					'nopaging' => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false
					);

				set_time_limit( $this->extended_max_execution_time  );

				//home
				++$posts_count;

				$full_cache_flag = true;
				$partial_cache_flag = false;

				$transient_id = $this->cache_engines[self::REF_SHARE_BASE]->get_cache_key( 'home' );

				if ( false !== ( $sns_counts = get_transient( $transient_id ) ) ) {

					foreach ( $share_base_cache_target as $sns => $active ) {
						if ( $active ) {
							if ( isset( $sns_counts[$sns] ) && $sns_counts[$sns] >= 0 ) {
								$sum[$sns] = $sum[$sns] + $sns_counts[$sns];
								$partial_cache_flag = true;
							} else {
								$full_cache_flag = false;
							}
						}
					}

					if ( $partial_cache_flag && $full_cache_flag ) {
						++$primary_full_cache_count;
					} elseif ( $partial_cache_flag && ! $full_cache_flag ) {
						++$primary_partial_cache_count;
					} else {
						++$primary_no_cache_count;
					}

					$full_cache_flag = true;
					$partial_cache_flag = false;

					$option_key = $this->cache_engines[self::REF_SHARE_2ND]->get_cache_key( 'home' );

					if ( false !== ( $sns_counts = get_option( $option_key ) ) ) {
						foreach ( $share_base_cache_target as $sns => $active ) {
							if ( $active ) {
								if ( $sns_counts[$sns] >= 0 ) {
									$partial_cache_flag  = true;
								} else {
									$full_cache_flag = false;
								}
							}
						}
					} else {
						foreach ( $share_base_cache_target as $sns => $active ) {
							if ( $active ){
								$full_cache_flag = false;
							}
						}
					}

					if ( $partial_cache_flag && $full_cache_flag ) {
						++$secondary_full_cache_count;
					} elseif ( $partial_cache_flag && ! $full_cache_flag ) {
						++$secondary_partial_cache_count;
					} else {
						++$secondary_no_cache_count;
					}

				} else {

					$option_key = $this->cache_engines[self::REF_SHARE_2ND]->get_cache_key( 'home' );

					if ( false !== ( $sns_counts = get_option( $option_key ) ) ) {
						foreach ( $share_base_cache_target as $sns => $active ) {
							if ( $active ){
								if ( $sns_counts[$sns] >= 0 ) {
								  	$sum[$sns] = $sum[$sns] + $sns_counts[$sns];
									$partial_cache_flag  = true;
								} else {
									$full_cache_flag = false;
								}
							}
						}
					} else {
						foreach ( $share_base_cache_target as $sns => $active ) {
							if ( $active ){
								$full_cache_flag = false;
							}
						}
					}

					if ( $partial_cache_flag && $full_cache_flag ) {
						++$secondary_full_cache_count;
					} elseif ( $partial_cache_flag && ! $full_cache_flag ) {
						++$secondary_partial_cache_count;
					} else {
						++$secondary_no_cache_count;
					}

					++$primary_no_cache_count;
				}

				$option_key = $this->analytical_engines[self::REF_SHARE_ANALYSIS]->get_delta_key( 'home' );

				if ( false !== ( $sns_deltas = get_option( $option_key ) ) ) {
					foreach ( $share_base_cache_target as $sns => $active ) {
						if ( $active ){
							if ( isset( $sns_deltas[$sns] ) ) {
								$delta[$sns] = $delta[$sns] + $sns_deltas[$sns];
							}
						}
					}
				}

				//page, post
				$site_query = new WP_Query( $query_args );

				if ( $site_query->have_posts() ) {
					while ( $site_query->have_posts() ) {
						$site_query->the_post();

						++$posts_count;

						$full_cache_flag = true;
						$partial_cache_flag = false;

						$transient_id = $this->cache_engines[self::REF_SHARE_BASE]->get_cache_key( get_the_ID() );

						if ( false !== ( $sns_counts = get_transient( $transient_id ) ) ) {

							foreach ( $share_base_cache_target as $sns => $active ) {
								if ( $active ) {
									if ( isset( $sns_counts[$sns] ) && $sns_counts[$sns] >= 0 ) {
										$sum[$sns] = $sum[$sns] + $sns_counts[$sns];
										$partial_cache_flag = true;
									} else {
										$full_cache_flag = false;
									}
								}
							}

							if ( $partial_cache_flag && $full_cache_flag ) {
								++$primary_full_cache_count;
							} elseif ( $partial_cache_flag && ! $full_cache_flag ) {
								++$primary_partial_cache_count;
							} else {
								++$primary_no_cache_count;
							}

							$full_cache_flag = true;
							$partial_cache_flag = false;

							$sns_counts = array();
							$sns_deltas = array();

							foreach ( $share_base_cache_target as $sns => $active ) {
								if ( $active ) {
									$meta_key = $this->cache_engines[self::REF_SHARE_2ND]->get_cache_key( $sns );
									$sns_counts[$sns] = get_post_meta( get_the_ID(), $meta_key, true );

									if ( isset( $sns_counts[$sns] ) && $sns_counts[$sns] !== '' &&  $sns_counts[$sns] >= 0 ) {
										$partial_cache_flag  = true;
									} else {
										$full_cache_flag = false;
									}

									if ( $this->share_variation_analysis_mode !== self::OPT_SHARE_VARIATION_ANALYSIS_NONE ) {
										//delta
										$meta_key = $this->analytical_engines[self::REF_SHARE_ANALYSIS]->get_delta_key( $sns );

										$sns_deltas[$sns] = get_post_meta( get_the_ID(), $meta_key, true );

										if ( isset( $sns_deltas[$sns] ) && $sns_deltas[$sns] !== '' ) {
											$delta[$sns] = $delta[$sns] + $sns_deltas[$sns];
										}
									}
								}
							}

							if ( $partial_cache_flag && $full_cache_flag ) {
								++$secondary_full_cache_count;
							} elseif ( $partial_cache_flag && ! $full_cache_flag ) {
								++$secondary_partial_cache_count;
							} else {
								++$secondary_no_cache_count;
							}

						} else {
							$sns_deltas = array();
							$sns_counts = array();

							foreach ( $share_base_cache_target as $sns => $active ) {
								if ( $active ){
									$meta_key = $this->cache_engines[self::REF_SHARE_2ND]->get_cache_key( $sns );

									$sns_counts[$sns] = get_post_meta( get_the_ID(), $meta_key, true );

									if ( isset( $sns_counts[$sns] ) && $sns_counts[$sns] !== '' &&  $sns_counts[$sns] >= 0 ) {
										$sum[$sns] = $sum[$sns] + $sns_counts[$sns];
										$partial_cache_flag  = true;
									} else {
										$full_cache_flag = false;
									}

									if ( $this->share_variation_analysis_mode !== self::OPT_SHARE_VARIATION_ANALYSIS_NONE ) {
										//delta
										$meta_key = $this->analytical_engines[self::REF_SHARE_ANALYSIS]->get_delta_key( $sns );

										$sns_deltas[$sns] = get_post_meta( get_the_ID(), $meta_key, true );

										if ( isset( $sns_deltas[$sns] ) && $sns_deltas[$sns] !== '' ) {
											$delta[$sns] = $delta[$sns] + $sns_deltas[$sns];
										}
									}
								}
							}

							if ( $partial_cache_flag && $full_cache_flag ) {
								++$secondary_full_cache_count;
							} elseif ( $partial_cache_flag && ! $full_cache_flag ) {
								++$secondary_partial_cache_count;
							} else {
								++$secondary_no_cache_count;
							}

							++$primary_no_cache_count;
						}
					}
					wp_reset_postdata();
				}

				set_time_limit( $this->original_max_execution_time  );

				foreach ( $share_base_cache_target as $sns => $active ) {
					if ( $active ) {
						if ( isset( $sum[$sns] ) ) {
							if ( $sns === self::REF_SHARE_GPLUS ){
								$return['share_count']['gplus'] = number_format( (int) $sum[$sns] );
								$return['share_delta']['gplus'] = number_format( (int) $delta[$sns] );
							} else {
								$return['share_count'][strtolower( $sns )] = number_format( (int) $sum[$sns] );
								$return['share_delta'][strtolower( $sns )] = number_format( (int) $delta[$sns] );
							}
						}
					}
				}

				$return['share']['post_count'] = $posts_count;
				$return['share']['primary']['full_cache_count'] = $primary_full_cache_count;
				$return['share']['primary']['partial_cache_count'] = $primary_partial_cache_count;
				$return['share']['primary']['no_cache_count'] = $primary_no_cache_count;
				$return['share']['secondary']['full_cache_count'] = $secondary_full_cache_count;
				$return['share']['secondary']['partial_cache_count'] = $secondary_partial_cache_count;
				$return['share']['secondary']['no_cache_count'] = $secondary_no_cache_count;

				if ( $primary_full_cache_count === $posts_count ) {
					$return['share']['primary']['cache_status'] = __( 'Completed', self::DOMAIN );
				} elseif ( ( $primary_full_cache_count + $primary_partial_cache_count ) === $posts_count ) {
					$return['share']['primary']['cache_status'] = __( 'Partially Completed', self::DOMAIN );
				} else {
					$return['share']['primary']['cache_status'] = __( 'Ongoing', self::DOMAIN );
				}

				if ( $secondary_full_cache_count === $posts_count ) {
					$return['share']['secondary']['cache_status'] = __( 'Completed', self::DOMAIN );
				} elseif ( ( $secondary_full_cache_count + $secondary_partial_cache_count ) === $posts_count ) {
					$return['share']['secondary']['cache_status'] = __( 'Partially Completed', self::DOMAIN );
				} else {
					$return['share']['secondary']['cache_status'] = __( 'Ongoing', self::DOMAIN );
				}

				// Follow count
				$follow_base_cache_target = $this->follow_base_cache_target;

				unset( $follow_base_cache_target[self::REF_CRAWL_DATE] );

				$primary_full_cache_count = 0;
				$primary_partial_cache_count = 0;
				$primary_no_cache_count = 0;

				$secondary_full_cache_count = 0;
				$secondary_partial_cache_count = 0;
				$secondary_no_cache_count = 0;

				$sum = array();
				$delta = array();

				foreach ( $follow_base_cache_target as $sns => $active ) {
					if ( $active ){
						$sum[$sns] = 0;
						$delta[$sns] = 0;
					}
				}

				$full_cache_flag = true;
				$partial_cache_flag = false;

				$transient_id = $this->cache_engines[self::REF_FOLLOW_BASE]->get_cache_key( 'follow' );

				if ( false !== ( $sns_followers = get_transient( $transient_id ) ) ) {

					foreach ( $follow_base_cache_target as $sns => $active ) {
						if ( $active ) {
							if ( isset( $sns_followers[$sns] ) && $sns_followers[$sns] >= 0 ) {
								$sum[$sns] = $sum[$sns] + $sns_followers[$sns];
								$partial_cache_flag = true;
							} else {
								$full_cache_flag = false;
							}
						}
					}

					if ( $partial_cache_flag && $full_cache_flag ) {
						++$primary_full_cache_count;
					} elseif ( $partial_cache_flag && ! $full_cache_flag ) {
						++$primary_partial_cache_count;
					} else {
						++$primary_no_cache_count;
					}

					$full_cache_flag = true;
					$partial_cache_flag = false;

					$option_key = $this->cache_engines[self::REF_FOLLOW_2ND]->get_cache_key( 'follow' );

					if ( false !== ( $sns_followers = get_option( $option_key ) ) ) {
						foreach ( $follow_base_cache_target as $sns => $active ) {
							if ( $active ) {
								if ( isset( $sns_followers[$sns] ) && $sns_followers[$sns] >= 0 ) {
									$partial_cache_flag  = true;
								} else {
									$full_cache_flag = false;
								}
							}
						}
					}

					if ( $this->follow_variation_analysis_mode !== self::OPT_FOLLOW_VARIATION_ANALYSIS_NONE ) {
						//delta
						$option_key = $this->analytical_engines[self::REF_FOLLOW_ANALYSIS]->get_delta_key( 'follow' );

						if ( false !== ( $sns_deltas = get_option( $option_key ) ) ) {
							foreach ( $follow_base_cache_target as $sns => $active ) {
								if ( $active ) {
									if ( isset( $sns_deltas[$sns] ) && $sns_deltas[$sns] >= 0 ) {
										$delta[$sns] = $delta[$sns] + $sns_deltas[$sns];
									}
								}
							}
						}
					}

					if ( $partial_cache_flag && $full_cache_flag ) {
						++$secondary_full_cache_count;
					} elseif ( $partial_cache_flag && ! $full_cache_flag ) {
						++$secondary_partial_cache_count;
					} else {
						++$secondary_no_cache_count;
					}

				} else {
					$option_key = $this->cache_engines[self::REF_FOLLOW_2ND]->get_cache_key( 'follow' );

					if ( false !== ( $sns_followers = get_option( $option_key ) ) ) {
						foreach ( $follow_base_cache_target as $sns => $active ) {
							if ( $active ) {
								if ( isset( $sns_followers[$sns] ) && $sns_followers[$sns] >= 0 ) {
									$sum[$sns] = $sum[$sns] + $sns_followers[$sns];
									$partial_cache_flag  = true;
								} else {
									$full_cache_flag = false;
								}
							}
						}
					}

					if ( $this->follow_variation_analysis_mode !== self::OPT_FOLLOW_VARIATION_ANALYSIS_NONE ) {
						//delta
						$option_key = $this->analytical_engines[self::REF_FOLLOW_ANALYSIS]->get_delta_key( 'follow' );

						if ( false !== ( $sns_deltas = get_option( $option_key ) ) ) {
							foreach ( $follow_base_cache_target as $sns => $active ) {
								if ( $active ) {
									if ( isset( $sns_deltas[$sns] ) && $sns_deltas[$sns] >= 0 ) {
										$delta[$sns] = $delta[$sns] + $sns_deltas[$sns];
									}
								}
							}
						}
					}

					if ( $partial_cache_flag && $full_cache_flag ) {
						++$secondary_full_cache_count;
					} elseif ( $partial_cache_flag && ! $full_cache_flag ) {
						++$secondary_partial_cache_count;
					} else {
						++$secondary_no_cache_count;
					}

					++$primary_no_cache_count;
				}

				foreach ( $follow_base_cache_target as $sns => $active ) {
					if ( $active ) {
						if ( isset( $sum[$sns] ) ) {
							if ( $sns === self::REF_SHARE_GPLUS ){
								$return['follow_count']['gplus'] = number_format( (int) $sum[$sns] );
								$return['follow_delta']['gplus'] = number_format( (int) $delta[$sns] );
							} else {
								$return['follow_count'][strtolower( $sns )] = number_format( (int) $sum[$sns] );
								$return['follow_delta'][strtolower( $sns )] = number_format( (int) $delta[$sns] );
							}
						}
					}
				}

				$posts_count = 1;
				$return['follow']['post_count'] = $posts_count;
				$return['follow']['primary']['full_cache_count'] = $primary_full_cache_count;
				$return['follow']['primary']['partial_cache_count'] = $primary_partial_cache_count;
				$return['follow']['primary']['no_cache_count'] = $primary_no_cache_count;
				$return['follow']['secondary']['full_cache_count'] = $secondary_full_cache_count;
				$return['follow']['secondary']['partial_cache_count'] = $secondary_partial_cache_count;
				$return['follow']['secondary']['no_cache_count'] = $secondary_no_cache_count;

				if ( $primary_full_cache_count === $posts_count ) {
					$return['follow']['primary']['cache_status'] = __( 'Completed', self::DOMAIN );
				} elseif ( ( $primary_full_cache_count + $primary_partial_cache_count ) === $posts_count ) {
					$return['follow']['primary']['cache_status'] = __( 'Partially Completed', self::DOMAIN );
				} else {
					$return['follow']['primary']['cache_status'] = __( 'Ongoing', self::DOMAIN );
				}

				if ( $secondary_full_cache_count === $posts_count ) {
					$return['follow']['secondary']['cache_status'] = __( 'Completed', self::DOMAIN );
				} elseif ( ( $secondary_full_cache_count + $secondary_partial_cache_count ) === $posts_count ) {
					$return['follow']['secondary']['cache_status'] = __( 'Partially Completed', self::DOMAIN );
				} else {
					$return['follow']['secondary']['cache_status'] = __( 'Ongoing', self::DOMAIN );
				}

				SCC_Common_Util::log( $return );

				$callback = $_REQUEST["callback"];

				header( 'Content-type: application/javascript; charset=utf-8' );

				echo $callback . '(' . json_encode( $return ) . ')';
			} else {
				status_header( '403' );
				echo 'Forbidden';
			}
		} else {
			status_header( '403' );
			echo 'Forbidden';
		}

		die();
	}

	/**
	 * Return share count
	 *
	 * @since 0.5.1
	 */
	public function get_share_counts( $post_ID = '', $sns_key = '' ) {

		$sns_counts = array();

		$transient_id = $this->cache_engines[self::REF_SHARE_BASE]->get_cache_key( $post_ID );

		if ( false !== ( $sns_counts = get_transient( $transient_id ) ) ) {
			if ( $sns_key ) {
				if ( ! isset( $sns_counts[$sns_key] ) || $sns_counts[$sns_key] < 0 ) {
					$sns_counts[$sns_key] = 0;
				}
				return $sns_counts[$sns_key];
			} else {
				foreach ( $this->share_base_cache_target as $sns => $active ) {
					if ( $active ) {
						if ( ! isset( $sns_counts[$sns] ) || $sns_counts[$sns] < 0 ) {
							$sns_counts[$sns] = 0;
						}
					}
				}
				return $sns_counts;
			}
		} else {
			if ( $sns_key ) {
				if ( $post_ID !== 'home' ) {
					$meta_key = $this->cache_engines[self::REF_SHARE_2ND]->get_cache_key( $sns_key );
					$sns_count = get_post_meta( $post_ID, $meta_key, true );

					$second_cache_flag = false;

					if ( isset( $sns_count ) && $sns_count !== '' ) {
						if ( $sns_count >= 0 ) {
							$sns_counts[$sns_key] = $sns_count;
							$second_cache_flag = true;
						} else {
							$sns_counts[$sns_key] = 0;
						}
					} else {
						$sns_counts[$sns_key] = 0;
					}

					if ( $second_cache_flag ) {
						$this->cache_engines[self::REF_SHARE_RESTORE]->prime_cache( $post_ID );
					} else {
						if ( $this->dynamic_cache_mode === self::OPT_COMMON_ACCESS_BASED_CACHE_ON ) {
							$this->cache_engines[self::REF_SHARE_LAZY]->prime_cache( $post_ID );
						}
					}
				} else {
					$option_key = $this->cache_engines[self::REF_SHARE_2ND]->get_cache_key( 'home' );

					$second_cache_flag = false;

					if ( false !== ( $sns_counts = get_option( $option_key ) ) ) {
						if ( ! isset( $sns_counts[$sns_key] ) || $sns_counts[$sns_key] < 0 ) {
							$sns_counts[$sns_key] = 0;
						} else {
							$second_cache_flag = true;
						}
					} else {
						$sns_counts[$sns_key] = 0;
					}

					if ( $second_cache_flag ) {
						$this->cache_engines[self::REF_SHARE_RESTORE]->prime_cache( $post_ID );
					} else {
						if ( $this->dynamic_cache_mode === self::OPT_COMMON_ACCESS_BASED_CACHE_ON ) {
							$this->cache_engines[self::REF_SHARE_LAZY]->prime_cache( $post_ID );
						}
					}
				}

				return $sns_counts[$sns_key];
			} else {
				if ( $post_ID !== 'home' ) {

					$second_cache_flag = false;

					foreach ( $this->share_base_cache_target as $sns => $active ) {
						if ( $active ) {
							$meta_key = $this->cache_engines[self::REF_SHARE_2ND]->get_cache_key( $sns );

							$sns_count = get_post_meta( $post_ID, $meta_key, true );

							if ( isset( $sns_count ) && $sns_count !== '' ) {
								if ( $sns_count >= 0 ) {
									$sns_counts[$sns] = $sns_count;
									$second_cache_flag = true;
								} else {
									$sns_counts[$sns] = 0;
								}
							} else {
								$sns_counts[$sns] = 0;
							}
						}
					}

					if ( $second_cache_flag ) {
						$this->cache_engines[self::REF_SHARE_RESTORE]->prime_cache( $post_ID );
					} else {
						if ( $this->dynamic_cache_mode === self::OPT_COMMON_ACCESS_BASED_CACHE_ON ) {
							$this->cache_engines[self::REF_SHARE_LAZY]->prime_cache( $post_ID );
						}
					}
				} else {
					$option_key = $this->cache_engines[self::REF_SHARE_2ND]->get_cache_key( 'home' );

					$second_cache_flag = false;

					if ( false !== ( $sns_counts = get_option( $option_key ) ) ) {
						foreach ( $this->share_base_cache_target as $sns => $active ) {
							if ( $active ) {
								if ( ! isset( $sns_counts[$sns] ) || $sns_counts[$sns] < 0 ) {
									$sns_counts[$sns] = 0;
								} else {
									$second_cache_flag = true;
								}
							}
						}
					} else {
						foreach ( $this->share_base_cache_target as $sns => $active ) {
							if ( $active ) {
								$sns_counts[$sns] = 0;
							}
						}
					}

					if ( $second_cache_flag ) {
						$this->cache_engines[self::REF_SHARE_RESTORE]->prime_cache( $post_ID );
					} else {
						if ( $this->dynamic_cache_mode === self::OPT_COMMON_ACCESS_BASED_CACHE_ON ) {
							$this->cache_engines[self::REF_SHARE_LAZY]->prime_cache( $post_ID );
						}
					}
				}
				return $sns_counts;
			}
		}
	}

	/**
	 * Return follow count
	 *
	 * @since 0.5.1
	 */
	public function get_follow_counts( $sns_key = '' ) {
		$sns_followers = array();

		$transient_id = $this->cache_engines[self::REF_FOLLOW_BASE]->get_cache_key( 'follow' );

		if ( false !== ( $sns_followers = get_transient( $transient_id ) ) ) {
			if ( $sns_key ) {
				if ( ! isset( $sns_followers[$sns_key] ) || $sns_followers[$sns_key] < 0 ) {
					$sns_followers[$sns_key] = 0;
				}
				return $sns_followers[$sns_key];
			} else {
				foreach ( $this->follow_base_cache_target as $sns => $active ) {
					if ( $active ) {
						if ( ! isset( $sns_followers[$sns] ) || $sns_followers[$sns] < 0 ) {
							$sns_followers[$sns] = 0;
						}
					}
				}
				return $sns_followers;
			}
		} else {
			$option_key = $this->cache_engines[self::REF_FOLLOW_2ND]->get_cache_key( 'follow' );

			if ( $sns_key ) {
				$second_cache_flag = false;

				if ( false !== ( $sns_followers = get_option( $option_key ) ) ) {
					if ( ! isset( $sns_followers[$sns_key] ) || $sns_followers[$sns_key] < 0 ) {
						$sns_followers[$sns_key] = 0;
					} else {
						$second_cache_flag = true;
					}
				} else {
					$sns_followers[$sns_key] = 0;
				}

				if ( $second_cache_flag ) {
					$this->cache_engines[self::REF_FOLLOW_RESTORE]->prime_cache();
				} else {
					$this->cache_engines[self::REF_FOLLOW_LAZY]->prime_cache();
				}

				return $sns_followers[$sns_key];
			} else {
				$second_cache_flag = false;

				if ( false !== ( $sns_followers = get_option( $option_key ) ) ) {
					foreach ( $this->follow_base_cache_target as $sns => $active ) {
						if ( $active ) {
							if ( ! isset( $sns_followers[$sns] ) || $sns_followers[$sns] < 0 ) {
								$sns_followers[$sns] = 0;
							} else {
								$second_cache_flag = true;
							}
						}
					}
				} else {
					foreach ( $this->follow_base_cache_target as $sns => $active ) {
						if ( $active ) {
							$sns_followers[$sns] = 0;
						}
					}
				}

				if ( $second_cache_flag ) {
					$this->cache_engines[self::REF_FOLLOW_RESTORE]->prime_cache();
				} else {
					if ( $this->dynamic_cache_mode === self::OPT_COMMON_ACCESS_BASED_CACHE_ON ) {
						$this->cache_engines[self::REF_FOLLOW_LAZY]->prime_cache();
					}
				}

				return $sns_followers;
			}
		}
	}

	/**
	 * Return if variation alaysis is enabled or not.
	 *
	 * @since 0.7.0
	 */
	public function is_share_variation_analysis_enabled() {

		if ( $this->share_variation_analysis_mode !== self::OPT_SHARE_VARIATION_ANALYSIS_NONE ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Return if variation alaysis is enabled or not.
	 *
	 * @since 0.10.1
	 */
	public function is_follow_variation_analysis_enabled() {

		if ( $this->follow_variation_analysis_mode !== self::OPT_FOLLOW_VARIATION_ANALYSIS_NONE ) {
			return true;
		} else {
			return false;
		}
	}

}

SNS_Count_Cache::get_instance();

/**
 * Get share count from cache
 *
 * @since 0.4.0
 */
function scc_get_share( $options = array( 'post_id' => '', 'url' => '', 'sns' => '' ) ) {
	$post_ID = '';
	$sns_key = '';

	if ( isset( $options['url'] ) && $options['url'] ) {
		$post_ID = url_to_postid( $options['url'] );
	} elseif ( isset( $options['post_id'] ) && $options['post_id'] ) {
		$post_ID = $options['post_id'];
	} else {
		$post_ID = get_the_ID();
	}

	if ( isset( $options['sns'] ) && $options['sns'] ) {
		$sns_key = $options['sns'];
	}

	$sns_count_cache = SNS_Count_Cache::get_instance();

	return $sns_count_cache->get_share_counts( $post_ID, $sns_key );
}

/**
 * Get follow count from cache
 *
 * @since 0.4.0
 */
function scc_get_follow( $options = array( 'sns' => '' ) ) {
	$sns_key = '';

	if ( isset( $options['sns'] ) && $options['sns'] ) {
		$sns_key = $options['sns'];
	}

	$sns_count_cache = SNS_Count_Cache::get_instance();

	return $sns_count_cache->get_follow_counts( $sns_key );
}

/**
 * Get share count from cache (Hatena Bookmark).
 *
 * @since 0.4.0
 */
function scc_get_share_hatebu( $options = array( 'post_id' => '', 'url' => '' ) ) {
	$options['sns'] = SNS_Count_Cache::REF_SHARE_HATEBU;
	return scc_get_share( $options );
}

/**
 * Get share count from cache (Twitter)
 *
 * @since 0.4.0
 */
function scc_get_share_twitter( $options = array( 'post_id' => '', 'url' => '' ) ) {
	$options['sns'] = SNS_Count_Cache::REF_SHARE_TWITTER;
	return scc_get_share( $options );
}

/**
 * Get share count from cache (Facebook)
 *
 * @since 0.4.0
 */
function scc_get_share_facebook( $options = array( 'post_id' => '', 'url' => '' ) ) {
	$options['sns'] = SNS_Count_Cache::REF_SHARE_FACEBOOK;
	return scc_get_share( $options );
}

/**
 * Get share count from cache (Google Plus)
 *
 * @since 0.4.0
 */
function scc_get_share_gplus( $options = array( 'post_id' => '', 'url' => '' ) ) {
	$options['sns'] = SNS_Count_Cache::REF_SHARE_GPLUS;
	return scc_get_share( $options );
}

/**
 * Get share count from cache (Pocket)
 *
 * @since 0.4.0
 */
function scc_get_share_pocket( $options = array( 'post_id' => '', 'url' => '' ) ) {
	$options['sns'] = SNS_Count_Cache::REF_SHARE_POCKET;
	return scc_get_share( $options );
}

/**
 * Get share count from cache (Pinterest)
 *
 * @since 0.9.3
 */
function scc_get_share_pinterest( $options = array( 'post_id' => '', 'url' => '' ) ) {
	$options['sns'] = SNS_Count_Cache::REF_SHARE_PINTEREST;
	return scc_get_share( $options );
}

/**
 * Get share count from cache (Linkedin)
 *
 * @since 0.11.0
 */
function scc_get_share_linkedin( $options = array( 'post_id' => '', 'url' => '' ) ) {
	$options['sns'] = SNS_Count_Cache::REF_SHARE_LINKEDIN;
	return scc_get_share( $options );
}

/**
 * Get share count from cache (Pocket)
 *
 * @since 0.4.0
 */
function scc_get_share_total( $options = array( 'post_id' => '', 'url' => '' ) ) {
	$options['sns'] = SNS_Count_Cache::REF_SHARE_TOTAL;
	return scc_get_share( $options );
}

/**
 * Get share count from cache (Hatena Bookmark).
 *
 * @since 0.1.0
 * @deprecated Function deprecated in Release 0.4.0
 */
function get_scc_hatebu( $options = array( 'post_id' => '', 'url' => '' ) ) {
	return scc_get_share_hatebu( $options );
}

/**
 * Get share count from cache (Twitter)
 *
 * @since 0.1.0
 * @deprecated Function deprecated in Release 0.4.0
 */
function get_scc_twitter( $options = array( 'post_id' => '', 'url' => '' ) ) {
	return scc_get_share_twitter( $options );
}

/**
 * Get share count from cache (Facebook)
 *
 * @since 0.1.0
 * @deprecated Function deprecated in Release 0.4.0
 */
function get_scc_facebook( $options = array( 'post_id' => '', 'url' => '' ) ) {
	return scc_get_share_facebook( $options );
}

/**
 * Get share count from cache (Google Plus)
 *
 * @since 0.1.0
 * @deprecated Function deprecated in Release 0.4.0
 */
function get_scc_gplus( $options = array( 'post_id' => '', 'url' => '' ) ) {
	return scc_get_share_gplus( $options );
}

/**
 * Get share count from cache (Pocket)
 *
 * @since 0.2.1
 * @deprecated Function deprecated in Release 0.4.0
 */
function get_scc_pocket( $options = array( 'post_id' => '', 'url' => '' ) ) {
	return scc_get_share_pocket( $options );
}

/**
 * Get follower count from cache (Feedly)
 *
 * @since 0.2.1
 */
function scc_get_follow_feedly() {
	$options['sns'] = SNS_Count_Cache::REF_FOLLOW_FEEDLY;
	return scc_get_follow( $options );
}

/**
 * Get follower count from cache (Feedly)
 *
 * @since 0.9.0
 */
function scc_get_follow_twitter() {
	$options['sns'] = SNS_Count_Cache::REF_FOLLOW_TWITTER;
	return scc_get_follow( $options );
}

/**
 * Get follower count from cache (Feedly)
 *
 * @since 0.9.0
 */
function scc_get_follow_facebook() {
	$options['sns'] = SNS_Count_Cache::REF_FOLLOW_FACEBOOK;
	return scc_get_follow( $options );
}

/**
 * Get follower count from cache (Push7)
 *
 * @since 0.9.0
 */
function scc_get_follow_push7() {
	$options['sns'] = SNS_Count_Cache::REF_FOLLOW_PUSH7;
	return scc_get_follow( $options );
}

/**
 * Get follower count from cache (Instagram)
 *
 * @since 0.9.0
 */
function scc_get_follow_instagram() {
	$options['sns'] = SNS_Count_Cache::REF_FOLLOW_INSTAGRAM;
	return scc_get_follow( $options );
}

/**
 * Return if variation alaysis for share count  is enabled or not.
 *
 * @since 0.7.0
 * @deprecated 0.11.0 Use scc_is_share_variation_analysis_enabled()
 */
function scc_is_variation_analysis_enabled() {
	return SNS_Count_Cache::get_instance()->is_share_variation_analysis_enabled();
}

/**
 * Return if variation alaysis for share count is enabled or not.
 *
 * @since 0.11.0
 */
function scc_is_share_variation_analysis_enabled() {
	return SNS_Count_Cache::get_instance()->is_share_variation_analysis_enabled();
}

/**
 * Return if variation alaysis for follower count is enabled or not.
 *
 * @since 0.11.0
 */
function scc_is_follow_variation_analysis_enabled() {
	return SNS_Count_Cache::get_instance()->is_follow_variation_analysis_enabled();
}

}

?>
