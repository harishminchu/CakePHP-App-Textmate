<?php
class UsersAlbumsController extends AppController {
    public $components = array("Auth","Upload");
    public $helpers = array("Jquery");
	
    public function beforeFilter() {
        parent::beforeFilter();
    }
    
    public function addNewPhotos() {
        $this->UsersAlbum->recursive = -1;
        $arg["conditions"] = array("UsersAlbum.user_id"=>$this->Auth->user('id'));
        $arg["fields"] = array("id", "album_name");
        $dataAlbums = $this->UsersAlbum->find("list", $arg);
        $this->set("dataAlbums", $dataAlbums);
        $this->setLayout(array("title"=>"Add New Photos","layout"=>"users_albums"));
    }
    
    public function browseAlbums() {
        $arg["conditions"] = array('UsersAlbum.result_display'=>1);
        $dataAlbums = $this->UsersAlbum->find("all", $arg["conditions"]);
        $this->set("dataAlbums", $dataAlbums);
        $this->setLayout(array("title"=>"Browse Albums","layout"=>"users_albums"));
    }
    
    public function addAlbum() {
        $this->securityCheck();
        $data["user_id"] = $this->Auth->user('id');
        $data["album_name"] = $this->data["album_name"];
        if(trim($data["album_name"]) != "") {
            if ($this->UsersAlbum->isUnique(array('UsersAlbum.user_id'=>$this->Auth->user("id"), 
               'UsersAlbum.album_name'=>$data["album_name"]), false)) {
                
                $this->UsersAlbum->save($data);
            }
            $results = $this->UsersAlbum->newData;
            $response = $results["UsersAlbum"];
        } else {
            $response = array("error"=>$this->errorList["User"][3]);
        }    
        echo json_encode($response);    
        exit;  
    }
    
    public function myAlbums() {
        $arg["conditions"] = array('UsersAlbum.user_id'=>$this->Auth->user("id"));
        $dataAlbums = $this->UsersAlbum->find("all", $arg);
        $this->set("dataAlbums", $dataAlbums);
        $this->setLayout(array("title"=>"My Albums","layout"=>"users_albums"));
    }
    
    public function editSettings($albumId) {
        $arg["conditions"] = array("UsersAlbum.id"=>$albumId, "UsersAlbum.user_id"=>$this->Auth->user('id'));
        $this->Session->write("UsersAlbum.id", $albumId);
        $dataAlbums = $this->UsersAlbum->find("first", $arg);
        $this->data = $dataAlbums;
        $this->setLayout(array("title"=>"Album Edit Settings","layout"=>"users_albums"));
    }
    
    function saveSettings() {
        $this->securityCheck();
        $this->request->data["UsersAlbum"]["id"] = $this->Session->read("UsersAlbum.id");    
        $this->UsersAlbum->save($this->request->data);
        exit;   
    }
    
    public function addPhoto() {
        $this->securityCheck();
        $destination = Configure::read("Album.photo.uploadpath");  
	    $totalUpload = 1; 
        $array = "";
        $isUploadOnly = false;
        $file = $this->request->data['photo'];
        if ($file['name']) {  
              $name =  $this->Upload->newName($file['name']); 
	          $this->Upload->upload($file, $destination, $name, array('type' => 'resizecrop', 'size' =>array(400,400), 'output' => 'jpg'));
              $newFile = $this->Upload->result;
              $errors = $this->Upload->errors;
              if($newFile!=null || !empty($errors)){	
					if(is_array($errors)){ 
                        echo json_encode($errors); 
						exit;
					}
              }  
              $this->savePhotoDetails( $newFile ); 
              $newData = $this->UsersAlbum->UsersAlbumsPhoto->newData;
              echo json_encode($newData["UsersAlbumsPhoto"]); 
              exit;
        }else {
             $error = array("error"=>$this->errorList["File"][1]);
             echo json_encode($error);    
        }
        exit;       
    }  
    
    function deleteAlbum() {
        $this->securityCheck();
        $albumId = $this->data["param_id"];
        
        $arg["conditions"] = array("UsersAlbumsPhoto.album_id"=>$albumId, "UsersAlbumsPhoto.user_id"=>$this->Auth->user("id"));
        $results = $this->UsersAlbum->UsersAlbumsPhoto->find("all", $arg);
            
        $conditions = array("UsersAlbum.id"=>$albumId, "UsersAlbum.user_id"=>$this->Auth->user("id"));
        $isSuccess = $this->UsersAlbum->deleteAll($conditions, true);
        
        if($isSuccess) {
        /* delete photos  from disk */
            foreach($results as $sourceImage) {
                $destination = Configure::read("Album.photo.uploadpath") . $sourceImage["UsersAlbumsPhoto"]["photo"]; 
                if(fileExistsInPath($destination)) {
                    unlink($destination);
                }   
            }
            echo json_encode(array("success"=>true));
        }
           
        exit;
    }
    
    private function savePhotoDetails($newFile) {
        $this->securityCheck();
        $data["photo"] = $newFile;
        $data["title"] = $this->data["UsersAlbumsPhoto"]["title"];
        $data["album_id"] = $this->data["UsersAlbum"]["album_id"];
        $data["user_id"] = $this->Auth->user('id');             
        $this->UsersAlbum->UsersAlbumsPhoto->save($data);
    }
}
?>
