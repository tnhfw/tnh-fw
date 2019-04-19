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

	class Cookie{
		
		/**
		 * The logger instance
		 * @var Log
		 */
		private static $logger;

		/**
		 * The signleton of the logger
		 * @return Object the Log instance
		 */
		private static function getLogger(){
			if(static::$logger == null){
				static::$logger[0] =& class_loader('Log');
				static::$logger[0]->setLogger('Library::Cookie');
			}
			return static::$logger[0];
		}

		/**
		 * Get the cookie item value
		 * @param  string $item    the cookie item name to get
		 * @param  mixed $default the default value to use if can not find the cokkie item in the list
		 * @return mixed          the cookie value if exist or the default value
		 */
		public static function get($item, $default = null){
			$logger = static::getLogger();
			if(isset($_COOKIE[$item])){
				return $_COOKIE[$item];
			}
			$logger->warning('Cannot find cookie item ['.$item.'], using the default value ['.$default.']');
			return $default;
		}

		/**
		 * Set the cookie item value
		 * @param string  $name     the cookie item name
		 * @param string  $value    the cookie value to set
		 * @param integer $expire   the time to live for this cookie
		 * @param string  $path     the path that the cookie will be available
		 * @param string  $domain   the domain that the cookie will be available
		 * @param boolean $secure   if this cookie will be available on secure mode or not
		 * @param boolean $httponly if this cookie will be available under HTTP protocol.
		 */
		public static function set($name, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false){
			if(headers_sent()){
				show_error('There exists a cookie that we wanted to create that we couldn\'t 
						    because headers was already sent. Make sure to do this first 
							before outputing anything.');
			}
			$timestamp = $expire;
			if($expire){
				$timestamp = time() + $expire;
			}
			setcookie($name, $value, $timestamp, $path, $domain, $secure, $httponly);
		}

		/**
		 * Delete the cookie item in the list
		 * @param  string $item the cookie item name to be cleared
		 */
		public static function clear($item){
			$logger = static::getLogger();
			if(isset($_COOKIE[$item])){
				$logger->info('Delete cookie item ['.$item.']');
				unset($_COOKIE[$item]);
			}
			else{
				$logger->warning('Cookie item ['.$item.'] to be deleted does not exists');
			}
		}

		/**
		 * Check if the given cookie item exists
		 * @param  string $item the cookie item name
		 * @return boolean       true if the cookie item is set, false or not
		 */
		public static function exists($item){
			return isset($_COOKIE[$item]);
		}

	}