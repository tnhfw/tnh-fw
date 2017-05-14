<?php
	if(!function_exists('attributes_to_string')){
		function attributes_to_string(array $attributes){
			$str = ' ';
			foreach($attributes as $key => $value){
				$key = trim(htmlspecialchars($key));
				$value = trim(htmlspecialchars($value));
				$str .= $key.' = "'.$value.'" ';
			}
			return $str;
		}
	}
	
if(!function_exists('get_random_string')){	
	function get_random_string($type = 'alnum',$length = 10, $lower = false){
		//$type must be alpha, alnum, num.
		$str = '';
		switch($type){
			case 'alpha':
				$str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
			case 'alnum':
				$str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
			break;
			case 'num':
				$str = '1234567890';
			break;
			default:
				$str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		}
		$random = null;
		for($i = 0 ; $i < $length ; $i++){
			$random .= $str[mt_rand()%strlen($str)];
		}
		return $random;
	}
}