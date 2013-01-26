<?php
class BlogsComment extends AppModel {
	var $name = 'BlogsComment';
    public $newData;  /* holds new data after saving*/
	public $limit = 100;
    public $order = array("BlogsComment.id"=>"DESC");
    
    public $belongsTo = array(
        'Blog' => array(
			'className'  => 'UsersVideo',
			'foreignKey' => 'blog_id',
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
