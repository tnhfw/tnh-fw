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


	class Cookie{

		public function __construct(){
			
		}


		static function get($item, $default = null){
			return isset($_COOKIE[$item])?($_COOKIE[$item]):$default;
		}

		static function set($name, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false){
			if(headers_sent()){
				show_error('There exists a cookie that we wanted to create that we couldn\'t 
							create because headers was already sent. Make sure to do the first 
							before outputing anything.');
			}
			$timestamp = $expire;
			if($expire){
				$timestamp = time() + $expire;
			}
			setcookie($name, $value, $timestamp, $path, $domain, $secure, $httponly);
		}


		static function clear($name){
			static::set($name, '');
		}

		static function exists($item){
			return isset($_COOKIE[$item]);
		}

	}