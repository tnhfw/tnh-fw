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
	
    class EventDispatcher extends BaseClass {
		
        /**
         * The list of the registered listeners
         * @var array
         */
        private $listeners = array();
		

        public function __construct() {
            parent::__construct();
        }

        /**
         * Register new listener
         * @param string   $eventName the name of the event to register for
         * @param callable $listener  the function or class method to receive the event information after dispatch
         */
        public function addListener($eventName, callable $listener) {
            $this->logger->debug('Adding new event listener for the event name [' . $eventName . '], listener [' . stringfy_vars($listener) . ']');
            if (!isset($this->listeners[$eventName])) {
                $this->logger->info('This event does not have the registered event listener before, adding new one');
                $this->listeners[$eventName] = array();
            } else {
                $this->logger->info('This event already have the registered listener, add this listener to the list');
            }
            $this->listeners[$eventName][] = $listener;
        }
		
        /**
         * Remove the event listener from list
         * @param  string   $eventName the event name
         * @param  callable $listener  the listener callback
         */
        public function removeListener($eventName, callable $listener) {
            $this->logger->debug('Removing of the event listener, the event name [' . $eventName . '], listener [' . stringfy_vars($listener) . ']');
            if (isset($this->listeners[$eventName])) {
                $this->logger->info('This event have the listeners, check if this listener exists');
                if (false !== $index = array_search($listener, $this->listeners[$eventName], true)) {
                    $this->logger->info('Found the listener at index [' . $index . '] remove it');
                    unset($this->listeners[$eventName][$index]);
                } else {
                    $this->logger->info('Cannot found this listener in the event listener list');
                }
            } else {
                $this->logger->info('This event does not have this listener ignore remove');
            }
        }
		
        /**
         * Remove all the event listener. If event name is null will remove all listeners, else will just 
         * remove all listeners for this event
         * @param  string $eventName the event name
         */
        public function removeAllListener($eventName = null) {
            $this->logger->debug('Removing of all event listener, the event name [' . $eventName . ']');
            if ($eventName !== null && isset($this->listeners[$eventName])) {
                $this->logger->info('The event name is set of exist in the listener just remove all event listener for this event');
                unset($this->listeners[$eventName]);
            } else {
                $this->logger->info('The event name is not set or does not exist in the listener, so remove all event listener');
                $this->listeners = array();
            }
        }
		
        /**
         * Get the list of listener for this event
         * @param string $eventName the event name
         * @return array the listeners for this event or empty array if this event does not contain any listener
         */
        public function getListeners($eventName) {
            return isset($this->listeners[$eventName]) ? $this->listeners[$eventName] : array();
        }
		
        /**
         * Dispatch the event to the registered listeners.
         * @param  mixed|object $event the event information
         * @return void|object if event need return, will return the final EventInfo object.
         */	
        public function dispatch($event) {
            if (!$event || !$event instanceof EventInfo) {
                $this->logger->info('The event is not set or is not an instance of "EventInfo" create the default "EventInfo" object to use instead of.');
                $event = new EventInfo((string) $event);
            }			
            $this->logger->debug('Dispatch to the event listener, the event [' . stringfy_vars($event) . ']');
            if (isset($event->stop) && $event->stop) {
                $this->logger->info('This event need stopped, no need call any listener');
                return;
            }
            if ($event->returnBack) {
                $this->logger->info('This event need return back, return the result for future use');
                return $this->dispatchToListerners($event);
            } else {
                $this->logger->info('This event no need return back the result, just dispatch it');
                $this->dispatchToListerners($event);
            }
        }
		
        /**
         * Dispatch the event to the registered listeners.
         * @param  object EventInfo $event  the event information
         * @return void|object if event need return, will return the final EventInfo instance.
         */	
        private function dispatchToListerners(EventInfo $event) {
            $eBackup = $event;
            $list = $this->getListeners($event->name);
            if (empty($list)) {
                $this->logger->info('No event listener is registered for the event [' . $event->name . '] skipping.');
                if ($event->returnBack) {
                    return $event;
                }
                return;
            } else {
                $this->logger->info('Found the registered event listener for the event [' . $event->name . '] the list are: ' . stringfy_vars($list));
            }
            foreach ($list as $listener) {
                if ($eBackup->returnBack) {
                    $returnedEvent = call_user_func_array($listener, array($event));
                    if ($returnedEvent instanceof EventInfo) {
                        $event = $returnedEvent;
                    } else {
                        show_error('This event [' . $event->name . '] need you return the event object after processing');
                    }
                } else {
                    call_user_func_array($listener, array($event));
                }
                if ($event->stop) {
                    break;
                }
            }
            //only test for original event may be during the flow some listeners change this parameter
            if ($eBackup->returnBack) {
                return $event;
            }
        }
    }
