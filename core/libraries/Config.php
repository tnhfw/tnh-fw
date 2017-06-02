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


	class Config{
		private static $config = array();

		static function init(){
			if(file_exists(CONFIG_PATH.'config.php')){
				require_once CONFIG_PATH.'config.php';
				if(!empty($config) && is_array($config)){
					static::$config = $config;
				}
				else{
					show_error('No configuration found in config.php');
				}
			}
			else{
				show_error('Unable to find the configuration file');
			}

			if(!static::$config['base_url'] || !is_url(static::$config['base_url'])){
				$base_url = null;
				if (isset($_SERVER['SERVER_ADDR'])){
					if (strpos($_SERVER['SERVER_ADDR'], ':') !== FALSE){
						$base_url = '['.$_SERVER['SERVER_ADDR'].']';
					}
					else{
						$base_url = $_SERVER['SERVER_ADDR'];
					}

					$base_url = (is_https() ? 'https' : 'http').'://'.$base_url
						.substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], basename($_SERVER['SCRIPT_FILENAME'])));
				}
				else{
					$base_url = 'http://localhost/';
				}
				self::set('base_url', $base_url);
			}
			else{
				static::$config['base_url'] = rtrim(static::$config['base_url'], '/').'/';
			}

		}

		static function get($item, $default = null){
			return isset(static::$config[$item])?(static::$config[$item]):$default;
		}

		static function set($item, $value){
			static::$config[$item] = $value;
		}

		static function getAll(){
			return static::$config;
		}

		static function setAll(array $config = array()){
			static::$config = array_merge(static::$config, $config);
		}
		
		static function delete($item){
			unset(static::$config[$item]);
		}
		
		static function load($config){
			Loader::config($config);
		}
	}
