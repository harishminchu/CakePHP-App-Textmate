<?php
class UsersFriend extends AppModel {
	var $name = 'UsersFriend';
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id'
		),
        'Friend' => array(
			'className' => 'User',
			'foreignKey' => 'friend_id'
		)
	);
}
