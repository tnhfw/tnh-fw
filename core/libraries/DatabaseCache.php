<?php
  defined('ROOT_PATH') || exit('Access denied');
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


  class DatabaseCache
  {
    private $cacheDir = null;
    private $cache    = null;
    private $finish   = null;
    private $logger;
    
    function __construct($dir = null, $time = 0)
    {
      if(!class_exists('Log')){
        //here the Log class is not yet loaded
        //load it manually
        require_once CORE_LIBRARY_PATH . 'Log.php';
      }
      $this->logger = new Log();
      $this->logger->setLogger('Library::DatabaseCache');

      if($dir && !file_exists($dir)){
        $this->logger->warning('Database cache directory ['. $dir .'] not exists try to create it now');
        mkdir($dir, 0755);
      }
      $this->cacheDir = $dir;
      $this->cache = $time;
      $this->finish = time() + $time;
       $this->logger->info('Database cache informations: directory: ['. $dir .'], cache time [' .$time. '] sec, cache expire time [' .$this->finish. '].');
    }
    
    public function setCache($sql, $result)
    {
      $this->logger->debug('Try to set the database cache for query ['. $sql .']');
      if (is_null($this->cache)){
         $this->logger->info('Database cache time is not set cannot set the cache for query ['. $sql .']');
        return false;
      }
      
      $cacheFile = $this->cacheDir . $this->fileName($sql);
      $fp = fopen($cacheFile, 'w');
      
      if($fp){
         $this->logger->info('Set database cache for query ['. $sql .'], filename [' .$cacheFile. '], expire time [' .$this->finish. ']');
        fputs($fp, json_encode(['data' => $result, 'finish' => $this->finish]));
      }
      return;
    }
    
    public function getCache($sql, $array = false)
    {
      $this->logger->debug('Try to get the database cache for query ['. $sql .']');
      if (is_null($this->cache)) {
        $this->logger->info('Database cache time is not set cannot get the cache for query ['. $sql .']');
        return false;
      }
      $cacheFile = $this->cacheDir . $this->fileName($sql);
      if (file_exists($cacheFile)){
        $this->logger->info('Database cache file [' .$cacheFile. '] for query ['. $sql .'] exists');
        $cache = json_decode(file_get_contents($cacheFile), $array);
        if (($array ? $cache['finish'] : $cache->finish) < time()){
          $this->logger->info('Database cache already expired delete the cache file [' .$cacheFile. '] for query ['. $sql .']');
          unlink($cacheFile);
          return false;
        }
  	  else{
  		    $this->logger->info('Database cache not yet expire, now return the database cache for query ['. $sql .']');
  			return ($array ? $cache['data'] : $cache->data);
  	  }
        return false;
      }
    }
    
    private function fileName($name){
      return md5($name) . '.dbcache';
    }
  }