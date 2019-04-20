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
         * @param array $overwriteConfig the list of configuration to overwrite the defined configuration in config_pagination.php
         */
        public function __construct($overwriteConfig = array()){
            if(file_exists(CONFIG_PATH . 'config_pagination.php')){
                require_once CONFIG_PATH . 'config_pagination.php';
                if(empty($config) || ! is_array($config)){
                    show_error('No configuration found in ' . CONFIG_PATH . 'config_pagination.php');
                }
				else{
					if(! empty($overwriteConfig)){
						$config = array_merge($config, $overwriteConfig);
					}
					$this->config = $config;
					Config::setAll($config);
					unset($config);
				}
            }
            else{
                show_error('Unable to find the pagination configuration file');
            }
        }


        /**
         * Set the pagination custom configuration to overwrite the default configuration in
         * config_pagination.php
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
         * @param  int $totalRows the total number of data
         * @param  int $currentPageNumber the current page number
         * @return string the pagination link
         */
        public function getLink($totalRows, $currentPageNumber){
            $pageQueryName = $this->config['page_query_string_name'];
            $numberOfLink = $this->config['nb_link'];
			$numberOfRowPerPage = $this->config['pagination_per_page'];
            $queryString = Url::queryString();
            $currentUrl = Url::current();
            if($queryString == ''){
                $query = '?' . $pageQueryName . '=';
            }
            else{
                $tab = explode($pageQueryName . '=', $queryString);
                $nb = count($tab);
                if($nb == 1){
                    $query = '?' . $queryString . '&' . $pageQueryName . '=';
                }
                else{
                    if($tab[0] == ''){
                        $query = '?' . $pageQueryName . '=';
                    }
                    else{
                        $query = '?' . $tab[0] . '' . $pageQueryName . '=';
                    }
                }
            }
            $temp = explode('?', $currentUrl);
            $query = $temp[0] . $query;
            $navbar = '';
            $numberOfPage = ceil($totalRows / $numberOfRowPerPage);
            if($numberOfPage <= 1 || $numberOfLink <= 0 || $numberOfRowPerPage <= 0 ||
                $currentPageNumber <= 0 || !is_numeric($numberOfLink) || !is_numeric($numberOfRowPerPage)
            ){
                return $navbar;
            }
            if($numberOfLink % 2 == 0){
                $start = $currentPageNumber - ($numberOfLink / 2) + 1;
                $end = $currentPageNumber + ($numberOfLink / 2);
            }
            else{
                $start = $currentPageNumber - floor($numberOfLink / 2);
                $end = $currentPageNumber + floor($numberOfLink / 2);
            }
            if($start <= 1){
                $begin = 1;
                $end = $numberOfLink;
            }
            else if($start > 1 && $end < $numberOfPage){
                $begin = $start;
                $end = $end;
            }
            else{
                $begin = ($numberOfPage - $numberOfLink) + 1;
                $end = $numberOfPage;
            }
            if($numberOfPage <= $numberOfLink){
                $begin = 1;
                $end = $numberOfPage;
            }
            if($currentPageNumber == 1){
                for($i = $begin; $i <= $end; $i++){
                    if($i == $currentPageNumber){
                        $navbar .= $this->config['active_link_open'] . $currentPageNumber . $this->config['active_link_close'];
                    }
                    else{
                        $navbar .= $this->config['digit_open'] . '<a href="' . $query . $i . '" ' . attributes_to_string($this->config['attributes']) . '>' . $i . '</a>' . $this->config['digit_close'];
                    }
                }
                $navbar .= $this->config['next_open'] . '<a href="' . $query . ($currentPageNumber + 1) . '">' . $this->config['next_text'] . '</a>' . $this->config['next_close'];
            }
            else if($currentPageNumber > 1 && $currentPageNumber < $numberOfPage){
                $navbar .= $this->config['previous_open'] . '<a href="' . $query . ($currentPageNumber - 1) . '">' . $this->config['previous_text'] . '</a>' . $this->config['previous_close'];
                for($i = $begin; $i <= $end; $i++){
                    if($i == $currentPageNumber){
                        $navbar .= $this->config['active_link_open'] . $currentPageNumber . $this->config['active_link_close'];
                    }
                    else{
                        $navbar .= $this->config['digit_open'] . '<a href="' . $query . $i . '"' . attributes_to_string($this->config['attributes']) . '>' . $i .'</a>' . $this->config['digit_close'];
                    }
                }
                $navbar .= $this->config['next_open']."<a href='$query".($currentPageNumber + 1)."'>".$this->config['next_text']."</a>".$this->config['next_close'];
            }
            else if($currentPageNumber == $numberOfPage){
                $navbar .= $this->config['previous_open'] . '<a href="' . $query . ($currentPageNumber - 1) . '">' . $this->config['previous_text'] . '</a>' . $this->config['previous_close'];
                for($i = $begin; $i <= $end; $i++){
                    if($i == $currentPageNumber){
                        $navbar .= $this->config['active_link_open'] . $currentPageNumber . $this->config['active_link_close'];
                    }
                    else{
                        $navbar .= $this->config['digit_open'] . '<a href="' . $query . $i . '"' . attributes_to_string($this->config['attributes']) . '>' . $i . '</a>' . $this->config['digit_close'];
                    }
                }
            }
            $navbar = $this->config['pagination_open'] . $navbar . $this->config['pagination_close'];
            return $navbar;
        }
    }