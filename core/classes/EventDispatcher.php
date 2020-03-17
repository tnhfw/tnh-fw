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
            if ($eventName !== null) {
                if (isset($this->listeners[$eventName])) {
                    $this->logger->info('The event name is set and exist in the listener just remove all event listener for this event');
                    unset($this->listeners[$eventName]);
                } else {
                    $this->logger->info('The event name is set and not exist in the listener nothing to do');
                }
            } else {
                $this->logger->info('The event name is not set, so remove all event listener');
                $this->listeners = array();
            }
        }
		
        /**
         * Get the list of listener for this event or all if event is null
         * @param string $eventName the event name
         * @return array the listeners for this event or all listeners if this event is null
         */
        public function getListeners($eventName = null) {
            if ($eventName === null) {
                return $this->listeners;
            }
            if (isset($this->listeners[$eventName])) {
                return $this->listeners[$eventName];
            }
            return array();
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
            if ($event->stop === true) {
                $this->logger->info('This event need stopped, no need call any listener');
                return;
            }
            if ($event->returnBack === true) {
                $this->logger->info('This event need return back, return the result for future use');
                return $this->dispatchToListerners($event);
            } 
            $this->logger->info('This event no need return back the result, just dispatch it');
            $this->dispatchToListerners($event);
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
            } 
            $this->logger->info('Found the registered event listener for the '
                                 . 'event [' . $event->name . '] the list are: ' . stringfy_vars($list));
            foreach ($list as $listener) {
                $result = call_user_func_array($listener, array($event));
                if ($eBackup->returnBack === true) {
                    if ($result instanceof EventInfo) {
                        $event = $result;
                    } else {
                        show_error('The event [' . $event->name . '] need you return the event object after processing');
                        return;
                    }
                }
                if ($event->stop === true) {
                    break;
                }
            }
            //only test for original event may be during the flow some listeners change this parameter
            if ($eBackup->returnBack === true) {
                return $event;
            }
        }
    }
