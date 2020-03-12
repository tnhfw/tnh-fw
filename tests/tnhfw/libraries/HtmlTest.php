<?php 

	/**
     * Html library class tests
     *
     * @group core
     * @group libraries
     * @group html
     */
	class HtmlTest extends TnhTestCase {	
		
		public function testAnchor() {
            $h = new Html();
            $expected = '<a href = ""></a>';
			$this->assertSame($expected, $h->anchor());
            
            //using attributes
            $expected = '<a href = "" class = "foo" id = "bar">foo</a>';
			$this->assertSame($expected, $h->anchor(null, 'foo', array('class' => 'foo', 'id' => 'bar')));
            
             //using absolute url
            $url = 'http://www.foo.bar/';
            $expected = '<a href = "'.$url.'">'.$url.'</a>';
			$this->assertSame($expected, $h->anchor($url));
            
            //Display it directly
            $expected = '<a href = ""></a>';
            $this->expectOutputString($expected);
			$h->anchor(null, null, array(), false);
            
             //using base url
            $base_url = 'http://localhost/';
            $this->config->set('base_url', $base_url);
            $expected = '<a href = "'.$base_url.'controller/method">'.$base_url.'controller/method</a>';
			$this->assertSame($expected, $h->anchor('controller/method'));
		}
        
        public function testMailto() {
            $h = new Html();
            $expected = '<a href = "mailto:"></a>';
			$this->assertSame($expected, $h->mailto(''));
            
            //using attributes
            $expected = '<a href = "mailto:foo@bar.com" class = "foo" id = "bar">foo</a>';
			$this->assertSame($expected, $h->mailto('foo@bar.com', 'foo', array('class' => 'foo', 'id' => 'bar')));
            
            //Display it directly
            $expected = '<a href = "mailto:foo@bar.com">foo@bar.com</a>';
            $this->expectOutputString($expected);
			$h->mailto('foo@bar.com', null, array(), false);
		}
        
        public function testBr() {
            $h = new Html();
            $expected = '<br />';
			$this->assertSame($expected, $h->br());
            
            //using parameter if count
            $expected = '<br /><br />';
			$this->assertSame($expected, $h->br(2));
            
            //Display it directly
            $expected = '<br /><br /><br />';
            $this->expectOutputString($expected);
			$h->br(3, false);
		}
        
        public function testHr() {
            $h = new Html();
            $expected = '<hr />';
			$this->assertSame($expected, $h->hr());
            
            //using parameter count
            $expected = '<hr /><hr />';
			$this->assertSame($expected, $h->hr(2));
            
            //using attributes
            $expected = '<hr class = "foo" id = "bar" /><hr class = "foo" id = "bar" />';
			$this->assertSame($expected, $h->hr(2, array('class' => 'foo', 'id' => 'bar')));
            
            //Display it directly
            $expected = '<hr /><hr /><hr /><hr /><hr />';
            $this->expectOutputString($expected);
			$h->hr(5, array(), false);
		}
        
        public function testHead() {
            $h = new Html();
            $expected = '<h1></h1>';
			$this->assertSame($expected, $h->head());
            $expected = '<h1></h1>';
			$this->assertSame($expected, $h->head(1));
            $expected = '<h2></h2>';
			$this->assertSame($expected, $h->head(2));
            $expected = '<h3></h3>';
			$this->assertSame($expected, $h->head(3));
            $expected = '<h4></h4>';
			$this->assertSame($expected, $h->head(4));
            $expected = '<h5></h5>';
			$this->assertSame($expected, $h->head(5));
            $expected = '<h6></h6>';
			$this->assertSame($expected, $h->head(6));
            
            //using parameter count
            $expected = '<h1></h1><h1></h1>';
			$this->assertSame($expected, $h->head(1, null, 2));
            
            //using attributes
            $expected = '<h1 class = "foo" id = "bar">foo</h1><h1 class = "foo" id = "bar">foo</h1>';
			$this->assertSame($expected, $h->head(1, 'foo', 2, array('class' => 'foo', 'id' => 'bar')));
            
            //Display it directly
            $expected = '<h5></h5><h5></h5><h5></h5>';
            $this->expectOutputString($expected);
			$h->head(5, null, 3, array(), false);
		}
        
        public function testUl() {
            $h = new Html();
            $expected = '<ul></ul>';
			$this->assertSame($expected, $h->ul());
            
            $expected = '<ul><li>1</li><li>2</li></ul>';
			$this->assertSame($expected, $h->ul(array(1, 2)));
            
            
            //using attributes
            //only ul 
            $expected = '<ul class = "foo" id = "bar"><li>1</li><li>2</li></ul>';
			$this->assertSame($expected, $h->ul(array(1, 2), array(
                                                                    'ul' => array('class' => 'foo', 'id' => 'bar')
                                                                    )));
                                                                    
             //only li
            $expected = '<ul><li class = "foo" id = "bar">1</li><li class = "foo" id = "bar">2</li></ul>';
			$this->assertSame($expected, $h->ul(array(1, 2), array(
                                                                    'li' => array('class' => 'foo', 'id' => 'bar')
                                                                    )));
              //all ul and li
            $expected = '<ul class = "fooz" id = "baz"><li class = "foo" id = "bar">1</li><li class = "foo" id = "bar">2</li></ul>';
			$this->assertSame($expected, $h->ul(array(1, 2), array(
                                                                    'ul' => array('class' => 'fooz', 'id' => 'baz'),
                                                                    'li' => array('class' => 'foo', 'id' => 'bar')
                                                                    )));
            
            //Display it directly
            $expected = '<ul><li>1</li><li>2</li><li>4</li></ul>';
            $this->expectOutputString($expected);
			$h->ul(array(1, 2, 4), array(), false);
		}
        
        public function testol() {
            $h = new Html();
            $expected = '<ol></ol>';
			$this->assertSame($expected, $h->ol());
            
            $expected = '<ol><li>1</li><li>2</li></ol>';
			$this->assertSame($expected, $h->ol(array(1, 2)));
            
            
            //using attributes
            //only ol 
            $expected = '<ol class = "foo" id = "bar"><li>1</li><li>2</li></ol>';
			$this->assertSame($expected, $h->ol(array(1, 2), array(
                                                                    'ol' => array('class' => 'foo', 'id' => 'bar')
                                                                    )));
                                                                    
             //only li
            $expected = '<ol><li class = "foo" id = "bar">1</li><li class = "foo" id = "bar">2</li></ol>';
			$this->assertSame($expected, $h->ol(array(1, 2), array(
                                                                    'li' => array('class' => 'foo', 'id' => 'bar')
                                                                    )));
              //all ol and li
            $expected = '<ol class = "fooz" id = "baz"><li class = "foo" id = "bar">1</li><li class = "foo" id = "bar">2</li></ol>';
			$this->assertSame($expected, $h->ol(array(1, 2), array(
                                                                    'ol' => array('class' => 'fooz', 'id' => 'baz'),
                                                                    'li' => array('class' => 'foo', 'id' => 'bar')
                                                                    )));
            
            //Display it directly
            $expected = '<ol><li>1</li><li>2</li><li>4</li></ol>';
            $this->expectOutputString($expected);
			$h->ol(array(1, 2, 4), array(), false);
		}
        
        public function testTable() {
            $h = new Html();
            $expected = '<table><thead><tr></tr></thead><tbody></tbody></table>';
			$this->assertSame($expected, $h->table());
            
            //using header
            $expected = '<table><thead><tr><th>foo</th><th>bar</th></tr></thead><tbody></tbody></table>';
			$this->assertSame($expected, $h->table(array('foo', 'bar')));
            
            //using footer
            $expected = '<table>'
                               . '<thead><tr><th>foo</th><th>bar</th></tr></thead>'
                               . '<tbody></tbody>'
                               . '<tfoot><tr><th>foo</th><th>bar</th></tr></tfoot>'
                          . '</table>';
			$this->assertSame($expected, $h->table(array('foo', 'bar'), array(), array(), true));
            
            //using content
            $expected = '<table>'
                           . '<thead><tr><th>foo</th><th>bar</th></tr></thead>'
                           . '<tbody><tr><td>1</td><td>2</td></tr><tr><td>3</td><td>4</td></tr></tbody>'
                           . '<tfoot><tr><th>foo</th><th>bar</th></tr></tfoot>'
                        .'</table>';
			$this->assertSame($expected, $h->table(array('foo', 'bar'), array(array('1', '2'), array('3', '4')), array(), true));
            
            //using attributes
            //table attributes
            $expected = '<table class = "foo" id = "bar"><thead><tr></tr></thead><tbody></tbody></table>';
			$this->assertSame($expected, $h->table(array(), array(), array('table' => array('class' => 'foo', 'id' => 'bar'))));
            
            //thead attributes
            $expected = '<table><thead class = "foo" id = "bar"><tr></tr></thead><tbody></tbody></table>';
			$this->assertSame($expected, $h->table(array(), array(), array('thead' => array('class' => 'foo', 'id' => 'bar'))));
            
            //thead > tr attributes
            $expected = '<table><thead><tr class = "foo" id = "bar"></tr></thead><tbody></tbody></table>';
			$this->assertSame($expected, $h->table(array(), array(), array('thead_tr' => array('class' => 'foo', 'id' => 'bar'))));
            
            //thead > th attributes
            $expected = '<table><thead><tr><th class = "foo" id = "bar">foo</th><th class = "foo" id = "bar">bar</th></tr></thead><tbody></tbody></table>';
			$this->assertSame($expected, $h->table(array('foo', 'bar'), array(), array('thead_th' => array('class' => 'foo', 'id' => 'bar'))));
            
            //tbody attributes
            $expected = '<table>'
                           . '<thead><tr><th>foo</th><th>bar</th></tr></thead>'
                           . '<tbody class = "foo" id = "bar"><tr><td>1</td><td>2</td></tr><tr><td>3</td><td>4</td></tr></tbody>'
                           . '<tfoot><tr><th>foo</th><th>bar</th></tr></tfoot>'
                        .'</table>';
			$this->assertSame($expected, $h->table(array('foo', 'bar'), array(array('1', '2'), array('3', '4')), array('tbody' => array('class' => 'foo', 'id' => 'bar')), true));
            
            //tbody > tr attributes
            $expected = '<table>'
                           . '<thead><tr><th>foo</th><th>bar</th></tr></thead>'
                           . '<tbody><tr class = "foo" id = "bar"><td>1</td><td>2</td></tr><tr class = "foo" id = "bar"><td>3</td><td>4</td></tr></tbody>'
                           . '<tfoot><tr><th>foo</th><th>bar</th></tr></tfoot>'
                        .'</table>';
			$this->assertSame($expected, $h->table(array('foo', 'bar'), array(array('1', '2'), array('3', '4')), array('tbody_tr' => array('class' => 'foo', 'id' => 'bar')), true));
            
            //tbody > td attributes
            $expected = '<table>'
                           . '<thead><tr><th>foo</th><th>bar</th></tr></thead>'
                           . '<tbody><tr><td class = "foo" id = "bar">1</td><td class = "foo" id = "bar">2</td></tr>'
                           . '<tr><td class = "foo" id = "bar">3</td><td class = "foo" id = "bar">4</td></tr></tbody>'
                           . '<tfoot><tr><th>foo</th><th>bar</th></tr></tfoot>'
                        .'</table>';
			$this->assertSame($expected, $h->table(array('foo', 'bar'), array(array('1', '2'), array('3', '4')), array('tbody_td' => array('class' => 'foo', 'id' => 'bar')), true));
            
            
            //tfoot attributes
            $expected = '<table>'
                               . '<thead><tr><th>foo</th><th>bar</th></tr></thead>'
                               . '<tbody></tbody>'
                               . '<tfoot class = "foo" id = "bar"><tr><th>foo</th><th>bar</th></tr></tfoot>'
                          . '</table>';
			$this->assertSame($expected, $h->table(array('foo', 'bar'), array(), array('tfoot' => array('class' => 'foo', 'id' => 'bar')), true));
            
            //tfoot > tr attributes
            $expected = '<table>'
                               . '<thead><tr><th>foo</th><th>bar</th></tr></thead>'
                               . '<tbody></tbody>'
                               . '<tfoot><tr class = "foo" id = "bar"><th>foo</th><th>bar</th></tr></tfoot>'
                          . '</table>';
			$this->assertSame($expected, $h->table(array('foo', 'bar'), array(), array('tfoot_tr' => array('class' => 'foo', 'id' => 'bar')), true));
            
             //tfoot > th attributes
            $expected = '<table>'
                               . '<thead><tr><th>foo</th><th>bar</th></tr></thead>'
                               . '<tbody></tbody>'
                               . '<tfoot><tr><th class = "foo" id = "bar">foo</th><th class = "foo" id = "bar">bar</th></tr></tfoot>'
                          . '</table>';
			$this->assertSame($expected, $h->table(array('foo', 'bar'), array(), array('tfoot_th' => array('class' => 'foo', 'id' => 'bar')), true));
            
            //Using all attributes
            $expected = '<table class = "footable" id = "bartable">'
                           . '<thead class = "foothead" id = "barthead">'
                           . '<tr class = "footheadtr" id = "bartheadtr">'
                           . '<th class = "footheadth" id = "bartheadth">foo</th>'
                           . '<th class = "footheadth" id = "bartheadth">bar</th>'
                           . '</tr></thead>'
                           . '<tbody class = "footbody" id = "bartbody">'
                           . '<tr class = "footbodytr" id = "bartbodytr">'
                           . '<td class = "footbodytd" id = "bartbodytd">1</td>'
                           . '<td class = "footbodytd" id = "bartbodytd">2</td></tr>'
                           . '<tr class = "footbodytr" id = "bartbodytr">'
                           . '<td class = "footbodytd" id = "bartbodytd">3</td>'
                           . '<td class = "footbodytd" id = "bartbodytd">4</td></tr></tbody>'
                           . '<tfoot class = "footfoot" id = "bartfoot">'
                           . '<tr class = "footfoottr" id = "bartfoottr">'
                           . '<th class = "footfootth" id = "bartfootth">foo</th>'
                           . '<th class = "footfootth" id = "bartfootth">bar</th></tr>'
                           . '</tfoot></table>';
			$this->assertSame($expected, $h->table(
                                                    array('foo', 'bar'), 
                                                    array(array('1', '2'), 
                                                    array('3', '4')), 
                                                    array(
                                                        'table' => array('class' => 'footable', 'id' => 'bartable'),
                                                        'thead' => array('class' => 'foothead', 'id' => 'barthead'),
                                                        'thead_tr' => array('class' => 'footheadtr', 'id' => 'bartheadtr'),
                                                        'thead_th' => array('class' => 'footheadth', 'id' => 'bartheadth'),
                                                        'tbody' => array('class' => 'footbody', 'id' => 'bartbody'),
                                                        'tbody_tr' => array('class' => 'footbodytr', 'id' => 'bartbodytr'),
                                                        'tbody_td' => array('class' => 'footbodytd', 'id' => 'bartbodytd'),
                                                        'tfoot' => array('class' => 'footfoot', 'id' => 'bartfoot'),
                                                        'tfoot_tr' => array('class' => 'footfoottr', 'id' => 'bartfoottr'),
                                                        'tfoot_th' => array('class' => 'footfootth', 'id' => 'bartfootth'),
                                                        ), 
                                                        true
                                                  )
                              );
            
            //Display it directly
            $expected = '<table><thead><tr></tr></thead><tbody></tbody></table>';
			$this->expectOutputString($expected);
			$h->table(array(), array(), array(), false, false);
		}
        

	}