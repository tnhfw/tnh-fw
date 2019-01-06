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

		/**
		 * the instance of the Log 
		 * @var Log
		 */
		private $logger;

		public function __construct(){
			if(!class_exists('Log')){
		        //here the Log class is not yet loaded
		        //load it manually
		        require_once CORE_LIBRARY_PATH . 'Log.php';
		    }
		    $this->logger = new Log();
		    $this->logger->setLogger('Library::' . __CLASS__, __FILE__);
			$this->OBJ = & get_instance();

			$secret = Config::get('session_secret', false);
			//try to check if session secret is set and the length is >= SESSION_SECRET_MIN_LENGTH
			if(!$secret || strlen($secret) < self::SESSION_SECRET_MIN_LENGTH){
				show_error('Session secret is not set or the length is below to '.self::SESSION_SECRET_MIN_LENGTH.' caracters');
			}
			$this->logger->info('session secret: ' . $secret);

			$modelName = Config::get('session_save_path');
			$this->logger->info('database session model: ' . $modelName);
			Loader::model($modelName);

			//set model instance name
			$this->modelInstanceName = $this->OBJ->{strtolower($modelName)};

			if(! $this->modelInstanceName instanceof DBSessionHandler_model){
				show_error('To use database session handler, your class model "'.$modelName.'" need extends "DBSessionHandler_model"');
			}

			//set session tables columns
			$this->sessionTableColumns = $this->modelInstanceName->getSessionTableColumns();

			if(empty($this->sessionTableColumns)){
				show_error('The session handler is "database" but the table columns not set');
			}
			$this->logger->info('database session, the model columns are listed below: ' . stringfy_vars($this->sessionTableColumns));

			$this->sessionSecret = $secret;
			$key = base64_decode($this->sessionSecret);
			
			$iv_length = openssl_cipher_iv_length(self::DB_SESSION_HASH_METHOD);
			$this->iv = substr(hash('sha256', $key), 0, $iv_length);
		}


		public function open($savePath, $sessionName){
			$this->logger->debug('opening database session handler');
			return true;
		}


		public function close(){
			$this->logger->debug('closing database session handler');
			return true;
		}

		public function read($sid){
			$this->logger->debug('reading database session data for SID: ' . $sid);
			$instance = $this->modelInstanceName;
			$columns = $this->sessionTableColumns;
			$data = $instance->get($sid);
			if($data && isset($data->{$columns['sdata']})){
				//checking inactivity 
				$timeInactivity = time() - Config::get('session_inactivity_time', 100);
				if($data->{$columns['stime']} < $timeInactivity){
					$this->logger->info('database session data for SID: ' . $sid . ' already expired, destroy it');
					$this->destroy($sid);
					return false;
				}
				return $this->decode($data->{$columns['sdata']});
			}
			$this->logger->info('database session data for SID: ' . $sid . ' is not valid return false, may be session ID is wrong');
			return false;
		}

		public function write($sid, $data){
			$this->logger->debug('writing database session data for SID: ' . $sid . ', data: ' . stringfy_vars($data));
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
			$this->logger->info('database session data to save are listed below :' . stringfy_vars($params));
			$exists = $instance->get($sid);
			if($exists){
				$this->logger->info('session data for SID: ' . $sid . ' already exists, just update it');
				//update
				unset($params[$columns['sid']]);
				$instance->update($sid, $params);
			}
			else{
				$this->logger->info('session data for SID: ' . $sid . ' not yet exists, just insert it now');
				$instance->insert($params);
			}
			return true;
		}


		public function destroy($sid){
			$this->logger->debug('destroy of session data for SID: ' . $sid);
			$instance = $this->modelInstanceName;
			$instance->delete($sid);
			return true;
		}

		public function gc($maxLifetime){
			$instance = $this->modelInstanceName;
			$time = time() - $maxLifetime;
			$instance->deleteByTime($time);
			$this->logger->debug('garbage collector of expired session. maxLifetime: ' . $maxLifetime . ', expired time:' . $time);
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