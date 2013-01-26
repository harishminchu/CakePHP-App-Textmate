<?php
class UsersVideosCategory extends AppModel {
	var $name = 'UsersVideosCategory';
	public $hasMany = array(
        'UsersVideo' => array(
			'className'  => 'UsersVideo',
			'foreignKey' => 'category_id',
            'dependent'  => true,
            "limit"      => 300,
		)
	);
    
    
    var $validate = array(
		'category_name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Category name is required.',
			),
		)
	);	
}
