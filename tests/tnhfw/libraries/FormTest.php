<?php 
	
    /**
     * Form library class tests
     *
     * @group core
     * @group libraries
     * @group html
     */
	class FormTest extends TnhTestCase {

		public function testOpen() {
            $obj = & get_instance();
            $obj->url = new Url();
            $f = new Form();
            $expected = '<form action = "" method = "POST" accept-charset = "UTF-8">';
			$this->assertSame($expected, $f->open());
            
            //using attributes
            $expected = '<form action = "" method = "POST" class = "foo" id = "bar" accept-charset = "UTF-8">';
			$this->assertSame($expected, $f->open(null, array('class' => 'foo', 'id' => 'bar')));
            
            //using param path
            $base_url = 'http://localhost/';
            $this->config->set('base_url', $base_url);
            $expected = '<form action = "'.$base_url.'controller/method" method = "POST" accept-charset = "UTF-8">';
			$this->assertSame($expected, $f->open('controller/method'));
            
            //When CSRF is enabled
            $this->config->set('csrf_enable', true);
			$this->config->set('csrf_key', 'kcsrf');
			$this->config->set('csrf_expire', 100);
            
            $csrfValue = uniqid();
            $_SESSION['kcsrf'] =  $csrfValue;
            $_SESSION['csrf_expire'] = time() + 600;
            
            $obj = &get_instance();
            $obj->security = new Security();
            
            $expected = '<form action = "" method = "POST" accept-charset = "UTF-8"><input type = "hidden" name = "kcsrf" value = "'.$csrfValue.'"/>';
			$this->assertSame($expected, $f->open());
            
            //using charset in config
            $this->config->set('charset', 'foo');
            $this->config->set('csrf_enable', false);
            $expected = '<form action = "" method = "POST" accept-charset = "foo">';
			$this->assertSame($expected, $f->open());
            
		}
        
        public function testOpenMultipart() {
            $f = new Form();
            $expected = '<form action = "" method = "POST" enctype = "multipart/form-data" accept-charset = "UTF-8">';
			$this->assertSame($expected, $f->openMultipart());
		}
        
        public function testClose() {
            $f = new Form();
            $expected = '</form>';
			$this->assertSame($expected, $f->close());
		}
        
        public function testFieldset() {
            $f = new Form();
            $expected = '<fieldset>';
			$this->assertSame($expected, $f->fieldset());
            
            //using legend
            $expected = '<fieldset><legend>foo</legend>';
			$this->assertSame($expected, $f->fieldset('foo'));
            
             //using fieldset attributes
            $expected = '<fieldset class = "baz"><legend>foo</legend>';
			$this->assertSame($expected, $f->fieldset('foo', array('class' => 'baz')));
            
             //using legend attributes
            $expected = '<fieldset class = "baz"><legend id = "bar">foo</legend>';
			$this->assertSame($expected, $f->fieldset('foo', array('class' => 'baz'), array('id' => 'bar')));
		}
        
        public function testFieldsetClose() {
            $f = new Form();
            $expected = '</fieldset>';
			$this->assertSame($expected, $f->fieldsetClose());
		}
        
        public function testError() {
            $fieldName = 'fooname';
            
            $fv = $this->getMockBuilder('FormValidation')->getMock();
			$fv->expects($this->any())
                    ->method('getErrors')
                    ->will($this->returnValue(array($fieldName => 'foo error message')));
                    
            $obj = & get_instance();
            $obj->formvalidation = $fv;
            
            $f = new Form();
            $expected = 'foo error message';
			$this->assertSame($expected, $f->error($fieldName));
		}
        
        public function testValue() {
            $fieldName = 'fooname';
            $fieldValue = 'foovalue';
            $_POST[$fieldName] = $fieldValue;
            
            
            $request = $this->getMockBuilder('Request')->getMock();
			$request->expects($this->any())
                    ->method('query')
                    ->with($fieldName)
                    ->will($this->returnValue($fieldValue));
                    
            $obj = & get_instance();
            $obj->request = $request;
            
            $f = new Form();
            $this->assertSame($fieldValue, $f->value($fieldName));
		}
        
        public function testValueNotExist() {
           
            $request = $this->getMockBuilder('Request')->getMock();
			$request->expects($this->any())
                    ->method('query')
                    ->will($this->returnValue(null));
                    
            $obj = & get_instance();
            $obj->request = $request;
            
            $f = new Form();
            $this->assertNull($f->value('foobarbaz'));
		}
        
        public function testLabel() {
            $f = new Form();
            $expected = '<label>foo</label>';
			$this->assertSame($expected, $f->label('foo'));
            
            //using for
            $expected = '<label for = "bar">foo</label>';
			$this->assertSame($expected, $f->label('foo', 'bar'));
            
             //using html attributes
            $expected = '<label class = "baz" for = "bar">foo</label>';
			$this->assertSame($expected, $f->label('foo', 'bar', array('class' => 'baz')));
		}
        
        
        public function testInput() {
            $f = new Form();
            //default value
            $expected = '<input type = "text" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->input('foo'));
            
            //using html attributes
            $expected = '<input class = "barbar" type = "text" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->input('foo', '', array('class' => 'barbar')));
		}
        
        public function testText() {
            $f = new Form();
            //default value
            $expected = '<input type = "text" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->text('foo'));
            
            //using html attributes
            $expected = '<input class = "barbar" type = "text" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->text('foo', '', array('class' => 'barbar')));
		}
        
        public function testPassword() {
            $f = new Form();
            //default value
            $expected = '<input type = "password" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->password('foo'));
            
            //using html attributes
            $expected = '<input class = "barbar" id = "baz" type = "password" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->password('foo', '', array('class' => 'barbar', 'id' => 'baz')));
		}
        
        public function testRadio() {
            $f = new Form();
            //default value
            $expected = '<input type = "radio" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->radio('foo'));
            
            //using html attributes
            $expected = '<input class = "barbar" id = "baz" type = "radio" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->radio('foo', '', false, array('class' => 'barbar', 'id' => 'baz')));
            
            //Using checked
            $expected = '<input checked = "1" type = "radio" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->radio('foo', '', true));
		}
        
        public function testCheckbox() {
            $f = new Form();
            //default value
            $expected = '<input type = "checkbox" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->checkbox('foo'));
            
            //using html attributes
            $expected = '<input class = "barbar" id = "baz" type = "checkbox" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->checkbox('foo', '', false, array('class' => 'barbar', 'id' => 'baz')));
            
            //Using checked
            $expected = '<input checked = "1" type = "checkbox" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->checkbox('foo', '', true));
		}
        
        public function testNumber() {
            $f = new Form();
            //default value
            $expected = '<input type = "number" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->number('foo'));
            
            //using html attributes
            $expected = '<input class = "barbar" id = "baz" type = "number" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->number('foo', '', array('class' => 'barbar', 'id' => 'baz')));
		}
        
        public function testPhone() {
            $f = new Form();
            //default value
            $expected = '<input type = "phone" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->phone('foo'));
            
            //using html attributes
            $expected = '<input class = "barbar" id = "baz" type = "phone" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->phone('foo', '', array('class' => 'barbar', 'id' => 'baz')));
		}
        
        public function testEmail() {
            $f = new Form();
            //default value
            $expected = '<input type = "email" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->email('foo'));
            
            //using html attributes
            $expected = '<input class = "barbar" id = "baz" type = "email" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->email('foo', '', array('class' => 'barbar', 'id' => 'baz')));
		}
        
        public function testSearch() {
            $f = new Form();
            //default value
            $expected = '<input type = "search" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->search('foo'));
            
            //using html attributes
            $expected = '<input class = "barbar" id = "baz" type = "search" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->search('foo', '', array('class' => 'barbar', 'id' => 'baz')));
		}
        
        public function testHidden() {
            $f = new Form();
            //default value
            $expected = '<input type = "hidden" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->hidden('foo'));
            
            //using html attributes
            $expected = '<input class = "barbar" id = "baz" type = "hidden" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->hidden('foo', '', array('class' => 'barbar', 'id' => 'baz')));
		}
        
        public function testFile() {
            $f = new Form();
            //default value
            $expected = '<input type = "file" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->file('foo'));
            
            //using html attributes
            $expected = '<input accept = "images/*" id = "baz" type = "file" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->file('foo', array('accept' => 'images/*', 'id' => 'baz')));
		}
        
        public function testButton() {
            $f = new Form();
            //default value
            $expected = '<input type = "button" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->button('foo'));
            
            //using html attributes
            $expected = '<input class = "barbar" id = "baz" type = "button" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->button('foo', '', array('class' => 'barbar', 'id' => 'baz')));
		}
        
        public function testReset() {
            $f = new Form();
            //default value
            $expected = '<input type = "reset" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->reset('foo'));
            
            //using html attributes
            $expected = '<input class = "barbar" id = "baz" type = "reset" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->reset('foo', '', array('class' => 'barbar', 'id' => 'baz')));
		}
        
        public function testSubmit() {
            $f = new Form();
            //default value
            $expected = '<input type = "submit" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->submit('foo'));
            
            //using html attributes
            $expected = '<input class = "barbar" id = "baz" type = "submit" name = "foo" value = ""/>';
			$this->assertSame($expected, $f->submit('foo', '', array('class' => 'barbar', 'id' => 'baz')));
		}
        
        public function testTextarea() {
            $f = new Form();
            $expected = '<textarea name = "foo"></textarea>';
			$this->assertSame($expected, $f->textarea('foo'));
            
             //using html attributes
            $expected = '<textarea name = "foo" class = "baz">bar</textarea>';
			$this->assertSame($expected, $f->textarea('foo', 'bar', array('class' => 'baz')));
		}
        
        public function testSelect() {
            $f = new Form();
            //Default
            $expected = '<select name = "foo"></select>';
			$this->assertSame($expected, $f->select('foo'));
            
            //Option values set
            $values = array(
                'a' => 'foo',
                'b' => 'bar',
                'c' => 'baz'
            );
            $expected = '<select name = "foo"><option value = "a">foo</option><option value = "b">bar</option><option value = "c">baz</option></select>';
			$this->assertSame($expected, $f->select('foo', $values));
            
            //using selected
            $expected = '<select name = "foo"><option value = "a">foo</option><option value = "b" selected>bar</option><option value = "c">baz</option></select>';
			$this->assertSame($expected, $f->select('foo', $values, 'b'));
            
           
		}
	}