<?php

class Blog extends AppModel {
	public $name = 'Blog';
    public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id'
		)
	);

	var $validate = array(
		'title' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Title  is required.',
			),
		),
        'entry' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Blog entry is required.',
			),
		)
	);

}
