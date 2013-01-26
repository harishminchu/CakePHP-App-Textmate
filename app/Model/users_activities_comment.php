<?php
class UsersActivitiesComment extends AppModel {
	public $name = 'UsersActivitiesComment';
	public $newData;  /* holds new data after saving*/
	public $order = array("UsersActivitiesComment.id"=>"ASC");
   	public $hasOne = array(
        'User' => array(
			'className'  => 'User',
			'foreignKey' => 'user_id',
		)
	);
    
    public $hasMany = array(
        'UsersLike' => array(
			'className'  => 'UsersLike',
			'foreignKey' => 'like_id',
            "limit"      => 100,
            "dependent"  => true,
            "conditions" => array("UsersLike.type"=>2),
            "order"      => array("UsersLike.id"=>"DESC")
		)
	);
    
    public function afterSave( $created ) {
		$this->newData = $this->data;	
	}
}
