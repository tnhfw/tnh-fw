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


	if(!function_exists('attributes_to_string')){
		function attributes_to_string(array $attributes){
			$str = ' ';
			foreach($attributes as $key => $value){
				$key = trim(htmlspecialchars($key));
				$value = trim(htmlspecialchars($value));
				$str .= $key.' = "'.$value.'" ';
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