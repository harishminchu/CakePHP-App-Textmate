<?php
/* @TODO Security check for the following: addPhoto(), removePhoto()*/ 
class UsersVideosController extends AppController {
	public $uses = array();
	public $helpers = array("Jquery");
    public $components = array("Auth", "Upload", "Notifications");
	public $autoLayout = false;
    public $userId = 0;
	
	function beforeFilter() {
        parent::beforeFilter();
        
    }
    function index() {
        $this->redirect(array("controller"=>"videos", "action"=>"browse"));
    }
    
    public function browse() {
        $arg["conditions"] = array();
        $records = $this->UsersVideo->find("all", $arg);
        $this->set("dataVideos",$records);
        $this->setLayout(array("title"=>"Videos","layout"=>"videos"));     
    }
    public function addCategory() {
       $this->search(); 
       $this->setLayout(array("title"=>"Add Category","layout"=>"videos")); 
       $this->render("search");
          
    }
    public function search() {
        $this->setLayout(array("title"=>"Search","layout"=>"videos"));  
        $this->myCategories();  
    }
    
    private function myCategories() {
        $authUserId = $this->Auth->user('id');
        $arg["conditions"] = array("UsersVideosCategory.user_id"=>$authUserId );
        $arg["fields"] = array("id", "category_name");
        $this->UsersVideo->UsersVideosCategory->recursive = -1;
        
        $records = $this->UsersVideo->UsersVideosCategory->find("list", $arg);
        $this->set("dataMyCategories",$records);     
    } 
    
    public function myVideos() {
        $authUserId = $this->Auth->user('id');
        $arg["conditions"] = array("UsersVideosCategory.user_id"=>$authUserId);
        $records = $this->UsersVideo->UsersVideosCategory->find("all", $arg);
       
        $this->set("dataMyVideos",$records);
        $this->setLayout(array("title"=>"My Videos","layout"=>"videos"));    
    }
    
    public function manageVideos($categoryId) {
        $authUserId = $this->Auth->user('id');
        $arg["conditions"] = array("UsersVideo.category_id"=>$categoryId, "UsersVideo.user_id"=>$authUserId );
        $records = $this->UsersVideo->find("all", $arg);
        $this->set("dataMyVideos",$records);
        $this->setLayout(array("title"=>"Manage My  Videos","layout"=>"videos")); 
    }
    
    public function deleteVideo() {
        $this->securityCheck();
        $conditions = array("UsersVideo.user_id"=>$this->Auth->user('id'), 
                            "UsersVideo.id"=>$this->request->data('param_id'));
        
        $arg["conditions"] = $conditions;
        $records = $this->UsersVideo->find("first", $arg);
        $this->UsersVideo->deleteAll($conditions);
        
        $photo = $records["UsersVideo"]["photo"];
        if($photo != "") {
            $destination = Configure::read("Video.photo.uploadpath") . $photo; 
            if(fileExistsInPath($destination)) {
                unlink($destination);
                echo json_encode(array("success"=>true));
                exit;
            }
            echo json_encode(array("error"=>$errorList["File"][3]));
        }
           
        exit;   
    }
    
    public function playVideo($videoId = 0) {
        $authUserId = $this->Auth->user('id');
        $arg["conditions"] = array("UsersVideo.id"=>$videoId);
        $this->UsersVideo->incrementViews = true;
        $records = $this->UsersVideo->find("first", $arg);
        
        $this->data = $records;
        $this->Session->write("Video.id", $videoId);
        $this->showsVideoComments($videoId);
        $this->setLayout(array("title"=>"Play Video","layout"=>"videos"));  
    }
    
    public function editVideo($videoId = 0) {
        $authUserId = $this->Auth->user('id');
        $arg["conditions"] = array("UsersVideo.user_id"=>$authUserId, "UsersVideo.id"=>$videoId);
        $records = $this->UsersVideo->find("first", $arg);
        $this->data = $records;
        $this->Session->write("Video.id", $videoId);
        $this->setLayout(array("title"=>"Edit Video","layout"=>"videos")); 
        $this->myCategories();  
    }
    
    public function saveVideoDetail() {
        $this->securityCheck();
        $authUserId = $this->Auth->user('id');
        $this->request->data["UsersVideo"]["id"] = $this->Session->read("Video.id");
        $this->UsersVideo->save($this->request->data);
        $response = array("success"=>true);
        echo json_encode($response);   
        exit;
    }    
        
    public function addVideo() {
        $this->securityCheck();
        $authUserId = $this->Auth->user('id');
        if($categoryId = $this->checkAddVideoCategories()) {
            $this->request->data["UsersVideo"]["category_id"] = $categoryId;
        }
        if($this->data["UsersVideo"]["video_id"]) {
            $this->request->data["UsersVideo"]["user_id"] = $authUserId;
            $this->UsersVideo->save($this->request->data);
            $response = array("success"=>true);
        } else {
            $response = array("error"=>true);  
        }  
        echo json_encode($response);   
        $this->Notifications->setAddedVideo();
        exit;
    } 
    
    private function checkAddVideoCategories() {
         $authUserId = $this->Auth->user('id');
         $categoryName = $this->data["UsersVideo"]["category_name"];
         $arg["conditions"] = array("UsersVideosCategory.category_name"=>$categoryName);
         $count = $this->UsersVideo->UsersVideosCategory->find("count", $arg);
         $id = 0 ;
         
         if(!$count) {
            $data["UsersVideosCategory"]["category_name"] = $categoryName;
            $data["UsersVideosCategory"]["user_id"] = $authUserId;
            $this->UsersVideo->UsersVideosCategory->save($data);
            $id = $this->UsersVideo->UsersVideosCategory->getLastInsertID();
         }  
         return $id;
    } 
    
    public function addPhoto() {
        $totalUpload = 1; 
        $file = $this->request->data['photo'];
        $destination = Configure::read("Video.photo.uploadpath");  
        
        if ($file['name']) {  
              $name =  $this->Upload->newName($file['name']); 
	          $this->Upload->upload($file, $destination, $name, array('type' => 'resizecrop', 'size' =>array(600,600), 'output' => 'jpg'));
              $newFile = $this->Upload->result;
              $errors = $this->Upload->errors;
              if($newFile!=null || !empty($errors)){	
					if(is_array($errors)){ 
                        echo json_encode($errors); 
						exit;
					}
              } 
              $this->addPhotoToVideo($newFile);
			  echo json_encode(array("imagefile"=>$newFile)); 
              exit;
        }else {
             $error = array("error"=>$this->errorList["File"][1]);
             echo json_encode($error);    
        }
        exit;       
    }
    private function addPhotoToVideo($newFile) {
        $authUserId = $this->Auth->user('id');
        $this->request->data["UsersVideo"]["id"] = $this->Session->read("Video.id");
        $this->request->data["UsersVideo"]["photo"] = $newFile;       
        $this->request->data["UsersVideo"]["user_id"] = $authUserId;
        $this->UsersVideo->save($this->data);   
    }
    
    public function addComment() {
        $this->securityCheck();
        if(!empty($this->data['comment']) && $this->Auth->user('id')) {  
            $this->loadModel("UsersVideosComment");
            $this->request->data["user_id"] = $this->Auth->user('id');
            $videoId = $this->Session->read("Video.id");
            $this->request->data["video_id"] = $videoId;
            $this->UsersVideosComment->save($this->data); 
            $responseData = $this->UsersVideosComment->newData;
            
            $view = new View($this);
            $time = $view->loadHelper('Time');
            
            $created = $responseData["UsersVideosComment"]["created"];
            $responseData["UsersVideosComment"]["created"] = $time->timeAgoInWords($created);
            $this->header('Content-Type: application/json');
            echo json_encode($responseData); 	    
		} 
       exit;
    }
    
    public function showsVideoComments($videoId) {
        $param["conditions"] = array("UsersVideosComment.video_id"=>$videoId);                                          
        $param["order"] = array("UsersVideosComment.id"=>"ASC");
        $this->loadModel("UsersVideosComment"); 
        $userPhotoComments = $this->UsersVideosComment->find("all", $param);
        $this->set("dataUserVideoComments", $userPhotoComments);
    }
    
    public function deleteComment() {
        $this->securityCheck();
        $this->loadModel("UsersVideosComment");
        $paramId = $this->request->data["param_id"];
        $conditions = array("UsersVideosComment.user_id"=>$this->Auth->user('id'), 
                            "UsersVideosComment.id"=>$paramId);                
        $this->UsersVideosComment->delete($paramId, true); 
        echo json_encode(array("success"=>true)); 
        exit;
    } 

}
?>
