<?php
class UsersActivity extends AppModel {
	public $name = 'UsersActivity';
	public $newData;  /* holds new data after saving*/
	public $limit = 100;
    public $order = array("UsersActivity.id"=>"DESC");
   
	public $hasAndBelongsToMany = 
		array(	'Commentor' => array(
				'className' => 'User',
				'joinTable' => 'users_activities_comments',
				'foreignKey' => 'activity_id',
				'associationForeignKey' => 'user_id',
                'order' => array("UsersActivitiesComment.id"=>"ASC"),
				'unique' => true),
	);
	
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
	
	public $hasMany = array(
        'UsersLike' => array(
			'className'  => 'UsersLike',
			'foreignKey' => 'like_id',
            "limit"      => 100,
            "dependent"  => true,
            "conditions" => array("UsersLike.type"=>1),
            "order"      => array("UsersLike.id"=>"DESC")
		)
	);
    
    
	public function afterSave( $created ) {
		$this->newData = $this->data;	
	}
    
     
}
