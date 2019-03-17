<?php
	defined('ROOT_PATH') || exit('Access denied');
	/**
	 * TNH Framework
	 *
	 * A simple PHP framework using HMVC architecture
	 *
	 * This content is released under the GNU GPL License (GPL)
	 *
	 * Copyright (C) 2017 Tony NGUEREZA
	 *
	 * This program is free software; you can redistribute it and/or
	 * modify it under the terms of the GNU General Public License
	 * as published by the Free Software Foundation; either version 3
	 * of the License, or (at your option) any later version.
	 *
	 * This program is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with this program; if not, write to the Free Software
	 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
	*/

	/**
	 *  @file function_url.php
	 *
	 *  Contains most functions dedicated to URL manipulation (protocol, domain, query, etc.)
	 *
	 *  @package	core
	 *  @author	Tony NGUEREZA
	 *  @copyright	Copyright (c) 2017
	 *  @license	https://opensource.org/licenses/gpl-3.0.html GNU GPL License (GPL)
	 *  @link	http://www.iacademy.cf
	 *  @version 1.0.0
	 *  @since 1.0.0
	 *  @filesource
	 */


	/**
	 *  Check if it's a valid URL.
	 *  
	 *  This function has the role of verifying if the chain passed 
	 *  in argument is a valid address that is to say using the protocol (http, https, ftp, ftps, etc.)
	 *
	 *  @param string $url the URL address to check
	 *  
	 *  @return boolean true if is a valid URL address.
	 */
	if(!function_exists('is_url')){
		function is_url($url){
			return preg_match('/^(http|https|ftp|ftps):\/\/(.*)/', $url);
		}
	}
