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