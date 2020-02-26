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

    class BaseStaticClass {
        /**
         * The logger instance
         * @var object
         */
        protected static $logger;

        /**
         * The signleton of the logger
         * @return Object the Log instance
         */
        public static function getLogger() {
            if (self::$logger == null) {
                $logger = array();
                $logger[0] = & class_loader('Log', 'classes');
                $logger[0]->setLogger('Class::' . get_called_class());
                self::$logger = $logger[0];
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
