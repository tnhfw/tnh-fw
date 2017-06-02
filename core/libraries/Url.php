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


	class Url{

		public static function base_url($path = ''){
			if(is_url($path)){
				return $path;
			}
			return Config::get('base_url').$path;
		}

		public static function site_url($path = ''){
			if(is_url($path)){
				return $path;
			}
			$base_url = Config::get('base_url');
			$front_controller = Config::get('front_controller');
			$url = $base_url;
			if($front_controller){
				$url .= $front_controller.'/';
			}
			return $url.$path;
		}

		public static function current(){
			$obj = & get_instance();
			$current = '/';
			$requestUri = $obj->request->requestUri();
			if($requestUri){
				$current = $requestUri;
			}
			return static::domain().$current;
		}


		public static function title($str = null, $separator = '-', $lowercase = true){
			$str = trim($str);
			$from = array('ç','À','Á','Â','Ã','Ä','Å','à','á','â','ã','ä','å','Ò','Ó','Ô','Õ','Ö','Ø','ò','ó','ô','õ','ö','ø','È','É','Ê','Ë','è','é','ê','ë','Ç','ç','Ì','Í','Î','Ï','ì','í','î','ï','Ù','Ú','Û','Ü','ù','ú','û','ü','ÿ','Ñ','ñ');
			$to = array('c','a','a','a','a','a','a','a','a','a','a','a','a','o','o','o','o','o','o','o','o','o','o','o','o','e','e','e','e','e','e','e','e','e','e','i','i','i','i','i','i','i','i','u','u','u','u','u','u','u','u','y','n','n');
			$str = str_replace($from,$to,$str);
			$str = preg_replace('#([^a-z0-9]+)#i', $separator, $str);
			$str = str_replace("--", $separator, $str);
			if(substr($str, -1) == $separator){
				$str = substr($str, 0, -1);
			}
			if($lowercase){
				$str = strtolower($str);
			}
			return $str;
		}

		public static function domain(){
			$obj = & get_instance();
			$domain = 'localhost';
			$port = $obj->request->server('SERVER_PORT');
			$protocol = is_https() ? 'https' : 'http';
			
			if($obj->request->server('HTTP_HOST')){
				$domain = $obj->request->server('HTTP_HOST');
			}
			else if($obj->request->server('SERVER_NAME')){
				$domain = $obj->request->server('SERVER_NAME');
			}
			else if($obj->request->server('SERVER_ADDR')){
				$domain = $obj->request->server('SERVER_ADDR');
			}
			if($port && (is_https() && $port != 443 || !is_https() && $port != 80)){
				//some server use SSL but the port doesn't equal 443 sometime is 80 if is the case don't try to emulate it
				if(is_https() && $port != 80){
					$domain .= ':'.$port;
				}
			}

			return $protocol.'://'.$domain;
		}

		static function queryString(){
			$obj = & get_instance();
			return $obj->request->server('QUERY_STRING');
		}
	}