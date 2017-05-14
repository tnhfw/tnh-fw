<?php
	if(!function_exists('get_ip')){
		/**
		* get the visitor ip
		*/
		function get_ip(){
			$ip = $_SERVER['REMOTE_ADDR'];
			
			if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
			else if(isset($_SERVER['HTTP_CLIENT_IP'])){
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			}
			
			return $ip;
		}
	}
	
	
	if(!function_exists('get_user_agent')){
		function get_user_agent(){
			$user_agent =  $_SERVER['HTTP_USER_AGENT'];
			return $user_agent;
		}
		
	}