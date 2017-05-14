<?php

	class Html{

		public static function a($link = '', $anchor = null, array $attributes = array()){
			if(!is_url($link)){
				$link = Url::site_url($link);
			}
			if(!$anchor){
				$anchor = $link;
			}
			$str = null;
			$str .= '<a href = "'.$link.'" ';
			$str .= attributes_to_string($attributes);
			$str .= '>';
			$str .= $anchor;
			$str .= '</a>';

			return $str;
		}
		
		
		public static function mailto($link = '', $anchor = null, array $attributes = array()){
			if(!$anchor){
				$anchor = $link;
			}
			$str = null;
			$str .= '<a href = "mailto:'.$link.'" ';
			$str .= attributes_to_string($attributes);
			$str .= '>';
			$str .= $anchor;
			$str .= '</a>';

			return $str;
		}

	}
