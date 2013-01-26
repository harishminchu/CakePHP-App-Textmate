<?php
class Province extends AppModel {
	public $name = 'Province';
    
    public $hasMany = array(
        'ProvincesLocation' => array(
			'className' => 'ProvincesLocation',
			'foreignKey' => 'province_id'
		)
	);
	
   
}
