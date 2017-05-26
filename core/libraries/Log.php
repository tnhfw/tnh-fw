<?php
	class Log{
		const NONE = -1;
		const ALL = 0;
		const SUCCESS = 1;
		const INFO = 2;
		const WARNING = 3;
		const ERROR = 4;
		const DEBUG = 5;
		
		
		public static function success($message){
			static::writeLog($message, self::SUCCESS);
		} 
		
		public static function info($message){
			static::writeLog($message, self::INFO);
		} 
		
		public static function warning($message){
			static::writeLog($message, self::WARNING);
		} 
		
		public static function error($message){
			static::writeLog($message, self::ERROR);
		} 
		
		public static function debug($message){
			static::writeLog($message, self::DEBUG);
		} 
		
		private static function writeLog($message, $level = self::INFO){
			$log_level = Config::get('log_level', -1);
			
			if($log_level == self::NONE || ($log_level != self::ALL && $log_level != $level)){
				return;
			}
			
			$log_save_path = Config::get('log_save_path');
			if(!$log_save_path){
				$log_save_path = LOGS_PATH;
			}
			
			if(!is_dir($log_save_path) || !is_writable($log_save_path)){
				show_error('Error : the log dir does not exists or is not writable');
			}
		
			
			$file = 'logs-'.date('d-m-Y').'.log';
			$path = $log_save_path.$file;
			if(!file_exists($path)){
				@touch($path);
			}
			$date = date('D d M Y H:i:s');
			$str = null;
			switch($level){
				case self::SUCCESS:
					$str .= '[SUCCESS]';
				break;
				case self::INFO:
					$str .= '[INFO]';
				break;
				case self::WARNING:
					$str .= '[WARNING]';
				break;
				case self::ERROR:
					$str .= '[ERROR]';
				break;
				case self::DEBUG:
					$str .= '[DEBUG]';
				break;
			}
			$str .= ' '.$date.' : '.$message."\n";
			$fp = fopen($path, "a+");
			fwrite($fp, $str);
			fclose($fp);
		}
		
	}