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

	class BaseClass{
		/**
		 * The logger instance
		 * @var object
		 */
		protected $logger;

		/**
		 * Class constructor
		 */
		public function __construct(){
			//Set Log instance to use
       		$this->setLoggerFromParamOrCreate(null);
		}

		/**
	     * Set the dependencies instance using argument or create new instance if is null
	     * @param string $name this class property name.
	     * @param object $instance the instance. If is not null will use it
	     * otherwise will create new instance.
	     * @param string $loadClassName the name of class to load using class_loader function.
	     * @param string $loadClassPath the path of class to load using class_loader function.
	     *
	     * @return object this current instance
	     */
	    protected function setDependencyInstanceFromParamOrCreate($name, $instance = null, $loadClassName = null, $loadClassePath = 'classes'){
	      if ($instance !== null){
	        $this->{$name} = $instance;
	        return $this;
	      }
	      $this->{$name} =& class_loader($loadClassName, $loadClassePath);
	      return $this;
	    }

	    /**
	     * Return the Log instance
	     * @return object
	     */
	    public function getLogger(){
	      return $this->logger;
	    }

	    /**
	     * Set the log instance
	     * @param object $logger the log object
		 * @return object Database
	     */
	    public function setLogger($logger){
	      $this->logger = $logger;
	      return $this;
	    }

	    /**
	     * Set the Log instance using argument or create new instance
	     * @param object $logger the Log instance if not null
	     *
	     * @return object this current instance
	     */
	    protected function setLoggerFromParamOrCreate(Log $logger = null){
	      $this->setDependencyInstanceFromParamOrCreate('logger', $logger, 'Log', 'classes');
	      if ($logger === null){
	        $this->logger->setLogger('Class::' . get_class($this));
	      }
	      return $this;
	    }

	}
