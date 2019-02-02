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


	class Response{
			private static $headers = array();
			private static $logger;
			public static $httpStatutCode = 200;
			protected static $http_code = array(
											100 => 'Continue',
											101 => 'Switching Protocols',
											200 => 'OK',
											201 => 'Created',
											202 => 'Accepted',
											203 => 'Non-Authoritative Information',
											204 => 'No Content',
											205 => 'Reset Content',
											206 => 'Partial Content',
											300 => 'Multiple Choices',
											301 => 'Moved Permanently',
											302 => 'Found',
											303 => 'See Other',
											304 => 'Not Modified',
											305 => 'Use Proxy',
											307 => 'Temporary Redirect',
											400 => 'Bad Request',
											401 => 'Unauthorized',
											402 => 'Payment Required',
											403 => 'Forbidden',
											404 => 'Not Found',
											405 => 'Method Not Allowed',
											406 => 'Not Acceptable',
											407 => 'Proxy Authentication Required',
											408 => 'Request Timeout',
											409 => 'Conflict',
											410 => 'Gone',
											411 => 'Length Required',
											412 => 'Precondition Failed',
											413 => 'Request Entity Too Large',
											414 => 'Request-URI Too Long',
											415 => 'Unsupported Media Type',
											416 => 'Requested Range Not Satisfiable',
											417 => 'Expectation Failed',
											418 => 'I\'m a teapot',
											500 => 'Internal Server Error',
											501 => 'Not Implemented',
											502 => 'Bad Gateway',
											503 => 'Service Unavailable',
											504 => 'Gateway Timeout',
											505 => 'HTTP Version Not Supported',
										);

			public function __construct(){
				if(!class_exists('Log')){
					//here the Log class is not yet loaded
					//load it manually, normally the class Config is loaded before
					require_once CORE_LIBRARY_PATH . 'Log.php';
				}
				static::$logger = new Log();
				static::$logger->setLogger('Library::Response');
			}

			public static function sendHeaders($http_code = 200, array $headers = array()){
				static::setStatutCode($http_code);
				static::setHeaders($headers);
				if(!headers_sent()){
					foreach(static::getHeaders() as $key => $value){
						header($key .':'.$value);
					}
					header('HTTP/1.1 '.static::$httpStatutCode.' '.static::$http_code[static::$httpStatutCode]);
				}
			}

			public static function getHeaders(){
				return static::$headers;
			}

			public static function redirect($path = ''){
				$url = Url::site_url($path);
				if(!headers_sent()){
					header('Location:'.$url);
					exit;
				}
				else{
					echo '<script>
							location.href = "'.$url.'";
						</script>';
				}
			}

			public static function getHeader($name){
				return isset(static::$headers[$name])?static::$headers[$name] : null;
			}


			public static function setHeader($name,$value){
				static::$headers[$name] = $value;
			}

			public static function setHeaders(array $headers){
				static::$headers = array_merge(static::getHeaders(), $headers);
			}

			public static function setStatutCode($code){
				static::$httpStatutCode = $code;
			}

			public function render($view, array $data = array(), $return = false){
				$view = str_ireplace('.php', '', $view);
				$path = APPS_VIEWS_PATH.$view.'.php';
				$found = false;
				if(file_exists($path)){
					$obj = & get_instance();
					foreach($obj as $key => $value){
						if(!isset($this->{$key})){
							$this->{$key} = & $obj->{$key};
						}
					}
					ob_start();
					extract($data);
					require_once $path;
					$output = ob_get_clean();
					if($return){
						return $output;
					}
					echo $output;
					$found = true;
				}
				if(!$found){
					show_error('Unable to find view '.$view);
				}

			}


			public static function send404(){
				/********* for logs **************/
				//can't use $obj = & get_instance()  here because the global super object will be available until
				//the main controller is loaded even for Loader::library('xxxx');
				$r = new Request();
				$b = new Browser();
				$browser = $b->getPlatform().', '.$b->getBrowser().' '.$b->getVersion();
				//here the helper not yet included
				Loader::functions('user_agent');

				$str = '[404 page not found] : ';
				$str .= ' Unable to find the page ['.$r->requestUri().'] the visitor IP address is : '.get_ip(). ', browser : '.$browser;
				if(static::$logger == null){
					static::$logger = new Log();
					static::$logger->setLogger('Library::Response');
				}
				static::$logger->error($str);
				/***********************************/
				$path = CORE_VIEWS_PATH.'404.php';
				if(file_exists($path)){
					static::sendHeaders(404);
					ob_start();
					require_once $path;
					$output = ob_get_clean();
					//template here
					echo $output;
				}
			}

			public static function sendError(array $data = array()){
				$path = CORE_VIEWS_PATH.'errors.php';
				if(file_exists($path)){
					static::sendHeaders(503);
					ob_start();
					extract($data);
					require_once $path;
					$output = ob_get_clean();
					//template here
					echo $output;
				}
				else{
					show_error('error view ' .$path. ' does not exist');
				}
			}
		}
