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
         * The defined constants for Log level
         */
        const NONE      = 99999999;
        const EMERGENCY = 800;
        const ALERT     = 700;
        const CRITICAL  = 600;
        const ERROR     = 500;
        const WARNING   = 400;
        const NOTICE    = 300;
        const INFO      = 200;
        const DEBUG     = 100;

        /**
         * The logger name
         * @var string
         */
        private $logger = 'ROOT_LOGGER';
		
        /**
         * List of valid log level to be checked for the configuration
         * @var array
         */
        private static $validConfigLevel = array(
                                                    'off', 
                                                    'none', 
                                                    'emergency', 
                                                    'alert', 
                                                    'critical', 
                                                    'error', 
                                                    'warning', 
                                                    'notice', 
                                                    'info', 
                                                    'debug'
                                                );

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
         * System is unusable.
         *
         * @see Log::log for more detail
         * @param  string $message the log message to save
         */
        public function emergency($message) {
            $this->log(self::EMERGENCY, $message);
        } 

        /**
         * Action must be taken immediately.
         *
         * Example: Entire website down, database unavailable, etc. This should
         * trigger the SMS alerts and wake you up.
         *
         * @see Log::log for more detail
         * @param  string $message the log message to save
         */
        public function alert($message) {
            $this->log(self::ALERT, $message);
        } 

        /**
         * Critical conditions.
         *
         * Example: Application component unavailable, unexpected exception.
         *
         * @see Log::log for more detail
         * @param  string $message the log message to save
         */
        public function critical($message) {
            $this->log(self::CRITICAL, $message);
        } 
		
        /**
         * Runtime errors that do not require immediate action but should typically
         * be logged and monitored.
         *
         * @see Log::log for more detail
         * @param  string $message the log message to save
         */
        public function error($message) {
            $this->log(self::ERROR, $message);
        } 

        /**
         * Exceptional occurrences that are not errors.
         *
         * Example: Use of deprecated APIs, poor use of an API, undesirable things
         * that are not necessarily wrong.
         *
         * @see Log::log for more detail
         * @param  string $message the log message to save
         */
        public function warning($message) {
            $this->log(self::WARNING, $message);
        } 

        /**
         * Normal but significant events.
         *
         * @see Log::log for more detail
         * @param  string $message the log message to save
         */
        public function notice($message) {
            $this->log(self::NOTICE, $message);
        } 
		
        /**
         * Interesting events.
         *
         * Example: User logs in, SQL logs.
         *
         * @see Log::log for more detail
         * @param  string $message the log message to save
         */
        public function info($message) {
            $this->log(self::INFO, $message);
        } 
		
        /**
         * Detailed debug information.
         *
         * @see Log::log for more detail
         * @param  string $message the log message to save
         */
        public function debug($message) {
            $this->log(self::DEBUG, $message);
        } 
		
	/**
         * Logs with an arbitrary level.
         *
         * @param  integer|string $level   the log level in integer or string format,
         * if is string will convert into integer. 
         * @param  string $message the log message to be saved
         */
        public function log($level, $message) {
            $configLogLevel = get_config('log_level');
            if (!$configLogLevel) {
                //so means no need log just stop here
                return;
            }
            //check config log level
            if (!self::isValidConfigLevel($configLogLevel)) {
                //NOTE: here need put the show_error() "logging" to false 
                //to prevent self function loop call
                show_error('Invalid config log level [' . $configLogLevel . '], '
                           . 'the value must be one of the following: ' 
                           . implode(', ', array_map('strtoupper', self::$validConfigLevel))
                           , 'Log Config Error', 
                           $logging = false
                       );
                return;	
            }
			
            //check if config log_logger_name and current log can save log data
            if (!$this->currentLoggerNameCanSaveLog()) {
                return;
            }
			
            //if $level is not an integer
            if (!is_numeric($level)) {
                $level = self::getLevelValue($level);
            }

            //check if can logging regarding the log level config
            //or custom logger level
            if (!$this->levelCanSaveLog($level)) {
                return;
            }
			  
            //save the log data
            $this->saveLogData($level, $message);
        }	

        /**
         * Save the log data into file
         * @param  integer $level   the log level in integer format.
         * @param  string $message the log message to save
         * @return void
         */
        protected function saveLogData($level, $message) {
            $path = $this->getLogFilePath();

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
            $fileInfo = $this->getLogDebugBacktraceInfo();

            $str = $logDate . ' [' . str_pad($levelName, 9 /*emergency length*/) . ']' 
                            . ' [' . str_pad($ip, 15) . '] ' . $this->logger . ': ' 
                            . $message . ' ' . '[' . substr($fileInfo['file'], strlen(ROOT_PATH)) . ' ' . $fileInfo['class'] . '->' . $fileInfo['function'] . '():' . $fileInfo['line'] . ']' . "\n";
            $fp = fopen($path, 'a+');
            if (is_resource($fp)) {
                flock($fp, LOCK_EX); // exclusive lock, will get released when the file is closed
                fwrite($fp, $str);
                fclose($fp);
            }
        }	
        
        /**
         * Check if the given level can save log data
         * @param  integer $level the current level value to save the log data
         * @return boolean
         */
        protected function levelCanSaveLog($level) {
            $result = true;
            $configLogLevel = get_config('log_level');
             //check if can save log regarding the log level configuration
            $configLevel = self::getLevelValue($configLogLevel);
            if ($configLevel > $level) {
                //can't log
                $result = false;
            }
            //If result is false so means current log level can not save log data
            //using the config level 
            if ($result === false) {
                //Check for logger rule overwritting
                $configLoggersNameLevel = get_config('log_logger_name_level', array());
                foreach ($configLoggersNameLevel as $loggerName => $loggerLevel) { 
                    if (preg_match('#' . $loggerName . '#', $this->logger)) {
                        $loggerLevelValue = self::getLevelValue($loggerLevel);
                        if ($loggerLevelValue <= $level) {
                            $result = true;
                        } 
                        break;
                    }
                }
            }
            return $result;
        }

        /**
         * Check if the current logger can save log data regarding the configuration
         * of logger filter
         * @return boolean
         */
        protected function currentLoggerNameCanSaveLog() {
                $configLoggersName = get_config('log_logger_name', array());
                if (!empty($configLoggersName)) {
                    if (!in_array($this->logger, $configLoggersName)) {
                        return false;
                    }
                }
            return true;
        }

        /**
         * Return the debug backtrace information
         * @return array the line number and file path
         */
        protected function getLogDebugBacktraceInfo() {
            $dtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $i = 0;
            while ($dtrace[$i]['file'] == __FILE__ ) {
                $i++;
            } 
            $fileInfo = $dtrace[$i];
            
            $line = -1;
            $file = '';
            $function = '';
            $class = '';
           
            if (isset($fileInfo['file'])) {
                $file = $fileInfo['file'];
            }
            if (isset($fileInfo['line'])) {
                $line = $fileInfo['line'];
            }
            if (isset($dtrace[$i+1]['function'])) {
                $function = $dtrace[$i+1]['function'];
            }
            if (isset($dtrace[$i+1]['class'])) {
                $class = $dtrace[$i+1]['class'];
            }
            
            return array(
                'file' => $file,
                'line' => $line,
                'function' => $function,
                'class' => $class
            );
        }

        /**
         * return the current log file path to use
         * @return string
         */
        protected function getLogFilePath() {
            $logSavePath = get_config('log_save_path', null);
            if (!$logSavePath) {
                $logSavePath = LOGS_PATH;
            }
            
            if (!is_dir($logSavePath) || !is_writable($logSavePath)) {
                //NOTE: here need put the show_error() "logging" to false 
                //to prevent self function loop call
                show_error('Error : the log dir does not exist or is not writable',
                           'Log directory error', $logging = false);
            }
            return $logSavePath . 'logs-' . date('Y-m-d') . '.log';
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
         * @return int the log level in integer format using the predefined constants
         */
        protected static function getLevelValue($level) {
            $level = strtolower($level);
            $levelMaps = array(
                'emergency' => self::EMERGENCY,
                'alert'     => self::ALERT,
                'critical'  => self::CRITICAL,
                'error'     => self::ERROR,
                'warning'   => self::WARNING,
                'notice'    => self::NOTICE,
                'info'      => self::INFO,
                'debug'     => self::DEBUG
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
                self::EMERGENCY => 'EMERGENCY',
                self::ALERT     => 'ALERT',
                self::CRITICAL  => 'CRITICAL',
                self::ERROR     => 'ERROR',
                self::WARNING   => 'WARNING',
                self::NOTICE    => 'NOTICE',
                self::INFO      => 'INFO',
                self::DEBUG     => 'DEBUG'
            );
            $value = '';
            if (isset($levelMaps[$level])) {
                $value = $levelMaps[$level];
            }
            return $value;
        }

    }
