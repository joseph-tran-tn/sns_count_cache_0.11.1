<?php
/*
interface-scc-order.php

Description: This is a interface
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

interface SCC_Order {

	const ORDER_DO_SECOND_CACHE = 'do_second_cache';

	const ORDER_GET_SECOND_CACHE = 'get_second_cache';

	const ORDER_DO_ANALYSIS = 'do_analysis';

	public function order( SCC_Cache_Engine $engine, $order, $options = array() );
}

?>
