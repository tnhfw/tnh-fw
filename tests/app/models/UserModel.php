<?php
	class UserModel extends Model{
		
		protected $table = 'user';
		protected $primaryKey = 'user_id';
        protected $manyToOne = array('country');
	}
