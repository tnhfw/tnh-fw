<?php

	class Assets{

		static function css($path){
			$path = str_ireplace('.css', '', $path);
			$path = ASSETS_PATH.'css/'.$path.'.css';
			if(file_exists($path)){
				return Url::base_url($path);
			}
			return null;
		}

		static function js($path){
			$path = str_ireplace('.js', '', $path);
			$path = ASSETS_PATH.'js/'.$path.'.js';
			if(file_exists($path)){
				return Url::base_url($path);
			}
			return null;
		}

		static function img($path){
			$path = ASSETS_PATH.'images/'.$path;
			if(file_exists($path)){
				return Url::base_url($path);
			}
			return null;
		}

	}
