<?php
class UsersPhotosComment extends AppModel {
	public $name = 'UsersPhotosComment';
	public $newData;  /* holds new data after saving*/
	public $limit = 100;
    public $order = array("UsersPhotosComment.id"=>"DESC");
   
	
	public $belongsTo = array(
        'User' => array(
			'className'  => 'User',
			'foreignKey' => 'user_id',
            "limit"      => 100
		)
	);
    
	public function afterSave( $created ) {
		$this->newData = $this->data;	
	}
    
     
}
