<?php 

	/**
     * DatabaseQueryBuilder class tests
     *
     * @group core
     * @group database
     */
	class DatabaseQueryBuilderTest extends TnhTestCase{ 
        /**
        * Mock object of DatabaseConnection
        */
        private $connection = null;
        
        //The DatabaseQueryBuilder instance
        private $obj = null;
        
        public function __construct(){
            parent::__construct();
            
            $cfg = $this->getDbConfig();
            $this->connection = new DatabaseConnection($cfg, true);
        }
        
        protected function setUp(){ parent::setUp();
            $this->obj = new DatabaseQueryBuilder($this->connection);
		}
        
		public function testConstructor(){ //Default param DatabaseConnection not set
            $r = new DatabaseQueryBuilder();
            $this->assertNull($r->getConnection());
            
            
            $r = new DatabaseQueryBuilder($this->connection);
            $this->assertNotNull($r->getConnection());
            $this->assertInstanceOf('DatabaseConnection', $r->getConnection());
		}
        
        public function testFrom(){ $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //After each call to getQuery() need reset to default values
            $this->obj->reset();
            $value = 'foo';
            $expected = 'SELECT * FROM foo';
            $this->obj->from($value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //using multiple values with comma
            $this->obj->reset();
            $value = 'foo, bar';
            $expected = 'SELECT * FROM foo, bar';
            $this->obj->from($value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //using multiple values with array
            $this->obj->reset();
            $value = array('foo', 'bar');
            $expected = 'SELECT * FROM foo, bar';
            $this->obj->from($value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //overwrite existing one
            $this->obj->reset();
            $value = 'foo';
            $expected = 'SELECT * FROM bar';
            $this->obj->from($value);
            $this->obj->from('bar');
            $this->assertSame($expected, $this->obj->getQuery());
            
             //When prefix is set 
            $this->obj->reset();
            $this->assertNull($this->obj->getConnection()->getPrefix());
            $this->obj->getConnection()->setPrefix('pf_');
            $this->assertSame('pf_', $this->obj->getConnection()->getPrefix());
            
            $value = 'foo';
            $expected = 'SELECT * FROM pf_foo';
            $this->obj->from($value);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testSelect(){ 
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //After each call to getQuery() need reset to default values
            $this->obj->reset();
            $value = 'foo';
            $expected = 'SELECT foo FROM';
            $this->obj->select($value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //using multiple values with comma
            $this->obj->reset();
            $value = 'foo, bar';
            $expected = 'SELECT foo, bar FROM';
            $this->obj->select($value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //using multiple values with array
            $this->obj->reset();
            $value = array('foo', 'bar');
            $expected = 'SELECT foo, bar FROM';
            $this->obj->select($value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //update existing one
            $this->obj->reset();
            $value = 'foo';
            $expected = 'SELECT foo, bar FROM';
            $this->obj->select($value);
            $this->obj->select('bar');
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testDistinct(){ 
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //After each call to getQuery() need reset to default values
            $this->obj->reset();
            $value = 'foo';
            $expected = 'SELECT DISTINCT foo FROM';
            $this->obj->distinct($value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            
            //using existing select
            $this->obj->reset();
            $value = 'foo';
            $expected = 'SELECT DISTINCT foo, bar FROM';
            $this->obj->distinct($value);
            $this->obj->select('bar');
            $this->assertSame($expected, $this->obj->getQuery());
		}

        public function testCount(){ 
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //After each call to getQuery() need reset to default values
            $this->obj->reset();
            //default behavior
            $expected = 'SELECT COUNT(*) FROM';
            $this->obj->count();
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            $value = 'foo';
            $expected = 'SELECT COUNT(foo) FROM';
            $this->obj->count($value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using alias
            $this->obj->reset();
            $value = 'foo';
            $expected = 'SELECT COUNT(foo) AS bar FROM';
            $this->obj->count($value, 'bar');
            $this->assertSame($expected, $this->obj->getQuery());
            
            
            //using existing select
            $this->obj->reset();
            $value = 'foo';
            $expected = 'SELECT bar, COUNT(foo) FROM';
            $this->obj->select('bar');
            $this->obj->count($value);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testMin(){ 
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //After each call to getQuery() need reset to default values
            $this->obj->reset();
            $value = 'foo';
            $expected = 'SELECT MIN(foo) FROM';
            $this->obj->min($value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using alias
            $this->obj->reset();
            $value = 'foo';
            $expected = 'SELECT MIN(foo) AS bar FROM';
            $this->obj->min($value, 'bar');
            $this->assertSame($expected, $this->obj->getQuery());
            
            
            //using existing select
            $this->obj->reset();
            $value = 'foo';
            $expected = 'SELECT bar, MIN(foo) FROM';
            $this->obj->select('bar');
            $this->obj->min($value);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testMax(){ 
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //After each call to getQuery() need reset to default values
            $this->obj->reset();
            $value = 'foo';
            $expected = 'SELECT MAX(foo) FROM';
            $this->obj->max($value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using alias
            $this->obj->reset();
            $value = 'foo';
            $expected = 'SELECT MAX(foo) AS bar FROM';
            $this->obj->max($value, 'bar');
            $this->assertSame($expected, $this->obj->getQuery());
            
            
            //using existing select
            $this->obj->reset();
            $value = 'foo';
            $expected = 'SELECT bar, MAX(foo) FROM';
            $this->obj->select('bar');
            $this->obj->max($value);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testSum(){ 
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //After each call to getQuery() need reset to default values
            $this->obj->reset();
            $value = 'foo';
            $expected = 'SELECT SUM(foo) FROM';
            $this->obj->sum($value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using alias
            $this->obj->reset();
            $value = 'foo';
            $expected = 'SELECT SUM(foo) AS bar FROM';
            $this->obj->sum($value, 'bar');
            $this->assertSame($expected, $this->obj->getQuery());
            
            
            //using existing select
            $this->obj->reset();
            $value = 'foo';
            $expected = 'SELECT bar, SUM(foo) FROM';
            $this->obj->select('bar');
            $this->obj->sum($value);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testAvg(){ 
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //After each call to getQuery() need reset to default values
            $this->obj->reset();
            $value = 'foo';
            $expected = 'SELECT AVG(foo) FROM';
            $this->obj->avg($value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using alias
            $this->obj->reset();
            $value = 'foo';
            $expected = 'SELECT AVG(foo) AS bar FROM';
            $this->obj->avg($value, 'bar');
            $this->assertSame($expected, $this->obj->getQuery());
            
            
            //using existing select
            $this->obj->reset();
            $value = 'foo';
            $expected = 'SELECT bar, AVG(foo) FROM';
            $this->obj->select('bar');
            $this->obj->avg($value);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testJoin(){ 
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //After each call to getQuery() need reset to default values
            $this->obj->reset();
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM  JOIN foo ON bar.x = foo.y';
            $this->obj->join($value, $on);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //Using from
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM baz JOIN foo ON bar.x = foo.y';
            $this->obj->from('baz');
            $this->obj->join($value, $on);
            $this->assertSame($expected, $this->obj->getQuery());
            
            
            $this->obj->reset();
            //field 1 and 2 are set
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  JOIN foo ON bar.x = foo.y';
            $this->obj->join($value, $field1, '=', $field2);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //operator is an shortcut to field2, so default to "="
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  JOIN foo ON bar.x = foo.y';
            $this->obj->join($value, $field1, $field2);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //using existing one
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  JOIN foo ON bar.x = foo.y JOIN barbaz ON baz.a = foobar.b';
            $this->obj->join($value1, $field1, $field2);
            $this->obj->join($value2, $field3, $field4);
            $this->assertSame($expected, $this->obj->getQuery());
		}
    
        public function testInnerJoin(){ 
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //After each call to getQuery() need reset to default values
            $this->obj->reset();
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM  INNER JOIN foo ON bar.x = foo.y';
            $this->obj->innerJoin($value, $on);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //Using from
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM baz INNER JOIN foo ON bar.x = foo.y';
            $this->obj->from('baz');
            $this->obj->innerJoin($value, $on);
            $this->assertSame($expected, $this->obj->getQuery());
            
            
            $this->obj->reset();
            //field 1 and 2 are set
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  INNER JOIN foo ON bar.x = foo.y';
            $this->obj->innerJoin($value, $field1, '=', $field2);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //operator is an shortcut to field2, so default to "="
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  INNER JOIN foo ON bar.x = foo.y';
            $this->obj->innerJoin($value, $field1, $field2);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //using existing one
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  INNER JOIN foo ON bar.x = foo.y INNER JOIN barbaz ON baz.a = foobar.b';
            $this->obj->innerJoin($value1, $field1, $field2);
            $this->obj->innerJoin($value2, $field3, $field4);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //using existing JOIN
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  JOIN foo ON bar.x = foo.y INNER JOIN barbaz ON baz.a = foobar.b';
            $this->obj->join($value1, $field1, $field2);
            $this->obj->innerJoin($value2, $field3, $field4);
            $this->assertSame($expected, $this->obj->getQuery());
		}

        public function testLeftJoin(){ 
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //After each call to getQuery() need reset to default values
            $this->obj->reset();
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM  LEFT JOIN foo ON bar.x = foo.y';
            $this->obj->leftJoin($value, $on);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //Using from
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM baz LEFT JOIN foo ON bar.x = foo.y';
            $this->obj->from('baz');
            $this->obj->leftJoin($value, $on);
            $this->assertSame($expected, $this->obj->getQuery());
            
            
            $this->obj->reset();
            //field 1 and 2 are set
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  LEFT JOIN foo ON bar.x = foo.y';
            $this->obj->leftJoin($value, $field1, '=', $field2);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //operator is an shortcut to field2, so default to "="
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  LEFT JOIN foo ON bar.x = foo.y';
            $this->obj->leftJoin($value, $field1, $field2);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //using existing one
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  LEFT JOIN foo ON bar.x = foo.y LEFT JOIN barbaz ON baz.a = foobar.b';
            $this->obj->leftJoin($value1, $field1, $field2);
            $this->obj->leftJoin($value2, $field3, $field4);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //using existing JOIN
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  JOIN foo ON bar.x = foo.y LEFT JOIN barbaz ON baz.a = foobar.b';
            $this->obj->join($value1, $field1, $field2);
            $this->obj->leftJoin($value2, $field3, $field4);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testRightJoin(){ 
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //After each call to getQuery() need reset to default values
            $this->obj->reset();
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM  RIGHT JOIN foo ON bar.x = foo.y';
            $this->obj->rightJoin($value, $on);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //Using from
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM baz RIGHT JOIN foo ON bar.x = foo.y';
            $this->obj->from('baz');
            $this->obj->rightJoin($value, $on);
            $this->assertSame($expected, $this->obj->getQuery());
            
            
            $this->obj->reset();
            //field 1 and 2 are set
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  RIGHT JOIN foo ON bar.x = foo.y';
            $this->obj->rightJoin($value, $field1, '=', $field2);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //operator is an shortcut to field2, so default to "="
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  RIGHT JOIN foo ON bar.x = foo.y';
            $this->obj->rightJoin($value, $field1, $field2);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //using existing one
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  RIGHT JOIN foo ON bar.x = foo.y RIGHT JOIN barbaz ON baz.a = foobar.b';
            $this->obj->rightJoin($value1, $field1, $field2);
            $this->obj->rightJoin($value2, $field3, $field4);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //using existing JOIN
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  JOIN foo ON bar.x = foo.y RIGHT JOIN barbaz ON baz.a = foobar.b';
            $this->obj->join($value1, $field1, $field2);
            $this->obj->rightJoin($value2, $field3, $field4);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testFullOuterJoin(){ 
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //After each call to getQuery() need reset to default values
            $this->obj->reset();
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM  FULL OUTER JOIN foo ON bar.x = foo.y';
            $this->obj->fullOuterJoin($value, $on);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //Using from
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM baz FULL OUTER JOIN foo ON bar.x = foo.y';
            $this->obj->from('baz');
            $this->obj->fullOuterJoin($value, $on);
            $this->assertSame($expected, $this->obj->getQuery());
            
            
            $this->obj->reset();
            //field 1 and 2 are set
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  FULL OUTER JOIN foo ON bar.x = foo.y';
            $this->obj->fullOuterJoin($value, $field1, '=', $field2);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //operator is an shortcut to field2, so default to "="
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  FULL OUTER JOIN foo ON bar.x = foo.y';
            $this->obj->fullOuterJoin($value, $field1, $field2);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //using existing one
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  FULL OUTER JOIN foo ON bar.x = foo.y FULL OUTER JOIN barbaz ON baz.a = foobar.b';
            $this->obj->fullOuterJoin($value1, $field1, $field2);
            $this->obj->fullOuterJoin($value2, $field3, $field4);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //using existing JOIN
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  JOIN foo ON bar.x = foo.y FULL OUTER JOIN barbaz ON baz.a = foobar.b';
            $this->obj->join($value1, $field1, $field2);
            $this->obj->fullOuterJoin($value2, $field3, $field4);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testLeftOuterJoin(){ 
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //After each call to getQuery() need reset to default values
            $this->obj->reset();
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM  LEFT OUTER JOIN foo ON bar.x = foo.y';
            $this->obj->leftOuterJoin($value, $on);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //Using from
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM baz LEFT OUTER JOIN foo ON bar.x = foo.y';
            $this->obj->from('baz');
            $this->obj->leftOuterJoin($value, $on);
            $this->assertSame($expected, $this->obj->getQuery());
            
            
            $this->obj->reset();
            //field 1 and 2 are set
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  LEFT OUTER JOIN foo ON bar.x = foo.y';
            $this->obj->leftOuterJoin($value, $field1, '=', $field2);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //operator is an shortcut to field2, so default to "="
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  LEFT OUTER JOIN foo ON bar.x = foo.y';
            $this->obj->leftOuterJoin($value, $field1, $field2);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //using existing one
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  LEFT OUTER JOIN foo ON bar.x = foo.y LEFT OUTER JOIN barbaz ON baz.a = foobar.b';
            $this->obj->leftOuterJoin($value1, $field1, $field2);
            $this->obj->leftOuterJoin($value2, $field3, $field4);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //using existing JOIN
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  JOIN foo ON bar.x = foo.y LEFT OUTER JOIN barbaz ON baz.a = foobar.b';
            $this->obj->join($value1, $field1, $field2);
            $this->obj->leftOuterJoin($value2, $field3, $field4);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
         public function testRightOuterJoin(){ 
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //After each call to getQuery() need reset to default values
            $this->obj->reset();
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM  RIGHT OUTER JOIN foo ON bar.x = foo.y';
            $this->obj->rightOuterJoin($value, $on);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //Using from
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM baz RIGHT OUTER JOIN foo ON bar.x = foo.y';
            $this->obj->from('baz');
            $this->obj->rightOuterJoin($value, $on);
            $this->assertSame($expected, $this->obj->getQuery());
            
            
            $this->obj->reset();
            //field 1 and 2 are set
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  RIGHT OUTER JOIN foo ON bar.x = foo.y';
            $this->obj->rightOuterJoin($value, $field1, '=', $field2);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //operator is an shortcut to field2, so default to "="
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  RIGHT OUTER JOIN foo ON bar.x = foo.y';
            $this->obj->rightOuterJoin($value, $field1, $field2);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //using existing one
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  RIGHT OUTER JOIN foo ON bar.x = foo.y RIGHT OUTER JOIN barbaz ON baz.a = foobar.b';
            $this->obj->rightOuterJoin($value1, $field1, $field2);
            $this->obj->rightOuterJoin($value2, $field3, $field4);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            //using existing JOIN
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  JOIN foo ON bar.x = foo.y RIGHT OUTER JOIN barbaz ON baz.a = foobar.b';
            $this->obj->join($value1, $field1, $field2);
            $this->obj->rightOuterJoin($value2, $field3, $field4);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testWhereIsNull(){ 
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //After each call to getQuery() need reset to default values
            $this->obj->reset();
            $value = 'foo';
            $expected = 'SELECT * FROM  WHERE foo IS NULL';
            $this->obj->whereIsNull($value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using existing one
            $this->obj->reset();
            $value1 = 'foo';
            $value2 = 'bar';
            $expected = 'SELECT * FROM  WHERE foo IS NULL AND bar IS NULL';
            $this->obj->whereIsNull($value1);
            $this->obj->whereIsNull($value2);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using array default param (AND)
            $this->obj->reset();
            $value = array('foo', 'bar');
            $expected = 'SELECT * FROM  WHERE foo IS NULL AND bar IS NULL';
            $this->obj->whereIsNull($value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using array default with param)
            $this->obj->reset();
            $value = array('foo', 'bar');
            $expected = 'SELECT * FROM  WHERE foo IS NULL OR bar IS NULL';
            $this->obj->whereIsNull($value, 'OR');
            $this->assertSame($expected, $this->obj->getQuery());  
		}
        
        public function testWhereIsNotNull(){ 
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //After each call to getQuery() need reset to default values
            $this->obj->reset();
            $value = 'foo';
            $expected = 'SELECT * FROM  WHERE foo IS NOT NULL';
            $this->obj->whereIsNotNull($value);
            $this->assertSame($expected, $this->obj->getQuery());
            
             //Using existing one
            $this->obj->reset();
            $value1 = 'foo';
            $value2 = 'bar';
            $expected = 'SELECT * FROM  WHERE foo IS NOT NULL AND bar IS NOT NULL';
            $this->obj->whereIsNotNull($value1);
            $this->obj->whereIsNotNull($value2);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using array default param (AND)
            $this->obj->reset();
            $value = array('foo', 'bar');
            $expected = 'SELECT * FROM  WHERE foo IS NOT NULL AND bar IS NOT NULL';
            $this->obj->whereIsNotNull($value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using array default with param)
            $this->obj->reset();
            $value = array('foo', 'bar');
            $expected = 'SELECT * FROM  WHERE foo IS NOT NULL OR bar IS NOT NULL';
            $this->obj->whereIsNotNull($value, 'OR');
            $this->assertSame($expected, $this->obj->getQuery()); 


            //Combinaison of whereIsNotNull and whereIsNull (Default value for 2nd param)
            $this->obj->reset();
            $value1 = 'foo';
            $value2 = 'bar';
            $expected = 'SELECT * FROM  WHERE foo IS NOT NULL AND bar IS NULL';
            $this->obj->whereIsNotNull($value1);
            $this->obj->whereIsNull($value2);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Combinaison of whereIsNotNull and whereIsNull (set value for 2nd param)
            $this->obj->reset();
            $value1 = 'foo';
            $value2 = 'bar';
            $expected = 'SELECT * FROM  WHERE foo IS NOT NULL OR bar IS NULL';
            $this->obj->whereIsNotNull($value1);
            $this->obj->whereIsNull($value2, 'OR');
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testWhere(){ $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //After each call to getQuery() need reset to default values
            $this->obj->reset();
            $field = 'foo';
            $value = 'bar';
            $expected = "SELECT * FROM  WHERE foo = 'bar'";
            $this->obj->where($field, $value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using null value
            $this->obj->reset();
            $field = 'foo';
            $value = null;
            $expected = "SELECT * FROM  WHERE foo = ''";
            $this->obj->where($field, $value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using array 
            $this->obj->reset();
            $value = array('foo' => 'bar', 'bar' => 'foo');
            $expected = "SELECT * FROM  WHERE foo = 'bar' AND bar = 'foo'";
            $this->obj->where($value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Dont escape the value
            $this->obj->reset();
            $field = 'date';
            $value = 'CURRENT_TIMESTAMP()';
            $expected = "SELECT * FROM  WHERE date >= CURRENT_TIMESTAMP()";
            $this->obj->where($field, '>=', $value, '', 'AND', false);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using "?" in place of value
            $this->obj->reset();
            $field = 'foo = ? AND bar = ?';
            $value = array('12', 'abc');
            $expected = "SELECT * FROM  WHERE foo = '12' AND bar = 'abc'";
            $this->obj->where($field, $value);
            $this->assertSame($expected, $this->obj->getQuery());    
		}
        
        public function testOrWhere(){ $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //After each call to getQuery() need reset to default values
            $this->obj->reset();
            $field = 'foo';
            $value = 'bar';
            $expected = "SELECT * FROM  WHERE foo = 'bar'";
            $this->obj->orWhere($field, $value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using array 
            $this->obj->reset();
            $value = array('foo' => 'bar', 'bar' => 'foo');
            $expected = "SELECT * FROM  WHERE foo = 'bar' OR bar = 'foo'";
            $this->obj->orWhere($value);
            $this->assertSame($expected, $this->obj->getQuery());
            
		}
        
        public function testNotWhere(){ $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //After each call to getQuery() need reset to default values
            $this->obj->reset();
            $field = 'foo';
            $value = 'bar';
            $expected = "SELECT * FROM  WHERE NOT foo = 'bar'";
            $this->obj->notWhere($field, $value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using array 
            $this->obj->reset();
            $value = array('foo' => 'bar', 'bar' => 'foo');
            $expected = "SELECT * FROM  WHERE NOT foo = 'bar' AND NOT bar = 'foo'";
            $this->obj->notWhere($value);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testOrNotWhere(){ $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //After each call to getQuery() need reset to default values
            $this->obj->reset();
            $field = 'foo';
            $value = 'bar';
            $expected = "SELECT * FROM  WHERE NOT foo = 'bar'";
            $this->obj->orNotWhere($field, $value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using array 
            $this->obj->reset();
            $value = array('foo' => 'bar', 'bar' => 'foo');
            $expected = "SELECT * FROM  WHERE NOT foo = 'bar' OR NOT bar = 'foo'";
            $this->obj->orNotWhere($value);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testGroupStartAndEnd(){ $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //After each call to getQuery() need reset to default values
            $this->obj->reset();
            $field = 'foo';
            $value = 'bar';
            $expected = "SELECT * FROM  WHERE foo = 'bar'";
            $this->obj->where($field, $value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using with existing WHERE
            $this->obj->reset();
            $field1 = 'foo';
            $field2 = 'baz';
            $value1 = '134';
            $value2 = 'abc';
            $expected = "SELECT * FROM  WHERE foo = '134' AND (baz = 'abc')";
            $this->obj->where($field1, $value1);
            $this->obj->groupStart();
            $this->obj->where($field2, $value2);
            $this->obj->groupEnd();
            $this->assertSame($expected, $this->obj->getQuery());
            
            //When existing WHERE does not set before
            $this->obj->reset();
            $field1 = 'foo';
            $field2 = 'baz';
            $value1 = '134';
            $value2 = 'abc';
            $expected = "SELECT * FROM  WHERE (foo = '134' AND baz = 'abc')";
            $this->obj->groupStart();
            $this->obj->where($field1, $value1);
            $this->obj->where($field2, $value2);
            $this->obj->groupEnd();
            $this->assertSame($expected, $this->obj->getQuery());
            
            
            //Using consecutive groupe
            $this->obj->reset();
            $field1 = 'foo';
            $field2 = 'baz';
            $field3 = 'foobar';
            $field4 = 'name';
            $value1 = '134';
            $value2 = 'abc';
            $value3 = 'xy';
            $value4 = '987';
            $expected = "SELECT * FROM  WHERE (NOT(foo = '134' OR baz = 'abc') AND foobar = 'xy') AND NOT name = '987'";
            $this->obj->groupStart();
            $this->obj->orNotGroupStart();
            $this->obj->where($field1, $value1);
            $this->obj->orWhere($field2, $value2);
            $this->obj->groupEnd();
            $this->obj->where($field3, $value3);
            $this->obj->groupEnd();
            $this->obj->notWhere($field4, $value4);
            $this->assertSame($expected, $this->obj->getQuery());
            
            
            //Using type param
            $this->obj->reset();
            $field1 = 'foo';
            $field2 = 'baz';
            $value1 = '134';
            $value2 = 'abc';
            $expected = "SELECT * FROM  WHERE foo = '134' AND NOT (baz = 'abc')";
            $this->obj->where($field1, $value1);
            $this->obj->notGroupStart();
            $this->obj->where($field2, $value2);
            $this->obj->groupEnd();
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using 2nd param
            $this->obj->reset();
            $field1 = 'foo';
            $field2 = 'baz';
            $value1 = '134';
            $value2 = 'abc';
            $expected = "SELECT * FROM  WHERE foo = '134' OR (baz = 'abc')";
            $this->obj->where($field1, $value1);
            $this->obj->orGroupStart();
            $this->obj->where($field2, $value2);
            $this->obj->groupEnd();
            $this->assertSame($expected, $this->obj->getQuery());
            
            
		}
        
        public function testIn(){ $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Numeric value
            $this->obj->reset();
            $field = 'foo';
            $value = array(3, 4, 89);
            $expected = "SELECT * FROM  WHERE foo IN (3, 4, 89)";
            $this->obj->in($field, $value);
            $this->assertSame($expected, $this->obj->getQuery());
            
             //String value
            $this->obj->reset();
            $field = 'foo';
            $value = array('a', 'bc', 'baz');
            $expected = "SELECT * FROM  WHERE foo IN ('a', 'bc', 'baz')";
            $this->obj->in($field, $value);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testNotIn(){ $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Numeric value
            $this->obj->reset();
            $field = 'foo';
            $value = array(3, 4, 89);
            $expected = "SELECT * FROM  WHERE foo NOT IN (3, 4, 89)";
            $this->obj->notIn($field, $value);
            $this->assertSame($expected, $this->obj->getQuery());
            
             //String value
            $this->obj->reset();
            $field = 'foo';
            $value = array('a', 'bc', 'baz');
            $expected = "SELECT * FROM  WHERE foo NOT IN ('a', 'bc', 'baz')";
            $this->obj->notIn($field, $value);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testOrIn(){ $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using existing where
            $this->obj->reset();
            $field = 'foo';
            $value = array(3, 4, 89);
            $field1 = 'foo';
            $value1 = '134';
            $expected = "SELECT * FROM  WHERE foo = '134' OR foo IN (3, 4, 89)";
            $this->obj->where($field1, $value1);
            $this->obj->orIn($field, $value);
            $this->assertSame($expected, $this->obj->getQuery());
            
             //WHERE is not set before
            $this->obj->reset();
            $field = 'foo';
            $value = array('a', 'bc', 'baz');
            $expected = "SELECT * FROM  WHERE foo IN ('a', 'bc', 'baz')";
            $this->obj->orIn($field, $value);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
         public function testOrNotIn(){ $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using existing where
            $this->obj->reset();
            $field = 'foo';
            $value = array(3, 4, 89);
            $field1 = 'foo';
            $value1 = '134';
            $expected = "SELECT * FROM  WHERE foo = '134' OR foo NOT IN (3, 4, 89)";
            $this->obj->where($field1, $value1);
            $this->obj->orNotIn($field, $value);
            $this->assertSame($expected, $this->obj->getQuery());
            
             //WHERE is not set before
            $this->obj->reset();
            $field = 'foo';
            $value = array('a', 'bc', 'baz');
            $expected = "SELECT * FROM  WHERE foo NOT IN ('a', 'bc', 'baz')";
            $this->obj->orNotIn($field, $value);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        
        public function testBetween(){ $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using existing where
            $this->obj->reset();
            $fieldw = 'bar';
            $value = 45;
            $field = 'foo';
            $value1 = '12';
            $value2 = '134';
            $expected = "SELECT * FROM  WHERE bar = '45' AND foo BETWEEN '12' AND '134'";
            $this->obj->where($fieldw, $value);
            $this->obj->between($field, $value1, $value2);
            $this->assertSame($expected, $this->obj->getQuery());
            
             //WHERE is not set before
            $this->obj->reset();
            $field = 'foo';
            $value1 = '12';
            $value2 = '134';
            $expected = "SELECT * FROM  WHERE foo BETWEEN '12' AND '134'";
            $this->obj->between($field, $value1, $value2);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testOrBetween(){ $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using existing where
            $this->obj->reset();
            $fieldw = 'bar';
            $value = 45;
            $field = 'foo';
            $value1 = '12';
            $value2 = '134';
            $expected = "SELECT * FROM  WHERE bar = '45' OR foo BETWEEN '12' AND '134'";
            $this->obj->where($fieldw, $value);
            $this->obj->orBetween($field, $value1, $value2);
            $this->assertSame($expected, $this->obj->getQuery());
            
             //WHERE is not set before
            $this->obj->reset();
            $field = 'foo';
            $value1 = '12';
            $value2 = '134';
            $expected = "SELECT * FROM  WHERE foo BETWEEN '12' AND '134'";
            $this->obj->orBetween($field, $value1, $value2);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testNotBetween(){ $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using existing where
            $this->obj->reset();
            $fieldw = 'bar';
            $value = 45;
            $field = 'foo';
            $value1 = '12';
            $value2 = '134';
            $expected = "SELECT * FROM  WHERE bar = '45' AND foo NOT BETWEEN '12' AND '134'";
            $this->obj->where($fieldw, $value);
            $this->obj->notBetween($field, $value1, $value2);
            $this->assertSame($expected, $this->obj->getQuery());
            
             //WHERE is not set before
            $this->obj->reset();
            $field = 'foo';
            $value1 = '12';
            $value2 = '134';
            $expected = "SELECT * FROM  WHERE foo NOT BETWEEN '12' AND '134'";
            $this->obj->notBetween($field, $value1, $value2);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testOrNotBetween(){ $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using existing where
            $this->obj->reset();
            $fieldw = 'bar';
            $value = 45;
            $field = 'foo';
            $value1 = '12';
            $value2 = '134';
            $expected = "SELECT * FROM  WHERE bar = '45' OR foo NOT BETWEEN '12' AND '134'";
            $this->obj->where($fieldw, $value);
            $this->obj->orNotBetween($field, $value1, $value2);
            $this->assertSame($expected, $this->obj->getQuery());
            
             //WHERE is not set before
            $this->obj->reset();
            $field = 'foo';
            $value1 = '12';
            $value2 = '134';
            $expected = "SELECT * FROM  WHERE foo NOT BETWEEN '12' AND '134'";
            $this->obj->orNotBetween($field, $value1, $value2);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testLike(){ $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using existing where
            $this->obj->reset();
            $fieldw = 'bar';
            $value = 45;
            $field = 'foo';
            $value1 = '%baz';
            $expected = "SELECT * FROM  WHERE bar = '45' AND foo LIKE '%baz'";
            $this->obj->where($fieldw, $value);
            $this->obj->like($field, $value1);
            $this->assertSame($expected, $this->obj->getQuery());
            
             //WHERE is not set before
            $this->obj->reset();
            $field = 'foo';
            $value1 = '%abc%';
            $expected = "SELECT * FROM  WHERE foo LIKE '%abc%'";
            $this->obj->like($field, $value1);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testOrLike(){ $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using existing where
            $this->obj->reset();
            $fieldw = 'bar';
            $value = 45;
            $field = 'foo';
            $value1 = '%baz';
            $expected = "SELECT * FROM  WHERE bar = '45' OR foo LIKE '%baz'";
            $this->obj->where($fieldw, $value);
            $this->obj->orLike($field, $value1);
            $this->assertSame($expected, $this->obj->getQuery());
            
             //WHERE is not set before
            $this->obj->reset();
            $field = 'foo';
            $value1 = '%abc%';
            $expected = "SELECT * FROM  WHERE foo LIKE '%abc%'";
            $this->obj->orLike($field, $value1);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testNotLike(){ $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using existing where
            $this->obj->reset();
            $fieldw = 'bar';
            $value = 45;
            $field = 'foo';
            $value1 = '%baz';
            $expected = "SELECT * FROM  WHERE bar = '45' AND foo NOT LIKE '%baz'";
            $this->obj->where($fieldw, $value);
            $this->obj->notLike($field, $value1);
            $this->assertSame($expected, $this->obj->getQuery());
            
             //WHERE is not set before
            $this->obj->reset();
            $field = 'foo';
            $value1 = '%abc%';
            $expected = "SELECT * FROM  WHERE foo NOT LIKE '%abc%'";
            $this->obj->notLike($field, $value1);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testOrNotLike(){ $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using existing where
            $this->obj->reset();
            $fieldw = 'bar';
            $value = 45;
            $field = 'foo';
            $value1 = '%baz';
            $expected = "SELECT * FROM  WHERE bar = '45' OR foo NOT LIKE '%baz'";
            $this->obj->where($fieldw, $value);
            $this->obj->orNotLike($field, $value1);
            $this->assertSame($expected, $this->obj->getQuery());
            
             //WHERE is not set before
            $this->obj->reset();
            $field = 'foo';
            $value1 = '%abc%';
            $expected = "SELECT * FROM  WHERE foo NOT LIKE '%abc%'";
            $this->obj->orNotLike($field, $value1);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testLimit(){ $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
           //Using only the first param
            $this->obj->reset();
            $expected = "SELECT * FROM  LIMIT 10";
            $this->obj->limit(10);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //param is null or empty
            $this->obj->reset();
            $expected = "SELECT * FROM";
            $this->obj->limit(null);
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            $expected = "SELECT * FROM  LIMIT 7, 10";
            $this->obj->limit(7, 10);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testOrderBy(){ $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
           //Using only the first param
            $this->obj->reset();
            $field = 'foo';
            $dir = 'ASC';
            $expected = "SELECT * FROM  ORDER BY foo ASC";
            $this->obj->orderBy($field);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using existing one
            $this->obj->reset();
            $field1 = 'foo';
            $field2 = 'bar';
            $dir1 = 'ASC';
            $dir2 = 'DESC';
            $expected = "SELECT * FROM  ORDER BY foo ASC, bar DESC";
            $this->obj->orderBy($field1, $dir1);
            $this->obj->orderBy($field2, $dir2);
            $this->assertSame($expected, $this->obj->getQuery());
            
             //Using rand()
            $this->obj->reset();
            $field = 'rand()';
            $dir = 'ASC';
            $expected = "SELECT * FROM  ORDER BY rand()";
            $this->obj->orderBy($field);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        public function testGroupBy(){ $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            $this->obj->reset();
            $field = 'foo';
            $expected = "SELECT * FROM  GROUP BY foo";
            $this->obj->groupBy($field);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using array
            $this->obj->reset();
            $fields = array('bar', 'foo');
            $expected = "SELECT * FROM  GROUP BY bar, foo";
            $this->obj->groupBy($fields);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Overwrite existing one
            $this->obj->reset();
            $field1 = 'foo';
            $field2 = 'bar';
            $expected = "SELECT * FROM  GROUP BY bar";
            $this->obj->groupBy($field1);
            $this->obj->groupBy($field2);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        
         public function testHaving(){ $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            //After each call to getQuery() need reset to default values
            $this->obj->reset();
            $field = 'COUNT(bar)';
            $value = '34';
            $expected = "SELECT * FROM  HAVING COUNT(bar) > '34'";
            $this->obj->having($field, $value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using null value
            $this->obj->reset();
            $field = 'COUNT(foo)';
            $value = null;
            $expected = "SELECT * FROM  HAVING COUNT(foo) > ''";
            $this->obj->having($field, $value);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Dont escape the value
            $this->obj->reset();
            $field = 'MIN(date)';
            $value = 'CURRENT_TIMESTAMP()';
            $expected = "SELECT * FROM  HAVING MIN(date) >= CURRENT_TIMESTAMP()";
            $this->obj->having($field, '>=', $value, false);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using "?" in place of value
            $this->obj->reset();
            $field = 'COUNT(foo) = ? AND SUM(bar) > ? OR AVG(baz) <= ?';
            $value = array(10, 367);
            $expected = "SELECT * FROM  HAVING COUNT(foo) = '10' AND SUM(bar) > '367' OR AVG(baz) <= ''";
            $this->obj->having($field, $value);
            $this->assertSame($expected, $this->obj->getQuery());    
		}
        
        public function testInsert(){ $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            $data = array(
                'foo' => 'bar',
                'bar' => 3766
            );
            $table = 'footable';
            
            $expected = "INSERT INTO footable(foo, bar) VALUES ('bar', '3766')";
            $this->obj->from($table);
            $this->obj->insert($data);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Dont escape
            $data = array(
                'foo' => 'NOW()',
                'bar' => 'CURRENT_TIMESTAMP()'
            );
            $expected = "INSERT INTO footable(foo, bar) VALUES (NOW(), CURRENT_TIMESTAMP())";
            $this->obj->from($table);
            $this->obj->insert($data, false);
            $this->assertSame($expected, $this->obj->getQuery()); 
		}
        
        public function testUpdate(){ $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            $data = array(
                'foo' => 'bar',
                'bar' => 3766
            );
            $table = 'footable';
            
            $expected = "UPDATE footable SET foo = 'bar', bar = '3766'";
            $this->obj->from($table);
            $this->obj->update($data);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Dont escape
            $data = array(
                'foo' => 'NOW()',
                'bar' => 'CURRENT_TIMESTAMP()'
            );
            $expected = "UPDATE footable SET foo = NOW(), bar = CURRENT_TIMESTAMP()";
            $this->obj->from($table);
            $this->obj->update($data, false);
            $this->assertSame($expected, $this->obj->getQuery());
            
            //Using LIMIT, ORDER BY, WHERE
            $data = array(
                'foo' => 'bar',
                'bar' => 3766
            );
            $fieldw = 'baz';
            $fieldob = 'obfoo';
            $valuew = '12';
            $expected = "UPDATE footable SET foo = 'bar', bar = '3766' WHERE baz = '12' ORDER BY obfoo ASC LIMIT 3, 14";
            $this->obj->from($table);
            $this->obj->where($fieldw, $valuew);
            $this->obj->orderBy($fieldob);
            $this->obj->limit(3, 14);
            $this->obj->update($data);
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
        
        public function testDelete(){
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $this->obj->getQuery());
            
            $table = 'footable';
            
            //Default behavior is to truncate if no where
            //Note currently the value of driver is "sqlite" need change it first
            $this->obj->getConnection()->setDriver(null);
            $this->assertNull($this->obj->getConnection()->getDriver());
            
            $expected = "TRUNCATE TABLE footable";
            $this->obj->from($table);
            $this->obj->delete();
            $this->assertSame($expected, $this->obj->getQuery());
            
            //When driver is "sqlite"
            $this->assertNull($this->obj->getConnection()->getDriver());
            $this->obj->getConnection()->setDriver('sqlite');
            $this->assertSame('sqlite', $this->obj->getConnection()->getDriver());
            $expected = "DELETE FROM footable";
            $this->obj->from($table);
            $this->obj->delete();
            $this->assertSame($expected, $this->obj->getQuery());
            
            //restore driver to null value
            $this->obj->getConnection()->setDriver(null);
            $this->assertNull($this->obj->getConnection()->getDriver());
            
     
            //Using LIMIT, ORDER BY, WHERE
            $fieldw = 'baz';
            $fieldob = 'obfoo';
            $valuew = '12';
            $expected = "DELETE FROM footable WHERE baz = '12' ORDER BY obfoo ASC LIMIT 3, 14";
            $this->obj->from($table);
            $this->obj->where($fieldw, $valuew);
            $this->obj->orderBy($fieldob);
            $this->obj->limit(3, 14);
            $this->obj->delete();
            $this->assertSame($expected, $this->obj->getQuery());
		}
        
       
	}