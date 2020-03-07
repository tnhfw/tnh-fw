<?php 

	/**
     * DatabaseQueryBuilder class tests
     *
     * @group core
     * @group database
     */
	class DatabaseQueryBuilderTest extends TnhTestCase {

        /**
        * Mock object of PDO
        */
        private $pdo = null;
        
        public function __construct(){
            parent::__construct();
            
            $pdo = $this->getMockBuilder('PDO')
                        ->disableOriginalConstructor()
                        ->getMock();
                        
            $pdo->expects($this->any())
                    ->method('quote')
                    ->will($this->returnCallback(array($this, 'mockPdoMethodQuoteReturnCallback')));
                    
            $this->pdo = $pdo;
        }
        
		public function testConstructor() {
            //Default param PDO not set
            $r = new DatabaseQueryBuilder();
            $this->assertNull($r->getPdo());
            
            
            $r = new DatabaseQueryBuilder($this->pdo);
            $this->assertNotNull($r->getPdo());
            $this->assertInstanceOf('PDO', $r->getPdo());
		}
        
        public function testFrom() {
            $r = new DatabaseQueryBuilder();
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //After each call to getQuery() need reset to default values
            $r->reset();
            $value = 'foo';
            $expected = 'SELECT * FROM foo';
            $r->from($value);
            $this->assertSame($expected, $r->getQuery());
            
            //using multiple values with comma
            $r->reset();
            $value = 'foo, bar';
            $expected = 'SELECT * FROM foo, bar';
            $r->from($value);
            $this->assertSame($expected, $r->getQuery());
            
            //using multiple values with array
            $r->reset();
            $value = array('foo', 'bar');
            $expected = 'SELECT * FROM foo, bar';
            $r->from($value);
            $this->assertSame($expected, $r->getQuery());
            
            //overwrite existing one
            $r->reset();
            $value = 'foo';
            $expected = 'SELECT * FROM bar';
            $r->from($value);
            $r->from('bar');
            $this->assertSame($expected, $r->getQuery());
            
             //When prefix is set 
            $r->reset();
            $this->assertNull($r->getPrefix());
            $r->setPrefix('pf_');
            $this->assertSame('pf_', $r->getPrefix());
            
            $value = 'foo';
            $expected = 'SELECT * FROM pf_foo';
            $r->from($value);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testSelect() {
            $r = new DatabaseQueryBuilder();
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //After each call to getQuery() need reset to default values
            $r->reset();
            $value = 'foo';
            $expected = 'SELECT foo FROM';
            $r->select($value);
            $this->assertSame($expected, $r->getQuery());
            
            //using multiple values with comma
            $r->reset();
            $value = 'foo, bar';
            $expected = 'SELECT foo, bar FROM';
            $r->select($value);
            $this->assertSame($expected, $r->getQuery());
            
            //using multiple values with array
            $r->reset();
            $value = array('foo', 'bar');
            $expected = 'SELECT foo, bar FROM';
            $r->select($value);
            $this->assertSame($expected, $r->getQuery());
            
            //update existing one
            $r->reset();
            $value = 'foo';
            $expected = 'SELECT foo, bar FROM';
            $r->select($value);
            $r->select('bar');
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testDistinct() {
            $r = new DatabaseQueryBuilder();
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //After each call to getQuery() need reset to default values
            $r->reset();
            $value = 'foo';
            $expected = 'SELECT DISTINCT foo FROM';
            $r->distinct($value);
            $this->assertSame($expected, $r->getQuery());
            
            
            //using existing select
            $r->reset();
            $value = 'foo';
            $expected = 'SELECT DISTINCT foo, bar FROM';
            $r->distinct($value);
            $r->select('bar');
            $this->assertSame($expected, $r->getQuery());
		}

        public function testCount() {
            $r = new DatabaseQueryBuilder();
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //After each call to getQuery() need reset to default values
            $r->reset();
            //default behavior
            $expected = 'SELECT COUNT(*) FROM';
            $r->count();
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            $value = 'foo';
            $expected = 'SELECT COUNT(foo) FROM';
            $r->count($value);
            $this->assertSame($expected, $r->getQuery());
            
            //Using alias
            $r->reset();
            $value = 'foo';
            $expected = 'SELECT COUNT(foo) AS bar FROM';
            $r->count($value, 'bar');
            $this->assertSame($expected, $r->getQuery());
            
            
            //using existing select
            $r->reset();
            $value = 'foo';
            $expected = 'SELECT bar, COUNT(foo) FROM';
            $r->select('bar');
            $r->count($value);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testMin() {
            $r = new DatabaseQueryBuilder();
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //After each call to getQuery() need reset to default values
            $r->reset();
            $value = 'foo';
            $expected = 'SELECT MIN(foo) FROM';
            $r->min($value);
            $this->assertSame($expected, $r->getQuery());
            
            //Using alias
            $r->reset();
            $value = 'foo';
            $expected = 'SELECT MIN(foo) AS bar FROM';
            $r->min($value, 'bar');
            $this->assertSame($expected, $r->getQuery());
            
            
            //using existing select
            $r->reset();
            $value = 'foo';
            $expected = 'SELECT bar, MIN(foo) FROM';
            $r->select('bar');
            $r->min($value);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testMax() {
            $r = new DatabaseQueryBuilder();
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //After each call to getQuery() need reset to default values
            $r->reset();
            $value = 'foo';
            $expected = 'SELECT MAX(foo) FROM';
            $r->max($value);
            $this->assertSame($expected, $r->getQuery());
            
            //Using alias
            $r->reset();
            $value = 'foo';
            $expected = 'SELECT MAX(foo) AS bar FROM';
            $r->max($value, 'bar');
            $this->assertSame($expected, $r->getQuery());
            
            
            //using existing select
            $r->reset();
            $value = 'foo';
            $expected = 'SELECT bar, MAX(foo) FROM';
            $r->select('bar');
            $r->max($value);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testSum() {
            $r = new DatabaseQueryBuilder();
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //After each call to getQuery() need reset to default values
            $r->reset();
            $value = 'foo';
            $expected = 'SELECT SUM(foo) FROM';
            $r->sum($value);
            $this->assertSame($expected, $r->getQuery());
            
            //Using alias
            $r->reset();
            $value = 'foo';
            $expected = 'SELECT SUM(foo) AS bar FROM';
            $r->sum($value, 'bar');
            $this->assertSame($expected, $r->getQuery());
            
            
            //using existing select
            $r->reset();
            $value = 'foo';
            $expected = 'SELECT bar, SUM(foo) FROM';
            $r->select('bar');
            $r->sum($value);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testAvg() {
            $r = new DatabaseQueryBuilder();
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //After each call to getQuery() need reset to default values
            $r->reset();
            $value = 'foo';
            $expected = 'SELECT AVG(foo) FROM';
            $r->avg($value);
            $this->assertSame($expected, $r->getQuery());
            
            //Using alias
            $r->reset();
            $value = 'foo';
            $expected = 'SELECT AVG(foo) AS bar FROM';
            $r->avg($value, 'bar');
            $this->assertSame($expected, $r->getQuery());
            
            
            //using existing select
            $r->reset();
            $value = 'foo';
            $expected = 'SELECT bar, AVG(foo) FROM';
            $r->select('bar');
            $r->avg($value);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testJoin() {
            $r = new DatabaseQueryBuilder();
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //After each call to getQuery() need reset to default values
            $r->reset();
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM  JOIN foo ON bar.x = foo.y';
            $r->join($value, $on);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //Using from
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM baz JOIN foo ON bar.x = foo.y';
            $r->from('baz');
            $r->join($value, $on);
            $this->assertSame($expected, $r->getQuery());
            
            
            $r->reset();
            //field 1 and 2 are set
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  JOIN foo ON bar.x = foo.y';
            $r->join($value, $field1, '=', $field2);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //operator is an shortcut to field2, so default to "="
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  JOIN foo ON bar.x = foo.y';
            $r->join($value, $field1, $field2);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //using existing one
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  JOIN foo ON bar.x = foo.y JOIN barbaz ON baz.a = foobar.b';
            $r->join($value1, $field1, $field2);
            $r->join($value2, $field3, $field4);
            $this->assertSame($expected, $r->getQuery());
		}
    
        public function testInnerJoin() {
            $r = new DatabaseQueryBuilder();
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //After each call to getQuery() need reset to default values
            $r->reset();
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM  INNER JOIN foo ON bar.x = foo.y';
            $r->innerJoin($value, $on);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //Using from
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM baz INNER JOIN foo ON bar.x = foo.y';
            $r->from('baz');
            $r->innerJoin($value, $on);
            $this->assertSame($expected, $r->getQuery());
            
            
            $r->reset();
            //field 1 and 2 are set
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  INNER JOIN foo ON bar.x = foo.y';
            $r->innerJoin($value, $field1, '=', $field2);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //operator is an shortcut to field2, so default to "="
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  INNER JOIN foo ON bar.x = foo.y';
            $r->innerJoin($value, $field1, $field2);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //using existing one
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  INNER JOIN foo ON bar.x = foo.y INNER JOIN barbaz ON baz.a = foobar.b';
            $r->innerJoin($value1, $field1, $field2);
            $r->innerJoin($value2, $field3, $field4);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //using existing JOIN
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  JOIN foo ON bar.x = foo.y INNER JOIN barbaz ON baz.a = foobar.b';
            $r->join($value1, $field1, $field2);
            $r->innerJoin($value2, $field3, $field4);
            $this->assertSame($expected, $r->getQuery());
		}

        public function testLeftJoin() {
            $r = new DatabaseQueryBuilder();
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //After each call to getQuery() need reset to default values
            $r->reset();
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM  LEFT JOIN foo ON bar.x = foo.y';
            $r->leftJoin($value, $on);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //Using from
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM baz LEFT JOIN foo ON bar.x = foo.y';
            $r->from('baz');
            $r->leftJoin($value, $on);
            $this->assertSame($expected, $r->getQuery());
            
            
            $r->reset();
            //field 1 and 2 are set
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  LEFT JOIN foo ON bar.x = foo.y';
            $r->leftJoin($value, $field1, '=', $field2);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //operator is an shortcut to field2, so default to "="
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  LEFT JOIN foo ON bar.x = foo.y';
            $r->leftJoin($value, $field1, $field2);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //using existing one
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  LEFT JOIN foo ON bar.x = foo.y LEFT JOIN barbaz ON baz.a = foobar.b';
            $r->leftJoin($value1, $field1, $field2);
            $r->leftJoin($value2, $field3, $field4);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //using existing JOIN
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  JOIN foo ON bar.x = foo.y LEFT JOIN barbaz ON baz.a = foobar.b';
            $r->join($value1, $field1, $field2);
            $r->leftJoin($value2, $field3, $field4);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testRightJoin() {
            $r = new DatabaseQueryBuilder();
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //After each call to getQuery() need reset to default values
            $r->reset();
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM  RIGHT JOIN foo ON bar.x = foo.y';
            $r->rightJoin($value, $on);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //Using from
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM baz RIGHT JOIN foo ON bar.x = foo.y';
            $r->from('baz');
            $r->rightJoin($value, $on);
            $this->assertSame($expected, $r->getQuery());
            
            
            $r->reset();
            //field 1 and 2 are set
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  RIGHT JOIN foo ON bar.x = foo.y';
            $r->rightJoin($value, $field1, '=', $field2);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //operator is an shortcut to field2, so default to "="
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  RIGHT JOIN foo ON bar.x = foo.y';
            $r->rightJoin($value, $field1, $field2);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //using existing one
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  RIGHT JOIN foo ON bar.x = foo.y RIGHT JOIN barbaz ON baz.a = foobar.b';
            $r->rightJoin($value1, $field1, $field2);
            $r->rightJoin($value2, $field3, $field4);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //using existing JOIN
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  JOIN foo ON bar.x = foo.y RIGHT JOIN barbaz ON baz.a = foobar.b';
            $r->join($value1, $field1, $field2);
            $r->rightJoin($value2, $field3, $field4);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testFullOuterJoin() {
            $r = new DatabaseQueryBuilder();
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //After each call to getQuery() need reset to default values
            $r->reset();
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM  FULL OUTER JOIN foo ON bar.x = foo.y';
            $r->fullOuterJoin($value, $on);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //Using from
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM baz FULL OUTER JOIN foo ON bar.x = foo.y';
            $r->from('baz');
            $r->fullOuterJoin($value, $on);
            $this->assertSame($expected, $r->getQuery());
            
            
            $r->reset();
            //field 1 and 2 are set
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  FULL OUTER JOIN foo ON bar.x = foo.y';
            $r->fullOuterJoin($value, $field1, '=', $field2);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //operator is an shortcut to field2, so default to "="
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  FULL OUTER JOIN foo ON bar.x = foo.y';
            $r->fullOuterJoin($value, $field1, $field2);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //using existing one
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  FULL OUTER JOIN foo ON bar.x = foo.y FULL OUTER JOIN barbaz ON baz.a = foobar.b';
            $r->fullOuterJoin($value1, $field1, $field2);
            $r->fullOuterJoin($value2, $field3, $field4);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //using existing JOIN
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  JOIN foo ON bar.x = foo.y FULL OUTER JOIN barbaz ON baz.a = foobar.b';
            $r->join($value1, $field1, $field2);
            $r->fullOuterJoin($value2, $field3, $field4);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testLeftOuterJoin() {
            $r = new DatabaseQueryBuilder();
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //After each call to getQuery() need reset to default values
            $r->reset();
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM  LEFT OUTER JOIN foo ON bar.x = foo.y';
            $r->leftOuterJoin($value, $on);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //Using from
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM baz LEFT OUTER JOIN foo ON bar.x = foo.y';
            $r->from('baz');
            $r->leftOuterJoin($value, $on);
            $this->assertSame($expected, $r->getQuery());
            
            
            $r->reset();
            //field 1 and 2 are set
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  LEFT OUTER JOIN foo ON bar.x = foo.y';
            $r->leftOuterJoin($value, $field1, '=', $field2);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //operator is an shortcut to field2, so default to "="
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  LEFT OUTER JOIN foo ON bar.x = foo.y';
            $r->leftOuterJoin($value, $field1, $field2);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //using existing one
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  LEFT OUTER JOIN foo ON bar.x = foo.y LEFT OUTER JOIN barbaz ON baz.a = foobar.b';
            $r->leftOuterJoin($value1, $field1, $field2);
            $r->leftOuterJoin($value2, $field3, $field4);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //using existing JOIN
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  JOIN foo ON bar.x = foo.y LEFT OUTER JOIN barbaz ON baz.a = foobar.b';
            $r->join($value1, $field1, $field2);
            $r->leftOuterJoin($value2, $field3, $field4);
            $this->assertSame($expected, $r->getQuery());
		}
        
         public function testRightOuterJoin() {
            $r = new DatabaseQueryBuilder();
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //After each call to getQuery() need reset to default values
            $r->reset();
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM  RIGHT OUTER JOIN foo ON bar.x = foo.y';
            $r->rightOuterJoin($value, $on);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //Using from
            $value = 'foo';
            $on = 'bar.x = foo.y';
            $expected = 'SELECT * FROM baz RIGHT OUTER JOIN foo ON bar.x = foo.y';
            $r->from('baz');
            $r->rightOuterJoin($value, $on);
            $this->assertSame($expected, $r->getQuery());
            
            
            $r->reset();
            //field 1 and 2 are set
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  RIGHT OUTER JOIN foo ON bar.x = foo.y';
            $r->rightOuterJoin($value, $field1, '=', $field2);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //operator is an shortcut to field2, so default to "="
            $value = 'foo';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $expected = 'SELECT * FROM  RIGHT OUTER JOIN foo ON bar.x = foo.y';
            $r->rightOuterJoin($value, $field1, $field2);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //using existing one
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  RIGHT OUTER JOIN foo ON bar.x = foo.y RIGHT OUTER JOIN barbaz ON baz.a = foobar.b';
            $r->rightOuterJoin($value1, $field1, $field2);
            $r->rightOuterJoin($value2, $field3, $field4);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            //using existing JOIN
            $value1 = 'foo';
            $value2 = 'barbaz';
            $field1 = 'bar.x';
            $field2 = 'foo.y';
            $field3 = 'baz.a';
            $field4 = 'foobar.b';
            $expected = 'SELECT * FROM  JOIN foo ON bar.x = foo.y RIGHT OUTER JOIN barbaz ON baz.a = foobar.b';
            $r->join($value1, $field1, $field2);
            $r->rightOuterJoin($value2, $field3, $field4);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testWhereIsNull() {
            $r = new DatabaseQueryBuilder();
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //After each call to getQuery() need reset to default values
            $r->reset();
            $value = 'foo';
            $expected = 'SELECT * FROM  WHERE foo IS NULL';
            $r->whereIsNull($value);
            $this->assertSame($expected, $r->getQuery());
            
            //Using existing one
            $r->reset();
            $value1 = 'foo';
            $value2 = 'bar';
            $expected = 'SELECT * FROM  WHERE foo IS NULL AND bar IS NULL';
            $r->whereIsNull($value1);
            $r->whereIsNull($value2);
            $this->assertSame($expected, $r->getQuery());
            
            //Using array default param (AND)
            $r->reset();
            $value = array('foo', 'bar');
            $expected = 'SELECT * FROM  WHERE foo IS NULL AND bar IS NULL';
            $r->whereIsNull($value);
            $this->assertSame($expected, $r->getQuery());
            
            //Using array default with param)
            $r->reset();
            $value = array('foo', 'bar');
            $expected = 'SELECT * FROM  WHERE foo IS NULL OR bar IS NULL';
            $r->whereIsNull($value, 'OR');
            $this->assertSame($expected, $r->getQuery());  
		}
        
        public function testWhereIsNotNull() {
            $r = new DatabaseQueryBuilder();
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //After each call to getQuery() need reset to default values
            $r->reset();
            $value = 'foo';
            $expected = 'SELECT * FROM  WHERE foo IS NOT NULL';
            $r->whereIsNotNull($value);
            $this->assertSame($expected, $r->getQuery());
            
             //Using existing one
            $r->reset();
            $value1 = 'foo';
            $value2 = 'bar';
            $expected = 'SELECT * FROM  WHERE foo IS NOT NULL AND bar IS NOT NULL';
            $r->whereIsNotNull($value1);
            $r->whereIsNotNull($value2);
            $this->assertSame($expected, $r->getQuery());
            
            //Using array default param (AND)
            $r->reset();
            $value = array('foo', 'bar');
            $expected = 'SELECT * FROM  WHERE foo IS NOT NULL AND bar IS NOT NULL';
            $r->whereIsNotNull($value);
            $this->assertSame($expected, $r->getQuery());
            
            //Using array default with param)
            $r->reset();
            $value = array('foo', 'bar');
            $expected = 'SELECT * FROM  WHERE foo IS NOT NULL OR bar IS NOT NULL';
            $r->whereIsNotNull($value, 'OR');
            $this->assertSame($expected, $r->getQuery()); 


            //Combinaison of whereIsNotNull and whereIsNull (Default value for 2nd param)
            $r->reset();
            $value1 = 'foo';
            $value2 = 'bar';
            $expected = 'SELECT * FROM  WHERE foo IS NOT NULL AND bar IS NULL';
            $r->whereIsNotNull($value1);
            $r->whereIsNull($value2);
            $this->assertSame($expected, $r->getQuery());
            
            //Combinaison of whereIsNotNull and whereIsNull (set value for 2nd param)
            $r->reset();
            $value1 = 'foo';
            $value2 = 'bar';
            $expected = 'SELECT * FROM  WHERE foo IS NOT NULL OR bar IS NULL';
            $r->whereIsNotNull($value1);
            $r->whereIsNull($value2, 'OR');
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testWhere() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //After each call to getQuery() need reset to default values
            $r->reset();
            $field = 'foo';
            $value = 'bar';
            $expected = "SELECT * FROM  WHERE foo = 'bar'";
            $r->where($field, $value);
            $this->assertSame($expected, $r->getQuery());
            
            //Using null value
            $r->reset();
            $field = 'foo';
            $value = null;
            $expected = "SELECT * FROM  WHERE foo = ''";
            $r->where($field, $value);
            $this->assertSame($expected, $r->getQuery());
            
            //Using array 
            $r->reset();
            $value = array('foo' => 'bar', 'bar' => 'foo');
            $expected = "SELECT * FROM  WHERE foo = 'bar' AND bar = 'foo'";
            $r->where($value);
            $this->assertSame($expected, $r->getQuery());
            
            //Dont escape the value
            $r->reset();
            $field = 'date';
            $value = 'CURRENT_TIMESTAMP()';
            $expected = "SELECT * FROM  WHERE date >= CURRENT_TIMESTAMP()";
            $r->where($field, '>=', $value, '', 'AND', false);
            $this->assertSame($expected, $r->getQuery());
            
            //Using "?" in place of value
            $r->reset();
            $field = 'foo = ? AND bar = ?';
            $value = array('12', 'abc');
            $expected = "SELECT * FROM  WHERE foo = '12' AND bar = 'abc'";
            $r->where($field, $value);
            $this->assertSame($expected, $r->getQuery());    
		}
        
        public function testOrWhere() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //After each call to getQuery() need reset to default values
            $r->reset();
            $field = 'foo';
            $value = 'bar';
            $expected = "SELECT * FROM  WHERE foo = 'bar'";
            $r->orWhere($field, $value);
            $this->assertSame($expected, $r->getQuery());
            
            //Using array 
            $r->reset();
            $value = array('foo' => 'bar', 'bar' => 'foo');
            $expected = "SELECT * FROM  WHERE foo = 'bar' OR bar = 'foo'";
            $r->orWhere($value);
            $this->assertSame($expected, $r->getQuery());
            
		}
        
        public function testNotWhere() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //After each call to getQuery() need reset to default values
            $r->reset();
            $field = 'foo';
            $value = 'bar';
            $expected = "SELECT * FROM  WHERE NOT foo = 'bar'";
            $r->notWhere($field, $value);
            $this->assertSame($expected, $r->getQuery());
            
            //Using array 
            $r->reset();
            $value = array('foo' => 'bar', 'bar' => 'foo');
            $expected = "SELECT * FROM  WHERE NOT foo = 'bar' AND NOT bar = 'foo'";
            $r->notWhere($value);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testOrNotWhere() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //After each call to getQuery() need reset to default values
            $r->reset();
            $field = 'foo';
            $value = 'bar';
            $expected = "SELECT * FROM  WHERE NOT foo = 'bar'";
            $r->orNotWhere($field, $value);
            $this->assertSame($expected, $r->getQuery());
            
            //Using array 
            $r->reset();
            $value = array('foo' => 'bar', 'bar' => 'foo');
            $expected = "SELECT * FROM  WHERE NOT foo = 'bar' OR NOT bar = 'foo'";
            $r->orNotWhere($value);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testGroupStartAndEnd() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //After each call to getQuery() need reset to default values
            $r->reset();
            $field = 'foo';
            $value = 'bar';
            $expected = "SELECT * FROM  WHERE foo = 'bar'";
            $r->where($field, $value);
            $this->assertSame($expected, $r->getQuery());
            
            //Using with existing WHERE
            $r->reset();
            $field1 = 'foo';
            $field2 = 'baz';
            $value1 = '134';
            $value2 = 'abc';
            $expected = "SELECT * FROM  WHERE foo = '134' AND (baz = 'abc')";
            $r->where($field1, $value1);
            $r->groupStart();
            $r->where($field2, $value2);
            $r->groupEnd();
            $this->assertSame($expected, $r->getQuery());
            
            //When existing WHERE does not set before
            $r->reset();
            $field1 = 'foo';
            $field2 = 'baz';
            $value1 = '134';
            $value2 = 'abc';
            $expected = "SELECT * FROM  WHERE (foo = '134' AND baz = 'abc')";
            $r->groupStart();
            $r->where($field1, $value1);
            $r->where($field2, $value2);
            $r->groupEnd();
            $this->assertSame($expected, $r->getQuery());
            
            
            //Using consecutive groupe
            $r->reset();
            $field1 = 'foo';
            $field2 = 'baz';
            $field3 = 'foobar';
            $field4 = 'name';
            $value1 = '134';
            $value2 = 'abc';
            $value3 = 'xy';
            $value4 = '987';
            $expected = "SELECT * FROM  WHERE (NOT(foo = '134' OR baz = 'abc') AND foobar = 'xy') AND NOT name = '987'";
            $r->groupStart();
            $r->orNotGroupStart();
            $r->where($field1, $value1);
            $r->orWhere($field2, $value2);
            $r->groupEnd();
            $r->where($field3, $value3);
            $r->groupEnd();
            $r->notWhere($field4, $value4);
            $this->assertSame($expected, $r->getQuery());
            
            
            //Using type param
            $r->reset();
            $field1 = 'foo';
            $field2 = 'baz';
            $value1 = '134';
            $value2 = 'abc';
            $expected = "SELECT * FROM  WHERE foo = '134' AND NOT (baz = 'abc')";
            $r->where($field1, $value1);
            $r->notGroupStart();
            $r->where($field2, $value2);
            $r->groupEnd();
            $this->assertSame($expected, $r->getQuery());
            
            //Using 2nd param
            $r->reset();
            $field1 = 'foo';
            $field2 = 'baz';
            $value1 = '134';
            $value2 = 'abc';
            $expected = "SELECT * FROM  WHERE foo = '134' OR (baz = 'abc')";
            $r->where($field1, $value1);
            $r->orGroupStart();
            $r->where($field2, $value2);
            $r->groupEnd();
            $this->assertSame($expected, $r->getQuery());
            
            
		}
        
        public function testIn() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //Numeric value
            $r->reset();
            $field = 'foo';
            $value = array(3, 4, 89);
            $expected = "SELECT * FROM  WHERE foo IN (3, 4, 89)";
            $r->in($field, $value);
            $this->assertSame($expected, $r->getQuery());
            
             //String value
            $r->reset();
            $field = 'foo';
            $value = array('a', 'bc', 'baz');
            $expected = "SELECT * FROM  WHERE foo IN ('a', 'bc', 'baz')";
            $r->in($field, $value);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testNotIn() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //Numeric value
            $r->reset();
            $field = 'foo';
            $value = array(3, 4, 89);
            $expected = "SELECT * FROM  WHERE foo NOT IN (3, 4, 89)";
            $r->notIn($field, $value);
            $this->assertSame($expected, $r->getQuery());
            
             //String value
            $r->reset();
            $field = 'foo';
            $value = array('a', 'bc', 'baz');
            $expected = "SELECT * FROM  WHERE foo NOT IN ('a', 'bc', 'baz')";
            $r->notIn($field, $value);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testOrIn() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //Using existing where
            $r->reset();
            $field = 'foo';
            $value = array(3, 4, 89);
            $field1 = 'foo';
            $value1 = '134';
            $expected = "SELECT * FROM  WHERE foo = '134' OR foo IN (3, 4, 89)";
            $r->where($field1, $value1);
            $r->orIn($field, $value);
            $this->assertSame($expected, $r->getQuery());
            
             //WHERE is not set before
            $r->reset();
            $field = 'foo';
            $value = array('a', 'bc', 'baz');
            $expected = "SELECT * FROM  WHERE foo IN ('a', 'bc', 'baz')";
            $r->orIn($field, $value);
            $this->assertSame($expected, $r->getQuery());
		}
        
         public function testOrNotIn() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //Using existing where
            $r->reset();
            $field = 'foo';
            $value = array(3, 4, 89);
            $field1 = 'foo';
            $value1 = '134';
            $expected = "SELECT * FROM  WHERE foo = '134' OR foo NOT IN (3, 4, 89)";
            $r->where($field1, $value1);
            $r->orNotIn($field, $value);
            $this->assertSame($expected, $r->getQuery());
            
             //WHERE is not set before
            $r->reset();
            $field = 'foo';
            $value = array('a', 'bc', 'baz');
            $expected = "SELECT * FROM  WHERE foo NOT IN ('a', 'bc', 'baz')";
            $r->orNotIn($field, $value);
            $this->assertSame($expected, $r->getQuery());
		}
        
        
        public function testBetween() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //Using existing where
            $r->reset();
            $fieldw = 'bar';
            $value = 45;
            $field = 'foo';
            $value1 = '12';
            $value2 = '134';
            $expected = "SELECT * FROM  WHERE bar = '45' AND foo BETWEEN '12' AND '134'";
            $r->where($fieldw, $value);
            $r->between($field, $value1, $value2);
            $this->assertSame($expected, $r->getQuery());
            
             //WHERE is not set before
            $r->reset();
            $field = 'foo';
            $value1 = '12';
            $value2 = '134';
            $expected = "SELECT * FROM  WHERE foo BETWEEN '12' AND '134'";
            $r->between($field, $value1, $value2);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testOrBetween() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //Using existing where
            $r->reset();
            $fieldw = 'bar';
            $value = 45;
            $field = 'foo';
            $value1 = '12';
            $value2 = '134';
            $expected = "SELECT * FROM  WHERE bar = '45' OR foo BETWEEN '12' AND '134'";
            $r->where($fieldw, $value);
            $r->orBetween($field, $value1, $value2);
            $this->assertSame($expected, $r->getQuery());
            
             //WHERE is not set before
            $r->reset();
            $field = 'foo';
            $value1 = '12';
            $value2 = '134';
            $expected = "SELECT * FROM  WHERE foo BETWEEN '12' AND '134'";
            $r->orBetween($field, $value1, $value2);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testNotBetween() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //Using existing where
            $r->reset();
            $fieldw = 'bar';
            $value = 45;
            $field = 'foo';
            $value1 = '12';
            $value2 = '134';
            $expected = "SELECT * FROM  WHERE bar = '45' AND foo NOT BETWEEN '12' AND '134'";
            $r->where($fieldw, $value);
            $r->notBetween($field, $value1, $value2);
            $this->assertSame($expected, $r->getQuery());
            
             //WHERE is not set before
            $r->reset();
            $field = 'foo';
            $value1 = '12';
            $value2 = '134';
            $expected = "SELECT * FROM  WHERE foo NOT BETWEEN '12' AND '134'";
            $r->notBetween($field, $value1, $value2);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testOrNotBetween() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //Using existing where
            $r->reset();
            $fieldw = 'bar';
            $value = 45;
            $field = 'foo';
            $value1 = '12';
            $value2 = '134';
            $expected = "SELECT * FROM  WHERE bar = '45' OR foo NOT BETWEEN '12' AND '134'";
            $r->where($fieldw, $value);
            $r->orNotBetween($field, $value1, $value2);
            $this->assertSame($expected, $r->getQuery());
            
             //WHERE is not set before
            $r->reset();
            $field = 'foo';
            $value1 = '12';
            $value2 = '134';
            $expected = "SELECT * FROM  WHERE foo NOT BETWEEN '12' AND '134'";
            $r->orNotBetween($field, $value1, $value2);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testLike() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //Using existing where
            $r->reset();
            $fieldw = 'bar';
            $value = 45;
            $field = 'foo';
            $value1 = '%baz';
            $expected = "SELECT * FROM  WHERE bar = '45' AND foo LIKE '%baz'";
            $r->where($fieldw, $value);
            $r->like($field, $value1);
            $this->assertSame($expected, $r->getQuery());
            
             //WHERE is not set before
            $r->reset();
            $field = 'foo';
            $value1 = '%abc%';
            $expected = "SELECT * FROM  WHERE foo LIKE '%abc%'";
            $r->like($field, $value1);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testOrLike() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //Using existing where
            $r->reset();
            $fieldw = 'bar';
            $value = 45;
            $field = 'foo';
            $value1 = '%baz';
            $expected = "SELECT * FROM  WHERE bar = '45' OR foo LIKE '%baz'";
            $r->where($fieldw, $value);
            $r->orLike($field, $value1);
            $this->assertSame($expected, $r->getQuery());
            
             //WHERE is not set before
            $r->reset();
            $field = 'foo';
            $value1 = '%abc%';
            $expected = "SELECT * FROM  WHERE foo LIKE '%abc%'";
            $r->orLike($field, $value1);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testNotLike() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //Using existing where
            $r->reset();
            $fieldw = 'bar';
            $value = 45;
            $field = 'foo';
            $value1 = '%baz';
            $expected = "SELECT * FROM  WHERE bar = '45' AND foo NOT LIKE '%baz'";
            $r->where($fieldw, $value);
            $r->notLike($field, $value1);
            $this->assertSame($expected, $r->getQuery());
            
             //WHERE is not set before
            $r->reset();
            $field = 'foo';
            $value1 = '%abc%';
            $expected = "SELECT * FROM  WHERE foo NOT LIKE '%abc%'";
            $r->notLike($field, $value1);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testOrNotLike() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //Using existing where
            $r->reset();
            $fieldw = 'bar';
            $value = 45;
            $field = 'foo';
            $value1 = '%baz';
            $expected = "SELECT * FROM  WHERE bar = '45' OR foo NOT LIKE '%baz'";
            $r->where($fieldw, $value);
            $r->orNotLike($field, $value1);
            $this->assertSame($expected, $r->getQuery());
            
             //WHERE is not set before
            $r->reset();
            $field = 'foo';
            $value1 = '%abc%';
            $expected = "SELECT * FROM  WHERE foo NOT LIKE '%abc%'";
            $r->orNotLike($field, $value1);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testLimit() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
           //Using only the first param
            $r->reset();
            $expected = "SELECT * FROM  LIMIT 10";
            $r->limit(10);
            $this->assertSame($expected, $r->getQuery());
            
            //param is null or empty
            $r->reset();
            $expected = "SELECT * FROM";
            $r->limit(null);
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            $expected = "SELECT * FROM  LIMIT 7, 10";
            $r->limit(7, 10);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testOrderBy() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
           //Using only the first param
            $r->reset();
            $field = 'foo';
            $dir = 'ASC';
            $expected = "SELECT * FROM  ORDER BY foo ASC";
            $r->orderBy($field);
            $this->assertSame($expected, $r->getQuery());
            
            //Using existing one
            $r->reset();
            $field1 = 'foo';
            $field2 = 'bar';
            $dir1 = 'ASC';
            $dir2 = 'DESC';
            $expected = "SELECT * FROM  ORDER BY foo ASC, bar DESC";
            $r->orderBy($field1, $dir1);
            $r->orderBy($field2, $dir2);
            $this->assertSame($expected, $r->getQuery());
            
             //Using rand()
            $r->reset();
            $field = 'rand()';
            $dir = 'ASC';
            $expected = "SELECT * FROM  ORDER BY rand()";
            $r->orderBy($field);
            $this->assertSame($expected, $r->getQuery());
		}
        
        public function testGroupBy() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            $r->reset();
            $field = 'foo';
            $expected = "SELECT * FROM  GROUP BY foo";
            $r->groupBy($field);
            $this->assertSame($expected, $r->getQuery());
            
            //Using array
            $r->reset();
            $fields = array('bar', 'foo');
            $expected = "SELECT * FROM  GROUP BY bar, foo";
            $r->groupBy($fields);
            $this->assertSame($expected, $r->getQuery());
            
            //Overwrite existing one
            $r->reset();
            $field1 = 'foo';
            $field2 = 'bar';
            $expected = "SELECT * FROM  GROUP BY bar";
            $r->groupBy($field1);
            $r->groupBy($field2);
            $this->assertSame($expected, $r->getQuery());
		}
        
        
         public function testHaving() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            //After each call to getQuery() need reset to default values
            $r->reset();
            $field = 'COUNT(bar)';
            $value = '34';
            $expected = "SELECT * FROM  HAVING COUNT(bar) > '34'";
            $r->having($field, $value);
            $this->assertSame($expected, $r->getQuery());
            
            //Using null value
            $r->reset();
            $field = 'COUNT(foo)';
            $value = null;
            $expected = "SELECT * FROM  HAVING COUNT(foo) > ''";
            $r->having($field, $value);
            $this->assertSame($expected, $r->getQuery());
            
            //Dont escape the value
            $r->reset();
            $field = 'MIN(date)';
            $value = 'CURRENT_TIMESTAMP()';
            $expected = "SELECT * FROM  HAVING MIN(date) >= CURRENT_TIMESTAMP()";
            $r->having($field, '>=', $value, false);
            $this->assertSame($expected, $r->getQuery());
            
            //Using "?" in place of value
            $r->reset();
            $field = 'COUNT(foo) = ? AND SUM(bar) > ? OR AVG(baz) <= ?';
            $value = array(10, 367);
            $expected = "SELECT * FROM  HAVING COUNT(foo) = '10' AND SUM(bar) > '367' OR AVG(baz) <= ''";
            $r->having($field, $value);
            $this->assertSame($expected, $r->getQuery());    
		}
        
        public function testInsert() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            $data = array(
                'foo' => 'bar',
                'bar' => 3766
            );
            $table = 'footable';
            
            $expected = "INSERT INTO footable(foo, bar) VALUES ('bar', '3766')";
            $r->from($table);
            $r->insert($data);
            $this->assertSame($expected, $r->getQuery());
            
            //Dont escape
            $data = array(
                'foo' => 'NOW()',
                'bar' => 'CURRENT_TIMESTAMP()'
            );
            $expected = "INSERT INTO footable(foo, bar) VALUES (NOW(), CURRENT_TIMESTAMP())";
            $r->from($table);
            $r->insert($data, false);
            $this->assertSame($expected, $r->getQuery()); 
		}
        
        public function testUpdate() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            $data = array(
                'foo' => 'bar',
                'bar' => 3766
            );
            $table = 'footable';
            
            $expected = "UPDATE footable SET foo = 'bar', bar = '3766'";
            $r->from($table);
            $r->update($data);
            $this->assertSame($expected, $r->getQuery());
            
            //Dont escape
            $data = array(
                'foo' => 'NOW()',
                'bar' => 'CURRENT_TIMESTAMP()'
            );
            $expected = "UPDATE footable SET foo = NOW(), bar = CURRENT_TIMESTAMP()";
            $r->from($table);
            $r->update($data, false);
            $this->assertSame($expected, $r->getQuery());
            
            //Using LIMIT, ORDER BY, WHERE
            $data = array(
                'foo' => 'bar',
                'bar' => 3766
            );
            $fieldw = 'baz';
            $fieldob = 'obfoo';
            $valuew = '12';
            $expected = "UPDATE footable SET foo = 'bar', bar = '3766' WHERE baz = '12' ORDER BY obfoo ASC LIMIT 3, 14";
            $r->from($table);
            $r->where($fieldw, $valuew);
            $r->orderBy($fieldob);
            $r->limit(3, 14);
            $r->update($data);
            $this->assertSame($expected, $r->getQuery());
		}
        
        
        public function testDelete() {
            $r = new DatabaseQueryBuilder($this->pdo);
            
            $expected = 'SELECT * FROM';
            $this->assertSame($expected, $r->getQuery());
            
            $table = 'footable';
            
            //Default behavior is to truncate if no where
            $expected = "TRUNCATE TABLE footable";
            $r->from($table);
            $r->delete();
            $this->assertSame($expected, $r->getQuery());
            
            //When driver is "sqlite"
            $this->assertNull($r->getDriver());
            $r->setDriver('sqlite');
            $this->assertSame('sqlite', $r->getDriver());
            $expected = "DELETE FROM footable";
            $r->from($table);
            $r->delete();
            $this->assertSame($expected, $r->getQuery());
            
            //restore driver to null value
            $r->setDriver(null);
            $this->assertNull($r->getDriver());
            
     
            //Using LIMIT, ORDER BY, WHERE
            $fieldw = 'baz';
            $fieldob = 'obfoo';
            $valuew = '12';
            $expected = "DELETE FROM footable WHERE baz = '12' ORDER BY obfoo ASC LIMIT 3, 14";
            $r->from($table);
            $r->where($fieldw, $valuew);
            $r->orderBy($fieldob);
            $r->limit(3, 14);
            $r->delete();
            $this->assertSame($expected, $r->getQuery());
		}
        
        /**
        * Mock method callback for PDO method quote()
        */
        public function mockPdoMethodQuoteReturnCallback() {
            $args = func_get_args();
            if($args[0] == null) {
                return "''";
            }
            return "'".$args[0]."'";
        }

	}