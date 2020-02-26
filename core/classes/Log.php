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

    class Log{
		
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
        public function __construct(){
        }

        /**
         * Set the logger to identify each message in the log
         * @param string $logger the logger name
         */
        public  function setLogger($logger){
            $this->logger = $logger;
        }

        /**
         * Save the fatal message in the log
         * @see Log::writeLog for more detail
         * @param  string $message the log message to save
         */
        public function fatal($message){
            $this->writeLog($message, self::FATAL);
        } 
		
        /**
         * Save the error message in the log
         * @see Log::writeLog for more detail
         * @param  string $message the log message to save
         */
        public function error($message){
            $this->writeLog($message, self::ERROR);
        } 

        /**
         * Save the warning message in the log
         * @see Log::writeLog for more detail
         * @param  string $message the log message to save
         */
        public function warning($message){
            $this->writeLog($message, self::WARNING);
        } 
		
        /**
         * Save the info message in the log
         * @see Log::writeLog for more detail
         * @param  string $message the log message to save
         */
        public function info($message){
            $this->writeLog($message, self::INFO);
        } 
		
        /**
         * Save the debug message in the log
         * @see Log::writeLog for more detail
         * @param  string $message the log message to save
         */
        public function debug($message){
            $this->writeLog($message, self::DEBUG);
        } 
		
		
        /**
         * Save the log message
         * @param  string $message the log message to be saved
         * @param  integer|string $level   the log level in integer or string format, if is string will convert into integer
         * to allow check the log level threshold.
         */
        public function writeLog($message, $level = self::INFO){
            $configLogLevel = get_config('log_level');
            if(! $configLogLevel){
                //so means no need log just stop here
                return;
            }
            //check config log level
            if(! self::isValidConfigLevel($configLogLevel)){
                //NOTE: here need put the show_error() "logging" to false to prevent loop
                show_error('Invalid config log level [' . $configLogLevel . '], the value must be one of the following: ' . implode(', ', array_map('strtoupper', self::$validConfigLevel)), $title = 'Log Config Error', $logging = false);	
            }
			
            //check if config log_logger_name and current log can save log data
            if(! $this->canSaveLogDataForLogger()){
                return;
            }
			
            //if $level is not an integer
            if(! is_numeric($level)){
                $level = self::getLevelValue($level);
            }
			
            //check if can logging regarding the log level config
            $configLevel = self::getLevelValue($configLogLevel);
            if($configLevel > $level){
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
         * @param  integer|string $level   the log level in integer or string format, if is string will convert into integer
         * @param  string $message the log message to save
         * @return void
         */
        protected function saveLogData($path, $level, $message){
            //may be at this time helper user_agent not yet included
            require_once CORE_FUNCTIONS_PATH . 'function_user_agent.php';
			
            ///////////////////// date //////////////
            $timestampWithMicro = microtime(true);
            $microtime = sprintf('%06d', ($timestampWithMicro - floor($timestampWithMicro)) * 1000000);
            $dateTime = new DateTime(date('Y-m-d H:i:s.' . $microtime, $timestampWithMicro));
            $logDate = $dateTime->format('Y-m-d H:i:s.u'); 
            //ip
            $ip = get_ip();
			
            //if $level is not an integer
            if(! is_numeric($level)){
                $level = self::getLevelValue($level);
            }

            //level name
            $levelName = self::getLevelName($level);
			
            //debug info
            $dtrace = debug_backtrace();
            $fileInfo = $dtrace[0];
            if ($dtrace[0]['file'] == __FILE__ || $dtrace[1]['file'] == __FILE__){
                $fileInfo = $dtrace[2];
            }
			
            $str = $logDate . ' [' . str_pad($levelName, 7 /*warning len*/) . '] ' . ' [' . str_pad($ip, 15) . '] ' . $this->logger . ' : ' . $message . ' ' . '[' . $fileInfo['file'] . '::' . $fileInfo['line'] . ']' . "\n";
            $fp = fopen($path, 'a+');
            if(is_resource($fp)){
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
        protected function canSaveLogDataForLogger(){
            if(! empty($this->logger)){
                $configLoggersName = get_config('log_logger_name', array());
                if (!empty($configLoggersName)) {
                    //for best comparaison put all string to lowercase
                    $configLoggersName = array_map('strtolower', $configLoggersName);
                    if(! in_array(strtolower($this->logger), $configLoggersName)){
                        return false;
                    }
                }
            }
            return true;
        }

        /**
         * Check the file and directory 
         * @return string the log file path
         */
        protected function checkAndSetLogFileDirectory(){
            $logSavePath = get_config('log_save_path');
            if(! $logSavePath){
                $logSavePath = LOGS_PATH;
            }
			
            if(! is_dir($logSavePath) || !is_writable($logSavePath)){
                //NOTE: here need put the show_error() "logging" to false to prevent loop
                show_error('Error : the log dir does not exists or is not writable', $title = 'Log directory error', $logging = false);
            }
			
            $path = $logSavePath . 'logs-' . date('Y-m-d') . '.log';
            if(! file_exists($path)){
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
        protected static function isValidConfigLevel($level){
            $level = strtolower($level);
            return in_array($level, self::$validConfigLevel);
        }

        /**
         * Get the log level number for the given level string
         * @param  string $level the log level in string format
         * 
         * @return int        the log level in integer format using the predefined constants
         */
        protected static function getLevelValue($level){
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
            if(isset($levelMaps[$level])){
                $value = $levelMaps[$level];
            }
            return $value;
        }

        /**
         * Get the log level string for the given log level integer
         * @param  integer $level the log level in integer format
         * @return string        the log level in string format
         */
        protected static function getLevelName($level){
            $levelMaps = array(
                self::FATAL   => 'FATAL',
                self::ERROR   => 'ERROR',
                self::WARNING => 'WARNING',
                self::INFO    => 'INFO',
                self::DEBUG   => 'DEBUG'
            );
            $value = '';
            if(isset($levelMaps[$level])){
                $value = $levelMaps[$level];
            }
            return $value;
        }

    }
