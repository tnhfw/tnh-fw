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

	class Request{
		public $get = null;
		public $post = null;
		public $server = null;
		public $cookie = null;
		public $file = null;
		public $session = null;
		public $query = null;
		public $method = null;
		public $requestUri = null;
		public $header = null;
		
		public function __construct(){
			$this->get = $_GET;
			$this->post = $_POST;
			$this->server = $_SERVER;
			$this->query = $_REQUEST;
			$this->cookie = $_COOKIE;
			$this->file = $_FILES;
			$this->session = new Session();
			$this->method = $this->server('REQUEST_METHOD');
			$this->requestUri = $this->server('REQUEST_URI');
			$this->header = array();
			if(function_exists('apache_request_headers')){
				$this->header = apache_request_headers();
			}
			else if(function_exists('getallheaders')){
				$this->header = getallheaders();
			}
		}
		
		public function get($key, $xss = true){
			if(empty($key)){
				//return all
				return $this->get;
			}
			$get = isset($this->get[$key])?$this->get[$key]:null;
			if($xss){
				if(is_array($get)){
					$get = array_map('htmlspecialchars', $get);
				}
				else{
					$get = htmlspecialchars($get);
				}
			}
			return $get;
		}
		
		
		public function query($key, $xss = true){
			if(empty($key)){
				//return all
				return $this->query;
			}
			$query = isset($this->query[$key])?$this->query[$key]:null;
			if($xss){
				if(is_array($query)){
					$query = array_map('htmlspecialchars', $query);
				}
				else{
					$query =  htmlspecialchars($query);
				}
			}
			return $query;
		}
		
		public function post($key, $xss = true){
			if(empty($key)){
				//return all
				return $this->post;
			}
			$post = isset($this->post[$key])?$this->post[$key]:null;
			if($xss){
				if(is_array($post)){
					$post = array_map('htmlspecialchars', $post);
				}
				else{
					$post =  htmlspecialchars($post);
				}
			}
			return $post;
		}
		
		public function server($key, $xss = true){
			if(empty($key)){
				//return all
				return $this->server;
			}
			$server = isset($this->server[$key])?$this->server[$key]:null;
			if($xss){
				if(is_array($server)){
					$server = array_map('htmlspecialchars', $server);
				}
				else{
					$server =  htmlspecialchars($server);
				}
			}
			return $server;
		}
		
		
		public function cookie($key, $xss = true){
			if(empty($key)){
				//return all
				return $this->cookie;
			}
			$cookie = isset($this->cookie[$key])?$this->cookie[$key]:null;
			if($xss){
				if(is_array($cookie)){
					$cookie = array_map('htmlspecialchars', $cookie);
				}
				else{
					$cookie =  htmlspecialchars($cookie);
				}
			}
			return $cookie;
		}
		
		public function file($key, $xss = true){
			$file = isset($this->file[$key])?$this->file[$key]:null;
			return $file;
		}
		
		
		public function session($key, $xss = true){
			$session = $this->session->get($key);
			if($xss){
				if(is_array($session)){
					$temp = array();
					foreach ($session as $key => $value){
						if(is_string($value)){
							$temp[$key] = htmlspecialchars($value);
						}
						else if(is_array($value)){
							$temp[$key] = array_map('htmlspecialchars', $value);
						}
						else{
							//TODO use best method to remove the dangerous chars
							 $temp[$key] = $value;
						}
					}
					$session = $temp;
					unset($temp);
				}
				else{
					$session =  htmlspecialchars($session);
				}
			}
			return $session;
		}
		
		public function method(){
			return $this->method;
		}
		
		public function requestUri(){
			return $this->requestUri;
		}
		
		public function header($key, $xss = true){
			$header = isset($this->header[$key])?$this->header[$key]:null;
			if($xss){
				if(is_array($header)){
					$header = array_map('htmlspecialchars', $header);
				}
				else{
					$header =  htmlspecialchars($header);
				}
			}
			return $header;
		}
	}