<?php
	defined('ROOT_PATH') || exit('Access denied');
    /**
     * File: Browser.php
     * Author: Chris Schuld (http://chrisschuld.com/)
     * Last Modified: July 22nd, 2016
     * @version 2.0
     * @package PegasusPHP
     *
     * Copyright (C) 2008-2010 Chris Schuld  (chris@chrisschuld.com)
     *
     * This program is free software; you can redistribute it and/or
     * modify it under the terms of the GNU General Public License as
     * published by the Free Software Foundation; either version 2 of
     * the License, or (at your option) any later version.
     *
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU General Public License for more details at:
     * http://www.gnu.org/copyleft/gpl.html
     *
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
									'/macintosh|mac os x/i' =>  'Mac OS X',
									'/mac_powerpc/i'        =>  'Mac OS 9',
									'/iphone/i'             =>  'iPhone',
									'/ipod/i'               =>  'iPod',
									'/ipad/i'               =>  'iPad',
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
            $this->_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
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
            $this->_agent = $agent_string;
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
			if (preg_match('/mobile|phone|ipod/i', $this->_agent) ) {
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
				$this->setTablet(true);
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
