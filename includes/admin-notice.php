<?php
/*
admin-notice.php

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

	if ( ! defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

	if ( $messages = get_option( self::OPT_COMMON_ERROR_MESSAGE  ) ) {
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
	delete_option( self::OPT_COMMON_ERROR_MESSAGE );
	}
?>
