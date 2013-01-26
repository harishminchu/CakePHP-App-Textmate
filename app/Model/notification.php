<?php
class Notification extends AppModel {
	var $name = 'Notification';
    var $useTable = "users_activities";
	var $order = array("Notification.id"=>"DESC");
  
	public $belongsTo = array(
        'User' => array(
			'className'  => 'User',
			'foreignKey' => 'user_id',
            "limit"      => 100
		),
        'Profile' => array(
			'className'  => 'User',
			'foreignKey' => 'friend_id',
            "limit"      => 100
		)
	);
}
