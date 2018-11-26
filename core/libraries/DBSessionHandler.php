<?php 
defined('ROOT_PATH') || exit('Access denied');

	/**
	 * check if the interface "SessionHandlerInterface" exists (normally in PHP 5.4 this already exists)
	 */
	if( !interface_exists('SessionHandlerInterface')){
		show_error('"SessionHandlerInterface" interface does not exists or is disabled can not use it to handler database session.');
	}

	class DBSessionHandler implements SessionHandlerInterface{
		/**
		 * the encryption method to use to encrypt session data in database
		 * @const string
		 */
		const DB_SESSION_HASH_METHOD = 'AES-256-CBC';


		/**
		 * session secret minimum lenght allowed
		 * @const int
		 */
		const SESSION_SECRET_MIN_LENGTH = 64;
		
		/**
		 * super global instance
		 * @var object
		 */
		protected $OBJ = null;

		/**
		 * session secret to use 
		 * @var string
		 */
		private $sessionSecret = null;

		/**
		 * the initialisation vector to use for openssl
		 * @var string
		 */
		private $iv = null;

		/**
		 * the model instance name to use after load model
		 * @var string
		 */
		private $modelInstanceName = null;

		/**
		 * the columns of the table to use to store session data
		 * @var array
		 */
		private $sessionTableColumns = array();

		public function __construct(){
			$this->OBJ = & get_instance();
			$secret = Config::get('session_secret', false);
			//try to check if session secret is set and the length is >= SESSION_SECRET_MIN_LENGTH
			if(!$secret || strlen($secret) < self::SESSION_SECRET_MIN_LENGTH){
				show_error('Session secret is not set or the length is below to '.self::SESSION_SECRET_MIN_LENGTH.' caracters', 1);
			}
			$modelName = Config::get('session_save_path');
			Loader::model($modelName);

			//set model instance name
			$this->modelInstanceName = $this->OBJ->{strtolower($modelName)};

			if(! $this->modelInstanceName instanceof DBSessionHandler_model){
				show_error('To use database session handler, your class model "'.$modelName.'" need extends "DBSessionHandler_model"');
			}

			//set session tables columns
			$this->sessionTableColumns = $this->modelInstanceName->getSessionTableColumns();

			if(empty($this->sessionTableColumns)){
				show_error('The session handler is "database" but the table columns does not set');
			}

			$this->sessionSecret = $secret;
			$key = base64_decode($this->sessionSecret);
			
			$iv_length = openssl_cipher_iv_length(self::DB_SESSION_HASH_METHOD);
			$this->iv = substr(hash('sha256', $key), 0, $iv_length);
		}


		public function open($savePath, $sessionName){
			return true;
		}


		public function close(){
			return true;
		}

		public function read($sid){
			$instance = $this->modelInstanceName;
			$columns = $this->sessionTableColumns;
			$data = $instance->get($sid);
			if($data && isset($data->{$columns['sdata']})){
				//checking inactivity 
				$timeInactivity = time() - Config::get('session_inactivity_time', 100);
				if($data->{$columns['stime']} < $timeInactivity){
					$this->destroy($sid);
					return false;
				}
				return $this->decode($data->{$columns['sdata']});
			}
			return false;
		}

		public function write($sid, $data){
			$instance = $this->modelInstanceName;
			$columns = $this->sessionTableColumns;

			Loader::functions('user_agent'); //for using get_ip()
			$ip = get_ip();
			$keyValue = $instance->getKeyValue();
			$host = gethostbyaddr($ip);
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
			
			$exists = $instance->get($sid);
			if($exists){
				//update
				unset($params[$columns['sid']]);
				$instance->update($sid, $params);
			}
			else{
				$instance->insert($params);
			}
			return true;
		}


		public function destroy($sid){
			$instance = $this->modelInstanceName;
			$instance->delete($sid);
			return true;
		}

		public function gc($maxLifetime){
			$instance = $this->modelInstanceName;
			$time = time() - $maxLifetime;
			$instance->deleteByTime($time);
			return true;
		}

		public function decode($data){
			$key = base64_decode($this->sessionSecret);
			$data = base64_decode($data);
			$data = openssl_decrypt($data, self::DB_SESSION_HASH_METHOD, $key, OPENSSL_RAW_DATA, $this->iv);
			return $data;
		}


		public function encode($data){
			$key = base64_decode($this->sessionSecret);
			$dataEncrypted = openssl_encrypt($data , self::DB_SESSION_HASH_METHOD, $key, OPENSSL_RAW_DATA, $this->iv);
			$output = base64_encode($dataEncrypted);
			return $output;
		}
	}