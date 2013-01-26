<?php
class ProvincesLocation extends AppModel {
	public $name = 'ProvincesLocation';
    
    public $belongsTo = array(
        'Province' => array(
			'className' => 'Province',
			'foreignKey' => 'province_id'
		)
	);
   
}
