<?php
class UsersVideosComment extends AppModel {
	var $name = 'UsersVideosComment';
    public $newData;  /* holds new data after saving*/
	public $limit = 100;
    public $order = array("UsersVideosComment.id"=>"DESC");
    
    public $belongsTo = array(
        'UsersVideo' => array(
			'className'  => 'UsersVideo',
			'foreignKey' => 'video_id',
            'dependent'  => false,
            "limit"      => 100,
		),
        'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id'
		)
	);
    
    var $validate = array(
		'comment' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Comment is required.',
			),
		)
	);
    
   	public function afterSave( $created ) {
		$this->newData = $this->data;	
	}	
}
