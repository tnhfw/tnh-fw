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


	class Session{
		const SESSION_FLASH_KEY = 'session_flash';

		static function get($item, $default = null){
			return isset($_SESSION[$item])?($_SESSION[$item]):$default;
		}

		static function set($item, $value){
			$_SESSION[$item] = $value;
		}

		static function getFlash($item, $default = null){
			$key = self::SESSION_FLASH_KEY.'_'.$item;
			$return = isset($_SESSION[$key])?
			($_SESSION[$key]):$default;
			if(isset($_SESSION[$key])){
				unset($_SESSION[$key]);
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
			unset($_SESSION[$item]);
		}
		
		static function clearFlash($item){
			$key = self::SESSION_FLASH_KEY.'_'.$item;
			unset($_SESSION[$key]);
		}

		static function exists($item){
			return isset($_SESSION[$item]);
		}

		static function clearAll(){
			session_unset();
			session_destroy();
		}

	}
