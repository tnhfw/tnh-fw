<?php
/**
 * TNH Framework
 *
 * A simple PHP framework created using the concept of codeigniter with bootstrap twitter
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
		const NONE = 99999999;
		const FATAL = 500;
		const ERROR = 400;
		const WARNING = 300;
		const INFO = 200;
		const DEBUG = 100;
		const ALL = -9999999999;

		private $logger = 'ROOT';
		
		private static $validConfigLevel = array('off', 'none', 'fatal', 'error', 'warning', 'warn', 'info', 'debug', 'all');

		public  function setLogger($newlogger){
			$this->logger = $newlogger;
		}

		public function fatal($message){
			$this->writeLog($message, self::FATAL);
		} 
		
		public function error($message){
			$this->writeLog($message, self::ERROR);
		} 

		public function warning($message){
			$this->writeLog($message, self::WARNING);
		} 
		

		public function info($message){
			$this->writeLog($message, self::INFO);
		} 
		
		public function debug($message){
			$this->writeLog($message, self::DEBUG);
		} 
		
		private static function isValidConfigLevel($level){
			$l = strtolower($level);
			return in_array($l, static::$validConfigLevel);
		}

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

		private function writeLog($message, $level = self::INFO){
			$log_level = Config::get('log_level');
			if(! $log_level){
				//so means no need log just stop here
				return;
			}
			//check config log level
			if(!static::isValidConfigLevel($log_level)){
				show_error('Invalid config log level, the value must be one of the following: ' . implode(', ', array_map('strtoupper', static::$validConfigLevel)), $title = 'Log Config Error', $logging = false);	
			}

			//check if can logging regarding the log level config
			$configLevel = static::getLevelValue($log_level);
			if($configLevel > $level){
				//can't log
				return;
			}
			
			$log_save_path = Config::get('log_save_path');
			if(!$log_save_path){
				$log_save_path = LOGS_PATH;
			}
			
			if(!is_dir($log_save_path) || !is_writable($log_save_path)){
				show_error('Error : the log dir does not exists or is not writable', $title = 'Log directory error', $logging = false);
			}
			$file = 'logs-'.date('d-m-Y').'.log';
			$path = $log_save_path.$file;
			if(!file_exists($path)){
				@touch($path);
			}
			//may be at this time helper user_agent not yet included
			require_once CORE_FUNCTIONS_PATH . 'function_user_agent.php';
			//date
			$date = date('Y-m-d H:i:s');
			//ip
			$ip = get_ip();
			//level name
			$levelName = static::getLevelName($level);
			//debug info
			$dtrace = debug_backtrace();
			array_shift($dtrace); //remove the first element
			$fileInfo = array_shift($dtrace);//use the second index that contains the caller info
			$l = $this->logger;
			$str = $date . ' [' .str_pad($levelName, 7 /*warning len*/) . '] '. ' [' .str_pad($ip, 15) . '] '.$l . ' : ' . $message . ' ' . '['.$fileInfo['file'] . '::' .$fileInfo['line']. ']'."\n";
			$fp = fopen($path, "a+");
			fwrite($fp, $str);
			fclose($fp);
		}		
	}