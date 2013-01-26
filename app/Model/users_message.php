<?php
class UsersMessage extends AppModel {
	public $name = 'UsersMessage';
	public $newData;  /* holds new data after saving*/
	public $limit = 100;
    public $order = array("UsersMessage.id"=>"DESC");
   
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
	    
	public function afterSave( $created ) {
		$this->newData = $this->data;	
	}
    
    
     
}
