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

    if (!function_exists('__')) {
        /**
         * function for the shortcut to Lang::get()
         * @param  string $key the language key to retrieve
         * @param mixed $default the default value to return if can not find the value
         * for the given key
         * @return string  the language value
         */
        function __($key, $default = 'LANGUAGE_ERROR') {
            return get_instance()->lang->get($key, $default);
        }

    }


    if (!function_exists('get_languages')) {
        /**
         * function for the shortcut to Lang::getSupported()
         * 
         * @return array all the supported languages
         */
        function get_languages() {
            return get_instance()->lang->getSupported();
        }

    }