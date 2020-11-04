<?php
    defined('ROOT_PATH') || exit('Access denied');
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

    class Pagination {
        
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
         * @param array $overwriteConfig the list of configuration to overwrite the 
         * defined configuration in config_pagination.php
         */
        public function __construct(array $overwriteConfig = array()) {
            $config = array();
            if (file_exists(CONFIG_PATH . 'config_pagination.php')) {
                require CONFIG_PATH . 'config_pagination.php';
            } 
            $config = array_merge($config, $overwriteConfig);
            $this->config = $config;
            //put it gobally
            get_instance()->config->setAll($config);
            unset($config);
        }


        /**
         * Set the pagination custom configuration to overwrite the default configuration in
         * config_pagination.php
         * @param array $config the configuration to overwrite
         */
        public function setConfig(array $config = array()) {
            if (!empty($config)) {
                $this->config = array_merge($this->config, $config);
                get_instance()->config->setAll($config);
            }
        }

        /**
         * Return the value of the pagination configuration
         * 
         * @return array
         */
        public function getConfig() {
            return $this->config;
        }

        /**
         * Get pagination information
         * @param  int $totalRows the total number of data
         * @param  int $currentPageNumber the current page number
         * @return array
         */
        public function getInfos($totalRows, $currentPageNumber){
            $numberOfRowPerPage = (int)$this->config['pagination_per_page'];
            $numberOfPage = (int)ceil($totalRows / $numberOfRowPerPage);
            $numberOfLink = (int)$this->config['nb_link'];
            $infos['current_page'] = $currentPageNumber;
            $infos['num_links'] = $numberOfLink;
            $infos['limit'] = $numberOfRowPerPage;
            $infos['total_page'] = $numberOfPage;
            $infos['total_rows'] = $totalRows;
            $infos['is_first_page'] = $currentPageNumber == 1;
            $infos['is_last_page'] = $currentPageNumber == $numberOfPage;
            $infos['prev_page'] = ($numberOfPage > 1 && $currentPageNumber > 1) ? $currentPageNumber - 1 : $currentPageNumber;
            $infos['next_page'] = ($numberOfPage > 1 && $currentPageNumber < $numberOfPage) ? $currentPageNumber + 1 : $currentPageNumber;
            $infos['has_prev_page'] = $numberOfPage > 1 && $currentPageNumber > 1;
            $infos['has_next_page'] = $numberOfPage > 1 && $currentPageNumber < $numberOfPage;
            
            return $infos;
        }

        /**
         * Generate the pagination link
         * @param  int $totalRows the total number of data
         * @param  int $currentPageNumber the current page number
         * 
         * @return null|string the pagination link
         */
        public function getLink($totalRows, $currentPageNumber) {
            $infos = $this->getInfos($totalRows, $currentPageNumber);
            $numberOfRowPerPage = $infos['limit'];
            $numberOfPage = $infos['total_page'];
            
            //determine the pagination query string value
            $this->determinePaginationQueryStringValue();
            
            $currentPageNumber = (int)$currentPageNumber;
            
            if ($currentPageNumber <= 0) {
                $currentPageNumber = 1;
            }
            if ($numberOfPage <= 1 || $infos['num_links'] <= 0 || $numberOfRowPerPage <= 0) {
                return null;
            }

            return $this->buildPaginationNavbar($currentPageNumber, $numberOfPage);
        }

        /**
         * Determine automatically the value of the pagination query string
         * Using the REQUEST URI
         * 
         * @return object
         */
        protected function determinePaginationQueryStringValue() {
            $pageQueryName = $this->config['page_query_string_name'];
            $queryString = get_instance()->url->queryString();
            $currentUrl = get_instance()->url->current();
            $query = '';
            if ($queryString == '') {
                $query = '?' . $pageQueryName . '=';
            } else {
                $tab = explode($pageQueryName . '=', $queryString);
                $nb = count($tab);
                if ($nb == 1) {
                    $query = '?' . $queryString . '&' . $pageQueryName . '=';
                } else {
                    if ($tab[0] == '') {
                        $query = '?' . $pageQueryName . '=';
                    } else {
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
         * Build the pagination navbar with the link numbers
         * @param  int $currentPageNumber the current page number
         * @param  int $numberOfPage      the total number of page
         * @return string
         */
        protected function buildPaginationNavbar($currentPageNumber, $numberOfPage) {
            $values = $this->getPaginationBeginAndEndNumber($currentPageNumber, $numberOfPage);
            $begin = $values['begin'];
            $end   = $values['end'];
            $navbar = null;
            if ($currentPageNumber == 1) {
                $navbar .= $this->buildPaginationLinkForFirstPage($begin, $end, $currentPageNumber);
            } else if ($currentPageNumber > 1 && $currentPageNumber < $numberOfPage) {
                $navbar .= $this->buildPaginationLinkForMiddlePage($begin, $end, $currentPageNumber);
            } else if ($currentPageNumber == $numberOfPage) {
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
        protected function getPaginationBeginAndEndNumber($currentPageNumber, $numberOfPage) {
            $start = null;
            $begin = null;
            $end   = null;
            $numberOfLink = $this->config['nb_link'];
            if ($numberOfLink % 2 == 0) {
                $start = $currentPageNumber - ($numberOfLink / 2) + 1;
                $end   = $currentPageNumber + ($numberOfLink / 2);
            } else {
                $start = $currentPageNumber - floor($numberOfLink / 2);
                $end   = $currentPageNumber + floor($numberOfLink / 2);
            }
            if ($start <= 1) {
                $begin = 1;
                $end   = $numberOfLink;
            } else if ($start > 1 && $end < $numberOfPage) {
                $begin = $start;
            } else {
                $begin = ($numberOfPage - $numberOfLink) + 1;
                $end   = $numberOfPage;
            }
            if ($numberOfPage <= $numberOfLink) {
                $begin = 1;
                $end = $numberOfPage;
            }
            return array(
                        'begin' => (int) $begin,
                        'end' => (int) $end
                    );
        }

        /**
         * Build the pagination link for the page in the middle
         * @param  int $begin             the pagination begin number
         * @param  int $end               the pagination end number
         * @param  int $currentPageNumber the pagination current page number
         * @return string                    
         */
        protected function buildPaginationLinkForMiddlePage($begin, $end, $currentPageNumber) {
            $navbar = null;
            $query = $this->paginationQueryString;
            $navbar .= $this->config['previous_open'] 
                            . '<a href="' . $query . ($currentPageNumber - 1) . '">' 
                            . $this->config['previous_text'] . '</a>' . $this->config['previous_close'];
            for ($i = $begin; $i <= $end; $i++) {
                if ($i == $currentPageNumber) {
                    $navbar .= $this->config['active_link_open'] . $currentPageNumber . $this->config['active_link_close'];
                } else {
                    $navbar .= $this->config['digit_open'] 
                                    . '<a href="' . $query . $i . '"' 
                                    . attributes_to_string($this->config['attributes']) . '>' . $i . '</a>' 
                                    . $this->config['digit_close'];
                }
            }
            $navbar .= $this->config['next_open'] . '<a href="' . $query . ($currentPageNumber + 1) . '">' 
                       . $this->config['next_text'] . '</a>' . $this->config['next_close'];
            return $navbar;
        }


         /**
         * Build the pagination link for the first page
         * @see Pagination::buildPaginationLinkForFirstAndLastPage
         */
        protected function buildPaginationLinkForFirstPage($begin, $end, $currentPageNumber) {
            return $this->buildPaginationLinkForFirstAndLastPage($begin, $end, $currentPageNumber, 'first');
        }

        /**
         * Build the pagination link for the last page
         * @see Pagination::buildPaginationLinkForFirstAndLastPage
         */
        protected function buildPaginationLinkForLastPage($begin, $end, $currentPageNumber) {
            return $this->buildPaginationLinkForFirstAndLastPage($begin, $end, $currentPageNumber, 'last');
        }

        /**
         * Build the pagination link for the first and last page
         * 
         * @param  int $begin the pagination begin number
         * @param  int $end the pagination end number
         * @param  int $currentPageNumber the pagination current page number
         * @param string $type can be "first", "last"
         * 
         * @return string                    
         */
        protected function buildPaginationLinkForFirstAndLastPage($begin, $end, $currentPageNumber, $type = 'first') {
            $navbar = null;
            $query = $this->paginationQueryString;
            if ($type == 'last') {
                $navbar .= $this->config['previous_open'] 
                        . '<a href="' . $query . ($currentPageNumber - 1) . '">' 
                        . $this->config['previous_text'] . '</a>' . $this->config['previous_close'];
            }
            for ($i = $begin; $i <= $end; $i++) {
                if ($i == $currentPageNumber) {
                    $navbar .= $this->config['active_link_open'] . $currentPageNumber . $this->config['active_link_close'];
                } else {
                    $navbar .= $this->config['digit_open'] 
                            . '<a href="' . $query . $i . '"' 
                            . attributes_to_string($this->config['attributes']) . '>' . $i . '</a>' 
                            . $this->config['digit_close'];
                }
            }
            if ($type == 'first') {
                $navbar .= $this->config['next_open']
                            . '<a href="' . $query . ($currentPageNumber + 1) . '">' 
                            . $this->config['next_text'] . '</a>' . $this->config['next_close'];
            }
            return $navbar;
        }
    }
