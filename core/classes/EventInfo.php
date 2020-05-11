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
        private $name;

        /**
         * The event data to send to the listeners
         * @var mixed
         */
        private $payload;

        /**
         * If the listeners need return the event after treatment or not, false means no need
         * return true need return the event. 
         * @var boolean
         */
        private $returnBack;

        /**
         * This variable indicates if need stop the event propagation
         * @var boolean
         */
        private $stop;
		
        public function __construct($name, $payload = array(), $returnBack = false, $stop = false) {
            $this->name = $name;
            $this->payload = $payload;
            $this->returnBack = $returnBack;
            $this->stop = $stop;
        }

        /**
         * Return the name of the event
         * 
         * @return string
         */
        public function getName() {
            return $this->name;
        }

       
        /**
         * Return the payload for this event
         * 
         * @return mixed
         */
        public function getPayload() {
            return $this->payload;
        }

        /**
         * Set the event payload
         * 
         * @param mixed $payload the data for this event
         *
         * @return object the current instance
         */
        public function setPayload($payload) {
            $this->payload = $payload;
            return $this;
        }

        /**
         * If we need return the event instance 
         * back after processing by each listener
         * 
         * @return boolean
         */
        public function isReturnBack() {
            return $this->returnBack;
        }

        /**
         * If we need stop the event propagation to listeners
         *  
         * @return boolean
         */
        public function isStop() {
            return $this->stop;
        }

        /**
         * Set the event stop status
         * 
         * @param boolean $stop the status value
         *
         * @return object the current instance
         */
        public function setStop($stop) {
            $this->stop = $stop;
            return $this;
        }
}
