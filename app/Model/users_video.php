<?php
class UsersVideo extends AppModel {
	var $name = 'UsersVideo';
    var $counter = 1;
    var $incrementViews = false;
    
    public $belongsTo = array(
        'UsersVideosCategory' => array(
			'className'  => 'UsersVideosCategory',
			'foreignKey' => 'category_id',
            'dependent'  => true,
            "limit"      => 100,
		),
        'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id'
		)
	);

        
    var $validate = array(
		'title' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Title is required.',
			),
		)
	);
    
    public function afterFind($results, $primary) {
        $counter = $this->counter++;
        if( $counter == 1) {
            if($this->incrementViews) {
                $this->incrementViews($results);
            }
        }
        return $results;
    }
    public function incrementViews($results) {
        $rand = mt_rand(1,2);
        $data["id"] =  $results[0]["UsersVideo"]["id"];
        $data["views"] = $results[0]["UsersVideo"]["views"] + $rand;
        $this->save($data);    
    }	
}
