<?php
	class SoftDeleteModel extends Model{
		
		protected $table = 'model_softdelete';
		protected $primaryKey = 'id';
        
        protected $softDeleteStatus = true;

        protected $softDeleteTableColumn = 'deleted';
	}
