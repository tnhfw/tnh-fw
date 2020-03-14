<?php
	class PostModel extends Model{
		
		protected $table = 'model_post';
		protected $primaryKey = 'id';
        protected $manyToOne = array('author' => array('primary_key' => 'id', 'model' => 'AuthorModel') //using array
                                    );
	}
