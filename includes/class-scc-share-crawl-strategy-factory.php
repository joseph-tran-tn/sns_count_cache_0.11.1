<?php
/*
class-scc-share-crawl-strategy-factory.php

Description: This class is a data crawler whitch get share count using given API and cURL
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

class SCC_Share_Crawl_Strategy_Factory {

	/**
	 * Carete crawl strategy
	 *
	 * @since 0.9.0
	 */
	public static function create_crawl_strategy( $sns ) {
		SCC_Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		switch ( $sns ) {
			case SNS_Count_Cache::REF_SHARE_TWITTER:
				SCC_Common_Util::log( '[' . __METHOD__ . '] create crawl strategy: ' . $sns );
				return SCC_Share_Twitter_Strategy::get_instance();
				break;
			case SNS_Count_Cache::REF_SHARE_FACEBOOK:
				SCC_Common_Util::log( '[' . __METHOD__ . '] create crawl strategy: ' . $sns );
				return SCC_Share_Facebook_Strategy::get_instance();
				break;
			case SNS_Count_Cache::REF_SHARE_GPLUS:
				SCC_Common_Util::log( '[' . __METHOD__ . '] create crawl strategy: ' . $sns );
				return SCC_Share_Google_Strategy::get_instance();
				break;
			case SNS_Count_Cache::REF_SHARE_POCKET:
				SCC_Common_Util::log( '[' . __METHOD__ . '] create crawl strategy: ' . $sns );
				return SCC_Share_Pocket_Strategy::get_instance();
				break;
			case SNS_Count_Cache::REF_SHARE_HATEBU:
				SCC_Common_Util::log( '[' . __METHOD__ . '] create crawl strategy: ' . $sns );
				return SCC_Share_Hatebu_Strategy::get_instance();
				break;
			case SNS_Count_Cache::REF_SHARE_PINTEREST:
				SCC_Common_Util::log( '[' . __METHOD__ . '] create crawl strategy: ' . $sns );
				return SCC_Share_Pinterest_Strategy::get_instance();
				break;
			case SNS_Count_Cache::REF_SHARE_LINKEDIN:
				SCC_Common_Util::log( '[' . __METHOD__ . '] create crawl strategy: ' . $sns );
				return SCC_Share_Linkedin_Strategy::get_instance();
				break;
		}
	}

}

?>
