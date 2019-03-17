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

    class Pagination{
        /**
         * The list of loaded config
         * @var array
         */
        private $config = array();

        /**
         * Create an instance of pagination
         * @param array $overwrite_config the list of configuration to overwrite the defined configuration in config_pagination.php
         */
        public function __construct($overwrite_config = array()){
            if(file_exists(CONFIG_PATH . 'config_pagination.php')){
                require_once CONFIG_PATH . 'config_pagination.php';
                if(empty($config) || ! is_array($config)){
                    show_error('No configuration found in ' . CONFIG_PATH . 'config_pagination.php');
                }
                $this->config = $config;
                Config::setAll($config);
                unset($config);
                $this->setConfig($overwrite_config);
            }
            else{
                show_error('Unable to find the pagination configuration file');
            }
        }


        /**
         * Set the pagination custom configuration to overwrite the default configuration in
         * config_pagination
         * @param array $config the configuration to set
         */
        public function setConfig(array $config = array()){
            if(! empty($config)){
                $this->config = array_merge($this->config, $config);
                Config::setAll($config);
            }
        }

        /**
         * Generate the pagination link
         * @param  int $total the total number of data
         * @param  int $current_page_no the current page number
         * @return string the pagination link
         */
        public function getLink($total, $current_page_no){
            $pageQueryName = $this->config['page_query_string_name'];
            $nb_link = $this->config['nb_link'];
			$nb_per_page = $this->config['pagination_per_page'];
            $queryString = Url::queryString();
            $current = Url::current();
            if($queryString == ''){
                $query = '?'.$pageQueryName.'=';
            }
            else{
                $tab = explode($pageQueryName.'=', $queryString);
                $nb = count($tab);
                if($nb == 1){
                    $query = '?'.$queryString.'&'.$pageQueryName.'=';
                 }
                else{
                    if($tab[0] == ''){
                        $query = '?'.$pageQueryName.'=';
                    }
                    else{
                        $query = '?'.$tab[0].''.$pageQueryName.'=';
                    }
                }
            }
            $temp = explode('?', $current);
            $query = $temp[0].$query;
            $navbar = '';
            $nb_page = ceil($total/$nb_per_page);
            if($nb_page <= 1 || $nb_link <= 0 || $nb_per_page <= 0 ||
                $current_page_no <= 0 || !is_numeric($nb_link) || !is_numeric($nb_per_page)
            ){
                return $navbar;
            }
            if($nb_link % 2 == 0){
                $start = $current_page_no - ($nb_link/2) + 1;
                $end = $current_page_no + ($nb_link/2);
            }
            else{
                $start = $current_page_no - floor($nb_link/2);
                $end = $current_page_no + floor($nb_link/2);
            }
            if($start <= 1){
                $begin = 1;
                $end = $nb_link;
            }
            else if($start > 1 && $end < $nb_page){
                $begin = $start;
                $end = $end;
            }
            else{
                $begin = ($nb_page-$nb_link) + 1;
                $end = $nb_page;
            }
            if($nb_page <= $nb_link){
                $begin = 1;
                $end = $nb_page;
            }
            if($current_page_no == 1){
                for($i = $begin; $i <= $end; $i++){
                    if($i == $current_page_no){
                        $navbar .= $this->config['active_link_open'].$current_page_no.$this->config['active_link_close'];
                    }
                    else{
                        $navbar .= $this->config['digit_open']."<a href='$query".$i."' ".attributes_to_string($this->config['attributes']).">$i</a>".$this->config['digit_close'];
                    }
                }
                $navbar .= $this->config['next_open']."<a href='$query".($current_page_no+1)."'>".$this->config['next_text']."</a>".$this->config['next_close'];
            }
            else if($current_page_no > 1 && $current_page_no < $nb_page){
                $navbar .= $this->config['previous_open']."<a href='$query".($current_page_no-1)."'>".$this->config['previous_text']."</a>".$this->config['previous_close'];
                for($i = $begin; $i <= $end; $i++){
                    if($i == $current_page_no){
                        $navbar .= $this->config['active_link_open'].$current_page_no.$this->config['active_link_close'];
                    }
                    else{
                        $navbar .= $this->config['digit_open']."<a href='$query".$i."' ".attributes_to_string($this->config['attributes']).">$i</a>".$this->config['digit_close'];
                    }
                }
                $navbar .= $this->config['next_open']."<a href='$query".($current_page_no+1)."'>".$this->config['next_text']."</a>".$this->config['next_close'];
            }
            else if($current_page_no == $nb_page){
                $navbar .= $this->config['previous_open']."<a href='$query".($current_page_no-1)."'>".$this->config['previous_text']."</a>".$this->config['previous_close'];
                for($i = $begin; $i <= $end; $i++){
                    if($i == $current_page_no){
                        $navbar .= $this->config['active_link_open'].$current_page_no.$this->config['active_link_close'];
                    }
                    else{
                        $navbar .= $this->config['digit_open']."<a href='$query".$i."' ".attributes_to_string($this->config['attributes']).">$i</a>".$this->config['digit_close'];
                    }
                }
            }
            $navbar = $this->config['pagination_open'].$navbar.$this->config['pagination_close'];
            return $navbar;
        }
    }