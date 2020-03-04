<?php 

	
	class LoaderTest extends TnhTestCase
	{	
		
		public function testLoadModel()
		{
            $l = new Loader();
            $obj = & get_instance();
			$l->model('DBSessionModel');
            //already exists nothing will be done
            //change some attribute
            $obj->dbsessionmodel->foo = 'bar';
            $this->assertSame('bar', $obj->dbsessionmodel->foo);
			$l->model('DBSessionModel');
            //the value will not overwrite
            $this->assertSame('bar',$obj->dbsessionmodel->foo);
			$l->model('DBSessionModel', 'fooInstance');
            $this->assertInstanceOf('DBSessionModel', $obj->dbsessionmodel);
            $this->assertInstanceOf('DBSessionModel', $obj->fooInstance);
            $this->assertObjectHasAttribute('dbsessionmodel', $obj);
            $this->assertAttributeInstanceOf('DBSessionModel', 'dbsessionmodel', $obj);
		}
        
        public function testLoadModelInMobule()
		{
            $l = new Loader();
            $obj = & get_instance();
            $m = new Module();
            $m->init();
			$l->model('testmodule/ModuleModelTest');
			$l->model('testmodule/ModuleModelTest', 'mod');
            
            $this->assertInstanceOf('ModuleModelTest', $obj->modulemodeltest);
            $this->assertInstanceOf('ModuleModelTest', $obj->mod);
		}
        
        public function testLoadModelClassNameNotSameFileName()
		{
            $l = new Loader();
            $obj = & get_instance();
			$l->model('ModelClassNotSameFileName');
            $this->assertFalse(isset($obj->modelclassnotsamefilename));
		}
        
        public function testLoadModelInModuleUsingModuleNameInController()
		{
            $obj = & get_instance();
            $obj->moduleName = 'testmodule';
            $l = new Loader();
            $m = new Module();
            $m->init();
			$l->model('ModuleModelTest');
			$l->model('ModuleModelTest', 'mod');
            
            $this->assertInstanceOf('ModuleModelTest', $obj->modulemodeltest);
            $this->assertInstanceOf('ModuleModelTest', $obj->mod);
		}
        
         public function testLoadModelNotExists()
		{
            $l = new Loader();
            $obj = & get_instance();
			$l->model('UnkownModel');
            $this->assertFalse(isset($obj->unkownmodel));   
		}
        
        public function testLoadLibrary()
		{
            $l = new Loader();
            $obj = & get_instance();
			$l->library('LibraryTest');
            //already exists nothing will be done
            //change some attribute
            $obj->librarytest->foo = 'bar';
            $this->assertSame('bar', $obj->librarytest->foo);
			$l->library('LibraryTest');
            //the value will not overwrite
            $this->assertSame('bar', $obj->librarytest->foo);
			$l->library('LibraryTest', 'fooInstance');
            $this->assertInstanceOf('LibraryTest', $obj->librarytest);
            $this->assertInstanceOf('LibraryTest', $obj->fooInstance);
		}
        
        public function testLoadDatabase()
		{
            $l = new Loader();
            $obj = & get_instance();
			$l->library('Database');
            $this->assertInstanceOf('Database', $obj->database);
		}
        
        public function testLoadSystemLibrary()
		{
            $l = new Loader();
            $obj = & get_instance();
			$l->library('Form');
            $this->assertInstanceOf('Form', $obj->form);
		}
        
        public function testLoadLibraryClassNameNotSameFileName()
		{
            $l = new Loader();
            $obj = & get_instance();
			$l->library('LibraryClassNotSameFileName');
            $this->assertFalse(isset($obj->libraryclassnotsamefilename));
		}
        
         public function testLoadLibraryInMobule()
		{
            $l = new Loader();
            $obj = & get_instance();
            $m = new Module();
            $m->init();
			$l->library('testmodule/ModuleLibraryTest');
			$l->library('testmodule/ModuleLibraryTest', 'mod');
            
            $this->assertInstanceOf('ModuleLibraryTest', $obj->modulelibrarytest);
            $this->assertInstanceOf('ModuleLibraryTest', $obj->mod);
		}
        
        public function testLoadLibraryInModuleUsingModuleNameInController()
		{
            $obj = & get_instance();
            $obj->moduleName = 'testmodule';
            $m = new Module();
            $m->init();
            $l = new Loader();
            $l->library('ModuleLibraryTest');
			$l->library('ModuleLibraryTest', 'mod');
            
            $this->assertInstanceOf('ModuleLibraryTest', $obj->modulelibrarytest);
            $this->assertInstanceOf('ModuleLibraryTest', $obj->mod);
		}
        
        
        public function testLoadLibraryNotExists()
		{
            $l = new Loader();
            $obj = & get_instance();
			$l->library('UnkownLibrary');
            $this->assertFalse(isset($obj->unkownlibrary));   
		}
        
        public function testLoadFunction()
		{
            $l = new Loader();
            $l->functions('test');
            $this->assertTrue(is_callable('foo_test'));
            //already exists nothing will be done
            $l->functions('test');
            $this->assertTrue(is_callable('foo_test'));
		}
        
        public function testLoadFunctionInModule()
		{
            $m = new Module();
            $m->init();
            $l = new Loader();
            $l->functions('testmodule/module_test');
            $this->assertTrue(is_callable('foo_module_test'));
            //already exists nothing will be done
            $l->functions('testmodule/module_test');
            $this->assertTrue(is_callable('foo_module_test'));
		}
        
        public function testLoadFunctionInModuleUsingModuleNameInController()
		{
            $obj = & get_instance();
            $obj->moduleName = 'testmodule';
            $m = new Module();
            $m->init();
            $l = new Loader();
            $l->functions('module_test');
            $this->assertTrue(is_callable('foo_module_test'));
            //already exists nothing will be done
            $l->functions('module_test');
            $this->assertTrue(is_callable('foo_module_test'));
		}
        
        public function testLoadFunctionNotExists()
		{
            $l = new Loader();
            $l->functions('unkown_function');
            $this->assertTrue(true);   
		}
        
        
        public function testLoadConfig()
		{
            $l = new Loader();
            $l->config('test');
            $this->assertSame('bar', $this->config->get('cfg_test'));
            //already exists nothing will be done
            $l->config('test');
            $this->assertSame('bar', $this->config->get('cfg_test'));
		}
        
        
        
        public function testLoadConfigInModule()
		{
            $m = new Module();
            $m->init();
            $l = new Loader();
            $l->config('testmodule/config');
            $this->assertSame('foo', $this->config->get('cfg_module'));
            //already exists nothing will be done
            $l->config('testmodule/config');
            $this->assertSame('foo', $this->config->get('cfg_module'));
		}
        
        
        public function testLoadConfigInModuleUsingModuleNameInController()
		{
            $obj = & get_instance();
            $obj->moduleName = 'testmodule';
            
            $m = new Module();
            $m->init();
            $l = new Loader();
            $l->config('config');
            $this->assertSame('foo', $this->config->get('cfg_module'));
            //already exists nothing will be done
            $l->config('config');
            $this->assertSame('foo', $this->config->get('cfg_module'));
		}
        
        
        public function testLoadConfigNotExists()
		{
            $l = new Loader();
            $l->config('unkown_config');
            $this->assertTrue(true);   
		}
        
        
        public function testLoadLang()
		{
            
            $this->config->set('default_language', 'en');
            $obj = & get_instance();
            $lg = $obj->lang;
            
            $l = new Loader();
            $l->lang('test');
            
            $this->assertNotEmpty($lg->getAll());
            
            $this->assertSame('foo lang', $lg->get('l_test'));
            //already exists nothing will be done
            $l->lang('test');
            $this->assertSame('foo lang', $lg->get('l_test'));
		}
        
        public function testLoadLangInModule()
		{
            
            $this->config->set('default_language', 'en');
            $obj = & get_instance();
            $lg = $obj->lang;
            $m = new Module();
            $m->init();
            $l = new Loader();
            $l->lang('testmodule/module_test');
            $this->assertSame('foo module lang', $lg->get('l_module'));
            //already exists nothing will be done
            $l->lang('testmodule/module_test');
            $this->assertSame('foo module lang', $lg->get('l_module'));
		}
        
        public function testLoadLangInModuleUsingModuleNameInController()
		{
            $this->config->set('default_language', 'en');
            
            $obj = & get_instance();
            $lg = $obj->lang;
            $m = new Module();
            $m->init();
            $obj->moduleName = 'testmodule';
            $m = new Module();
            $m->init();
            $l = new Loader();
            $l->lang('module_test');
            $this->assertSame('foo module lang', $lg->get('l_module'));
            //already exists nothing will be done
            $l->lang('module_test');
            $this->assertSame('foo module lang', $lg->get('l_module'));
		}
        
        public function testLoadLangUsingAppLangFromCookie()
		{
            $_COOKIE['clang'] = 'en';
            
            $this->config->set('language_cookie_name', 'clang');
            $this->config->set('default_language', 'fr');
            $obj = & get_instance();
            $lg = $obj->lang;
            
            $l = new Loader();
            $l->lang('test');
            
            $this->assertNotEmpty($lg->getAll());
            
            $this->assertSame('foo lang', $lg->get('l_test'));
            //already exists nothing will be done
            $l->lang('test');
            $this->assertSame('foo lang', $lg->get('l_test'));
		}
        
        
        public function testLoadLangNotExists()
		{
            $l = new Loader();
            $l->lang('unkown_config');
            $this->assertTrue(true);   
		}


	}