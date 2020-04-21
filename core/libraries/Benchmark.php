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
     * Class for Benchmark
     */
    class Benchmark {
        /**
         * The markers for excution time
         * @var array
         */
        private $markersTime = array();
		
        /**
         * The markers for memory usage
         * @var array
         */
        private $markersMemory = array();
		
        /**
         * This method is used to mark one point for benchmark (execution time and memory usage)
         * @param  string $name the marker name
         */
        public function mark($name) {
            //Marker for execution time
            $this->markersTime[$name] = microtime(true);
            //Marker for memory usage
            $this->markersMemory[$name] = memory_get_usage(true);
        }
		
        /**
         * This method is used to get the total excution time in second between two markers
         * @param  string  $startMarkerName the marker for start point
         * @param  string  $endMarkerName   the marker for end point
         * @param  integer $precision   the number of decimal
         * @return string         the total execution time
         */
        public function elapsedTime($startMarkerName = null, $endMarkerName = null, $precision = 6) {
            if (!$startMarkerName || !isset($this->markersTime[$startMarkerName])) {
                return 0;
            }
			
            if (!isset($this->markersTime[$endMarkerName])) {
                $this->markersTime[$endMarkerName] = microtime(true);
            }
            return number_format(
                                    $this->markersTime[$endMarkerName] - $this->markersTime[$startMarkerName], 
                                    $precision, 
                                    '.',
                                    ''
                                );
        }
		
        /**
         * This method is used to get the total memory usage in byte between two markers
         * @param  string  $startMarkerName the marker for start point
         * @param  string  $endMarkerName   the marker for end point
         * @param  integer $precision   the number of decimal
         * @return string         the total memory usage
         */
        public function memoryUsage($startMarkerName = null, $endMarkerName = null, $precision = 6) {
            if (!$startMarkerName || !isset($this->markersMemory[$startMarkerName])) {
                return 0;
            }
			
            if (!isset($this->markersMemory[$endMarkerName])) {
                $this->markersMemory[$endMarkerName] = microtime(true);
            }
            return number_format(
                                    $this->markersMemory[$endMarkerName] - $this->markersMemory[$startMarkerName], 
                                    $precision,
                                    '.',
                                    ''
                                );
        }
    }
