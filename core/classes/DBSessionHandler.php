<?php 
    defined('ROOT_PATH') || exit('Access denied');
    /**
     * TNH Framework
     *
     * A simple PHP framework using HMVC architecture
     *
     * This content is released under the MIT License (MIT)
     *
     * Copyright (c) 2017 TNH Framework
     *
     * Permission is hereby granted, free of charge, to any person obtaining a copy
     * of this software and associated documentation files (the "Software"), to deal
     * in the Software without restriction, including without limitation the rights
     * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
     * copies of the Software, and to permit persons to whom the Software is
     * furnished to do so, subject to the following conditions:
     *
     * The above copyright notice and this permission notice shall be included in all
     * copies or substantial portions of the Software.
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
     * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
     * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
     * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
     * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
     * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
     * SOFTWARE.
     */
	
    /**
     * check if the interface "SessionHandlerInterface" exists (normally in PHP 5.4 this already exists)
     */
    if (!interface_exists('SessionHandlerInterface')) {
        show_error('"SessionHandlerInterface" interface does not exists or is disabled can not use it to handler database session.');
    }

    class DBSessionHandler extends BaseClass implements SessionHandlerInterface {
		
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
        private $initializerVector = null;

        /**
         * The model instance to use
         * @var object
         */
        private $model = null;

        /**
         * The columns of the table to use to store session data
         * @var array
         */
        private $sessionTableColumns = array();

        /**
         * Create new instance of Database session handler
         * @param object $model the model instance
         */
        public function __construct(DBSessionHandlerModel $model = null) {
            parent::__construct();
            $this->OBJ = & get_instance();
            if (is_object($model)) {
                $this->setModel($model);
            }
        }

        
        /**
         * Open the database session handler, here nothing to do just return true
         * @param  string $savePath    the session save path
         * @param  string $sessionName the session name
         * @return boolean 
         */
        public function open($savePath, $sessionName) {
            $this->logger->debug('Opening database session handler save path [' . $savePath . '], session name [' . $sessionName . ']');
            //try to check if session secret is set before
            $secret = $this->getSessionSecret();
            if (empty($secret)) {
                $secret = get_config('session_secret', null);
                $this->setSessionSecret($secret);
            }
            $this->logger->info('Session secret: ' . $secret);

            if (!is_object($this->model)) {
                $this->setModelFromSessionConfig();
            }
            $this->setInitializerVector($secret);

            //set session tables columns
            $this->sessionTableColumns = $this->model->getSessionTableColumns();

            if (empty($this->sessionTableColumns)) {
                show_error('The session handler is "database" but the table columns is not set');
            }
            $this->logger->info('Database session, the model columns are listed below: ' . stringify_vars($this->sessionTableColumns));
			
            //delete the expired session
            $timeActivity = get_config('session_inactivity_time', 100);
            $this->gc($timeActivity);
			
            return true;
        }

        /**
         * Close the session
         * @return boolean
         */
        public function close() {
            $this->logger->debug('Closing database session handler');
            return true;
        }

        /**
         * Get the session value for the given session id
         * @param  string $sid the session id to use
         * @return string      the session data in serialiaze format
         */
        public function read($sid) {
            $this->logger->debug('Reading database session data for SID: ' . $sid);
            $instance = $this->getModel();
            $columns = $this->sessionTableColumns;
            list(, $host, $browser) = $this->getSessionDataParams();
			
            $data = $instance->getSingleRecordCond(array($columns['sid'] => $sid, $columns['shost'] => $host, $columns['sbrowser'] => $browser));
            if ($data && isset($data->{$columns['sdata']})) {
                //checking inactivity 
                $timeInactivity = time() - get_config('session_inactivity_time', 100);
                if ($data->{$columns['stime']} < $timeInactivity) {
                    $this->logger->info('Database session data for SID: ' . $sid . ' already expired, destroy it');
                    $this->destroy($sid);
                    return null;
                }
                return $this->decode($data->{$columns['sdata']});
            }
            $this->logger->warning('Database session data for SID: ' . $sid . ' is not valid return false, may be the session ID is wrong');
            return null;
        }

        /**
         * Save the session data
         * @param  string $sid  the session ID
         * @param  mixed $data the session data to save in serialize format
         * @return boolean 
         */
        public function write($sid, $data) {
            $this->logger->debug('Saving database session data for SID: ' . $sid . ', data: ' . stringify_vars($data));
            $instance = $this->getModel();
            $columns = $this->sessionTableColumns;
            $keyValue = $instance->getKeyValue();
            list($ip, $host, $browser) = $this->getSessionDataParams();
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
            $this->logger->info('Database session data to save are listed below :' . stringify_vars($params));
            $exists = $instance->getSingleRecord($sid);
            if ($exists) {
                $this->logger->info('Session data for SID: ' . $sid . ' already exists, just update it');
                //update
                unset($params[$columns['sid']]);
                return $instance->update($sid, $params);
            }
            $this->logger->info('Session data for SID: ' . $sid . ' not yet exists, insert it now');
            return $instance->insert($params);
        }


        /**
         * Destroy the session data for the given session id
         * @param  string $sid the session id value
         * @return boolean
         */
        public function destroy($sid) {
            $this->logger->debug('Destroy of session data for SID: ' . $sid);
            $this->model->delete($sid);
            return true;
        }

        /**
         * Clean the expire session data to save espace
         * @param  integer $maxLifetime the max lifetime
         * @return boolean
         */
        public function gc($maxLifetime) {
            $time = time() - $maxLifetime;
            $this->logger->debug('Garbage collector of expired session. maxLifetime [' . $maxLifetime . '] sec, expired time [' . $time . ']');
            $this->model->deleteExipredSession($time);
            return true;
        }

        /**
         * Set the session secret used to encrypt the data in database 
         * @param string $secret the base64 string secret
         */
        public function setSessionSecret($secret) {
            $this->sessionSecret = $secret;
            return $this;
        }

        /**
         * Return the session secret
         * @return string 
         */
        public function getSessionSecret() {
            return $this->sessionSecret;
        }


        /**
         * Set the initializer vector for openssl 
         * @param string $key the session secret used in base64 format
         */
        public function setInitializerVector($key) {
            $ivLength = openssl_cipher_iv_length(self::DB_SESSION_HASH_METHOD);
            $key = base64_decode($key);
            $this->initializerVector = substr(hash('sha256', $key), 0, $ivLength);
            return $this;
        }

        /**
         * Return the initializer vector
         * @return string 
         */
        public function getInitializerVector() {
            return $this->initializerVector;
        }

        /**
         * Return the model instance
         * @return object DBSessionHandlerModel the model instance
         */
        public function getModel() {
            return $this->model;
        }

        /**
         * set the model instance for future use
         * @param DBSessionHandlerModel $model the model object
         */
        public function setModel(DBSessionHandlerModel $model) {
            $this->model = $model;
            return $this;
        }

        /**
         * Encode the session data using the openssl
         * @param  mixed $data the session data to encode
         * @return mixed the encoded session data
         */
        protected function encode($data) {
            $key = base64_decode($this->sessionSecret);
            $dataEncrypted = openssl_encrypt($data, self::DB_SESSION_HASH_METHOD, $key, OPENSSL_RAW_DATA, $this->getInitializerVector());
            $output = base64_encode($dataEncrypted);
            return $output;
        }


        /**
         * Decode the session data using the openssl
         * @param  mixed $data the data to decode
         * @return mixed       the decoded data
         */
        protected function decode($data) {
            $key = base64_decode($this->sessionSecret);
            $data = base64_decode($data);
            $data = openssl_decrypt($data, self::DB_SESSION_HASH_METHOD, $key, OPENSSL_RAW_DATA, $this->getInitializerVector());
            return $data;
        }


        /**
         * Set the model instance using the configuration for session
         */
        protected function setModelFromSessionConfig() {
            $modelName = get_config('session_save_path');
            $this->logger->info('The database session model: ' . $modelName);
            $this->OBJ->loader->model($modelName, 'dbsessionhandlerinstance'); 
            //@codeCoverageIgnoreStart
            if (isset($this->OBJ->dbsessionhandlerinstance) 
                && !($this->OBJ->dbsessionhandlerinstance instanceof DBSessionHandlerModel)
            ) {
                show_error('To use database session handler, your class model "' . get_class($this->OBJ->dbsessionhandlerinstance) . '" need extends "DBSessionHandlerModel"');
            }  
            //@codeCoverageIgnoreEnd
			
            //set model instance
            $this->model = $this->OBJ->dbsessionhandlerinstance;
        }

        /**
         * Get some parameters need like ip address, hostname, browser info, etc.
         * @return array
         */
        protected function getSessionDataParams(){
            $this->OBJ->loader->functions('user_agent'); 
            $this->OBJ->loader->library('Browser'); 
            
            $ip = get_ip();
            $host = gethostbyaddr($ip);
            $browser = $this->OBJ->browser->getPlatform() . ', ' 
                            . $this->OBJ->browser->getBrowser() 
                            . ' ' . $this->OBJ->browser->getVersion();
            return array($ip, $host, $browser);
        }
    }
