<?php
	if(!function_exists('is_https')){
		function is_https(){
			if(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off'){
				return true;
			}
			else if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'){
				return true;
			}
			else if(isset($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off'){
				return true;
			}

			return false;
		}
	}
	
	if(!function_exists('is_url')){
		function is_url($url){
			return preg_match('/^(http|https|ftp|ftps):\/\/(.*)/', $url);
		}
	}