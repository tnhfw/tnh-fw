<?php
	defined('ROOT_PATH') or exit('Access denied');
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


	class Url{

		/**
		 * Return the link using base_url config without front controller "index.php"
		 * @param  string $path the link path or full URL
		 * @return string the full link URL
		 */
		public static function base_url($path = ''){
			if(is_url($path)){
				return $path;
			}
			return get_config('base_url') . $path;
		}

		/**
		 * Return the link using base_url config with front controller "index.php"
		 * @param  string $path the link path or full URL
		 * @return string the full link URL
		 */
		public static function site_url($path = ''){
			if(is_url($path)){
				return $path;
			}
			$path = rtrim($path, '/');
			$baseUrl = get_config('base_url');
			$frontController = get_config('front_controller');
			$url = $baseUrl;
			if($frontController){
				$url .= $frontController . '/';
			}
			if(($suffix = get_config('url_suffix')) && $path){
				if(strpos($path, '?') !== false){
					$query = explode('?', $path);
					$query[0] = str_ireplace($suffix, '', $query[0]);
					$query[0] = rtrim($query[0], '/');
					$query[0] .= $suffix;
					$path = implode('?', $query);
				}
				else{
					$path .= $suffix;
				}
			}
			return $url . $path;
		}

		/**
		 * Return the current site URL
		 * @return string
		 */
		public static function current(){
			$current = '/';
			$requestUri = get_instance()->request->requestUri();
			if($requestUri){
				$current = $requestUri;
			}
			return static::domain() . $current;
		}

		/**
		 * Generate a friendly  text to use in link (slugs)
		 * @param  string  $str       the title or text to use to get the friendly text
		 * @param  string  $separator the caracters separator
		 * @param  boolean $lowercase whether to set the final text to lowe case or not
		 * @return string the friendly generated text
		 */
		public static function title($str = null, $separator = '-', $lowercase = true){
			$str = trim($str);
			$from = array('ç','À','Á','Â','Ã','Ä','Å','à','á','â','ã','ä','å','Ò','Ó','Ô','Õ','Ö','Ø','ò','ó','ô','õ','ö','ø','È','É','Ê','Ë','è','é','ê','ë','Ç','ç','Ì','Í','Î','Ï','ì','í','î','ï','Ù','Ú','Û','Ü','ù','ú','û','ü','ÿ','Ñ','ñ');
			$to = array('c','a','a','a','a','a','a','a','a','a','a','a','a','o','o','o','o','o','o','o','o','o','o','o','o','e','e','e','e','e','e','e','e','e','e','i','i','i','i','i','i','i','i','u','u','u','u','u','u','u','u','y','n','n');
			$str = str_replace($from, $to, $str);
			$str = preg_replace('#([^a-z0-9]+)#i', $separator, $str);
			$str = str_replace('--', $separator, $str);
			//if after process we get something like one-two-three-, need truncate the last separator "-"
			if(substr($str, -1) == $separator){
				$str = substr($str, 0, -1);
			}
			if($lowercase){
				$str = strtolower($str);
			}
			return $str;
		}

		/**
		 * Get the current application domain with protocol
		 * @return string the domain name
		 */
		public static function domain(){
			$obj = & get_instance();
			$domain = 'localhost';
			$port = $obj->request->server('SERVER_PORT');
			$protocol = 'http';
			if(is_https()){
				$protocol = 'https';
			}

			$domainserverVars = array(
				'HTTP_HOST',
				'SERVER_NAME',
				'SERVER_ADDR'
			);

			foreach ($domainserverVars as $var) {
				$value = $obj->request->server($var);
				if($value){
					$domain = $value;
					break;
				}
			}
			
			if($port && ((is_https() && $port != 443) || (!is_https() && $port != 80))){
				//some server use SSL but the port doesn't equal 443 sometime is 80 if is the case put the port at this end
				//of the domain like https://my.domain.com:787
				if(is_https() && $port != 80){
					$domain .= ':'.$port;
				}
			}
			return $protocol.'://'.$domain;
		}

		/**
		 * Get the current request query string
		 * @return string
		 */
		public static function queryString(){
			return get_instance()->request->server('QUERY_STRING');
		}
	}
