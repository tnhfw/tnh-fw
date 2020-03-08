<?php
    defined('ROOT_PATH') or exit('Access denied');
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
     * This class represent the event detail to dispatch to correspond listener
     */
    class EventInfo {
		
        /**
         * The event name
         * @var string
         */
        public $name;

        /**
         * The event data to send to the listeners
         * @var mixed
         */
        public $payload;

        /**
         * If the listeners need return the event after treatment or not, false means no need
         * return true need return the event. 
         * @var boolean
         */
        public $returnBack;

        /**
         * This variable indicates if need stop the event propagation
         * @var boolean
         */
        public $stop;
		
        public function __construct($name, $payload = array(), $returnBack = false, $stop = false) {
            $this->name = $name;
            $this->payload = $payload;
            $this->returnBack = $returnBack;
            $this->stop = $stop;
        }
    }
