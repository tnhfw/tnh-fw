<?php
	defined('ROOT_PATH') || exit('Access denied');
	/**
	 * TNH Framework
	 *
	 * A simple PHP framework using HMVC architecture
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
		 * The logger instance
		 * @var Log
		 */
		private static $logger;

		/**
		 * The signleton of the logger
		 * @return Object the Log instance
		 */
		private static function getLogger(){
			if(self::$logger == null){
				//can't assign reference to static variable
				self::$logger[0] =& class_loader('Log', 'classes');
				self::$logger[0]->setLogger('Library::Assets');
			}
			return self::$logger[0];
		}


		/**
		 *  Generate the link of the assets file.
		 *  
		 *  Generates the absolute link of a file inside ASSETS_PATH folder.
		 *  For example :
		 *  	echo Assets::path('foo/bar/css/style.css'); => http://mysite.com/assets/foo/bar/css/style.css
		 *  Note:
		 *  The argument passed to this function must be the relative link to the folder that contains the static contents defined by the constant ASSETS_PATH.
		 *  
		 *  @param string $asset the name of the assets file path with the extension.
		 *  @return string|null the absolute path of the assets file, if it exists otherwise returns null if the file does not exist.
		 */
		public static function path($asset){
			$logger = self::getLogger();	
			$path = ASSETS_PATH . $asset;
			
			$logger->debug('Including the Assets file [' . $path . ']');
			//Check if the file exists
			if(file_exists($path)){
				$logger->info('Assets file [' . $path . '] included successfully');
				return Url::base_url($path);
			}
			$logger->warning('Assets file [' . $path . '] does not exist');
			return null;
		}
		
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
		public static function css($path){
			$logger = self::getLogger();
			/*
			* if the file name contains the ".css" extension, replace it with 
			* an empty string for better processing.
			*/
			$path = str_ireplace('.css', '', $path);
			$path = ASSETS_PATH . 'css/' . $path . '.css';
			
			$logger->debug('Including the Assets file [' . $path . '] for CSS');
			//Check if the file exists
			if(file_exists($path)){
				$logger->info('Assets file [' . $path . '] for CSS included successfully');
				return Url::base_url($path);
			}
			$logger->warning('Assets file [' . $path . '] for CSS does not exist');
			return null;
		}

		/**
		 *  Generate the link of the javascript file.
		 *  
		 *  Generates the absolute link of a file containing the javascript.
		 *  For example :
		 *  	echo Assets::js('myscript'); => http://mysite.com/assets/js/myscript.js
		 *  Note:
		 *  The argument passed to this function must be the relative link to the folder that contains the static contents defined by the constant ASSETS_PATH.
		 *  
		 *  @param $path the name of the javascript file without the extension.
		 *  @return string|null the absolute path of the javascript file, if it exists otherwise returns null if the file does not exist.
		 */
		public static function js($path){
			$logger = self::getLogger();
			$path = str_ireplace('.js', '', $path);
			$path = ASSETS_PATH . 'js/' . $path . '.js';
			$logger->debug('Including the Assets file [' . $path . '] for javascript');
			if(file_exists($path)){
				$logger->info('Assets file [' . $path . '] for Javascript included successfully');
				return Url::base_url($path);
			}
			$logger->warning('Assets file [' . $path . '] for Javascript does not exist');
			return null;
		}

		/**
		 *  Generate the link of the image file.
		 *  
		 *  Generates the absolute link of a file containing the image.
		 *  For example :
		 *  	echo Assets::img('myimage.png'); => http://mysite.com/assets/images/myimage.png
		 *  Note:
		 *  The argument passed to this function must be the relative link to the folder that contains the static contents defined by the constant ASSETS_PATH.
		 *  
		 *  @param $path the name of the image file with the extension.
		 *  @return string|null the absolute path of the image file, if it exists otherwise returns null if the file does not exist.
		 */
		public static function img($path){
			$logger = self::getLogger();
			$path = ASSETS_PATH . 'images/' . $path;
			$logger->debug('Including the Assets file [' . $path . '] for image');
			if(file_exists($path)){
				$logger->info('Assets file [' . $path . '] for image included successfully');
				return Url::base_url($path);
			}
			$logger->warning('Assets file [' . $path . '] for image does not exist');
			return null;
		}
	}
