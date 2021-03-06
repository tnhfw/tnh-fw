<?php
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
    
    class ListenersEventDispatcherTest {
        
        function stopEventListener($e){
            //never call by dispatcher
        } 
        
        public function returnBackEventListener($e){
            $e->setPayload('foo');
            return $e;
        }
        
        public function returnBackEventListenerAndStop($e){
            $e->setPayload('bar');
            //no more listener will be call
            $e->setStop(true);
            return $e;
        }
        
        public function returnBackEventListenerButNotReturnIt($e){
            //normally need return it
            //but we not return will got an error
        }
        
        public function emptyListener($e){}
        
        public function fooListener($e){}
        
        /**
        * Used in Response::testXXX
        */
        public function responseTestListener($e){
            $e->setPayload(null);
            return $e;
        }

    }
    
