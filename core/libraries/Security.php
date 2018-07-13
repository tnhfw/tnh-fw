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


	class Security{

		/**
		 * this method is used to generate the CSRF token
		 * @return strint the generated token
		 */
		static public function generateCSRF(){
			//first check if enable in configuration (false by default if not enable)
			$isEnable = Config::get('csrf_enable', false);

			if($isEnable){
				$key = Config::get('csrf_key', 'csrf_key');
				$expire = Config::get('csrf_expire', 60);
				$keyExpire = 'csrf_expire';
				$currentTime = time();
				$newTime = $currentTime + $expire;

				Session::set($keyExpire, $newTime);
				Session::set($key, sha1(uniqid()).sha1(uniqid()));
				return Session::get($key);
			}
			else{
				//not enable in configuration
				return null;
			}
		}

		/**
		 * used to check the CSRF status is valid, not expire
		 * @return boolean true if valid, false if not valid
		 */

		static public function validateCSRF(){
			$isEnable = Config::get('csrf_enable', false);
			if($isEnable){
				$key = Config::get('csrf_key', 'csrf_key');
				$expire = Config::get('csrf_expire', 60);
				$keyExpire = 'csrf_expire';
				$currentTime = time();

				if(!Session::exists($key) || Session::get($keyExpire) <= $currentTime){
					return false;
				}
				else{
					//perform form data
					//need use request->query() for best retrieve
					//super instance
					$obj = & get_instance();
					$token = $obj->request->query($key);
					if(!$token || $token !== Session::get($key) || Session::get($keyExpire) <= $currentTime){
						return false;
					}
					else{
						return true;
					}
				}
			}
			else{
				return false; //TODO no need to return false if CSRF not enable no need to call this method
			}
		}
	}