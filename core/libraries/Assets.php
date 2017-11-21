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

	/**
	 *  @file Assets.php
	 *    
	 *  This class contains static methods for generating static content links (images, Javascript, CSS, etc.).
	 *  
	 *  @package	core	
	 *  @author	Tony NGUEREZA
	 *  @copyright	Copyright (c) 2017
	 *  @license	https://opensource.org/licenses/gpl-3.0.html GNU GPL License (GPL)
	 *  @link	http://www.iacademy.cf
	 *  @version 1.0.0
	 *  @since 1.0.0
	 *  @filesource
	 */
	class Assets{

		/**
		 *  Generate the link of the css file.
		 *  
		 *  Generates the absolute link of a file containing the CSS style.
		 *  For example :
		 *  	echo Assets::css('mystyle'); => http://mysite.com/assets/css/mystyle.css
		 *  Note:
		 *  The argument passed to this function must be the relative link to the folder that contains the static contents defined by the constant ASSETS_PATH.
		 *  
		 *  @param $path the name of the css file without the extension.
		 *  @return string|null the absolute path of the css file, if it exists otherwise returns null if the file does not exist.
		 */
		static function css($path){
			/*
			* if the file name contains the ".css" extension, replace it with 
			* an empty string for better processing.
			*/
			$path = str_ireplace('.css', '', $path);
			$path = ASSETS_PATH.'css/'.$path.'.css';
			
			//Check if the file exists
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
