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
		const ALL = -9999999999;

		/**
		 * The logger instance
		 * @var Log
		 */
		private $logger = 'ROOT';
		
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
		 * @param string $newlogger the logger name
		 */
		public  function setLogger($newlogger){
			$this->logger = $newlogger;
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
		 * @param  int|string $level   the log level in integer or string format, if is string will convert into integer
		 * to allow check the log level threshold.
		 */
		public function writeLog($message, $level = self::INFO){
			$log_level = get_config('log_level');
			if(! $log_level){
				//so means no need log just stop here
				return;
			}
			//check config log level
			if(!static::isValidConfigLevel($log_level)){
				//NOTE: here need put the show_error() "logging" to false to prevent loop
				show_error('Invalid config log level, the value must be one of the following: ' . implode(', ', array_map('strtoupper', static::$validConfigLevel)), $title = 'Log Config Error', $logging = false);	
			}

			//if $level is not an integer
			if(!is_numeric($level)){
				$level = static::getLevelValue($level);
			}
			//check if can logging regarding the log level config
			$configLevel = static::getLevelValue($log_level);
			if($configLevel > $level){
				//can't log
				return;
			}
			
			$log_save_path = get_config('log_save_path');
			if(!$log_save_path){
				$log_save_path = LOGS_PATH;
			}
			
			if(!is_dir($log_save_path) || !is_writable($log_save_path)){
				//NOTE: here need put the show_error() "logging" to false to prevent loop
				show_error('Error : the log dir does not exists or is not writable', $title = 'Log directory error', $logging = false);
			}
			$file = 'logs-'.date('d-m-Y').'.log';
			$path = $log_save_path . $file;
			if(!file_exists($path)){
				@touch($path);
			}
			//may be at this time helper user_agent not yet included
			require_once CORE_FUNCTIONS_PATH . 'function_user_agent.php';
			///////////////////// date //////////////
			$timestamp_with_micro = microtime(true);
			$microtime = sprintf("%06d", ($timestamp_with_micro - floor($timestamp_with_micro)) * 1000000);
			$d = new DateTime(date('Y-m-d H:i:s.' . $microtime, $timestamp_with_micro));
			$date = $d->format("Y-m-d H:i:s.u"); 
			//ip
			$ip = get_ip();
			//level name
			$levelName = static::getLevelName($level);
			//debug info
			$dtrace = debug_backtrace();
			array_shift($dtrace); //remove the first element
			$fileInfo = array_shift($dtrace);//use the second index that contains the caller info
			$str = $date . ' [' .str_pad($levelName, 7 /*warning len*/) . '] '. ' [' .str_pad($ip, 15) . '] '.$this->logger . ' : ' . $message . ' ' . '['.$fileInfo['file'] . '::' .$fileInfo['line']. ']'."\n";
			$fp = fopen($path, "a+");
			flock($fp, LOCK_EX); // exclusive lock, will get released when the file is closed
			fwrite($fp, $str);
			fclose($fp);
		}		
		/**
		 * Check if the given log level is valid
		 * @param  string  $level the log level
		 * @return boolean        true if the given log level is valid, false if not
		 */
		private static function isValidConfigLevel($level){
			$l = strtolower($level);
			return in_array($l, static::$validConfigLevel);
		}

		/**
		 * Get the log level number for the given level string
		 * @param  string $level the log level in string format
		 * @return int        the log level in integer format using the predefinied constants
		 */
		private static function getLevelValue($level){
			$l = strtolower($level);
			$value = self::NONE;
			//the default value is NONE, so means no need test for NONE
			if($l == 'fatal'){
				$value = self::FATAL;
			}
			else if($l == 'error'){
				$value = self::ERROR;
			}
			else if($l == 'warning' || $l == 'warn'){
				$value = self::WARNING;
			}
			else if($l == 'info'){
				$value = self::INFO;
			}
			else if($l == 'debug'){
				$value = self::DEBUG;
			}
			else if($l == 'all'){
				$value = self::ALL;
			}
			return $value;
		}

		/**
		 * Get the log level string for the given level integer
		 * @param  integer $level the log level in integer format
		 * @return int        the log level in string format
		 */
		private static function getLevelName($level){
			$l = strtolower($level);
			$value = '';
			//the default value is NONE, so means no need test for NONE
			if($l == self::FATAL){
				$value = 'FATAL';
			}
			else if($l == self::ERROR){
				$value = 'ERROR';
			}
			else if($l == self::WARNING){
				$value = 'WARNING';
			}
			else if($l == self::INFO){
				$value = 'INFO';
			}
			else if($l == self::DEBUG){
				$value = 'DEBUG';
			}
			//no need for ALL
			return $value;
		}

	}