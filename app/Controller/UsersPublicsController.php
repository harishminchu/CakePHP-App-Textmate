<?php
class UsersPublicsController extends AppController {
    public $components = array();
    public $helpers = array("Jquery");
	public $autoLayout = false;
	
    public function beforeFilter() {
        parent::beforeFilter();
    }
	 
    public function shows($userId) {
        $this->showFriends($userId);
        $this->showAlbums($userId);
    }  
    
    public function albumPhotos($albumId = 0) {
         $this->loadModel("UsersAlbumsPhoto");
         $this->UsersAlbumsPhoto->recursive = -1;
         $arg["conditions"] = array("UsersAlbumsPhoto.album_id"=>$albumId);
         $dataAlbumPhotos = $this->UsersAlbumsPhoto->find("all", $arg);
         $this->set("dataAlbumPhotos", $dataAlbumPhotos);   
    }
    
    public function photoComments($photoId) {
        $param["conditions"] = array("UsersPhotosComment.photo_id"=>$photoId);                                          
        $param["order"] = array("UsersPhotosComment.id"=>"ASC");
        $this->loadModel("UsersPhotosComment"); 
        $userPhotoComments = $this->UsersPhotosComment->find("all", $param);
        $this->set("dataUserPhotoComments", $userPhotoComments);
    }
    
    /* @TODO check if this account is valid to view the profile */
    private function showFriends($userId) {
        $this->loadModel("UsersFriend");
        $arg["conditions"] = array("UsersFriend.user_id"=>$userId); 
        $dataShowFriends = $this->UsersFriend->find("all", $arg);
        $this->set("datashowFriends", $dataShowFriends);
        
    }
    
    /* @TODO check if this account is valid to view the profile */
    private function showAlbums($userId) {
        $this->loadModel("UsersAlbum");
        $arg["conditions"] = array("UsersAlbum.user_id"=>$userId); 
        $dataShowAlbums = $this->UsersAlbum->find("all", $arg);
        $this->set("dataShowAlbums", $dataShowAlbums);
        
    }
    
    
}
?>
