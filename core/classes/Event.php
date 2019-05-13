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
	 * This class represent the event detail to dispatch to correspond listener
	 */
	class Event{
		
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
		
		public function __construct($name, $payload = array(), $returnBack = false, $stop = false){
			$this->name = $name;
			$this->payload = $payload;
			$this->returnBack = $returnBack;
			$this->stop = $stop;
		}
	}