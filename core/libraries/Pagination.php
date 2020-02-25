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
         * The pagination current query string
         * @var string
         */
        private $paginationQueryString = null;

        /**
         * Create an instance of pagination
         * @param array $overwriteConfig the list of configuration to overwrite the defined configuration in config_pagination.php
         */
        public function __construct(array $overwriteConfig = array()){
            if (file_exists(CONFIG_PATH . 'config_pagination.php')){
                $config = array();
                require_once CONFIG_PATH . 'config_pagination.php';
                if (empty($config) || ! is_array($config)){
                    show_error('No configuration found in ' . CONFIG_PATH . 'config_pagination.php');
                }
				else{
					$config = array_merge($config, $overwriteConfig);
					$this->config = $config;
                    //put it gobally
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
         * @param array $config the configuration to overwrite
         */
        public function setConfig(array $config = array()){
            if (! empty($config)){
                $this->config = array_merge($this->config, $config);
                Config::setAll($config);
            }
        }

        /**
         * Return the value of the pagination configuration
         * 
         * @return array
         */
        public function getConfig(){
            return $this->config;
        }

        /**
         * Return the value of the pagination query string
         * @return string
         */
        public function getPaginationQueryString(){
            return $this->paginationQueryString;
        }

         /**
         * Set the value of the pagination query string
         * @param string $paginationQueryString the new value
         * @return object
         */
        public function setPaginationQueryString($paginationQueryString){
            $this->paginationQueryString = $paginationQueryString;
            return $this;
        }

        /**
         * Determine automatically the value of the pagination query string
         * Using the REQUEST URI
         * 
         * @return object
         */
        public function determinePaginationQueryStringValue(){
            $pageQueryName = $this->config['page_query_string_name'];
            $queryString = Url::queryString();
            $currentUrl = Url::current();
            $query = '';
             if ($queryString == ''){
                $query = '?' . $pageQueryName . '=';
            }
            else{
                $tab = explode($pageQueryName . '=', $queryString);
                $nb = count($tab);
                if ($nb == 1){
                    $query = '?' . $queryString . '&' . $pageQueryName . '=';
                }
                else{
                    if ($tab[0] == ''){
                        $query = '?' . $pageQueryName . '=';
                    }
                    else{
                        $query = '?' . $tab[0] . '' . $pageQueryName . '=';
                    }
                }
            }
            $temp = explode('?', $currentUrl);
            $query = $temp[0] . $query;
            $this->paginationQueryString = $query;
            return $this;
        }

        /**
         * Generate the pagination link
         * @param  int $totalRows the total number of data
         * @param  int $currentPageNumber the current page number
         * 
         * @return string the pagination link
         */
        public function getLink($totalRows, $currentPageNumber){
            $numberOfLink = $this->config['nb_link'];
			$numberOfRowPerPage = $this->config['pagination_per_page'];
            if (empty($this->paginationQueryString)){
                //determine the pagination query string value
                $this->determinePaginationQueryStringValue();
            }
            //************************************
            $navbar = '';
            $numberOfPage = (int) ceil($totalRows / $numberOfRowPerPage);
            $currentPageNumber = (int) $currentPageNumber;
            $numberOfLink = (int) $numberOfLink;
            $numberOfRowPerPage = (int) $numberOfRowPerPage;
			
            if ($currentPageNumber <= 0){
				$currentPageNumber = 1;
			}
            if ($numberOfPage <= 1 || $numberOfLink <= 0 || $numberOfRowPerPage <= 0) {
                return $navbar;
            }
            return $this->buildPaginationNavbar($currentPageNumber, $numberOfPage);
        }

        /**
         * Build the pagination navbar with the link numbers
         * @param  int $currentPageNumber the current page number
         * @param  int $numberOfPage      the total number of page
         * @return string
         */
        protected function buildPaginationNavbar($currentPageNumber, $numberOfPage){
            $values = $this->getPaginationBeginAndEndNumber($currentPageNumber, $numberOfPage);
            $begin = $values['begin'];
            $end   = $values['end'];
            $navbar = null;
            if ($currentPageNumber == 1){
                $navbar .= $this->buildPaginationLinkForFirstPage($begin, $end, $currentPageNumber);
            }
            else if ($currentPageNumber > 1 && $currentPageNumber < $numberOfPage){
                $navbar .= $this->buildPaginationLinkForMiddlePage($begin, $end, $currentPageNumber);
            }
            else if ($currentPageNumber == $numberOfPage){
               $navbar .= $this->buildPaginationLinkForLastPage($begin, $end, $currentPageNumber);
            }
            $navbar = $this->config['pagination_open'] . $navbar . $this->config['pagination_close'];
            return $navbar;
        }

        /**
         * Get the pagination begin and end link numbers
         * @param  int $currentPageNumber the current page number
         * @param  int $numberOfPage      the total number of page
         * @return array                    the begin and end number
         */
        protected function getPaginationBeginAndEndNumber($currentPageNumber, $numberOfPage){
            $start = null;
            $begin = null;
            $end   = null;
            $numberOfLink = $this->config['nb_link'];
            if ($numberOfLink % 2 == 0){
                $start = $currentPageNumber - ($numberOfLink / 2) + 1;
                $end   = $currentPageNumber + ($numberOfLink / 2);
            }
            else{
                $start = $currentPageNumber - floor($numberOfLink / 2);
                $end   = $currentPageNumber + floor($numberOfLink / 2);
            }
            if ($start <= 1){
                $begin = 1;
                $end   = $numberOfLink;
            }
            else if ($start > 1 && $end < $numberOfPage){
                $begin = $start;
                $end = $end;
            }
            else{
                $begin = ($numberOfPage - $numberOfLink) + 1;
                $end   = $numberOfPage;
            }
            if ($numberOfPage <= $numberOfLink){
                $begin = 1;
                $end = $numberOfPage;
            }
            return array(
                        'begin' => (int) $begin,
                        'end' => (int) $end
                    );
        }

        /**
         * Build the pagination link for the first page
         * @param  int $begin             the pagination begin number
         * @param  int $end               the pagination end number
         * @param  int $currentPageNumber the pagination current page number
         * @return string                    
         */
        protected function buildPaginationLinkForFirstPage($begin, $end, $currentPageNumber){
            $navbar = null;
            $query = $this->paginationQueryString;
            for($i = $begin; $i <= $end; $i++){
                if ($i == $currentPageNumber){
                    $navbar .= $this->config['active_link_open'] . $currentPageNumber . $this->config['active_link_close'];
                }
                else{
                    $navbar .= $this->config['digit_open'] 
                            . '<a href="' . $query . $i . '" ' . attributes_to_string($this->config['attributes']) . '>' . $i . '</a>' 
                            . $this->config['digit_close'];
                }
            }
            $navbar .= $this->config['next_open']
                         . '<a href="' . $query . ($currentPageNumber + 1) . '">' 
                         . $this->config['next_text'] . '</a>' . $this->config['next_close'];
            return $navbar;
        }

        /**
         * Build the pagination link for the page in the middle
         * @param  int $begin             the pagination begin number
         * @param  int $end               the pagination end number
         * @param  int $currentPageNumber the pagination current page number
         * @return string                    
         */
        protected function buildPaginationLinkForMiddlePage($begin, $end, $currentPageNumber){
            $navbar = null;
            $query = $this->paginationQueryString;
            $navbar .= $this->config['previous_open'] 
                            . '<a href="' . $query . ($currentPageNumber - 1) . '">' 
                            . $this->config['previous_text'] . '</a>' . $this->config['previous_close'];
            for($i = $begin; $i <= $end; $i++){
                if ($i == $currentPageNumber){
                    $navbar .= $this->config['active_link_open'] . $currentPageNumber . $this->config['active_link_close'];
                }
                else{
                    $navbar .= $this->config['digit_open'] 
                                    . '<a href="' . $query . $i . '"' . attributes_to_string($this->config['attributes']) . '>' . $i .'</a>' 
                                    . $this->config['digit_close'];
                }
            }
            $navbar .= $this->config['next_open']."<a href='$query".($currentPageNumber + 1)."'>".$this->config['next_text']."</a>".$this->config['next_close'];
            return $navbar;
        }

        /**
         * Build the pagination link for the last page
         * @param  int $begin             the pagination begin number
         * @param  int $end               the pagination end number
         * @param  int $currentPageNumber the pagination current page number
         * @return string                    
         */
        protected function buildPaginationLinkForLastPage($begin, $end, $currentPageNumber){
            $navbar = null;
            $query = $this->paginationQueryString;
            $navbar .= $this->config['previous_open'] 
                        . '<a href="' . $query . ($currentPageNumber - 1) . '">' 
                        . $this->config['previous_text'] . '</a>' . $this->config['previous_close'];
            for($i = $begin; $i <= $end; $i++){
                if ($i == $currentPageNumber){
                    $navbar .= $this->config['active_link_open'] 
                                . $currentPageNumber 
                                . $this->config['active_link_close'];
                }
                else{
                    $navbar .= $this->config['digit_open'] 
                                . '<a href="' . $query . $i . '"' . attributes_to_string($this->config['attributes']) . '>' . $i . '</a>' 
                                . $this->config['digit_close'];
                }
            }
            return $navbar;
        }
    }
