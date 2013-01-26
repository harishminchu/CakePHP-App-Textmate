<?php
class UsersAlbumsPhoto extends AppModel {
	var $name = 'UsersAlbumsPhoto';
	public $newData;  /* holds new data after saving*/
    
    public $belongsTo = array(
        'UsersAlbum' => array(
			'className'  => 'UsersAlbum',
			'foreignKey' => 'album_id',
            'dependent'  => true,
            "limit"      => 100,
		)
	);
        
    public function afterSave( $created ) {
		$this->newData = $this->data;	
	}
}