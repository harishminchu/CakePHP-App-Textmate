<?php
class UsersLike extends AppModel {
	public $name = 'UsersLike';
	public $newData;  /* holds new data after saving*/
	public $limit = 100;
    public $order = array("UsersLike.id"=>"DESC");
   
    public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id'
		)
	);
	

     
}
