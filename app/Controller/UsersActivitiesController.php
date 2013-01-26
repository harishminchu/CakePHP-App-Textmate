<?php
class UsersActivitiesController extends AppController {
    public $components = array("Auth");
    public $helpers = array("Jquery");
	public $autoLayout = false;
	public $userId = 0;
	
    public function beforeFilter() {
        parent::beforeFilter();
        $actionAjax = array("addPost");
        if(in_array($this->action,$actionAjax)) {
            $this->securityCheck();
        }
    }
	
	public function addPost() {
        $this->securityCheck();
        if(!empty($this->data["message"]) && $this->Auth->user('id')) {  
            $this->extractVideoParam();
            $this->request->data["user_id"] = $this->Auth->user('id');
            $this->request->data["type"] = "1";
            $this->UsersActivity->save($this->data); 
            $responseData = $this->UsersActivity->newData;
            $view = new View($this);
            $time = $view->loadHelper('Time');
            
            $this->addPhotoToWallAlbum();
            
            $created = $responseData["UsersActivity"]["created"];
            $responseData["UsersActivity"]["created"] = $time->timeAgoInWords($created);
            $this->header('Content-Type: application/json');
            echo json_encode($responseData); 	    
		} 
       exit;
   }
   
   private function extractVideoParam() {
       $video = $this->data["video"];
       if($video!="") {
           $explode = explode("{}", $video);
           $this->request->data["video_url"] = $explode[3];
           $this->request->data["video_thumb_url"] = $explode[4];
       }    
   }
   
   function addPhotoToWallAlbum() {
        $fileName = $this->data["photo"];
        if($fileName != "") {
            $this->loadModel("UsersAlbum");
            $authUserId = $this->Auth->user('id');
            $arg["conditions"] = array("UsersAlbum.user_id"=>$authUserId, "UsersAlbum.album_name"=>"Wall");
            $arg["fields"] = array("id");
            $this->UsersAlbum->recursive = -1;
            $results = $this->UsersAlbum->find("first", $arg);
           
            $data["user_id"] =  $authUserId;
            $data["photo"] = $fileName;
            if(!empty($results)) {
                $albumId = $results["UsersAlbum"]["id"];
                $data["album_id"] = $albumId;
                $this->UsersAlbum->UsersAlbumsPhoto->save($data);
            } else {
                 $data["album_name"] = "Wall";
                 $this->UsersAlbum->save($data);
                 $lastInsertId = $this->UsersAlbum->getLastInsertID();
                 $data["album_id"] = $lastInsertId;
                 $this->UsersAlbum->UsersAlbumsPhoto->save($data);
            }
            $userPhotoPath = Configure::read("Activity.photo.uploadpath");  
            copy($userPhotoPath . $fileName , Configure::read("Album.photo.uploadpath") . $fileName);
        }    
   }
	
   public function deletePost() {
        $this->securityCheck();
        $paramId = $this->request->data["param_id"];
        $conditions = array("UsersActivity.user_id"=>$this->Auth->user('id'), 
                            "UsersActivity.id"=>$paramId);                
        $this->UsersActivity->delete($paramId, true); 
        echo json_encode(array("success"=>true)); 
        exit;
   }
   
   public function loadMorePost() {}
    
   public function displayLiked() {
        $postId = $this->request->query["postId"];
       
        $arg["conditions"] = array("UsersLike.like_id"=>$postId, "UsersLike.type"=>1);
        $arg["fields"] = array("user_id");
        $userIds = $this->UsersActivity->UsersLike->find("list", $arg);
        
        $userIds = implode(",", $userIds);
        
        $param["conditions"] = array("User.id IN ($userIds)","User.active"=>1);
        $this->UsersActivity->User->Behaviors->attach("Containable");
        $this->UsersActivity->User->contain(array("UsersFriend"));
        $dataUsersLiked = $this->UsersActivity->User->find("all", $param);
        $this->set("dataUsersLiked", $dataUsersLiked);
   }
   
   public function iLike() {
        if(!empty($this->request->data["postId"]) && !empty($this->request->data["likeType"])) { 
           $this->loadModel("UsersLike");
            if($this->request->data["likeType"] == "Like") { 
                $this->request->data["user_id"] = $this->Auth->user('id');  
                $this->request->data["like_id"] = $this->request->data["postId"];
                $this->UsersLike->save($this->request->data);
               
            } else {
               $conditions = array("UsersLike.user_id"=>$this->Auth->user('id'), 
                                    "UsersLike.like_id"=>$this->request->data["postId"]); 
               $this->UsersLike->deleteAll($conditions);   
            }         
        }    
        exit;
   }
   
   public function whatsNew() {
        $userPost = array();
        $param["conditions"] = array("User.id"=>$this->Auth->user('id'));
        $this->UsersActivity->User->recursive = 0;
        $userDetail = $this->UsersActivity->User->find("first", $param);
        $toUserId = $userDetail["User"]["id"];
        
        /* @TODO please check this redundant codes Users.publicProfile*/
        $this->loadModel("UsersFriend");
        $this->UsersFriend->recursive = 0;
        $arg["conditions"] = array("UsersFriend.user_id"=>$this->Auth->user('id'), "request_granted"=>1);
        $arg["fields"] = array("UsersFriend.friend_id");
        $userFriendIds = $this->UsersFriend->find("list", $arg);
        $merge = array_merge($userFriendIds, array($this->Auth->user('id')));
        $userFriendIds = implode(",", $merge);
        
        if(!empty($userFriendIds)) { 
            $types = "1,4,5";
            $param["conditions"] = array("(UsersActivity.user_id IN ($userFriendIds) OR UsersActivity.friend_id =$toUserId) AND UsersActivity.type IN($types)");                                          
            $arg["param"] = array("UsersActivity.id"=>"DESC");
            $userPost = $this->UsersActivity->find("all", $param);
        }
        
        /* users datings */
        $this->loadModel("UsersDating");
        $dataUsersDating = $this->UsersDating->getDatingsPreferences($this->Auth->user('id'));
        $this->data = $dataUsersDating;
        
        $this->newestUser( $this->UsersActivity->User );
        $this->popularUser($this->UsersActivity->User );
            
        $this->set("dataUserDetail", $userDetail);
        $this->set("dataUsersDating", $dataUsersDating);
        $this->set("dataUserPost", $userPost);
   }
   
    public function removePhoto() {
        $sourceImage = $this->request->data["sourceImage"];
        $destination = Configure::read("Activity.photo.uploadpath") . $sourceImage; 
        if(fileExistsInPath($destination)) {
            unlink($destination);
            echo json_encode(array("success"=>true));
            exit;
        }
        echo json_encode(array("error"=>$this->errorList["File"][3]));
        exit;   
    }
    
     
}
?>
