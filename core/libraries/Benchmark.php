<?php
	defined('ROOT_PATH') or exit('Access denied');
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
	 * Class for Benchmark
	 */
	class Benchmark{
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
		public function mark($name){
			//Marker for execution time
			$this->markersTime[$name] = microtime(true);
			//Marker for memory usage
			$this->markersMemory[$name] = memory_get_usage(true);
		}
		
		/**
		 * This method is used to get the total excution time in second between two markers
		 * @param  string  $startMarkerName the marker for start point
		 * @param  string  $endMarkerName   the marker for end point
		 * @param  integer $decimalCount   the number of decimal
		 * @return double         the total execution time
		 */
		public function elapsedTime($startMarkerName = null, $endMarkerName = null, $decimalCount = 6){
			if(! $startMarkerName || !isset($this->markersTime[$startMarkerName])){
				return 0;
			}
			
			if(! isset($this->markersTime[$endMarkerName])){
				$this->markersTime[$endMarkerName] = microtime(true);
			}
			return number_format($this->markersTime[$endMarkerName] - $this->markersTime[$startMarkerName], $decimalCount);
		}
		
		/**
		 * This method is used to get the total memory usage in byte between two markers
		 * @param  string  $startMarkerName the marker for start point
		 * @param  string  $endMarkerName   the marker for end point
		 * @param  integer $decimalCount   the number of decimal
		 * @return double         the total memory usage
		 */
		public function memoryUsage($startMarkerName = null, $endMarkerName = null, $decimalCount = 6){
			if(! $startMarkerName || !isset($this->markersMemory[$startMarkerName])){
				return 0;
			}
			
			if(! isset($this->markersMemory[$endMarkerName])){
				$this->markersMemory[$endMarkerName] = microtime(true);
			}
			return number_format($this->markersMemory[$endMarkerName] - $this->markersMemory[$startMarkerName], $decimalCount);
		}
	}