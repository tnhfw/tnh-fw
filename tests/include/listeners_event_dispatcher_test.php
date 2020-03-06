<?php
    class ListenersEventDispatcherTest {
        
        function stopEventListener($e){
            //never call by dispatcher
        } 
        
        public function returnBackEventListener($e){
            $e->payload = 'foo';
            return $e;
        }
        
        public function returnBackEventListenerAndStop($e){
            $e->payload = 'bar';
            //no more listener will be call
            $e->stop = true;
            return $e;
        }
        
        public function returnBackEventListenerButNotReturnIt($e){
            //normally need return it
            //but we not return will got an error
        }
        
        public function emptyListener($e){}
        
        public function fooListener($e){}
        
        /**
        * Used in Response::test
        */
        public function responseTestListener($e){
            $e->payload = null;
            return $e;
        }

    }
    