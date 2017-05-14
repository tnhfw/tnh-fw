<?php
	class Log{
		const SUCCESS = 0;
		const INFO = 1;
		const WARNING = 2;
		const ERROR = 3;
		
		
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
		
		private static function writeLog($message, $level = self::INFO){
			$file = 'logs-'.date('d-m-Y').'.log';
			$path = LOGS_PATH.$file;
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
			}
			$str .= ' '.$date.' : '.$message."\n";
			$fp = fopen($path, "a+");
			fwrite($fp, $str);
			fclose($fp);
		}
		
	}