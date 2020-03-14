<?php
	class AuthorModel extends Model{
		
		protected $table = 'model_author';
		protected $primaryKey = 'id';
        protected $oneToMany = array('posts' => array('primary_key' => 'author', 'model' => 'PostModel'));
	}
