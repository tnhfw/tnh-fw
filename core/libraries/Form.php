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


	class Form{

		static function open($path = null, array $attributes = array(), $method = 'POST', $enctype = null){
			if($path){
				$path = Url::site_url($path);
			}
			$method = strtoupper($method);
			$str = null;
			$str .= '<form action = "'.$path.'" method = "'.$method.'"';
			if(!empty($enctype)){
				$str .= ' enctype = "'.$enctype.'" ';
			}
			$str .= attributes_to_string($attributes);
			$str .= '>';

			return $str;
		}

		static function open_multipart($path = null, array $attributes = array(), $method = 'POST'){
			return self::open($path, $attributes, $method, 'multipart/form-data');
		}

		static function close(){
			return '</form>';
		}

		static function fieldset($legend = ''){
			return '<fieldset>
						<legend>'.$legend.'</legend>';
		}

		static function fieldset_close(){
			return '</fieldset>';
		}

		static function error($name){
			$return = null;
			$validation = null;
			$obj = & get_instance();
			foreach($obj as $var => $value){
				if($obj->{$var} instanceof FormValidation){
					$validation = & $obj->{$var};
					break;
				}
			}
			if($validation){
				$errors = $validation->returnErrors();
				$error =  isset($errors[$name])?$errors[$name]:null;

				if($error){
					list($errorStart, $errorEnd) = $validation->getErrorDelimiter();
					$return = $errorStart.$error.$errorEnd;
				}
			}
			return $return;
		}

		static function value($name, $default = null){
			return isset($_POST[$name])?$_POST[$name]:$default;
		}

		static function label($label, $for = '',  array $attributes = array()){
			$str = null;
			$str .= '<label for = "'.$for.'" ';
			$str .= attributes_to_string($attributes);
			$str .= '>';
			$str .= $label.'</label>';
			return $str;
		}


		static function input($name, $value = null,  array $attributes = array(), $type = 'text'){
			$str = null;
			$str .= '<input name = "'.$name.'" value = "'.$value.'" type = "'.$type.'" ';
			$str .= attributes_to_string($attributes);
			$str .= '/>';
			return $str;
		}


		static function password($name, $value = null,  array $attributes = array()){
			return self::input($name, $value, $attributes, 'password');
		}

		static function text($name, $value = null,  array $attributes = array()){
			return self::input($name, $value, $attributes, 'text');
		}

		static function file($name, array $attributes = array()){
			return self::input($name, null, $attributes, 'file');
		}

		static function radio($name, $value = null,  $checked = false, array $attributes = array()){
			if($checked){
				$attributes['checked'] = true;
			}
			return self::input($name, $value, $attributes, 'radio');
		}

		static function checkbox($name, $value = null, $checked = false, array $attributes = array()){
			if($checked){
				$attributes['checked'] = true;
			}
			return self::input($name, $value, $attributes, 'checkbox');
		}

		static function number($name, $value = null,  array $attributes = array()){
			return self::input($name, $value, $attributes, 'number');
		}

		static function phone($name, $value = null,  array $attributes = array()){
			return self::input($name, $value, $attributes, 'phone');
		}

		static function email($name, $value = null,  array $attributes = array()){
			return self::input($name, $value, $attributes, 'email');
		}

		static function select($name, $value = null,  $selected = null, array $attributes = array()){
			if(!is_array($value)){
				$value = array('' => $value);
			}
			$str = null;
			$str .= '<select name = "'.$name.'" ';
			$str .= attributes_to_string($attributes);
			$str .= '>';
			foreach($value as $key => $val){
				$select = '';
				if($key == $selected){
					$select = 'selected';
				}
				$str .= '<option value = "'.$key.'" '.$select.'>'.$val.'</option>';
			}
			$str .= '</select>';
			return $str;
		}

		static function submit($name, $value = null,  array $attributes = array()){
			return self::input($name, $value, $attributes, 'submit');
		}


		static function button($name, $value = null,  array $attributes = array()){
			return self::input($name, $value, $attributes, 'button');
		}

		static function hidden($name, $value = null,  array $attributes = array()){
			return self::input($name, $value, $attributes, 'hidden');
		}

		static function reset($name, $value = null,  array $attributes = array()){
			return self::input($name, $value, $attributes, 'reset');
		}

		static function textarea($name, $value = '',  array $attributes = array()){
			$str = null;
			$str .= '<textarea name = "'.$name.'" ';
			$str .= attributes_to_string($attributes);
			$str .= '>';
			$str .= $value.'</textarea>';
			return $str;
		}
	}
