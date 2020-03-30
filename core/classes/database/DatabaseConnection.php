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
    
    class DatabaseConnection extends BaseClass {
	
        /**
         * The PDO instance
         * @var object
         */
        private $pdo = null;
      
        /**
         * The database driver name to use
         * @var string
         */
        private $driver = null;

         /**
         * The database hostname
         * @var string
         */
        private $hostname = null;

          /**
         * The database port
         * @var integer
         */
        private $port = null;
        
         /**
         * The database username
         * @var string
         */
        private $username = null;

         /**
         * The database password
         * @var string
         */
        private $password = null;

        /**
         * The database name used for the application
         * @var string
         */
        private $databaseName = null;

         /**
         * The database charset
         * @var string
         */
        private $charset = null;

        /**
         * The database collation
         * @var string
         */
        private $collation = null;

         /**
         * The database tables prefix
         * @var string
         */
        private $prefix = null;

        /**
         * The database configuration
         * @var array
         */
        private $config = array();
	
        /**
         * Construct new DatabaseConnection
         * 
         * @param array $config the database configuration config
         * @param boolean $autoConnect whether to connect to database automatically
         */
        public function __construct(array $config = array(), $autoConnect = false) {
            parent::__construct();
            //Note need use the method to set config
            $this->setConfig($config);

            //if we need connect or not
            if ($autoConnect) {
                $this->connect();
            }
        }

         /**
         * This is method is used to connect to database
         * 
         * @return boolean true in case of successfully connection false if error
         */
        public function connect() {
            try {
                $options = array(
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
                );
                $this->pdo = new PDO($this->getDsnValue(), $this->getUsername(), $this->getPassword(), $options);
                if($this->getDriver() == 'mysql') {
                    $this->pdo->exec("SET NAMES '" . $this->getCharset() . "' COLLATE '" . $this->getCollation() . "'");
                    $this->pdo->exec("SET CHARACTER SET '" . $this->getCharset() . "'");
                }
                return is_object($this->pdo);
            } catch (PDOException $e) {
                $this->logger->critical($e->getMessage());
                show_error('Cannot connect to Database.');
                return false;
            }
        }

        /**
         * Disconnect from database server
         */
        public function disconnect() {
            $this->pdo = null;
        }

        /**
         * Escape the data before execute query useful for security.
         * @param  mixed $data the data to be escaped
         * @param boolean $escaped whether we can do escape of not 
         * @return mixed       the data after escaped or the same data if no
         * need escaped
         */
        public function escape($data, $escaped = true) {
            $data = trim($data);
            if ($escaped) {
                return $this->pdo->quote($data);
            }
            return $data; 
        }

        /**
         * Get the DSN value for the configuration driver
         * 
         * @return string|null         the dsn value
         */
        public function getDsnValue() {
            $dsn    = '';
            $port   = $this->getPort();
            $driver = $this->getDriver();

            $driversDsn = array(
                'mysql'  => 'mysql:host=' . $this->getHostname() . ';%sdbname=' . $this->getDatabase() . ';charset=' . $this->getCharset(),
                'pgsql'  => 'pgsql:host=' . $this->getHostname() . ';%sdbname=' . $this->getDatabase() . ';charset=' . $this->getCharset(),
                'oracle' => 'oci:dbname=' . $this->getHostname() . '%s/' . $this->getDatabase() . ';charset=' . $this->getCharset(),
                'sqlite' => 'sqlite:' . $this->getDatabase()
            );
            if ($port) {
                $driversPort = array(
                      'mysql'  => 'port=' . $port . ';',
                      'pgsql'  => 'port=' . $port . ';',
                      'oracle' => ':' . $port
                );
                if (isset($driversPort[$driver])) {
                    $port = $driversPort[$driver];
                }
            }
            if (isset($driversDsn[$driver])) {
                $dsn = sprintf($driversDsn[$driver], $port);
            }
            return $dsn;
        }
        
        /**
         * Return the PDO instance
         * @return object
         */
        public function getPdo() {
            return $this->pdo;
        }

        /**
         * Set the PDO object
         * @param object $pdo the instance of PDO
         *
         * @return object the current instance
         */
        public function setPdo(PDO $pdo = null) {
            $this->pdo = $pdo;
            return $this;
        }

        /**
         * Return the driver
         * 
         * @return string
         */
        public function getDriver() {
            return $this->driver;
        }

        /**
         * Set the driver
         * 
         * @param string $driver the drive to set like "pgsql", "mysql", etc.
         *
         * @return object the current instance
         */
        public function setDriver($driver) {
            $this->driver = $driver;
            return $this;
        }

        /**
         * Return the hostname
         * @return string
         */
        public function getHostname() {
            return $this->hostname;
        }

        /**
         * Set the hostname
         * @param string $hostname the hostname to set
         *
         * @return object the current instance
         */
        public function setHostname($hostname) {
            $this->hostname = $hostname;
            return $this;
        }

        /**
         * Return the port
         * 
         * @return int
         */
        public function getPort() {
            return $this->port;
        }

        /**
         * Set the port number
         * 
         * @param int $port the port to set
         *
         * @return object the current instance
         */
        public function setPort($port) {
            $this->port = $port;
            return $this;
        }

        /**
         * Return the username
         * 
         * @return string
         */
        public function getUsername() {
            return $this->username;
        }

        /**
         * Set the username
         * 
         * @param string $username the username to set
         *
         * @return object the current instance
         */
        public function setUsername($username) {
            $this->username = $username;
            return $this;
        }

        /**
         * Return the password
         * @return string
         */
        public function getPassword() {
            return $this->password;
        }

        /**
         * Set the password
         * 
         * @param string $password the password to set
         *
         * @return object the current instance
         */
        public function setPassword($password) {
            $this->password = $password;
            return $this;
        }

        /**
         * Return the database name
         * @return string
         */
        public function getDatabase() {
            return $this->databaseName;
        }

        /**
         * Set the database name
         * 
         * @param string $database the name of the database to set
         *
         * @return object the current instance
         */
        public function setDatabase($database) {
            $this->databaseName = $database;
            return $this;
        }

        /**
         * Return the charset
         * 
         * @return string
         */
        public function getCharset() {
            return $this->charset;
        }

        /**
         * Set the charset
         * 
         * @param string $charset the charset to set
         *
         * @return object the current instance
         */
        public function setCharset($charset) {
            $this->charset = $charset;
            return $this;
        }

        /**
         * Return the collation
         * 
         * @return string
         */
        public function getCollation() {
            return $this->collation;
        }

        /**
         * Set the collation
         * 
         * @param string $collation the collation to set
         *
         * @return object the current instance
         */
        public function setCollation($collation) {
            $this->collation = $collation;
            return $this;
        }

        /**
         * Return the prefix
         * 
         * @return string
         */
        public function getPrefix() {
            return $this->prefix;
        }

        /**
         * Set the tables prefix
         * 
         * @param string $prefix the prefix to set
         *
         * @return object the current instance
         */
        public function setPrefix($prefix) {
            $this->prefix = $prefix;
            return $this;
        }

        /**
         * Return the database configuration
         * 
         * @return array
         */
        public function getConfig() {
            return $this->config;
        }

        /**
         * Set the database configuration
         * 
         * @param array $config the configuration to set
         *
         * @return object the current instance
         */
        public function setConfig(array $config) {
            $this->config = $config;
            //populate the properties
            $this->populatePropertiesFromConfig();

            if (!empty($this->config)) {
               //For logging
                $configInfo = $this->config;
                //Hide password from log
                $configInfo['password'] = string_hidden($this->getPassword());
                $this->logger->info('The database configuration are listed below: ' . stringfy_vars($configInfo));
            }
            return $this;
        }

         /**
         * Get the database configuration using the configuration file
         
         * @return array the database configuration from file
         */
        public function getDatabaseConfigFromFile() {
            $db = array();
            if (file_exists(CONFIG_PATH . 'database.php')) {
                //here don't use require_once because somewhere can 
                //create database instance directly
                require CONFIG_PATH . 'database.php';
            }
            return $db;
        }

         /**
         * Update the properties using the current database configuration
         * 
         * @return object the current instance
         */
        protected function populatePropertiesFromConfig() {
            foreach ($this->config as $key => $value) {
                $setter = 'set' . ucfirst($key);
                if (method_exists($this, $setter)) {
                    $this->{$setter}($value);
                }
            }
            //determine the port using the hostname like localhost:34455
            //hostname will be "localhost", and port "34455"
            $part = explode(':', $this->hostname);
            if (count($part) >= 2) {
                $this->config['hostname'] = $part[0];
                $this->config['port'] = $part[1];
                $this->hostname = $part[0];
                $this->port = (int) $part[1];
            }
            return $this;
        }

        /**
         * Class desctructor this is used to disconnect to server
         * and call $this->disconnect
         */
        public function __destruct() {
            $this->disconnect();
        }
    }
