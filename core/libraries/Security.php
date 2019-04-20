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

	class Security{
		
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
				static::$logger[0]->setLogger('Library::Security');
			}
			return static::$logger[0];
		}


		/**
		 * This method is used to generate the CSRF token
		 * @return string the generated CSRF token
		 */
		public static function generateCSRF(){
			$logger = static::getLogger();
			$logger->debug('Generation of CSRF ...');
			
			$key = get_config('csrf_key', 'csrf_key');
			$expire = get_config('csrf_expire', 60);
			$keyExpire = 'csrf_expire';
			$currentTime = time();
			if(Session::exists($key) && Session::exists($keyExpire) && Session::get($keyExpire) > $currentTime){
				$logger->info('The CSRF token not yet expire just return it');
				return Session::get($key);
			}
			else{
				$newTime = $currentTime + $expire;
				$token = sha1(uniqid()) . sha1(uniqid());
				$logger->info('The CSRF informations are listed below: key [' .$key. '], key expire [' .$keyExpire. '], expire time [' .$expire. '], token [' .$token. ']');
				Session::set($keyExpire, $newTime);
				Session::set($key, $token);
				return Session::get($key);
			}
		}

		/**
		 * This method is used to check the CSRF if is valid, not yet expire, etc.
		 * @return boolean true if valid, false if not valid
		 */
		public static function validateCSRF(){
			$logger = static::getLogger();
			$logger->debug('Validation of CSRF ...');
				
			$key = get_config('csrf_key', 'csrf_key');
			$expire = get_config('csrf_expire', 60);
			$keyExpire = 'csrf_expire';
			$currentTime = time();
			$logger->info('The CSRF informations are listed below: key [' .$key. '], key expire [' .$keyExpire. '], expire time [' .$expire. ']');
			if(! Session::exists($key) || Session::get($keyExpire) <= $currentTime){
				$logger->warning('The CSRF session data is not valide');
				return false;
			}
			else{
				//perform form data
				//need use request->query() for best retrieve
				//super instance
				$obj = & get_instance();
				$token = $obj->request->query($key);
				if(! $token || $token !== Session::get($key) || Session::get($keyExpire) <= $currentTime){
					$logger->warning('The CSRF data [' .$token. '] is not valide may be attacker do his job');
					return false;
				}
				else{
					$logger->info('The CSRF data [' .$token. '] is valide the form data is safe continue');
					return true;
				}
			}
		}
		
		/**
		 * This method is used to check the whitelist IP address access
		 */
		 public static function checkWhiteListIpAccess(){
			$logger = static::getLogger();
			$logger->debug('Validation of the IP address access ...');
			$logger->debug('Check if whitelist IP access is enabled in the configuration ...');
			$isEnable = get_config('white_list_ip_enable', false);
			if($isEnable){
				$logger->info('Whitelist IP access is enabled in the configuration');
				$list = get_config('white_list_ip_addresses', array());
				if(! empty($list)){
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
							$wildcardPosition = strpos($ipaddr, '*');
							if ($wildcardPosition === false) {
								// no wild card in whitelisted ip --continue searching
								continue;
							}
							// cut ip at the position where we got the wild card on the whitelisted ip
							// and add the wold card to get the same pattern
							if (substr($ip, 0, $wildcardPosition) . '*' === $ipaddr) {
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
				$logger->info('Whitelist IP access is not enabled in the configuration, ignore checking');
			}
		 }
	}