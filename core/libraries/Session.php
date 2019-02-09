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
	 * TODO: use the best way to include the Log class
	 */
	if(!class_exists('Log')){
		//here the Log class is not yet loaded
		//load it manually, normally the class Config is loaded before
		require_once CORE_LIBRARY_PATH . 'Log.php';
	}


	class Session{
		const SESSION_FLASH_KEY = 'session_flash';
		private static $logger;

		private static function getLogger(){
			if(static::$logger == null){
				static::$logger = new Log();
				static::$logger->setLogger('Library::Session');
			}
			return static::$logger;
		}

		static function get($item, $default = null){
			$logger = static::getLogger();
			$logger->debug('Getting session data for item [' .$item. '] ...');
			if(isset($_SESSION[$item])){
				$logger->info('Session data for item [' .$item. '] is : [' .stringfy_vars($_SESSION[$item]). ']');
				return $_SESSION[$item];
			}
			$logger->warning('Cannot find session item ['.$item.'] using the default value ['.$default.']');
			return $default;
		}

		static function set($item, $value){
			$logger = static::getLogger();
			$logger->debug('Setting session data for item [' .$item. '], value [' .stringfy_vars($value). ']');
			$_SESSION[$item] = $value;
		}

		static function getFlash($item, $default = null){
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

		static function hasFlash($item){
			$key = self::SESSION_FLASH_KEY.'_'.$item;
			return isset($_SESSION[$key]);
		}

		static function setFlash($item, $value){
			$key = self::SESSION_FLASH_KEY.'_'.$item;
			$_SESSION[$key] = $value;
		}

		static function clear($item){
			$logger = static::getLogger();
			if(isset($_SESSION[$item])){
				$logger->info('Delete session for item ['.$item.' ]');
				unset($_SESSION[$item]);
			}
			else{
				$logger->warning('Session item ['.$item.'] to be deleted does not exists');
			}
		}
		
		static function clearFlash($item){
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

		static function exists($item){
			return isset($_SESSION[$item]);
		}

		static function clearAll(){
			session_unset();
			session_destroy();
		}

	}
