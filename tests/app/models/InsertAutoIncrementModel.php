<?php
	class InsertAutoIncrementModel extends Model{
		
		protected $table = 'model_insert_auto_increment';
		protected $primaryKey = 'id';
        
        //validation rules
        protected $validationRules = array(
            array(
                'name' => 'name',
                'label' => 'name label',
                'rules' => 'required'
            )
        );
	}
