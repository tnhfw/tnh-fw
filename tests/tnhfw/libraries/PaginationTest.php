<?php 

	/**
     * Pagination library class tests
     *
     * @group core
     * @group libraries
     * @group html
     */
	class PaginationTest extends TnhTestCase {	
	
		public function testContructor() {
            $p = new Pagination();
			$this->assertArrayHasKey('pagination_per_page', $p->getConfig());
		}
        
        public function testContructorWithOverwriteConfig() {
            $p = new Pagination();
            //Default config fog "pagination_per_page" is 10 in "tests/app/config/config_pagination.php"
            $this->assertArrayHasKey('pagination_per_page', $p->getConfig());
            
            $value = $this->config->get('pagination_per_page');
            $this->assertSame(10, $value);
           
            //Now overwriten config 
			$cfg = array(
                'pagination_per_page' => 20
            );
            $p = new Pagination($cfg);
            //Default config fog "pagination_per_page" is 10 in "tests/app/config/config_pagination.php"
            $this->assertArrayHasKey('pagination_per_page', $p->getConfig());
			$value = $this->config->get('pagination_per_page');
            $this->assertSame(20, $value);
		}
        
        public function testSetConfig() {
            $p = new Pagination();
            //Default config fog "pagination_per_page" is 10 in "tests/app/config/config_pagination.php"
            $this->assertArrayHasKey('pagination_per_page', $p->getConfig());
            
            $value = $this->config->get('pagination_per_page');
            $this->assertSame(10, $value);
           
            //Now set config 
			$cfg = array(
                'pagination_per_page' => 20
            );
            $p->setConfig($cfg);
            //Default config fog "pagination_per_page" is 10 in "tests/app/config/config_pagination.php"
            $this->assertArrayHasKey('pagination_per_page', $p->getConfig());
			$value = $this->config->get('pagination_per_page');
            $this->assertSame(20, $value);
		}
        
         public function testSetPaginationQueryString() {
             $p = new Pagination();
             $this->assertNull($p->getPaginationQueryString());
             $p->setPaginationQueryString('foo');
             $this->assertSame('foo', $p->getPaginationQueryString());
         }
         
         public function testDeterminePaginationQueryStringValue() {
             $defaultCurrentUrl = 'http://localhost/';
             $p = new Pagination();
             $this->assertNull($p->getPaginationQueryString());
             $p->determinePaginationQueryStringValue();
             //default value is 'http://localhost/' for Url::current() and query string is empty so just
             //append page?=
             $this->assertSame($defaultCurrentUrl . '?page=', $p->getPaginationQueryString());
             
             //When query string is not null and not contain 'queryPageName='
              $qs = 'foo=bar&bar=baz';
              //Note Url::queryString() use internally Request::server that apply 
              //XSS filter by default so char '&'will be '&amp;' 
              $qsExpected = 'foo=bar&amp;bar=baz';
              $_SERVER['QUERY_STRING'] = $qs;
            
              $obj = & get_instance();
              $obj->request = new Request();
            
              $p->determinePaginationQueryStringValue();
             
              $this->assertSame($defaultCurrentUrl . '?' . $qsExpected . '&page=', $p->getPaginationQueryString());
             
               //When query string is not null and contains 'queryPageName='
              $qs = 'foo=bar&bar=baz&page=3';
              //Note Url::queryString() use internally Request::server that apply 
              //XSS filter by default so char '&'will be '&amp;' 
              $qsExpected = 'foo=bar&amp;bar=baz&amp;page=';
              $_SERVER['QUERY_STRING'] = $qs;
            
             $obj = & get_instance();
             $obj->request = new Request();
            
             $p->determinePaginationQueryStringValue();
             
             $this->assertSame($defaultCurrentUrl . '?' . $qsExpected, $p->getPaginationQueryString());
             
             //When query string is not null and contains 'queryPageName=' without other query string
             $qs = 'page=3';
             //Note Url::queryString() use internally Request::server that apply 
             //XSS filter by default so char '&'will be '&amp;' 
             $qsExpected = 'page=';
             $_SERVER['QUERY_STRING'] = $qs;
            
             $obj = & get_instance();
             $obj->request = new Request();
            
             $p->determinePaginationQueryStringValue();
             
             $this->assertSame($defaultCurrentUrl . '?' . $qsExpected, $p->getPaginationQueryString());
         }
         
          public function testGetLink() {
             $p = new Pagination();
             //Default behavior is to return null
             $this->assertNull($p->getLink(0, 0));
             //totalRows is less than or equal to number of pagination_per_page
             $this->assertNull($p->getLink(1, 1)); 
             $this->assertNull($p->getLink(9, 1));
             $this->assertNull($p->getLink(10, 1));
             
             //Active link is in the first page
             $expected = '<ul class = "pagination">'
                         . '<li class = "active"><a href = "#">1</a></li>'
                         . '<li><a href="http://localhost/?page=2">2</a></li>'
                         . '<li><a href="http://localhost/?page=2">Next</a></li>'
                         . '</ul>';
             $this->assertSame($expected, $p->getLink(11, 1));
             
             //Active link is in the middle page
             $expected = '<ul class = "pagination">'
                         . '<li><a href="http://localhost/?page=1">Previous</a></li>'
                         . '<li><a href="http://localhost/?page=1">1</a></li>'
                         . '<li class = "active"><a href = "#">2</a></li>'
                         . '<li><a href="http://localhost/?page=3">3</a></li>'
                         . '<li><a href="http://localhost/?page=3">Next</a></li>'
                         . '</ul>';
             $this->assertSame($expected, $p->getLink(30, 2));
             
             //Active link is in the last page
             $expected = '<ul class = "pagination">'
                         . '<li><a href="http://localhost/?page=1">Previous</a></li>'
                         . '<li><a href="http://localhost/?page=1">1</a></li>'
                         . '<li class = "active"><a href = "#">2</a></li>'
                         . '</ul>';
             $this->assertSame($expected, $p->getLink(11, 2));
             
             
             //When number of link is not module % 2
             $cfg = array(
                'nb_link' => 1,
                'pagination_per_page' => 3
            );
            $p->setConfig($cfg);
            $expected = '<ul class = "pagination">'
                         . '<li><a href="http://localhost/?page=1">Previous</a></li>'
                         . '<li class = "active"><a href = "#">2</a></li>'
                         . '<li><a href="http://localhost/?page=3">Next</a></li>'
                         . '</ul>';
            $this->assertSame($expected, $p->getLink(30, 2));
             
             
            $expected = '<ul class = "pagination">'
                         . '<li><a href="http://localhost/?page=1">Previous</a></li>'
                         . '<li class = "active"><a href = "#">2</a></li>'
                         . '</ul>';
            $this->assertSame($expected, $p->getLink(4, 2)); 
         }
         
	}