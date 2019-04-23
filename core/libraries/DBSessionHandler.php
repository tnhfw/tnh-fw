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
	
	/**
	 * check if the interface "SessionHandlerInterface" exists (normally in PHP 5.4 this already exists)
	 */
	if( !interface_exists('SessionHandlerInterface')){
		show_error('"SessionHandlerInterface" interface does not exists or is disabled can not use it to handler database session.');
	}

	class DBSessionHandler implements SessionHandlerInterface{
		
		/**
		 * The encryption method to use to encrypt session data in database
		 * @const string
		 */
		const DB_SESSION_HASH_METHOD = 'AES-256-CBC';


		/**
		 * Session secret minimum length allowed
		 * @const int
		 */
		const SESSION_SECRET_MIN_LENGTH = 64;
		
		/**
		 * Super global instance
		 * @var object
		 */
		protected $OBJ = null;

		/**
		 * Session secret to use 
		 * @var string
		 */
		private $sessionSecret = null;

		/**
		 * The initialisation vector to use for openssl
		 * @var string
		 */
		private $iv = null;

		/**
		 * The model instance name to use after load model
		 * @var string
		 */
		private $modelInstanceName = null;

		/**
		 * The columns of the table to use to store session data
		 * @var array
		 */
		private $sessionTableColumns = array();

		/**
		 * The instance of the Log 
		 * @var Log
		 */
		private $logger;

		public function __construct(){
		    $this->logger =& class_loader('Log'); 
		    $this->logger->setLogger('Library::DBSessionHandler');
			$this->OBJ = & get_instance();

			$secret = get_config('session_secret', false);
			//try to check if session secret is set and the length is >= SESSION_SECRET_MIN_LENGTH
			if(! $secret || strlen($secret) < self::SESSION_SECRET_MIN_LENGTH){
				show_error('Session secret is not set or the length is below to '.self::SESSION_SECRET_MIN_LENGTH.' caracters');
			}
			$this->logger->info('Session secret: ' . $secret);

			$modelName = get_config('session_save_path');
			$this->logger->info('The database session model: ' . $modelName);
			Loader::model($modelName, 'dbsessionhanlderinstance');

			//set model instance name
			$this->modelInstanceName = $this->OBJ->dbsessionhanlderinstance;

			if(! $this->modelInstanceName instanceof DBSessionHandlerModel){
				show_error('To use database session handler, your class model "'.$modelName.'" need extends "DBSessionHandlerModel"');
			}

			//set session tables columns
			$this->sessionTableColumns = $this->modelInstanceName->getSessionTableColumns();

			if(empty($this->sessionTableColumns)){
				show_error('The session handler is "database" but the table columns not set');
			}
			$this->logger->info('Database session, the model columns are listed below: ' . stringfy_vars($this->sessionTableColumns));

			$this->sessionSecret = $secret;
			$key = base64_decode($secret);
			
			$iv_length = openssl_cipher_iv_length(self::DB_SESSION_HASH_METHOD);
			$this->iv = substr(hash('sha256', $key), 0, $iv_length);
			
			//delete the expired session
			$timeActivity = get_config('session_inactivity_time', 100);
			$this->gc($timeActivity);
		}

		/**
		 * Open the database session handler, here nothing to do just return true
		 * @param  string $savePath    the session save path
		 * @param  string $sessionName the session name
		 * @return boolean 
		 */
		public function open($savePath, $sessionName){
			$this->logger->debug('Opening database session handler for [' . $sessionName . ']');
			return true;
		}

		/**
		 * Close the session
		 * @return boolean
		 */
		public function close(){
			$this->logger->debug('Closing database session handler');
			return true;
		}

		/**
		 * Get the session value for the given session id
		 * @param  string $sid the session id to use
		 * @return mixed      the session data in serialiaze format
		 */
		public function read($sid){
			$this->logger->debug('Reading database session data for SID: ' . $sid);
			$instance = $this->modelInstanceName;
			$columns = $this->sessionTableColumns;
			Loader::functions('user_agent'); //for using get_ip()
			$ip = get_ip();
			$keyValue = $instance->getKeyValue();
			$host = @gethostbyaddr($ip) or null;
			Loader::library('Browser');
			$browser = $this->OBJ->browser->getPlatform().', '.$this->OBJ->browser->getBrowser().' '.$this->OBJ->browser->getVersion();
			
			$data = $instance->get_by(array($columns['sid'] => $sid, $columns['shost'] => $host, $columns['sbrowser'] => $browser));
			if($data && isset($data->{$columns['sdata']})){
				//checking inactivity 
				$timeInactivity = time() - get_config('session_inactivity_time', 100);
				if($data->{$columns['stime']} < $timeInactivity){
					$this->logger->info('Database session data for SID: ' . $sid . ' already expired, destroy it');
					$this->destroy($sid);
					return false;
				}
				return $this->decode($data->{$columns['sdata']});
			}
			$this->logger->info('Database session data for SID: ' . $sid . ' is not valid return false, may be the session ID is wrong');
			return false;
		}

		/**
		 * Save the session data
		 * @param  string $sid  the session ID
		 * @param  mixed $data the session data to save in serialize format
		 * @return boolean 
		 */
		public function write($sid, $data){
			$this->logger->debug('Saving database session data for SID: ' . $sid . ', data: ' . stringfy_vars($data));
			$instance = $this->modelInstanceName;
			$columns = $this->sessionTableColumns;

			Loader::functions('user_agent'); //for using get_ip()
			$ip = get_ip();
			$keyValue = $instance->getKeyValue();
			$host = @gethostbyaddr($ip) or null;
			Loader::library('Browser');
			$browser = $this->OBJ->browser->getPlatform().', '.$this->OBJ->browser->getBrowser().' '.$this->OBJ->browser->getVersion();
			$data = $this->encode($data);
			$params = array(
					$columns['sid'] => $sid,
					$columns['sdata'] => $data,
					$columns['stime'] => time(),
					$columns['shost'] => $host,
					$columns['sbrowser'] => $browser,
					$columns['sip'] => $ip,
					$columns['skey'] => $keyValue
			);
			$this->logger->info('Database session data to save are listed below :' . stringfy_vars($params));
			$exists = $instance->get($sid);
			if($exists){
				$this->logger->info('Session data for SID: ' . $sid . ' already exists, just update it');
				//update
				unset($params[$columns['sid']]);
				$instance->update($sid, $params);
			}
			else{
				$this->logger->info('Session data for SID: ' . $sid . ' not yet exists, insert it now');
				$instance->insert($params);
			}
			return true;
		}


		/**
		 * Destroy the session data for the given session id
		 * @param  string $sid the session id value
		 * @return boolean
		 */
		public function destroy($sid){
			$this->logger->debug('Destroy of session data for SID: ' . $sid);
			$instance = $this->modelInstanceName;
			$instance->delete($sid);
			return true;
		}

		/**
		 * Clean the expire session data to save espace
		 * @param  ineteger $maxLifetime the max lifetime
		 * @return boolean
		 */
		public function gc($maxLifetime){
			$instance = $this->modelInstanceName;
			$time = time() - $maxLifetime;
			$this->logger->debug('Garbage collector of expired session. maxLifetime [' . $maxLifetime . '] sec, expired time [' . $time . ']');
			$instance->deleteByTime($time);
			return true;
		}

		/**
		 * Decode the session data using the openssl
		 * @param  mixed $data the data to decode
		 * @return mixed       the decoded data
		 */
		public function decode($data){
			$key = base64_decode($this->sessionSecret);
			$data = base64_decode($data);
			$data = openssl_decrypt($data, self::DB_SESSION_HASH_METHOD, $key, OPENSSL_RAW_DATA, $this->iv);
			return $data;
		}

		/**
		 * Encode the session data using the openssl
		 * @param  mixed $data the session data to encode
		 * @return mixed the encoded session data
		 */
		public function encode($data){
			$key = base64_decode($this->sessionSecret);
			$dataEncrypted = openssl_encrypt($data , self::DB_SESSION_HASH_METHOD, $key, OPENSSL_RAW_DATA, $this->iv);
			$output = base64_encode($dataEncrypted);
			return $output;
		}
	}