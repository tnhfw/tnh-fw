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

    class Log {
		
        /**
         * The defined constante for Log level
         */
        const NONE = 99999999;
        const FATAL = 500;
        const ERROR = 400;
        const WARNING = 300;
        const INFO = 200;
        const DEBUG = 100;
        const ALL = -99999999;

        /**
         * The logger name
         * @var string
         */
        private $logger = 'ROOT_LOGGER';
		
        /**
         * List of valid log level to be checked for the configuration
         * @var array
         */
        private static $validConfigLevel = array('off', 'none', 'fatal', 'error', 'warning', 'warn', 'info', 'debug', 'all');

        /**
         * Create new Log instance
         */
        public function __construct() {
        }

        /**
         * Set the logger to identify each message in the log
         * @param string $logger the logger name
         */
        public  function setLogger($logger) {
            $this->logger = $logger;
        }

        /**
         * get the logger name
         *
         * @return string
         */
        public  function getLogger() {
            return $this->logger;
        }

        /**
         * Save the fatal message in the log
         * @see Log::writeLog for more detail
         * @param  string $message the log message to save
         */
        public function fatal($message) {
            $this->writeLog($message, self::FATAL);
        } 
		
        /**
         * Save the error message in the log
         * @see Log::writeLog for more detail
         * @param  string $message the log message to save
         */
        public function error($message) {
            $this->writeLog($message, self::ERROR);
        } 

        /**
         * Save the warning message in the log
         * @see Log::writeLog for more detail
         * @param  string $message the log message to save
         */
        public function warning($message) {
            $this->writeLog($message, self::WARNING);
        } 
		
        /**
         * Save the info message in the log
         * @see Log::writeLog for more detail
         * @param  string $message the log message to save
         */
        public function info($message) {
            $this->writeLog($message, self::INFO);
        } 
		
        /**
         * Save the debug message in the log
         * @see Log::writeLog for more detail
         * @param  string $message the log message to save
         */
        public function debug($message) {
            $this->writeLog($message, self::DEBUG);
        } 
		
		
        /**
         * Save the log message
         * @param  string $message the log message to be saved
         * @param  integer|string $level   the log level in integer or string format, if is string will convert into integer
         * to allow check the log level threshold.
         */
        public function writeLog($message, $level = self::INFO) {
            $configLogLevel = get_config('log_level');
            if (!$configLogLevel) {
                //so means no need log just stop here
                return;
            }
            //check config log level
            if (!self::isValidConfigLevel($configLogLevel)) {
                //NOTE: here can not use show_error() because during the application bootstrap some dependencies are not yet loaded
                die('Invalid config log level [' . $configLogLevel . '], the value must be one of the following: ' . implode(', ', array_map('strtoupper', self::$validConfigLevel)));	
            }
			
            //check if config log_logger_name and current log can save log data
            if (!$this->canSaveLogDataForLogger()) {
                return;
            }
			
            //if $level is not an integer
            if (!is_numeric($level)) {
                $level = self::getLevelValue($level);
            }
			
            //check if can logging regarding the log level config
            $configLevel = self::getLevelValue($configLogLevel);
            if ($configLevel > $level) {
                //can't log
                return;
            }
            //check log file and directory
            $path = $this->checkAndSetLogFileDirectory();
            //save the log data
            $this->saveLogData($path, $level, $message);
        }	

        /**
         * Save the log data into file
         * @param  string $path    the path of the log file
         * @param  integer $level   the log level in integer format.
         * @param  string $message the log message to save
         * @return void
         */
        protected function saveLogData($path, $level, $message) {
            //may be at this time helper user_agent not yet included
            require_once CORE_FUNCTIONS_PATH . 'function_user_agent.php';
			
            ///////////////////// date //////////////
            $timestampWithMicro = microtime(true);
            $microtime = sprintf('%06d', ($timestampWithMicro - floor($timestampWithMicro)) * 1000000);
            $dateTime = new DateTime(date('Y-m-d H:i:s.' . $microtime, $timestampWithMicro));
            $logDate = $dateTime->format('Y-m-d H:i:s.u'); 
            //ip
            $ip = get_ip();

            //level name
            $levelName = self::getLevelName($level);
			
            //debug info
            $dtrace = debug_backtrace();
            $fileInfo = $dtrace[0];
            if ($dtrace[0]['file'] == __FILE__ || $dtrace[1]['file'] == __FILE__) {
                $fileInfo = $dtrace[2];
            }

            $line = -1;
            $file = -1;

            if (isset($fileInfo['file'])) {
                $file = $fileInfo['file'];
            }

            if (isset($fileInfo['line'])) {
                $line = $fileInfo['line'];
            }
			
            $str = $logDate . ' [' . str_pad($levelName, 7 /*warning len*/) . '] ' . ' [' . str_pad($ip, 15) . '] ' . $this->logger . ': ' . $message . ' ' . '[' . $file . '::' . $line . ']' . "\n";
            $fp = fopen($path, 'a+');
            if (is_resource($fp)) {
                flock($fp, LOCK_EX); // exclusive lock, will get released when the file is closed
                fwrite($fp, $str);
                fclose($fp);
            }
        }	

        /**
         * Check if the current logger can save log data regarding the configuration
         * of logger filter
         * @return boolean
         */
        protected function canSaveLogDataForLogger() {
                $configLoggersName = get_config('log_logger_name', array());
                if (!empty($configLoggersName)) {
                    //for best comparaison put all string to lowercase
                    $configLoggersName = array_map('strtolower', $configLoggersName);
                    if (!in_array(strtolower($this->logger), $configLoggersName)) {
                        return false;
                    }
                }
            return true;
        }

        /**
         * Check the file and directory 
         * @return string the log file path
         */
        protected function checkAndSetLogFileDirectory() {
            $logSavePath = get_config('log_save_path');
            if (!$logSavePath) {
                $logSavePath = LOGS_PATH;
            }
			
            if (!is_dir($logSavePath) || !is_writable($logSavePath)) {
                //NOTE: here can not use show_error() during bootstrap some dependencies needed are not yet loaded
                die('Error : the log dir does not exist or is not writable');
            }
			
            $path = $logSavePath . 'logs-' . date('Y-m-d') . '.log';
            if (!file_exists($path)) {
                touch($path);
            }
            return $path;
        }
		
        /**
         * Check if the given log level is valid
         *
         * @param  string  $level the log level
         *
         * @return boolean        true if the given log level is valid, false if not
         */
        protected static function isValidConfigLevel($level) {
            $level = strtolower($level);
            return in_array($level, self::$validConfigLevel);
        }

        /**
         * Get the log level number for the given level string
         * @param  string $level the log level in string format
         * 
         * @return int        the log level in integer format using the predefined constants
         */
        protected static function getLevelValue($level) {
            $level = strtolower($level);
            $levelMaps = array(
                'fatal'   => self::FATAL,
                'error'   => self::ERROR,
                'warning' => self::WARNING,
                'warn'    => self::WARNING,
                'info'    => self::INFO,
                'debug'   => self::DEBUG,
                'all'     => self::ALL
            );
            //the default value is NONE, so means no need test for NONE
            $value = self::NONE;
            if (isset($levelMaps[$level])) {
                $value = $levelMaps[$level];
            }
            return $value;
        }

        /**
         * Get the log level string for the given log level integer
         * @param  integer $level the log level in integer format
         * @return string        the log level in string format
         */
        protected static function getLevelName($level) {
            $levelMaps = array(
                self::FATAL   => 'FATAL',
                self::ERROR   => 'ERROR',
                self::WARNING => 'WARNING',
                self::INFO    => 'INFO',
                self::DEBUG   => 'DEBUG'
            );
            $value = '';
            if (isset($levelMaps[$level])) {
                $value = $levelMaps[$level];
            }
            return $value;
        }

    }
