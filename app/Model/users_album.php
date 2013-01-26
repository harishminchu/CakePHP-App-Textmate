<?php
class UsersAlbum extends AppModel {
	var $name = 'UsersAlbum';
	var $order = array("UsersAlbum.album_name"=>"ASC");
    public $newData;  /* holds new data after saving*/
    
    public $hasMany = array(
        'UsersAlbumsPhoto' => array(
			'className'  => 'UsersAlbumsPhoto',
			'foreignKey' => 'album_id',
            'dependent'  => true,
            "limit"      => 300,
		)
	);
    
    var $validate = array(
		'album_name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Album name is required.',
			),
		)
	);	
    
    public function afterSave( $created ) {
		$this->newData = $this->data;	
	}
}
