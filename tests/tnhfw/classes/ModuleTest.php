<?php 

	 /**
     * Module class tests
     *
     * @group core
     * @group core_classes
     */
	class ModuleTest extends TnhTestCase {	
		
        public function testHasModule()
		{
            $m = new Module();
            $this->assertNotEmpty($m->getModuleList());
			$this->assertSame(1, count($m->getModuleList()));
			$this->assertTrue($m->hasModule());
		}
        
        public function testAddNewModule()
		{
            $m = new Module();
            $this->assertNotEmpty($m->getModuleList());
			$this->assertSame(1, count($m->getModuleList()));
            $m->add('fooModule');
            $this->assertSame(2, count($m->getModuleList()));
		}
        
        public function testAddNewModuleAlreadyExists()
		{
            $m = new Module();
            $this->assertNotEmpty($m->getModuleList());
			$this->assertSame(1, count($m->getModuleList()));
            $m->add('testmodule');
            $this->assertSame(1, count($m->getModuleList()));   
		}
        
        public function testRemoveModuleNotExist()
		{
            $m = new Module();
            $this->assertNotEmpty($m->getModuleList());
			$this->assertSame(1, count($m->getModuleList()));
            $m->remove('fooModule');
            $this->assertSame(1, count($m->getModuleList()));
		}
        
        public function testRemoveModuleExist()
		{
            $m = new Module();
            $this->assertNotEmpty($m->getModuleList());
			$this->assertSame(1, count($m->getModuleList()));
            $m->remove('testmodule');
            $this->assertSame(0, count($m->getModuleList()));
		}
        
        public function testRemoveAllModule()
		{
            $m = new Module();
            $this->assertNotEmpty($m->getModuleList());
			$this->assertSame(1, count($m->getModuleList()));
            $m->add('fooModule');
            $this->assertSame(2, count($m->getModuleList()));
            $m->removeAll();
            $this->assertSame(0, count($m->getModuleList()));
		}
        
        public function testGetModulesAutoloadConfigWhenNoModule()
		{
            $m = new Module();
            $this->assertNotEmpty($m->getModuleList());
            $m->removeAll();
            $this->assertSame(0, count($m->getModuleList()));
            $this->assertFalse($m->getModulesAutoloadConfig()); 
		}
        
        public function testGetModulesAutoloadConfigWhenHaveModule()
		{
            $m = new Module();
            $this->assertNotEmpty($m->getModuleList());
            $this->assertSame(1, count($m->getModuleList()));
            $this->assertNotEmpty($m->getModulesAutoloadConfig()); 
		}
        
        
        public function testGetModulesRoutesConfigWhenNoModule()
		{
            $m = new Module();
            $this->assertNotEmpty($m->getModuleList());
            $m->removeAll();
            $this->assertSame(0, count($m->getModuleList()));
            $this->assertFalse($m->getModulesRoutesConfig()); 
		}
        
        public function testGetModulesRoutesConfigWhenHaveModule()
		{
            $m = new Module();
            $this->assertNotEmpty($m->getModuleList());
            $this->assertSame(1, count($m->getModuleList()));
            $this->assertNotEmpty($m->getModulesRoutesConfig()); 
		}
        
        public function testFindControllerFullPath()
		{
            $m = new Module();
            $this->assertNotEmpty($m->getModuleList());
            $this->assertSame(1, count($m->getModuleList()));
            $this->assertNotFalse($m->findControllerFullPath('TestModuleController', 'testmodule')); 
		}
        
        public function testFindModelFullPath()
		{
            $m = new Module();
            $this->assertNotEmpty($m->getModuleList());
            $this->assertSame(1, count($m->getModuleList()));
            $this->assertNotFalse($m->findModelFullPath('ModuleModelTest', 'testmodule')); 
		}
        
        public function testFindLibraryFullPath()
		{
            $m = new Module();
            $this->assertNotEmpty($m->getModuleList());
            $this->assertSame(1, count($m->getModuleList()));
            $this->assertNotFalse($m->findLibraryFullPath('ModuleLibraryTest', 'testmodule')); 
		}
        
        public function testFindConfigFullPath()
		{
            $m = new Module();
            $this->assertNotEmpty($m->getModuleList());
            $this->assertSame(1, count($m->getModuleList()));
            $this->assertNotFalse($m->findConfigFullPath('config', 'testmodule')); 
		}
        
        public function testFindFunctionFullPath()
		{
            $m = new Module();
            $this->assertNotEmpty($m->getModuleList());
            $this->assertSame(1, count($m->getModuleList()));
            $this->assertNotFalse($m->findFunctionFullPath('module_test', 'testmodule')); 
		}
        
        public function testFindViewFullPath()
		{
            $m = new Module();
            $this->assertNotEmpty($m->getModuleList());
            $this->assertSame(1, count($m->getModuleList()));
            $this->assertNotFalse($m->findViewFullPath('module_view', 'testmodule')); 
		}
        
        public function testFindLanguageFullPath()
		{
            $m = new Module();
            $this->assertNotEmpty($m->getModuleList());
            $this->assertSame(1, count($m->getModuleList()));
            $this->assertNotFalse($m->findLanguageFullPath('module_test', 'en', 'testmodule')); 
		}
        
        public function testFindResourceNotExistForNoClass()
		{
            $m = new Module();
            $this->assertNotEmpty($m->getModuleList());
            $this->assertSame(1, count($m->getModuleList()));
            $this->assertFalse($m->findFunctionFullPath('foobarbaz', 'testmodule')); 
		}
        
        
        public function testFindResourceNotExistForClass()
		{
            $m = new Module();
            $this->assertNotEmpty($m->getModuleList());
            $this->assertSame(1, count($m->getModuleList()));
            $this->assertFalse($m->findModelFullPath('foobarbaz', 'testmodule')); 
		}

	}
