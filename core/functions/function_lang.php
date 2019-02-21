<?php
	defined('ROOT_PATH') || exit('Access denied');
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

	if(!function_exists('__')){
		/**
		 * function for the shortcut to Lang::get
		 * @param  string $key the language key to retrieve
		 * @return string  the language value
		 */
		function __($key){
			$obj = & get_instance();
			return $obj->lang->get($key);
		}

	}


	if(!function_exists('get_languages')){
		/**
		 * function for the shortcut to Lang::getSupported
		 * 
		 * @return arry all the supported languages
		 */
		function get_languages(){
			$obj = & get_instance();
			return $obj->lang->getSupported();
		}

	}