<?php
	defined('ROOT_PATH') or exit('Access denied');
	/**
	 * TNH Framework
	 *
	 * A simple PHP framework created using the concept of codeigniter with bootstrap twitter
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
		private $markers_time = array();
		
		/**
		 * The markers for memory usage
		 * @var array
		 */
		private $markers_memory = array();
		
		/**
		 * This method is used to mark one point for benchmark (execution time and memory usage)
		 * @param  string $name the marker name
		 */
		public function mark($name){
			//Marker for execution time
			$this->markers_time[$name] = microtime(true);
			//Marker for memory usage
			$this->markers_memory[$name] = memory_get_usage(true);
		}
		
		/**
		 * This method is used to get the total excution time in second between two markers
		 * @param  string  $begin the marker for start point
		 * @param  string  $end   the marker for end point
		 * @param  integer $dec   the number of decimal
		 * @return double         the total execution time
		 */
		public function elapsed_time($begin = null, $end = null, $dec = 6){
			if(! $begin || !isset($this->markers_time[$begin])){
				return 0;
			}
			
			if(!isset($this->markers_time[$end])){
				$this->markers_time[$end] = microtime(true);
			}
			return number_format($this->markers_time[$end] - $this->markers_time[$begin], $dec);
		}
		
		/**
		 * This method is used to get the total memory usage in byte between two markers
		 * @param  string  $begin the marker for start point
		 * @param  string  $end   the marker for end point
		 * @param  integer $dec   the number of decimal
		 * @return double         the total memory usage
		 */
		public function memory_usage($begin = null, $end = null, $dec = 6){
			if(! $begin || !isset($this->markers_memory[$begin])){
				return 0;
			}
			
			if(!isset($this->markers_memory[$end])){
				$this->markers_memory[$end] = microtime(true);
			}
			return number_format($this->markers_memory[$end] - $this->markers_memory[$begin], $dec);
		}
	}