<?php
	class CoursModel extends Model{
		
		protected $_table = 'specialite';
		protected $validate = array(
										array(
												'name'=>'spe_lib', 
												'label' => 'libelle', 
												'rules' => 'required'
										)
									);
		protected $primary_key = 'spe_id';
	}