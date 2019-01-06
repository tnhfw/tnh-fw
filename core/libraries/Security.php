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


	class Security{

		private static $logger;


		private static function getLogger(){
			if(static::$logger == null){
				static::$logger = new Log();
				static::$logger->setLogger('Library::Security');
			}
			return static::$logger;
		}


		/**
		 * this method is used to generate the CSRF token
		 * @return strint the generated token
		 */
		static public function generateCSRF(){
			$logger = static::getLogger();
			$logger->debug('Generation of CSRF ...');
			$logger->debug('Check if CSRF is enabled in the configuration ...');
			//first check if enable in configuration (false by default if not enable)
			$isEnable = Config::get('csrf_enable', false);
			if($isEnable){
				$logger->info('CSRF is enabled in the configuration');
				$key = Config::get('csrf_key', 'csrf_key');
				$expire = Config::get('csrf_expire', 60);
				$keyExpire = 'csrf_expire';
				$currentTime = time();
				$newTime = $currentTime + $expire;
				$logger->info('The CSRF informations are listed below: key [' .$key. '], key expire [' .$keyExpire. '], expire time [' .$expire. '] sec');
				Session::set($keyExpire, $newTime);
				Session::set($key, sha1(uniqid()).sha1(uniqid()));
				return Session::get($key);
			}
			else{
				$logger->info('CSRF is not enabled in the configuration');
				//not enable in configuration
				return null;
			}
		}

		/**
		 * used to check the CSRF status is valid, not expire
		 * @return boolean true if valid, false if not valid
		 */

		static public function validateCSRF(){
			$logger = static::getLogger();
			$logger->debug('Validation of CSRF ...');
			$logger->debug('Check if CSRF is enabled in the configuration ...');
			$isEnable = Config::get('csrf_enable', false);
			if($isEnable){
				$logger->info('CSRF is enabled in the configuration');
				$key = Config::get('csrf_key', 'csrf_key');
				$expire = Config::get('csrf_expire', 60);
				$keyExpire = 'csrf_expire';
				$currentTime = time();
				$logger->info('The CSRF informations are listed below: key [' .$key. '], key expire [' .$keyExpire. '], expire time [' .$expire. '] sec');
				if(!Session::exists($key) || Session::get($keyExpire) <= $currentTime){
					$logger->warning('The CSRF session data is not valide');
					return false;
				}
				else{
					//perform form data
					//need use request->query() for best retrieve
					//super instance
					$obj = & get_instance();
					$token = $obj->request->query($key);
					if(!$token || $token !== Session::get($key) || Session::get($keyExpire) <= $currentTime){
						$logger->warning('The CSRF data is not valide may be attacker do his job');
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