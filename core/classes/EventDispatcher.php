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
	 * This class represent the event dispatcher management, permit to record the listener and 
	 * also to dispatch the event
	 */
	
	class EventDispatcher{
		
		/**
		 * The list of the registered listeners
		 * @var array
		 */
		private $listeners = array();
		

		/**
		 * The logger instance
		 * @var Log
		 */
		private $logger;

		public function __construct(){
			$this->logger =& class_loader('Log', 'classes');
			$this->logger->setLogger('Library::EventDispatcher');
		}

		/**
		 * Register new listener
		 * @param string   $eventName the name of the event to register for
		 * @param callable $listener  the function or class method to receive the event information after dispatch
		 */
		public function addListener($eventName, callable $listener){
			$this->logger->debug('Adding new Event Listener for the event name [' .$eventName. '], listener [' .stringfy_vars($listener). ']');
			if(! isset($this->listeners[$eventName])){
				$this->logger->info('This event does not have the registered event listener before, adding new one');
				$this->listeners[$eventName] = array();
			}
			else{
				$this->logger->info('This event already have the registered listener, add this listener to the list');
			}
			$this->listeners[$eventName][] = $listener;
		}
		
		/**
		 * Remove the event listener from list
		 * @param  string   $eventName the event name
		 * @param  callable $listener  the listener callback
		 */
		public function removeListener($eventName, callable $listener){
			$this->logger->debug('Removing of the Event Listener, the event name [' .$eventName. '], listener [' .stringfy_vars($listener). ']');
			if(isset($this->listeners[$eventName])){
				$this->logger->info('This event have the listeners, check if this listener exists');
				if(false !== $index = array_search($listener, $this->listeners[$eventName], true)){
					$this->logger->info('Found the listener at index [' .$index. '] remove it');
					unset($this->listeners[$eventName][$index]);
				}
				else{
					$this->logger->info('Cannot found this listener in the even listener list');
				}
			}
			else{
				$this->logger->info('This event does not have this listener ignore remove');
			}
		}
		
		/**
		 * Remove all the event listener. If event name is null will remove all listeners, else will just 
		 * remove all listeners for this event
		 * @param  string $eventName the event name
		 */
		public function removeAllListener($eventName = null){
			$this->logger->debug('Removing of all Event Listener, the event name [' .$eventName. ']');
			if($eventName != null && isset($this->listeners[$eventName])){
				$this->logger->info('The Event name is set of exist in the listener just remove all Event Listener for this event');
				unset($this->listeners[$eventName]);
			}
			else{
				$this->logger->info('The Event name is not set or does not exist in the listener, so remove all Event Listener');
				$this->listeners = array();
			}
		}
		
		/**
		 * Get the list of listener for this event
		 * @param string $eventName the event name
		 * @return array the listeners for this event or empty array if this event does not contain any listener
		 */
		public function getListeners($eventName){
			return isset($this->listeners[$eventName]) ? $this->listeners[$eventName] : array();
		}
		
		/**
		 * Dispatch the event to the registered listeners.
		 * @param  string $eventName the name of the event to dispatch
		 * @param  mixed|Event $e  the event information
		 * @return void|Event if event need return, will return the final Event object.
		 */	
		public function dispatch($eventName, $e = null){
			$this->logger->debug('Dispatch to the Event Listener, the event name [' .$eventName. '], event data [' .stringfy_vars($e). ']');
			if($e == null || ! $e instanceof Event){
				$this->logger->info('The event data is not set or is not an instance of "Event" create the default "Event" object to use instead of.');
				$e = new Event($eventName);
			}
			if(! $e->name){
				$e->name = $eventName;
			}
			if(isset($e->stop) && $e->stop){
				$this->logger->info('This event need stopped, no need call any listener');
				return;
			}
			if($e->returnBack){
				$this->logger->info('This event need return back, return the result for future use');
				return $this->dispatchToListerners($eventName, $e);
			}
			else{
				$this->logger->info('This event no need return back the result, just dispatch it');
				$this->dispatchToListerners($eventName, $e);
			}
		}
		
		
		/**
		 * Dispatch the event to the registered listeners.
		 * @param  string $eventName the name of the event to dispatch
		 * @param  Event $e  the event information
		 * @return void|Event if event need return, will return the final Event instance.
		 */	
		private function dispatchToListerners($eventName, Event $e){
			$eBackup = $e;
			$list = $this->getListeners($eventName);
			if(empty($list)){
				$this->logger->info('No event listener is registered for the event [' .$eventName. '] skipping.');
				if($e->returnBack){
					return $e;
				}
				return;
			}
			else{
				$this->logger->info('Found the registered Event listener for the event [' .$eventName. '] the list are: ' . stringfy_vars($list));
			}
			foreach($list as $listener){
				if($eBackup->returnBack){
					$returnedEvent = call_user_func_array($listener, array($e));
					if($returnedEvent instanceof Event){
						$e = $returnedEvent;
					}
					else{
						show_error('This event [' .$eventName. '] need you return the event object after processing');
					}
				}
				else{
					call_user_func_array($listener, array($e));
				}
				if($e->stop){
					break;
				}
			}
			//only test for original event may be during the flow some listeners change this parameter
			if($eBackup->returnBack){
				return $e;
			}
		}
	}