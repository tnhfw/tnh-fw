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

		/**
         * Instance of the Loader class
         * @var Loader
         */
        protected $loader = null;

		public function __construct(DBSessionHandlerModel $modelInstance = null, Log $logger = null, Loader $loader = null){
			/**
	         * instance of the Log class
	         */
	        if(is_object($logger)){
	          $this->setLogger($logger);
	        }
	        else{
	            $this->logger =& class_loader('Log', 'classes');
	            $this->logger->setLogger('Library::DBSessionHandler');
	        }

	        if(is_object($loader)){
	          $this->setLoader($loader);
	        }
		    $this->OBJ = & get_instance();

		    
			if(is_object($modelInstance)){
				$this->setModelInstance($modelInstance);
			}
		}

		/**
		 * Set the session secret used to encrypt the data in database 
		 * @param string $secret the base64 string secret
		 */
		public function setSessionSecret($secret){
			$this->sessionSecret = $secret;
			return $this;
		}

		/**
		 * Return the session secret
		 * @return string 
		 */
		public function getSessionSecret(){
			return $this->sessionSecret;
		}


		/**
		 * Set the initializer vector for openssl 
		 * @param string $key the session secret used
		 */
		public function setInitializerVector($key){
			$iv_length = openssl_cipher_iv_length(self::DB_SESSION_HASH_METHOD);
			$key = base64_decode($key);
			$this->iv = substr(hash('sha256', $key), 0, $iv_length);
			return $this;
		}

		/**
		 * Return the initializer vector
		 * @return string 
		 */
		public function getInitializerVector(){
			return $this->iv;
		}

		/**
		 * Open the database session handler, here nothing to do just return true
		 * @param  string $savePath    the session save path
		 * @param  string $sessionName the session name
		 * @return boolean 
		 */
		public function open($savePath, $sessionName){
			$this->logger->debug('Opening database session handler for [' . $sessionName . ']');
			//try to check if session secret is set before
			if(! $this->getSessionSecret()){
				$secret = get_config('session_secret', false);
				$this->setSessionSecret($secret);
			}
			$this->logger->info('Session secret: ' . $this->getSessionSecret());

			if(! $this->getModelInstance()){
				$this->setModelInstanceFromConfig();
			}
			$this->setInitializerVector($this->getSessionSecret());

			//set session tables columns
			$this->sessionTableColumns = $this->getModelInstance()->getSessionTableColumns();

			if(empty($this->sessionTableColumns)){
				show_error('The session handler is "database" but the table columns not set');
			}
			$this->logger->info('Database session, the model columns are listed below: ' . stringfy_vars($this->sessionTableColumns));
			
			//delete the expired session
			$timeActivity = get_config('session_inactivity_time', 100);
			$this->gc($timeActivity);
			
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
			$instance = $this->getModelInstance();
			$columns = $this->sessionTableColumns;
			if($this->getLoader()){
				$this->getLoader()->functions('user_agent'); 
				$this->getLoader()->library('Browser'); 
			}
			else{
            	Loader::functions('user_agent');
            	Loader::library('Browser');
            }
			
			$ip = get_ip();
			$keyValue = $instance->getKeyValue();
			$host = @gethostbyaddr($ip) or null;
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
			$instance = $this->getModelInstance();
			$columns = $this->sessionTableColumns;

			if($this->getLoader()){
				$this->getLoader()->functions('user_agent'); 
				$this->getLoader()->library('Browser'); 
			}
			else{
            	Loader::functions('user_agent');
            	Loader::library('Browser');
            }

			$ip = get_ip();
			$keyValue = $instance->getKeyValue();
			$host = @gethostbyaddr($ip) or null;
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
		 * Encode the session data using the openssl
		 * @param  mixed $data the session data to encode
		 * @return mixed the encoded session data
		 */
		public function encode($data){
			$key = base64_decode($this->sessionSecret);
			$dataEncrypted = openssl_encrypt($data , self::DB_SESSION_HASH_METHOD, $key, OPENSSL_RAW_DATA, $this->getInitializerVector());
			$output = base64_encode($dataEncrypted);
			return $output;
		}


		/**
		 * Decode the session data using the openssl
		 * @param  mixed $data the data to decode
		 * @return mixed       the decoded data
		 */
		public function decode($data){
			$key = base64_decode($this->sessionSecret);
			$data = base64_decode($data);
			$data = openssl_decrypt($data, self::DB_SESSION_HASH_METHOD, $key, OPENSSL_RAW_DATA, $this->getInitializerVector());
			return $data;
		}

		
		/**
         * Return the loader instance
         * @return Loader the loader instance
         */
        public function getLoader(){
            return $this->loader;
        }

        /**
         * set the loader instance for future use
         * @param Loader $loader the loader object
         */
         public function setLoader($loader){
            $this->loader = $loader;
            return $this;
        }

        /**
         * Return the model instance
         * @return DBSessionHandlerModel the model instance
         */
        public function getModelInstance(){
            return $this->modelInstanceName;
        }

        /**
         * set the model instance for future use
         * @param DBSessionHandlerModel $modelInstance the model object
         */
         public function setModelInstance(DBSessionHandlerModel $modelInstance){
            $this->modelInstanceName = $modelInstance;
            return $this;
        }

        /**
	     * Return the Log instance
	     * @return Log
	     */
	    public function getLogger(){
	      return $this->logger;
	    }

	    /**
	     * Set the log instance
	     * @param Log $logger the log object
	     */
	    public function setLogger(Log $logger){
	      $this->logger = $logger;
	      return $this;
	    }

	    /**
	     * Set the model instance using the configuration for session
	     */
	    private function setModelInstanceFromConfig(){
	    	$modelName = get_config('session_save_path');
			$this->logger->info('The database session model: ' . $modelName);
			if($this->getLoader()){
				$this->getLoader()->model($modelName, 'dbsessionhandlerinstance'); 
			}
			//@codeCoverageIgnoreStart
			else{
            	Loader::model($modelName, 'dbsessionhandlerinstance'); 
            }
            if(isset($this->OBJ->dbsessionhandlerinstance) && ! $this->OBJ->dbsessionhandlerinstance instanceof DBSessionHandlerModel){
				show_error('To use database session handler, your class model "'.get_class($this->OBJ->dbsessionhandlerinstance).'" need extends "DBSessionHandlerModel"');
			}  
			//@codeCoverageIgnoreEnd
			
			//set model instance
			$this->setModelInstance($this->OBJ->dbsessionhandlerinstance);
	    }
	}
