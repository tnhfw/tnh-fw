<?php 

	/**
     * Model class tests
     *
     * @group core
     * @group database
     * @group model
     */
     class ModelTest extends TnhTestCase {	
    
        public static function setUpBeforeClass() {
		require APPS_MODEL_PATH . 'DefaultModel.php';
         	require APPS_MODEL_PATH . 'InsertAutoIncrementModel.php';
		require APPS_MODEL_PATH . 'InsertNoAutoIncrementModel.php';
		require APPS_MODEL_PATH . 'UpdateModel.php';
		require APPS_MODEL_PATH . 'DeleteModel.php';
		require APPS_MODEL_PATH . 'PostModel.php';
		require APPS_MODEL_PATH . 'AuthorModel.php';
		require APPS_MODEL_PATH . 'SoftDeleteModel.php';
		require APPS_MODEL_PATH . 'TriggerEventModel.php';
		require APPS_MODEL_PATH . 'Country_model.php';
		require APPS_MODEL_PATH . 'UserModel.php';
	}
        
        
	public function testConstructorUsingDbParam() {
            $db = $this->getMockBuilder('Database')
                        ->disableOriginalConstructor()
                        ->getMock();
			$m = new Model($db);
            $this->assertInstanceOf('Database', $m->getDb());
	}
        
        public function testConstructorUsingDbFromSuperController() {
            $db = $this->getMockBuilder('Database')
                        ->disableOriginalConstructor()
                        ->getMock();
            $obj = & get_instance();
            $obj->database = $db;
			$m = new Model();
            $this->assertInstanceOf('Database', $m->getDb());
	}
        
        public function testGetSingleRecord() {
            $db = $this->getDbInstanceForTest();
            $m = new DefaultModel($db);
            $record = $m->getSingleRecord(1);
            $this->assertNotEmpty($record);
            $this->assertObjectHasAttribute('id', $record);
            $this->assertObjectHasAttribute('name', $record);
            $this->assertObjectHasAttribute('status', $record);
            $this->assertEquals(1, $record->id);
            $this->assertEquals('bangui', $record->name);
            
            //record not exists
            $this->assertNull($m->getSingleRecord(99999999));
	}
        
        public function testGetSingleRecordCond() {
            $db = $this->getDbInstanceForTest();
            $m = new DefaultModel($db);
            $record = $m->getSingleRecordCond('id', 1);
            $this->assertNotEmpty($record);
            $this->assertObjectHasAttribute('id', $record);
            $this->assertObjectHasAttribute('name', $record);
            $this->assertEquals(1, $record->id);
            $this->assertEquals('bangui', $record->name);
            
            //record not exists
            $this->assertNull($m->getSingleRecordCond('id', 99999999));
	}
        
        public function testGetListRecord() {
            $db = $this->getDbInstanceForTest();
            $m = new DefaultModel($db);
            $records = $m->getListRecord();
            $this->assertNotEmpty($records);
            $this->assertSame(4, count($records));
            
            //using the list of primary keys
            $records = $m->getListRecord(array(2,1,4));
            $this->assertNotEmpty($records);
            $this->assertSame(3, count($records));
            
            //using the list of primary keys some value not exist
            $records = $m->getListRecord(array(2,1,41, 34));
            $this->assertNotEmpty($records);
            $this->assertSame(2, count($records));
	}
        
        public function testGetListRecordCond() {
            $db = $this->getDbInstanceForTest();
            $m = new DefaultModel($db);
            $records = $m->getListRecordCond('status', 1);
            $this->assertNotEmpty($records);
            $this->assertSame(2, count($records));
            
            //using array
            $records = $m->getListRecordCond(array('status' => 1, 'name' => 'pk12'));
            $this->assertNotEmpty($records);
            $this->assertSame(1, count($records));
            
            //no record found
            $records = $m->getListRecordCond('status', 3);
            $this->assertEmpty($records);
            $this->assertSame(0, count($records));    
	}
      
      
        public function testRulesValidation() {
            $db = $this->getDbInstanceForTest();
            $m = new InsertAutoIncrementModel($db);
            
            $obj = &get_instance();
            $obj->formvalidation = new FormValidation();//Using for validation
                        
            //truncate first
            $this->truncateAndResetSequenceSqliteTable($m);
            
            $data = array(
                'name' => 'foo'
            );
            $id = $m->insert($data);
            $this->assertEquals(1, $id);
            
            $data = array(
                'name' => 'bar'
            );
            $id = $m->insert($data);
            $this->assertEquals(2, $id);
	}
        
        public function testInsertWithAutoIncrement() {
            $db = $this->getDbInstanceForTest();
            $m = new InsertAutoIncrementModel($db);
            
             //no need do validation
            $m->setSkipRulesValidation(true);
            
            //truncate first
            $this->truncateAndResetSequenceSqliteTable($m);
            
            $data = array(
                'name' => 'foo'
            );
            $id = $m->insert($data);
            $this->assertEquals(1, $id);
            
            $data = array(
                'name' => 'bar'
            );
            $id = $m->insert($data);
            $this->assertEquals(2, $id);
            
            //Data validation failed
            $obj = &get_instance();
            $obj->formvalidation = new FormValidation();//Using for validation
            $m->setSkipRulesValidation(false);
            
            $data = array(
                'name' => null
            );
            $id = $m->insert($data);
            $this->assertFalse($id);  
	}
        
        
        public function testInsertMultipleWithAutoIncrement() { 
            $db = $this->getDbInstanceForTest();
            $m = new InsertAutoIncrementModel($db);
            //truncate first
            $this->truncateAndResetSequenceSqliteTable($m);
            
            //no need do validation
            $m->setSkipRulesValidation(true);
            
            $data1 = array(
                'name' => 'foo'
            );
            
            $data2 = array(
                'name' => 'bar'
            );
            
            $data = array($data1, $data2);
            $expectedIds = array(1, 2);
            $ids = $m->insertMultiple($data);
            $this->assertEquals($expectedIds, $ids);
	}
        
       
        
        public function testInsertWithoutAutoIncrement() {
            $db = $this->getDbInstanceForTest();
            $m = new InsertNoAutoIncrementModel($db);
            
            //truncate first
            $this->truncateAndResetSequenceSqliteTable($m);
            
            $data = array(
                'id' => 1,
                'name' => 'foo'
            );
            $id = $m->insert($data);
            $this->assertTrue($id);
            
            $data = array(
                'id' => 8,
                'name' => 'bar'
            );
            $id = $m->insert($data);
            $this->assertTrue($id);
         }
        
        
        public function testInsertMultipleWithoutAutoIncrement() { 
            $db = $this->getDbInstanceForTest();
            $m = new InsertNoAutoIncrementModel($db);
            //truncate first
            $this->truncateAndResetSequenceSqliteTable($m);
            
            
            $data1 = array(
                'id' => 2,
                'name' => 'foo'
            );
            
            $data2 = array(
                'id' => 3,
                'name' => 'bar'
            );
            
            $data = array($data1, $data2);
            $expected = array(true, true);
            $ids = $m->insertMultiple($data);
            $this->assertEquals($expected, $ids);
	}
        
        
         public function testUpdate() {
            $db = $this->getDbInstanceForTest();
            $m = new UpdateModel($db);
            
             //no need do validation
            $m->setSkipRulesValidation(true);
             
            $data = array(
                'name' => 'foobar'
            );
            $result = $m->update(1, $data);
            $this->assertTrue($result);
            
            $data = array(
                'name' => 'foo'
            );
            $result = $m->update(1, $data);
            $this->assertTrue($result);
            
            //Data validation failed
            $obj = &get_instance();
            $obj->formvalidation = new FormValidation();//Using for validation
            $m->setSkipRulesValidation(false);
            
            $data = array(
                'name' => null
            );
            $result = $m->update(1, $data);
            $this->assertFalse($result);  
	}
        
        public function testUpdateMultiple() {
            $db = $this->getDbInstanceForTest();
            $m = new UpdateModel($db);
             //no need do validation
            $m->setSkipRulesValidation(true);
          
            $data = array(
                'name' => 'foobar'
            );
            $result = $m->updateMultiple(array(1, 2), $data);
            $this->assertTrue($result);
            
            //Data validation failed
            $obj = &get_instance();
            $obj->formvalidation = new FormValidation();//Using for validation
            $m->setSkipRulesValidation(false);
            
            $data = array(
                'name' => null
            );
            $result = $m->updateMultiple(array(1, 2), $data);
            $this->assertFalse($result);
         }
        
        public function testUpdateCond() {
            $db = $this->getDbInstanceForTest();
            $m = new UpdateModel($db);
            
             //no need do validation
            $m->setSkipRulesValidation(true);
             
            $data = array(
                'name' => 'foobar'
            );
            $result = $m->updateCond('id', 1, $data);
            $this->assertTrue($result);
            
            $data = array(
                'name' => 'foo'
            );
            //using field array of condition
            $result = $m->updateCond('id', array(1,2), $data);
            $this->assertTrue($result);
            
            $data = array(
                'name' => 'foo'
            );
            //using field array of condition
            $result = $m->updateCond(array('id' => 1, 'name' => 'foo'), $data);
            $this->assertTrue($result);
            
            $data = array(
                'name' => 'bar'
            );
            $result = $m->updateCond(array('id' => 2, 'name' => 'bar'), $data);
            $this->assertTrue($result);
            
            //Data validation failed
            $obj = &get_instance();
            $obj->formvalidation = new FormValidation();//Using for validation
            $m->setSkipRulesValidation(false);
            
            $data = array(
                'name' => null
            );
            $result = $m->updateCond('id', 1, $data);
            $this->assertFalse($result);  
	}
        
        public function testUpdateAll() {
            $db = $this->getDbInstanceForTest();
            $m = new UpdateModel($db);
            
             //no need do validation
            $m->setSkipRulesValidation(true);
             
            $data = array(
                'name' => 'allrecord'
            );
            $result = $m->updateAllRecord($data);
            $this->assertTrue($result);
         }
         
         public function testDelete() {
            $db = $this->getDbInstanceForTest();
            $m = new DeleteModel($db);
            $this->prepareDataBeforeDeleteTest($m);
           
            $this->assertTrue($m->delete(1));
         }
         
         public function testDeleteCond() {
            $db = $this->getDbInstanceForTest();
            $m = new DeleteModel($db);
            $this->prepareDataBeforeDeleteTest($m);
           
            $this->assertTrue($m->deleteCond('id', 1));
            
            $this->assertTrue($m->deleteCond(array('id' => 1, 'name' => 'foo')));
         }
         
         
         public function testDeleteListRecord() {
            $db = $this->getDbInstanceForTest();
            $m = new DeleteModel($db);
            $this->prepareDataBeforeDeleteTest($m);
           
            $this->assertTrue($m->deleteListRecord(array(1,3,4)));
            
            $this->assertSame(1, count($m->getListRecord()));
         }
         
         
         public function testRelationship() {
            $db = $this->getDbInstanceForTest();
            $ma = new AuthorModel($db);
            $mp = new PostModel($db);
            
            //Assign database instance to super objet model relationship need it
            $obj = & get_instance();
            $obj->database = $db;
            
            $posts = $mp->with('author')->getSingleRecord(1);
            $author = $ma->with('posts')->getSingleRecord(1);
            
            $this->assertInstanceOf('stdClass', $posts);
            $this->assertInstanceOf('stdClass', $author);
            $this->assertObjectHasAttribute('posts', $author);
            $this->assertObjectHasAttribute('author', $posts);
            $this->assertSame(3, count($author->posts));
            
            //For no result
            $posts = $mp->with('author')->getSingleRecord(1999999);
            $this->assertNull($posts);
            
            //Using return type array
            $posts = $mp->with('author')->asArray()->getSingleRecord(1);
            $author = $ma->with('posts')->asArray()->getSingleRecord(1);
            
            $this->assertArrayHasKey('author', $posts);
            $this->assertArrayHasKey('id', $author);
            $this->assertArrayHasKey('posts', $author);
            $this->assertArrayHasKey('author', $posts);
            $this->assertSame(3, count($author['posts']));
         }
         
         
         public function testRelationshipString() {
            $db = $this->getDbInstanceForTest();
            $m = new UserModel($db);
            
            //Assign database instance to super objet model relationship need it
            $obj = & get_instance();
            $obj->database = $db;
            
            $result = $m->with('country')->getSingleRecord(1);
            
            $this->assertInstanceOf('stdClass', $result);
            $this->assertObjectHasAttribute('country', $result);
            
            //For no result
             $result = $m->with('country')->getSingleRecord(1999999);
             $this->assertNull($result);
         }
        
        
        public function testDropdown() {
            $db = $this->getDbInstanceForTest();
            $m = new DefaultModel($db);
            
            //Using only one param (primary key will be value of options)
            $this->assertArrayHasKey('1', $m->dropdown('name'));
            $this->assertSame(4, count($m->dropdown('name')));
            
            //Using two params (first param will be value of options)
            $this->assertArrayHasKey('bangui', $m->dropdown('name', 'status'));
            $this->assertSame(4, count($m->dropdown('name', 'status')));
         }
         
         public function testCountAllRecord() {
            $db = $this->getDbInstanceForTest();
            $m = new DefaultModel($db);
            
            $this->assertSame(4, $m->countAllRecord());
         }
         
         public function testCountCond() {
            $db = $this->getDbInstanceForTest();
            $m = new DefaultModel($db);
            
            $this->assertSame(1, $m->countCond('name', 'bangui'));
            $this->assertSame(2, $m->countCond('status', 1));
            $this->assertSame(1, $m->countCond(array('status'=> 1, 'name' => 'pk12')));
         }
         
         public function testCached() {
            $db = $this->getDbInstanceForTest();
            $m = new DefaultModel($db); //In DefaultModel the default cache time is 800 sec
            
            $this->assertSame(800, $m->getDb()->getTempCacheTimeToLive());
            $this->assertSame(800, $m->getDb()->getCacheTimeToLive());
            $m->cached(300);
            $this->assertSame(800, $m->getDb()->getCacheTimeToLive());
            $this->assertSame(300, $m->getDb()->getTempCacheTimeToLive());
         }
         
          public function testGetNextAutoIncrementId() {
            $db = $this->getDbInstanceForTest();
            $m = new DefaultModel($db);
            
            $this->assertSame(5, $m->getNextAutoIncrementId());
            
            //No supported driver
            $db = $this->getDbInstanceForTest();
            $db->getConnection()->setDriver('fooooodriver');
            $m->setDb($db);
            $this->assertNull($m->getNextAutoIncrementId());
         }
         
         public function testGetPrimaryKey() {
            $db = $this->getDbInstanceForTest();
            $m = new DefaultModel($db);
            
            $this->assertSame('id', $m->getPrimaryKey());
         }
         
         public function testReturnDataTypeAsArray() {
            $db = $this->getDbInstanceForTest();
            $m = new DefaultModel($db);
            $result = $m->asArray()->getSingleRecord(1);
            $this->assertArrayHasKey('id', $result);
         }
         
         public function testReturnDataTypeAsObject() {
            $db = $this->getDbInstanceForTest();
            $m = new DefaultModel($db);
            $result = $m->asObject()->getSingleRecord(1);
            $this->assertObjectHasAttribute('id', $result);
         }
        
         public function testSoftDelete() {
            $db = $this->getDbInstanceForTest();
            $m = new SoftDeleteModel($db);
            
            $this->prepareDataBeforeSoftDeleteTest($m);
            
            //get record list
            $result = $m->getListRecord();
            $this->assertSame(1, count($result));
            
            //get single record deleted 
            $result = $m->getSingleRecord(1);
            $this->assertNull($result);
            
            //get list record with deleted 
            $result = $m->recordWithDeleted()->getListRecord();
            $this->assertSame(2, count($result));
            
            //get list record only deleted 
            $m = new SoftDeleteModel($db);
            $result = $m->onlyRecordDeleted()->getListRecord();
            $this->assertSame(1, count($result));
            
            //test delete
            $m = new SoftDeleteModel($db);
            $this->assertTrue($m->delete(2));
            $result = $m->getListRecord();
            $this->assertEmpty($result);
            
            $result = $m->recordWithDeleted()->getListRecord();
            $this->assertSame(2, count($result));
         }
         
         public function testTriggerObserverEventProtectColumns() {
            $db = $this->getDbInstanceForTest();
            $m = new TriggerEventModel($db);
            $this->truncateAndResetSequenceSqliteTable($m);
            
            $o = new stdClass();
            $o->name = 'foo';
            $o->age = 34;
            
            $data = array(
                'id' => 3344, //will remove as this is protected columns
                'blob' => $o
            );
            
            $this->assertEquals(1, $m->insert($data));
            //Get it
            //Using result type object
            $result = $m->getSingleRecord(1);
            $this->assertNotEmpty($result);
            $this->assertInstanceOf('stdClass', $result->blob);
            $this->assertSame('foo', $result->blob->name);
            $this->assertSame(34, $result->blob->age);
            
            //Using result type array
            $result = $m->asArray()->getSingleRecord(1);
            $this->assertNotEmpty($result);
            $this->assertInstanceOf('stdClass', $result['blob']);
            $this->assertSame('foo', $result['blob']->name);
            $this->assertSame(34, $result['blob']->age);
         }
         
         public function testSetQueryBuilder() {
            $db = $this->getDbInstanceForTest();
            $m = new DefaultModel($db);
            
            $qb = new DatabaseQueryBuilder($db->getConnection());
            $m->setQueryBuilder($qb);
            $result = $m->getSingleRecord(1);
            $this->assertObjectHasAttribute('id', $result);
         }
         
         public function testOrderByLimit() {
            $db = $this->getDbInstanceForTest();
            $m = new DefaultModel($db);
            
            $result = $m->orderBy('id', 'desc')->limit(0,1)->getListRecord();
            $this->assertObjectHasAttribute('id', $result[0]);
            $this->assertEquals('bouar', $result[0]->name);
            
            //Using array of order
            $result = $m->orderBy(array('status' => 'asc', 'id' => 'desc'))->limit(0,1)->getListRecord();
            $this->assertObjectHasAttribute('id', $result[0]);
            $this->assertEquals('begoua', $result[0]->name);
         }
         
         
         public function testSetWhereValues() {
            $db = $this->getDbInstanceForTest();
            $m = new DefaultModel($db);
            
            /* One param
               * array
               1- array that have a key and value is an array
               2- array that have numeric key with string value
               * string            
            */
            //array#1
            $result = $m->getListRecordCond(array('id' => array(1,3), 'status' => 0));
            $this->assertSame(2, count($result));
            //array#2
            $result = $m->getListRecordCond(array('id'));
            $this->assertEmpty($result);
            
            //string
            $result = $m->getListRecordCond('status');
            $this->assertEmpty($result);
            
            
            /* Two params
               1- 2nd param is an array
               2- 2nd param is not an array 
            */
            //#1
            $result = $m->getListRecordCond('id', array(1,3,2));
            $this->assertSame(3, count($result));
            
            //#2
            $result = $m->getListRecordCond('status', 1);
            $this->assertSame(2, count($result)); 

            /* Three params
               1st param is field
               2nd param is operator
               3rd param is the value
            */
            $result = $m->getListRecordCond('id', '>', 3);
            $this->assertSame(1, count($result));
         }
        
        
        /**
        * Return the database instance for test
        */
        private function getDbInstanceForTest() {
            
            $cfg = $this->getDbConfig();
            $connection = new DatabaseConnection($cfg, true);
            $db = new Database($connection);
                        
            $qr = new DatabaseQueryRunner($connection);
            $qr->setBenchmark(new Benchmark());
            
            $qresult = new DatabaseQueryResult();
            $qr->setQueryResult($qresult);
            
            $db->setQueryRunner($qr);
            
            $qb = new DatabaseQueryBuilder($connection);
            $db->setQueryBuilder($qb);
            
            return $db;
        }
        
        /**
        * Truncate the table and reset SQLite sequence 
        */
        private function truncateAndResetSequenceSqliteTable($m) {
            $db = $this->getDbInstanceForTest();
            $this->assertTrue($m->truncate());
            $db->getQueryBuilder()->from('sqlite_sequence')->where('name', $m->getTable());
            $this->assertTrue($db->delete());
        }
        
        /**
        * Prepare data for delete tests
        */
        private function prepareDataBeforeDeleteTest($m) {
            $db = $this->getDbInstanceForTest();
            $this->assertTrue($m->truncate());
            
            $data = array(
                array(
                    'id' => 1,
                    'name' => 'foo'
                ),
                array(
                    'id' => 2,
                    'name' => 'bar'
                ),
                array(
                    'id' => 3,
                    'name' => 'foobar'
                ),
                array(
                    'id' => 4,
                    'name' => 'barfoo'
                )
            );
            $this->assertSame(4, count($m->insertMultiple($data)));
        }
        
        /**
        * Prepare data for soft delete tests
        */
        private function prepareDataBeforeSoftDeleteTest($m) {
            $db = $this->getDbInstanceForTest();
            $this->assertTrue($m->truncate());
            
            $data = array(
                array(
                    'id' => 1,
                    'name' => 'foo',
                    'deleted' => 1
                ),
                array(
                    'id' => 2,
                    'name' => 'bar',
                    'deleted' => 0
                )
            );
            $this->assertSame(2, count($m->insertMultiple($data)));
        }


	}
