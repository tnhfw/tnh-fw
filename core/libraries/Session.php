<?php
	defined('ROOT_PATH') or exit('Access denied');
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
	class Session{
		/**
		 * The session flash key to use
		 * @const
		 */
		const SESSION_FLASH_KEY = 'session_flash';

		/**
		 * The logger instance
		 * @var Log
		 */
		private static $logger;

		/**
		 * Get the logger singleton instance
		 * @return Log the logger instance
		 */
		private static function getLogger(){
			if(static::$logger == null){
				static::$logger[0] =& class_loader('Log');
				static::$logger[0]->setLogger('Library::Session');
			}
			return static::$logger[0];
		}

		/**
		 * Get the session item value
		 * @param  string $item    the session item name to get
		 * @param  mixed $default the default value to use if can not find the session item in the list
		 * @return mixed          the session value if exist or the default value
		 */
		public static function get($item, $default = null){
			$logger = static::getLogger();
			$logger->debug('Getting session data for item [' .$item. '] ...');
			if(isset($_SESSION[$item])){
				$logger->info('Found session data for item [' .$item. '] the vaue is : [' .stringfy_vars($_SESSION[$item]). ']');
				return $_SESSION[$item];
			}
			$logger->warning('Cannot find session item ['.$item.'] using the default value ['.$default.']');
			return $default;
		}

		/**
		 * Set the session item value
		 * @param string $item  the session item name to set
		 * @param mixed $value the session item value
		 */
		public static function set($item, $value){
			$logger = static::getLogger();
			$logger->debug('Setting session data for item [' .$item. '], value [' .stringfy_vars($value). ']');
			$_SESSION[$item] = $value;
		}

		/**
		 * Get the session flash item value
		 * @param  string $item    the session flash item name to get
		 * @param  mixed $default the default value to use if can not find the session flash item in the list
		 * @return mixed          the session flash value if exist or the default value
		 */
		public static function getFlash($item, $default = null){
			$logger = static::getLogger();
			$key = self::SESSION_FLASH_KEY.'_'.$item;
			$return = isset($_SESSION[$key])?
			($_SESSION[$key]):$default;
			if(isset($_SESSION[$key])){
				unset($_SESSION[$key]);
			}
			else{
				$logger->warning('Cannot find session flash item ['.$item.'] using the default value ['.$default.']');
			}
			return $return;
		}

		/**
		 * Check whether the given session flash item exists
		 * @param  string  $item the session flash item name
		 * @return boolean 
		 */
		public static function hasFlash($item){
			$key = self::SESSION_FLASH_KEY.'_'.$item;
			return isset($_SESSION[$key]);
		}

		/**
		 * Set the session flash item value
		 * @param string $item  the session flash item name to set
		 * @param mixed $value the session flash item value
		 */
		public static function setFlash($item, $value){
			$key = self::SESSION_FLASH_KEY.'_'.$item;
			$_SESSION[$key] = $value;
		}

		/**
		 * Clear the session item in the list
		 * @param  string $item the session item name to be deleted
		 */
		public static function clear($item){
			$logger = static::getLogger();
			if(isset($_SESSION[$item])){
				$logger->info('Deleting of session for item ['.$item.' ]');
				unset($_SESSION[$item]);
			}
			else{
				$logger->warning('Session item ['.$item.'] to be deleted does not exists');
			}
		}
		
		/**
		 * Clear the session flash item in the list
		 * @param  string $item the session flash item name to be deleted
		 */
		public static function clearFlash($item){
			$logger = static::getLogger();
			$key = self::SESSION_FLASH_KEY.'_'.$item;
			if(isset($_SESSION[$item])){
				$logger->info('Delete session flash for item ['.$item.']');
				unset($_SESSION[$item]);
			}
			else{
				$logger->warning('Dession flash item ['.$item.'] to be deleted does not exists');
			}
		}

		/**
		 * Check whether the given session item exists
		 * @param  string  $item the session item name
		 * @return boolean 
		 */
		public static function exists($item){
			return isset($_SESSION[$item]);
		}

		/**
		 * Destroy all session data values
		 */
		public static function clearAll(){
			session_unset();
			session_destroy();
		}

	}
