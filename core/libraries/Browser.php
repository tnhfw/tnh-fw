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
    
	class Browser {

		/**
		 * List of know platforms
		 * @var array
		 */
		private $_platforms = array(
									'/windows nt 10/i'      =>  'Windows 10',
									'/windows phone 10/i'   =>  'Windows Phone 10',
									'/windows phone 8.1/i'  =>  'Windows Phone 8.1',
									'/windows phone 8/i'    =>  'Windows Phone 8',
									'/windows nt 6.3/i'     =>  'Windows 8.1',
									'/windows nt 6.2/i'     =>  'Windows 8',
									'/windows nt 6.1/i'     =>  'Windows 7',
									'/windows nt 6.0/i'     =>  'Windows Vista',
									'/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
									'/windows nt 5.1/i'     =>  'Windows XP',
									'/windows xp/i'         =>  'Windows XP',
									'/windows nt 5.0/i'     =>  'Windows 2000',
									'/windows me/i'         =>  'Windows ME',
									'/win98/i'              =>  'Windows 98',
									'/win95/i'              =>  'Windows 95',
									'/win16/i'              =>  'Windows 3.11',
									'/ipad/i'               =>  'iPad',
                                    '/ipod/i'               =>  'iPod',
                                    '/iphone/i'             =>  'iPhone',
                                    '/macintosh|mac os x/i' =>  'Mac OS X',
									'/mac_powerpc/i'        =>  'Mac OS 9',
									'/android/i'            =>  'Android',
									'/ubuntu/i'             =>  'Ubuntu',
									'/linux/i'              =>  'Linux',
									'/blackberry/i'         =>  'BlackBerry',
									'/webos/i'              =>  'Mobile'
								);

		/**
		 * List of know browsers
		 * @var array
		 */
	 	private $_browsers = array(
									'/mobile/i'     =>  'Handheld Browser',
									'/msie/i'       =>  'Internet Explorer',
									'/firefox/i'    =>  'Firefox',
									'/chrome/i'     =>  'Chrome',
									'/safari/i'     =>  'Safari',
									'/edge/i'       =>  'Edge',
									'/opera/i'      =>  'Opera',
									'/netscape/i'   =>  'Netscape',
									'/maxthon/i'    =>  'Maxthon',
									'/konqueror/i'  =>  'Konqueror'
								);

	 	/**
	 	 * Agent string
	 	 * @var string
	 	 */
		private $_agent = '';

		/**
		 * Browser name
		 * @var string
		 */
        private $_browser_name = '';

        /**
         * Browser version
         * @var string
         */
        private $_version = '';

        /**
         * Platform or OS
         * @var string
         */
        private $_platform = '';

        /**
         * Whether is mobile
         * @var boolean
         */
        private $_is_mobile = false;

        /**
         * Whether is table
         * @var boolean
         */
        private $_is_tablet = false;

        /**
         * Whether is robot
         * @var boolean
         */
        private $_is_robot = false;

        /**
         * Whether is facebook external hit
         * @var boolean
         */
        private $_is_facebook = false;

		/**
         * Class constructor
         */
        public function __construct($userAgent = '') {
            $this->reset();
            if ($userAgent != '') {
                $this->setUserAgent($userAgent);
            } else {
                $this->determine();
            }
        }

        /**
         * Reset all properties
         */
        public function reset() {
            $this->_agent =  get_instance()->globalvar->server('HTTP_USER_AGENT');
            $this->_browser_name = 'unknown';
            $this->_version = 'unknown';
            $this->_platform = 'unknown';
            $this->_is_mobile = false;
            $this->_is_tablet = false;
            $this->_is_robot = false;
            $this->_is_facebook = false;
        }

        /**
         * Get the user agent value in use to determine the browser
         * @return string the user agent
         */
        public function getUserAgent() {
            return $this->_agent;
        }

        /**
         * Set the user agent value
         * @param string $agentString the value for the User Agent to set
         */
        public function setUserAgent($agentString)
        {
            $this->reset();
            $this->_agent = $agentString;
            $this->determine();
        }

        /**
         * The name of the browser.
         * @return string name of the browser
         */
        public function getBrowser() {
            return $this->_browser_name;
        }

        /**
         * Set the name of the browser
         * @param $browser string The name of the browser
         */
        public function setBrowser($browser) {
            $this->_browser_name = $browser;
        }

        /**
         * The name of the platform. 
         * @return string name of the platform
         */
        public function getPlatform() {
            return $this->_platform;
        }

        /**
         * Set the name of the platform
         * @param string $platform the name of the platform
         */
        public function setPlatform($platform) {
            $this->_platform = $platform;
        }

        /**
         * The version of the browser.
         * @return string Version of the browser (will only contain 
         * alpha-numeric characters and a period)
         */
        public function getVersion() {
            return $this->_version;
        }

        /**
         * Set the version of the browser
         * @param string $version the version of the browser
         */
        public function setVersion($version) {
            $this->_version = $version;
        }


        /**
         * Is the browser from a mobile device?
         * @return boolean true if the browser is from a mobile device otherwise false
         */
        public function isMobile() {
            return $this->_is_mobile;
        }

        /**
         * Is the browser from a tablet device?
         * @return boolean true if the browser is from a tablet device otherwise false
         */
        public function isTablet() {
            return $this->_is_tablet;
        }

        /**
         * Is the browser from a robot (ex Slurp,GoogleBot)?
         * @return boolean true if the browser is from a robot otherwise false
         */
        public function isRobot() {
            return $this->_is_robot;
        }

        /**
         * Is the browser from facebook?
         * @return boolean true if the browser is from facebook otherwise false
         */
        public function isFacebook() {
            return $this->_is_facebook;
        }

         /**
         * Returns a formatted string with a summary of the details of the browser.
         * @codeCoverageIgnore
         * 
         * @return string formatted string with a summary of the browser
         */
        public function __toString() {
            return "<strong>Browser Name:</strong> {$this->getBrowser()}<br/>\n" .
                "<strong>Browser Version:</strong> {$this->getVersion()}<br/>\n" .
                "<strong>Browser User Agent String:</strong> {$this->getUserAgent()}<br/>\n" .
                "<strong>Platform:</strong> {$this->getPlatform()}<br/>";
        }


        /**
         * Set the browser to be mobile
         * @param boolean $value is the browser a mobile browser or not
         */
        protected function setMobile($value = true) {
            $this->_is_mobile = $value;
        }

        /**
         * Set the browser to be tablet
         * @param boolean $value is the browser a tablet browser or not
         */
        protected function setTablet($value = true) {
            $this->_is_tablet = $value;
        }

        /**
         * Set the browser to be a robot
         * @param boolean $value is the browser a robot or not
         */
        protected function setRobot($value = true) {
            $this->_is_robot = $value;
        }

        /**
         * Set the browser to be a facebook request
         * @param boolean $value is the browser a robot or not
         */
        protected function setFacebook($value = true) {
            $this->_is_facebook = $value;
        }

        /**
         * Determine the user's platform
         */
		protected function checkPlatform() { 
			foreach ($this->_platforms as $regex => $value) { 
				if (preg_match($regex, $this->_agent) ) {
					$this->setPlatform($value);
					break;
				}
			}   
		}

		/**
         * Routine to determine the browser type
         */
		protected function checkBrowser() {
			foreach ($this->_browsers as $regex => $value) { 
				if (preg_match($regex, $this->_agent ) ) {
					$this->setBrowser($value);
					break;
				}
			}
		}

		/**
         * Routine to determine the browser version
         */
		protected function checkBrowserVersion(){
			$detected = $this->getBrowser();
			$d = array_search($detected, $this->_browsers);
			$browser = str_replace(array("/i","/"), "", $d);
			$regex = "/(?<browser>version|{$browser})[\/]+(?<version>[0-9.|a-zA-Z.]*)/i";
			if (preg_match_all($regex, $this->_agent, $matches)) {
				$found = array_search($browser, $matches["browser"]);
				$this->setVersion($matches["version"][$found]);
			}
		}

		/**
         * Determine if the browser is Mobile or not
         */
		protected function checkMobile() {
			if (preg_match('/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i', $this->_agent) ) {
				$this->setMobile(true);
			}
		}

		/**
         * Determine if the browser is Tablet or not
         */
		protected function checkTablet() {
			if (preg_match('/tablet|ipad/i', $this->_agent) ) {
				$this->setTablet(true);
			}
		}

		/**
         * Determine if the browser is Robot or not
         */
		protected function checkBot() {
			if (preg_match('/bot/i', $this->_agent) ) {
				$this->setRobot(true);
			}
		}

		/**
         * Detect if URL is loaded from FacebookExternalHit
         */
        protected function checkFacebook() {
            if (stristr($this->_agent, 'FacebookExternalHit')) {
                $this->setRobot(true);
                $this->setFacebook(true);
            }  else if (stristr($this->_agent, 'FBIOS')) {
                $this->setFacebook(true);
            }
        }


		 /**
         * Protected routine to calculate and determine what
         *  the browser is in use (including platform)
         */
        protected function determine() {
            $this->checkPlatform();
            $this->checkBrowser();
            $this->checkBrowserVersion();
            $this->checkMobile();
            $this->checkTablet();
            $this->checkBot();
            $this->checkFacebook();
        }
		
	}
