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

    class BaseStaticClass {
        /**
         * The logger instance
         * @var object
         */
        protected static $logger;

        /**
         * The signleton of the logger
         * @param boolean $setLoggerName whether to set logger name
         * 
         * @return Object the Log instance
         */
        public static function getLogger($setLoggerName = true) {
            if (self::$logger == null) {
                $logger = array();
                $logger[0] = & class_loader('Log', 'classes');
                self::$logger = $logger[0];
            }
            if ($setLoggerName) {
               self::$logger->setLogger('Class::' . get_called_class());
            }
            return self::$logger;			
        }

        /**
         * Set the log instance for future use
         * @param object $logger the log object
         * @return object the log instance
         */
        public static function setLogger($logger) {
            self::$logger = $logger;
            return self::$logger;
        }

    }
