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
				if(Session::exists($key) && Session::exists($keyExpire) && Session::get($keyExpire) < $currentTime){
					$logger->info('The CSRF token not yet expire just return it');
					return Session::get($key);
				}
				else{
					$newTime = $currentTime + $expire;
					$token = sha1(uniqid()).sha1(uniqid());
					$logger->info('The CSRF informations are listed below: key [' .$key. '], key expire [' .$keyExpire. '], expire time [' .$expire. '] sec, token [' .$token. ']');
					Session::set($keyExpire, $newTime);
					Session::set($key, $token);
					return Session::get($key);
				}
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
						$logger->warning('The CSRF data [' .$token. '] is not valide may be attacker do his job');
						return false;
					}
					else{
						return true;
					}
				}
			}
			else{
				$logger->info('CSRF is not enabled in the configuration, ignore checking');
				return false; //TODO no need to return false if CSRF not enable no need to call this method
			}
		}
		
		/**
		 * used to check the whitelist address allowed
		 */
		 static public function checkWhiteListIpAccess(){
			$logger = static::getLogger();
			$logger->debug('Validation of the IP address access ...');
			$logger->debug('Check if whitelist ip access is enabled in the configuration ...');
			$isEnable = Config::get('white_list_ip_enable', false);
			if($isEnable){
				$logger->info('Whitelist ip access is enabled in the configuration');
				$list = Config::get('white_list_ip_addresses', array());
				if(!empty($list)){
					//may be at this time helper user_agent not yet included
					require_once CORE_FUNCTIONS_PATH . 'function_user_agent.php';
					$ip = get_ip();
					if(count($list) == 1 && $list[0] == '*' || in_array($ip, $list)){
						$logger->info('IP address ' . $ip . ' allowed using the wildcard "*" or the full IP');
						//wildcard to access all ip address
						return;
					}
					else{
						// go through all whitelisted ips
						foreach ($list as $ipaddr) {
							// find the wild card * in whitelisted ip (f.e. find position in "127.0.*" or "127*")
							$wildcardPosition = strpos($ipaddr, "*");
							if ($wildcardPosition === false) {
								// no wild card in whitelisted ip --continue searching
								continue;
							}
							// cut ip at the position where we got the wild card on the whitelisted ip
							// and add the wold card to get the same pattern
							if (substr($ip, 0, $wildcardPosition) . "*" === $ipaddr) {
								// f.e. we got
								//  ip "127.0.0.1"
								//  whitelisted ip "127.0.*"
								// then we compared "127.0.*" with "127.0.*"
								// return success
								$logger->info('IP address ' . $ip . ' allowed using the wildcard like "x.x.x.*"');
								return;
							}
						}
						$logger->warning('IP address ' . $ip . ' is not allowed to access to this application');
						show_error('Access to this application is not allowed');
					}
				}
			}
			else{
				$logger->info('Whitelist ip access is not enabled in the configuration, ignore checking');
			}
		 }
	}