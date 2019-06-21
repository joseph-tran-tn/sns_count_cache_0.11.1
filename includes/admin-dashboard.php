<?php
/*
admin-dashboard.php

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

	$query_args = array(
		'post_type' => $this->share_base_cache_post_types,
		'post_status' => 'publish',
		'nopaging' => true,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false
		);

	$site_query = new WP_Query( $query_args );
	?>
	<div class="wrap">
		<h2><a href="admin.php?page=scc-dashboard"><?php _e( 'SNS Count Cache', self::DOMAIN ); ?></a></h2>
		<div class="sns-cnt-cache">
			<div id="scc-dashboard">
				<h3 class="nav-tab-wrapper">
					<a class="nav-tab nav-tab-active" href="admin.php?page=scc-dashboard"><?php _e( 'Dashboard', self::DOMAIN ); ?></a>
					<a class="nav-tab" href="admin.php?page=scc-cache-status"><?php _e( 'Cache Status', self::DOMAIN ); ?></a>
					<a class="nav-tab" href="admin.php?page=scc-share-count"><?php _e( 'Share Count', self::DOMAIN ); ?></a>
				<?php if ( $this->share_variation_analysis_mode !== self::OPT_SHARE_VARIATION_ANALYSIS_NONE ) { ?>
					<a class="nav-tab" href="admin.php?page=scc-hot-content"><?php _e( 'Hot Content', self::DOMAIN ); ?></a>
				<?php } ?>
					<a class="nav-tab" href="admin.php?page=scc-setting"><?php _e( 'Setting', self::DOMAIN ); ?></a>
					<a class="nav-tab" href="admin.php?page=scc-help"><?php _e( 'Help', self::DOMAIN ); ?></a>
				</h3>
				<div class="metabox-holder">
					<div id="site-summary-share-cache" class="site-summary postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php _e( 'Share', self::DOMAIN ); ?></span></h3>
						<div class="inside">
							<h4><a href="admin.php?page=scc-cache-status"><?php _e( 'Cache Status', self::DOMAIN ); ?></a></h4>
							<table class="view-table">
								<thead>
									<tr>
										<th><?php _e( 'Cache Type', self::DOMAIN ); ?></th>
										<th><?php _e( 'Cache Progress', self::DOMAIN ); ?></th>
										<th><?php _e( 'Total Content', self::DOMAIN ); ?></th>
										<th><?php _e( 'State - Full Cache', self::DOMAIN ); ?></th>
										<th><?php _e( 'State - Partial Cache', self::DOMAIN ); ?></th>
										<th><?php _e( 'State - No Cache', self::DOMAIN ); ?></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td><?php _e( 'Primary Cache', self::DOMAIN ); ?></td>
										<td>
											<img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc="spcs"></span>
										</td>
										<td class="share-count"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='spc'></span></td>
										<td class="share-count full-cache"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='spfcc'></span></td>
										<td class="share-count partial-cache"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='sppcc'></span></td>
										<td class="share-count no-cache"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='spncc'></span></td>
									</tr>
									<tr>
										<td><?php _e( 'Secondary Cache', self::DOMAIN ); ?></td>
										<td>
											<img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc="sscs"></span>
										</td>
										<td class="share-count"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='spc'></span></td>
										<td class="share-count full-cache"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='ssfcc'></span></td>
										<td class="share-count partial-cache"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='sspcc'></span></td>
										<td class="share-count no-cache"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='ssncc'></span></td>
									</tr>
								</tbody>
							</table>
							<h4><a href="admin.php?page=scc-share-count"><?php _e( 'Share Count', self::DOMAIN ); ?></a></h4>
							<table class="view-table">
								<thead>
									<tr>
										<?php
											$share_base_cache_target = $this->share_base_cache_target ;
											unset( $share_base_cache_target[self::REF_CRAWL_DATE] );

											foreach ( $share_base_cache_target as $sns => $active ){
												if ( $active ) {
													$sns_name = '';

													switch ( $sns ) {
														case self::REF_SHARE_TWITTER:
															$sns_name = __( 'Twitter', self::DOMAIN );
															break;
														case self::REF_SHARE_FACEBOOK:
															$sns_name = __( 'Facebook', self::DOMAIN );
															break;
														case self::REF_SHARE_GPLUS:
															$sns_name = __( 'Google+', self::DOMAIN );
															break;
														case self::REF_SHARE_POCKET:
															$sns_name = __( 'Pocket', self::DOMAIN );
															break;
														case self::REF_SHARE_HATEBU:
															$sns_name = __( 'Hatebu', self::DOMAIN );
															break;
														case self::REF_SHARE_PINTEREST:
															$sns_name = __( 'Pinterest', self::DOMAIN );
															break;
														case self::REF_SHARE_LINKEDIN:
															$sns_name = __( 'Linkedin', self::DOMAIN );
															break;
														case self::REF_SHARE_TOTAL:
															$sns_name = __( 'Total', self::DOMAIN );
															break;
													}

													echo '<th>' . esc_html( $sns_name ) . '</th>';
												}
											}
										?>
									</tr>
								</thead>
								<tbody>
									<tr>
										<?php
											foreach ( $share_base_cache_target as $sns => $active ) {
												if ( $active ) {
													if ( $sns === self::REF_SHARE_GPLUS ){
														echo '<td class="share-count">';
														echo '<img class="loading" src="' . $this->loading_img_url . '" /><span data-scc="sgplus"></span>';
														echo '</td>';
													} else {
														echo '<td class="share-count">';
														echo '<img class="loading" src="' . $this->loading_img_url . '" /><span data-scc="s' . strtolower( $sns ) . '"></span>';
														echo '</td>';
													}
												}
											}
										?>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="metabox-holder">
					<div id="site-summary-follow-cache" class="site-summary postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php _e( 'Follow', self::DOMAIN ); ?></span></h3>
						<div class="inside">
							<h4><?php _e( 'Cache Status', self::DOMAIN ); ?></h4>
								<table class="view-table">
								<thead>
									<tr>
										<th><?php _e( 'Cache Type', self::DOMAIN ); ?></th>
										<th><?php _e( 'Cache Progress', self::DOMAIN ); ?></th>
										<th><?php _e( 'Total Content', self::DOMAIN ); ?></th>
										<th><?php _e( 'State - Full Cache', self::DOMAIN ); ?></th>
										<th><?php _e( 'State - Partial Cache', self::DOMAIN ); ?></th>
										<th><?php _e( 'State - No Cache', self::DOMAIN ); ?></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td><?php _e( 'Primary Cache', self::DOMAIN ); ?></td>
										<td>
											<img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc="fpcs"></span>
										</td>
										<td class="share-count"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='fpc'></span></td>
										<td class="share-count full-cache"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='fpfcc'></span></td>
										<td class="share-count partial-cache"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='fppcc'></span></td>
										<td class="share-count no-cache"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='fpncc'></span></td>
									</tr>
									<tr>
										<td><?php _e( 'Secondary Cache', self::DOMAIN ); ?></td>
										<td>
											<img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc="fscs"></span>
										</td>
										<td class="share-count"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='fpc'></span></td>
										<td class="share-count full-cache"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='fsfcc'></span></td>
										<td class="share-count partial-cache"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='fspcc'></span></td>
										<td class="share-count no-cache"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='fsncc'></span></td>
									</tr>
								</tbody>
							</table>
							<h4><?php _e( 'Follower Count', self::DOMAIN ); ?></h4>
							<table class="view-table">
								<thead>
									<tr>
										<?php
											$follow_base_cache_target = $this->follow_base_cache_target ;
											unset( $follow_base_cache_target[self::REF_CRAWL_DATE] );

											foreach ( $follow_base_cache_target as $sns => $active ){
												if ( $active ) {
													$sns_name = '';

													switch ( $sns ) {
														case self::REF_FOLLOW_TWITTER:
															$sns_name = __( 'Twitter', self::DOMAIN );
															break;
														case self::REF_FOLLOW_FACEBOOK:
															$sns_name = __( 'Facebook', self::DOMAIN );
															break;
														case self::REF_FOLLOW_FEEDLY:
															$sns_name = __( 'Feedly', self::DOMAIN );
															break;
														case self::REF_FOLLOW_INSTAGRAM:
															$sns_name = __( 'Instagram', self::DOMAIN );
															break;
														case self::REF_FOLLOW_PUSH7:
															$sns_name = __( 'Push7', self::DOMAIN );
															break;
													}

													echo '<th>' . esc_html( $sns_name ) . '</th>';
												}
											}
										?>
									</tr>
								</thead>
								<tbody>
									<tr>
										<?php
											foreach ( $follow_base_cache_target as $sns => $active ) {
												if ( $active ) {
													echo '<td class="share-count">';
													echo '<img class="loading" src="' . $this->loading_img_url . '" /><span data-scc="f' . strtolower( $sns ) . '"></span>';
													echo '</td>';
												}
											}
										?>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
