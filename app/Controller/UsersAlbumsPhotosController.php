<?php
class UsersAlbumsPhotosController extends AppController {
    public $components = array("Auth","Upload");
    public $helpers = array("Jquery");
	public $autoLayout = false;
	
    public function beforeFilter() {
        parent::beforeFilter();
    }
    
    public function managePhotos($photoAlbum = 0) {
        $dataAlbums = array();
        if($photoAlbum) {
            $authUserId = $this->Auth->user('id');
            $this->UsersAlbumsPhoto->UsersAlbum->recursive = -1;
            $arg["conditions"] = array("UsersAlbum.user_id"=>$authUserId );
            $arg["fields"] = array("id", "album_name");
            $dataAlbums = $this->UsersAlbumsPhoto->UsersAlbum->find("list", $arg);
            
            $param["conditions"] = array("UsersAlbumsPhoto.album_id"=>$photoAlbum, "UsersAlbumsPhoto.user_id"=>$authUserId); 
            $dataAlbumsPhoto = $this->UsersAlbumsPhoto->find("all", $param);
        }   
        $this->set("dataAlbumsPhoto", $dataAlbumsPhoto);   
        $this->set("dataAlbums", $dataAlbums);
        $this->setLayout(array("title"=>"Manage Photos","layout"=>"users_albums_photos"));      
    } 
    
    public function gallery($albumId) {
        $dataAlbums = array();
        $authUserId = $this->Auth->user('id');
        
        $this->UsersAlbumsPhoto->recursive = -1;
        $arg["conditions"] = array("UsersAlbumsPhoto.album_id"=>$albumId, "UsersAlbumsPhoto.user_id"=>$authUserId);
        $dataAlbumPhotos = $this->UsersAlbumsPhoto->find("all", $arg);
        
        $this->set("dataAlbumPhotos", $dataAlbumPhotos);   
        $this->setLayout(array("title"=>"Gallery Photos","layout"=>"users_albums_photos"));      
    } 
    
    public function saveDetails() {
       $this->securityCheck();
       $authUserId = $this->Auth->user('id');
       $records = $this->data["UsersAlbumsPhoto"];
       $conditions = array("UsersAlbumsPhoto.user_id"=>$authUserId, "UsersAlbumsPhoto.id"=>$records["id"]);
       
       $title = $records["title"];
       $caption = $records["caption"];
       $data["title"]= "'$title'";
       $data["album_id"]= $records["album_id"];
       $data["caption"]= "'$caption'";
    
       $isSuccess = $this->UsersAlbumsPhoto->updateAll( $data, $conditions );
       exit;
    }
/**
 * @TODO clean string
 * 
 */     
    public function saveMultipleDetails() {
       $this->securityCheck();
       $authUserId = $this->Auth->user('id');
       
       if(isset($this->data["delete"])) {
            $deleteIds = implode(",", $this->data["delete"]);
            $conditions = array("UsersAlbumsPhoto.user_id"=>$authUserId, "UsersAlbumsPhoto.id IN($deleteIds)");
            $this->UsersAlbumsPhoto->deleteAll($conditions);
       } 
       
       $i = 0;
       foreach($this->data["id"] as $photoId) {
            $data["title"] = "'" . $this->data["title"][$i] . "'";
            $data["caption"] = "'" . $this->data["caption"][$i] . "'";
            $data["album_id"] = $this->data["album_id"][$i];
            $data["album_cover"] = 0;
            if(isset($this->data["cover"])) {
                if($this->data["cover"] == $photoId && $this->data["default_album_id"] == $data["album_id"]) {
                    $data["album_cover"] = 1; 
                }
            }
            $i++;
            $conditions = array("UsersAlbumsPhoto.id"=>$photoId, "UsersAlbumsPhoto.user_id"=>$authUserId);
            $this->UsersAlbumsPhoto->updateAll($data, $conditions);
       }    
       exit;
    }
    
    public function removePhoto() {
        $this->securityCheck();
        $sourceImage = $this->request->data["sourceImage"];
        $destination = Configure::read("Album.photo.uploadpath") . $sourceImage; 
        $conditions = array("UsersAlbumsPhoto.user_id"=>$this->Auth->user('id'), 
                            "UsersAlbumsPhoto.id"=>$this->request->data('photoId'));
        $this->UsersAlbumsPhoto->deleteAll($conditions);
        if(fileExistsInPath($destination)) {
            unlink($destination);
            echo json_encode(array("success"=>true));
            exit;
        }
        echo json_encode(array("error"=>$errorList["File"][3]));
        exit;   
    }
    
    public function addComment() {
        $this->securityCheck();
        if(!empty($this->data['comment']) && $this->Auth->user('id')) {  
            $this->loadModel("UsersPhotosComment");
            $this->request->data["user_id"] = $this->Auth->user('id');
            $this->UsersPhotosComment->save($this->data); 
            $responseData = $this->UsersPhotosComment->newData;
            
            $view = new View($this);
            $time = $view->loadHelper('Time');
            
            $created = $responseData["UsersPhotosComment"]["created"];
            $responseData["UsersPhotosComment"]["created"] = $time->timeAgoInWords($created);
            $this->header('Content-Type: application/json');
            echo json_encode($responseData); 	    
		} 
       exit;
    }
    
    public function deleteComment() {
        $this->securityCheck();
        $this->loadModel("UsersPhotosComment");
        $paramId = $this->request->data["param_id"];
        $conditions = array("UsersPhotosComment.user_id"=>$this->Auth->user('id'), 
                            "UsersPhotosComment.id"=>$paramId);                
        $this->UsersPhotosComment->delete($paramId, true); 
        echo json_encode(array("success"=>true)); 
        exit;
    }    
}
?>
