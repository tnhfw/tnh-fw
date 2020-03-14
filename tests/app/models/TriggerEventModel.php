<?php
	class TriggerEventModel extends Model{
		
		protected $table = 'model_trigger_event';
		protected $primaryKey = 'id';
        
        protected $beforeCreateCallbacks = array('createdAt', 'updatedAt', 'serialize(blob)');
        protected $beforeUpdateCallbacks = array('updatedAt', 'serialize(blob)');
        protected $afterGetCallbacks = array('unserialize(blob)');
        
        protected $callbackParameters = array('blob');
        
         protected $protectedTableColumns = array('id');
	}
