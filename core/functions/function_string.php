<?php
	/**
	 * TNH Framework
	 *
	 * A simple PHP framework created using the concept of codeigniter with bootstrap twitter
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
	 *  @file function_string.php
	 *
	 *  This file contains the definition of the functions relating to the processing of strings characters.
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

	if(!function_exists('attributes_to_string')){
		/**
		 *  Convert array to attributes string
		 *
		 *  This function converts an associative array into HTML attributes.
		 *  For example :
		 *  $a = array('name' => 'Foo', 'type' => 'text'); => produces the following string:
		 *  name = "Foo" type = "text"
		 *
		 *  @param $attributes associative array to convert to a string attribute.
		 *  @return string string of the HTML attribute.
		 */
		function attributes_to_string(array $attributes){
			$str = ' ';
			//we check that the array passed as an argument is not empty.
			if(!empty($attributes)){
				foreach($attributes as $key => $value){
					$key = trim(htmlspecialchars($key));
					$value = trim(htmlspecialchars($value));
					if(strpos('"', $value) != false){
						$value = addslashes($value);
					}
					$str .= $key.' = "'.$value.'" ';
				}
			}
			return $str;
		}
	}

	if(!function_exists('get_random_string')){	
		function get_random_string($type = 'alnum',$length = 10, $lower = false){
			//$type must be alpha, alnum, num.
			$str = '';
			switch($type){
				case 'alpha':
					$str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;
				case 'alnum':
					$str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
				break;
				case 'num':
					$str = '1234567890';
				break;
				default:
					$str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
			}
			$random = null;
			for($i = 0 ; $i < $length ; $i++){
				$random .= $str[mt_rand()%strlen($str)];
			}
			return $random;
		}
	}
