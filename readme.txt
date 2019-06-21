=== SNS Count Cache ===
Contributors: marubon
Donate link:
Tags: performance, SNS, social, cache, share, follower
Requires at least: 3.7
Tested up to: 4.7.2
Stable tag: 0.11.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin gets and caches SNS counts in the background, and help you to shorten page loading time through the use of cache mechanism.

== Description ==

SNS Count Cache is a plugin which helps you to shorten page loading time displaying share and follower counts through the use of cache mechanism.

Notice: PHP Version 5.3+ is required in order to activate and execute this plugin.

The plugin gets share counts for the following SNS and caches these counts in the background.

* Twitter
* Facebook
* Google+
* Pocket
* Pinterest
* Linkedin
* Hatena Bookmark

Note: You can select alternative Twitter API for share count retrieval from the following alternatives.

- widgetoon.js & count.jsoon
- OpenShareCount
- TwitCount
- NewShareCounts

The plugin also caches follower counts for the following SNS in the same way.

* Twitter
* Facebook
* Feedly
* Instagram
* Push7

The share and follower counts can be retrieved quickly not through network but through the cache using given functions.

The following shows functions to get share count from the cache:

* scc_get_share_twitter()
* scc_get_share_facebook()
* scc_get_share_gplus()
* scc_get_share_pocket()
* scc_get_share_pinterest()
* scc_get_share_linkedin()
* scc_get_share_hatebu()
* scc_get_share_total()

The following shows functions to get follower count from the cache:

* scc_get_follow_feedly()
* scc_get_follow_twitter()
* scc_get_follow_facebook()
* scc_get_follow_instagram()
* scc_get_follow_push7()

The following describes meta keys to get share count from custom field.

* scc_share_count_twitter
* scc_share_count_facebook
* scc_share_count_google+
* scc_share_count_pocket
* scc_share_count_pinterest
* scc_share_count_linkedin
* scc_share_count_hatebu
* scc_share_count_total

The following describes meta keys to get delta of share count from custom field.

* scc_share_delta_twitter
* scc_share_delta_facebook
* scc_share_delta_google+
* scc_share_delta_pocket
* scc_share_delta_pinterest
* scc_share_delta_linkedin
* scc_share_delta_hatebu
* scc_share_delta_total

== Installation ==

1. Download zip archive file from this repository.

2. Login as an administrator to your WordPress admin page.
   Using the "Add New" menu option under the "Plugins" section of the navigation,
   Click the "Upload" link, find the .zip file you download and then click "Install Now".
   You can also unzip and upload the plugin to your plugins directory (i.e. wp-content/plugins/) through FTP/SFTP.

3. Finally, activate the plugin on the "Plugins" page.

== Frequently Asked Questions ==
There are no questions.

== Screenshots ==
1. Dashboard page
2. Cache status page
3. Share count page
4. Hot content page
5. Setting page
6. Help page

== Changelog ==

= 0.1.0 =
* Initial working version.

= 0.2.0 =
* Added: function to modify check interval of SNS share count and its number of target posts and pages at a time
* Added: function to cache SNS share count for latest posts and pages preferentially
* Added: function to cache SNS share count based on user access dynamically

= 0.3.0 =
* Added: Pocket is included as one of cache targets.
* Added: function to modify target SNS that share count is cached
* Added: function to modify term considering posted content as new content in the rush cache.
* Added: page to display share count for specified all targets.
* Added: function to query pages and posts based on SNS share count using specific custom fields in WP_Query and so on.

= 0.4.0 =
* Added: admin page is totally improved.
* Added: function to sort contents based on share count is added to admin page of share count.
* Added: content of custom post type is added as share count cache target.
* Added: number of Feedly follower is included as one of cache targets.
* Added: function to export share count data is added.
* Added: cache logic is improved.

= 0.5.0 =
* Added: function to cache share count for both old and new url in https migration.
* Fixed: share count of Facebook becomes invalid when the count is more than four digits.

= 0.6.0 =
* Added: function to cache share count for home page.
* Improved: Each retrieval time of SNS count is shortened.
* Improved: loading time of dashboard page is shortened using ajax loading technique.
* Fixed: SNS count of facebook can be 0.
* Fixed: "PHP Notice: has_cap..." is output.

= 0.7.0 =
* Added: function to display variation of SNS count
* Added: function to access variation of SNS count through custom filed
* Fixed: custom filed used in this plugin is not deleted in a certain case.

= 0.7.1 =
* Modified: Check interval of follower count is tuned.

= 0.8.0 =
* Added: Japanese translation
* Improved: Cache processing is stabilized.
* Added: function to select feed type for feedly follower retrieval.

= 0.9.0 =
* Fixed: Twitter share count retrieval is implemented using alternative Twitter APIs.
* Improved: Cache processing is stabilized.
* Added: function to retrieve follower count of Twitter.
* Added: function to retrieve follower count of Facebook page.
* Added: function to retrieve follower count of Instagram.
* Added: function to retrieve follower count of Push7.
* Added: Information related to the above new functions is added to the help page.

= 0.9.1 =
* Fixed: Follower count is not retrieved and cached at intervals according to your configuration.

= 0.9.2 =
* Fixed: Facebook share count is not retrieved and cached.

= 0.10.0 =
* Fixed: Facebook share count can not be retrieved and cached.
* Added: function to configure parameters for share rush cache.
* Added: function to keep cached share counts when share count retrieval fails.
* Added: function to retrieve share count of Pinterest.

= 0.11.0 =
* Fixed: Facebook share count is not retrieved and cached.
* Fixed: Pocket share count is not retrieved and cached.
* Added: function to display variation of follower count.
* Added: NewShareCounts is included in selectable Twitter API alternatives.
* Added: function to retrieve share count of Linkedin.

= 0.11.1 =
* Fixed: Facebook share count becomes N/A in share count page in a certain situation.

== Upgrade Notice ==
The following functions are deprecated.

* get_scc_twitter()
* get_scc_facebook()
* get_scc_gplus()
* get_scc_pocket()
* get_scc_hatebu()

== Arbitrary section ==
