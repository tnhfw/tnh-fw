<?php
	class DefaultModel extends Model{
		
		protected $table = 'model_default';
		protected $primaryKey = 'id';
        protected $dbCacheTimeToLive = 800;
	}
