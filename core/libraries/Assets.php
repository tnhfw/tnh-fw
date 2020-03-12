<?php
    defined('ROOT_PATH') || exit('Access denied');
    /**
     * TNH Framework
     *
     * A simple PHP framework using HMVC architecture
     *
     * This content is released under the MIT License (MIT)
     *
     * Copyright (c) 2017 TNH Framework
     *
     * Permission is hereby granted, free of charge, to any person obtaining a copy
     * of this software and associated documentation files (the "Software"), to deal
     * in the Software without restriction, including without limitation the rights
     * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
     * copies of the Software, and to permit persons to whom the Software is
     * furnished to do so, subject to the following conditions:
     *
     * The above copyright notice and this permission notice shall be included in all
     * copies or substantial portions of the Software.
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
     * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
     * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
     * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
     * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
     * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
     * SOFTWARE.
     */

    /**
     *  @file Assets.php
     *    
     *  This class contains static methods for generating static content links (images, Javascript, CSS, etc.).
     *  
     *  @package	core	
     *  @author	TNH Framework team
     *  @copyright	Copyright (c) 2017
     *  @license	http://opensource.org/licenses/MIT	MIT License
     *  @link	http://www.iacademy.cf
     *  @version 1.0.0
     *  @since 1.0.0
     *  @filesource
     */
    class Assets extends BaseClass {

         /**
         * Construct new instance
         */
        public function __construct() {
            parent::__construct();
        }

        /**
         *  Generate the link of the assets file.
         *  
         *  Generates the absolute link of a file inside ASSETS_PATH folder.
         *  For example :
         *  	path('foo/bar/css/style.css'); => http://mysite.com/assets/foo/bar/css/style.css
         *  Note:
         *  The argument passed to this function must be the relative link to the 
         *  folder that contains the static contents defined by the constant ASSETS_PATH.
         *  
         *  @param string $asset the name of the assets file path with the extension.
         *  @return string|null the absolute path of the assets file, if it exists o
         *  therwise returns null if the file does not exist.
         */
        public function path($asset) {
            $path = ASSETS_PATH . $asset;
            $this->logger->debug('Including the Assets file [' . $path . ']');
            //Check if the file exists
            if (file_exists($path)) {
                $this->logger->info('Assets file [' . $path . '] included successfully');
                return get_instance()->url->mainUrl($path);
            }
            $this->logger->warning('Assets file [' . $path . '] does not exist');
            return null;
        }
		
        /**
         *  Generate the link of the css file.
         *  
         *  Generates the absolute link of a file containing the CSS style.
         *  For example :
         *  	css('mystyle'); => http://mysite.com/assets/css/mystyle.css
         *  Note:
         *  The argument passed to this function must be the relative link to the folder that contains the static contents defined by the constant ASSETS_PATH.
         *  
         *  @param string $path the name of the css file without the extension.
         *  @return string|null the absolute path of the css file, if it exists otherwise returns null if the file does not exist.
         */
        public function css($path) {
            /*
			* if the file name contains the ".css" extension, replace it with 
			* an empty string for better processing.
			*/
            $path = str_ireplace('.css', '', $path);
            $path = ASSETS_PATH . 'css/' . $path . '.css';
            $this->logger->debug('Including the Assets file [' . $path . '] for CSS');
            //Check if the file exists
            if (file_exists($path)) {
                $this->logger->info('Assets file [' . $path . '] for CSS included successfully');
                return get_instance()->url->mainUrl($path);
            }
            $this->logger->warning('Assets file [' . $path . '] for CSS does not exist');
            return null;
        }

        /**
         *  Generate the link of the javascript file.
         *  
         *  Generates the absolute link of a file containing the javascript.
         *  For example :
         *  	js('myscript'); => http://mysite.com/assets/js/myscript.js
         *  Note:
         *  The argument passed to this function must be the relative link to 
         *  the folder that contains the static contents defined by the constant ASSETS_PATH.
         *  
         *  @param string $path the name of the javascript file without the extension.
         *  @return string|null the absolute path of the javascript file, 
         *  if it exists otherwise returns null if the file does not exist.
         */
        public function js($path) {
            $path = str_ireplace('.js', '', $path);
            $path = ASSETS_PATH . 'js/' . $path . '.js';
            $this->logger->debug('Including the Assets file [' . $path . '] for javascript');
            if (file_exists($path)) {
                $this->logger->info('Assets file [' . $path . '] for Javascript included successfully');
                return get_instance()->url->mainUrl($path);
            }
            $this->logger->warning('Assets file [' . $path . '] for Javascript does not exist');
            return null;
        }

        /**
         *  Generate the link of the image file.
         *  
         *  Generates the absolute link of a file containing the image.
         *  For example :
         *  	img('myimage.png'); => http://mysite.com/assets/images/myimage.png
         *  Note:
         *  The argument passed to this function must be the relative link to 
         *  the folder that contains the static contents defined by the constant ASSETS_PATH.
         *  
         *  @param string $path the name of the image file with the extension.
         *  @return string|null the absolute path of the image file, if it exists 
         *  otherwise returns null if the file does not exist.
         */
        public function img($path) {
            $path = ASSETS_PATH . 'images/' . $path;
            $this->logger->debug('Including the Assets file [' . $path . '] for image');
            if (file_exists($path)) {
                $this->logger->info('Assets file [' . $path . '] for image included successfully');
                return get_instance()->url->mainUrl($path);
            }
            $this->logger->warning('Assets file [' . $path . '] for image does not exist');
            return null;
        }
    }
