<?php
/* @TODO optimize all find() options across the controllers */

class Member extends AppModel {
	public $name = 'Member';
    
	public $hasMany = array(
        'UsersFriend' => array(
			'className' => 'UsersFriend',
			'foreignKey' => 'user_id'
		)
	);
    


}
