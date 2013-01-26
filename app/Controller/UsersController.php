<?php
/* @TODO Security check for the following: addPhoto(), removePhoto()*/ 
class UsersController extends AppController {
	public $uses = array();
	public $helpers = array("Jquery");
    public $components = array("Auth","Upload","Notifications");
	public $autoLayout = false;
    public $userId = 0;
	
	function beforeFilter() {
        parent::beforeFilter();
        
    }
    
/**
 * Shows user public profile
 * @TODO optimize query
 */	    
    public function publicProfile() {
   	    $username = $this->request->params["pass"];
        $param["conditions"] = array("User.username"=>$username[0]);
        $this->User->Behaviors->attach("Containable");
        $this->User->contain(array("UsersPrivacy", "UsersFriend"=>array("conditions"=>array("request_granted"=>1))));
        $userDetail = $this->User->find("first", $param);
        $toUserId = $userDetail["User"]["id"];
        
        if(!empty($userDetail)) { 
            $activity = Configure::read('Activity.performsaction'); 
            $param["conditions"] = array("UsersActivity.user_id = $toUserId OR UsersActivity.friend_id =$toUserId");                         
            $arg["param"] = array("UsersActivity.id"=>"DESC");
            $userPost = $this->User->UsersActivity->find("all", $param);
        }
        
        $dataLocation = $this->User->getUsersLocation($userDetail["User"]["location_id"]);
                
        /* users flirts */
        $this->loadModel("UsersDating");
        $dataUsersDating = $this->UsersDating->getDatingsPreferences($toUserId);
        
        $this->set("dataUsersDating", $dataUsersDating);
        $this->set("dataLocation", $dataLocation);
        $this->set("dataUserDetail", $userDetail);
        $this->set("dataUserPost", $userPost);
        $this->render("publicProfile");
    }
    	
    public function login() {
        if ($this->request->is('post') && $this->request->is('ajax')) {
            $this->Session->destroy();
            $this->Auth->logout();
            if ($this->Auth->login()) {
                $json = array("success"=>true, "redirect"=>$this->Auth->loginRedirect);
                $this->jsonResponse($json);   
            } else {
                $this->jsonResponse(array("error"=>$this->errorList["User"][1])); 
            }
        } else {
             $this->Session->setFlash($this->errorList["User"][2]); 
        } 
        exit;  
    }
   
    public function register() {
        $emailError = array();
        $passwordError = array();
        
        $response = array("success"=>true);
        $this->request->data["User"]["activation_code"] = String::uuid();
        $isSuccess = $this->User->save($this->data);
        if($isSuccess) {
            $newUserId =  $this->User->getLastInsertID();
            $this->Session->write("NewUser.id", $newUserId);
        }
        $errors = $this->User->invalidFields();
        if(isset($errors["email"])) {
            $emailError["email"]  = array_unique($errors["email"]);
        }
        if(isset($errors["password"])) {
            $passwordError["password"]  = array_unique($errors["password"]);
        }
        $merge = array_merge($emailError, $passwordError);
        if(!empty($merge)) {
            $response = array("error"=>$merge);
        }
        echo json_encode($response); 
        exit;    
    } 
    
    public function activate() {
        $newId = $this->Session->read("NewUser.id");
        $arg["conditions"] = array("User.id"=>$newId);
        $results = $this->User->find("first", $arg);
        $this->set("dataNewUser", $results);
    }
    
    public function activateUser() {
        $activationCode = $this->data["User"]["activation_code"];
        $arg["conditions"] = array("User.activation_code"=>$activationCode, "User.active"=>0);
        $records = $this->User->find("first", $arg);
        if(!empty($records)) {
            $data["active"] = 1; 
            $this->User->updateAll($data, $arg["conditions"]);
            $response = array("success"=>true);
        } else {
            $response = array("error"=>true);
        }
        echo json_encode($response); 
        exit;
    }
    
    public function members() {
        $this->User->recursive = -1;
        $param = array("conditions"=>array("User.id !=" . $this->Auth->user('id')));
        $dataMembers = $this->User->find("all",$param);
        $granted = array();
        $usersFriend = array();
        
        $param = array("conditions"=>array("UsersFriend.user_id"=>$this->Auth->user('id')),"limit"=>50,
                 "fields"=>array("id","request_granted","friend_id"));
        $dataUsersFriend = $this->User->UsersFriend->find("all",$param);
        foreach($dataUsersFriend as $records){
            $friend = $records["UsersFriend"];
            if($friend["request_granted"] == 1){
                $granted[] = $friend["friend_id"];
            }else {
                $requesting[] = $friend["friend_id"];
            }
            $usersFriend[] = $friend["friend_id"];
        }
        $this->set("dataRequestGranted", $granted);
        $this->set("dataUsersFriend", $usersFriend);
        $this->set("dataMembers", $dataMembers);
    } 
    
    public function basicInformation() {
        $this->usersInformation();
        $this->setLayout(array("title"=>"Basic Information", "layout"=>"users"));
    }
        
    public function usersInformation() {
        $param["conditions"] = array("User.id"=>$this->Auth->user('id'));
        $this->User->recursive = 0;
        $userDetail = $this->User->find("first", $param);
        $this->data = $userDetail;
        
        $this->getUserPrivacy();
        $this->set("dataUserDetail", $userDetail );
        return  $userDetail;
    }
    
    public function contactInformation() {
       $this->usersInformation();
       $this->setLayout(array("title"=>"Contact Information", "layout"=>"users"));
    }
    
    public function account() {
       $this->usersInformation();
       $this->setLayout(array("title"=>"Account", "layout"=>"users"));
    }
	
 /********** profile location start ************/    
  
    public function location() {
       $this->loadModel("Province");
       $dataProvinces = $this->Province->find("all");
       $userDetail = $this->usersInformation();
       $this->setProvincesAndLocation($dataProvinces, $userDetail);
       $this->setLayout(array("title"=>"Location", "layout"=>"users"));
    }
    
    private function setProvincesAndLocation($dataProvinces, $userDetail) {
       $userLocationId = $userDetail["User"]["location_id"];
       $dataLocations = array();
      
       $userProvinceId = 0;
       foreach($dataProvinces as $record) {
            $province = $record["Province"];
            $provinceId = $province["id"];
            $location  = $record["ProvincesLocation"];
           
            foreach($location as $recordLocation) {
                $locationId = $recordLocation["id"];
                if($userLocationId == $locationId) {
                    $userProvinceId = $provinceId;
                    foreach($location as $finalLocation) {
                       $id = $finalLocation["id"];
                       $dataLocations[$id] =$finalLocation["location"];  
                    }    
                    break;
                } 
            }
            $provinces[$provinceId] =  $province["province"];   
       }  
        $this->set("dataLocations", $dataLocations); 
        $this->set("dataProvinces", $provinces); 
        $this->set("varProvinceId", $userProvinceId); 
    }
    
    public function loadAllLocations() {
        $this->loadModel("ProvincesLocation");
        $dataLocation = $this->ProvincesLocation->find("all");  
        foreach($dataLocation as $record) {
            $location[] = $record["ProvincesLocation"];
        }
        echo json_encode($location);  
        exit;
    } 
    
    public function saveUserDetail() {
        $this->securityCheck();
        $this->request->data["User"]["id"] = $this->Auth->user('id');  
        $privacy = $this->getUserPrivacy(); 
        $this->request->data["UsersPrivacy"]["id"] = $privacy["id"];
        
        $isSuccess = $this->User->saveAll($this->request->data);
        
        if($isSuccess) {
            $success = array("success"=>true);
            echo json_encode($success);   
        }else {
            $error = array("error"=>$this->errorList["User"][3]); 
            echo json_encode($error);   
        }
        exit;
    }

    public function saveNewDetails() {
        $response = array("error"=>true);
        $newUserId = $this->Session->read("NewUser.id");
        $this->request->data["User"]["id"] = $newUserId;
        $this->User->saveAll($this->request->data);
        $isValid = $this->User->invalidFields();

        if(empty($isValid)) {
            $response = array("success"=>true);
        }
        echo json_encode($response);
        exit;
    }
  /********** user privacy start ************/   
    
    public function getUserPrivacy() {
        $dataPrivacy = $this->User->UsersPrivacy->getPrivacy($this->Auth->user('id'));
        $this->set("dataPrivacy", $dataPrivacy["UsersPrivacy"]); 
        return $dataPrivacy["UsersPrivacy"];
    }
 
 /********** profile photo start ************/ 
   
    public function profilePhoto() {
        $param["conditions"] = array("User.id"=>$this->Auth->user('id'));
        $this->User->recursive = 0;
        $userDetail = $this->User->find("first", $param);
        $this->set("dataUserDetail", $userDetail );
        $this->setLayout(array("title"=>"Profile Photo", "layout"=>"users"));
    }
    
    public function addPhoto() {
        $actionType = $this->request->data["actionType"];
        if($actionType == "upload") {
            $this->uploadPhoto();  
        } else {
            $this->cropPhoto();     
        }
        exit;  
    }
    
    public function removePhoto() {
        $sourceImage = $this->request->data["sourceImage"];
        $isSuccess = $this->updateDeletedPhoto();
        if($isSuccess) {
            $this->deleteOldPhotoOnUpload($sourceImage);
        }
        exit;   
    }
    
    private function uploadDetinations() {
        $isUploadOnly = false;  
        $destination = Configure::read("User.photo.uploadpath");
          
        if(isset($this->request->data["uploadDestination"])){
           switch($this->request->data["uploadDestination"]){
                case "activities":{
                    $destination = Configure::read("Activity.photo.uploadpath"); 
                    $isUploadOnly = true; 
                    break;  
                }
                case "messages":{
                    $destination = Configure::read("Message.photo.uploadpath");  
                    $isUploadOnly = true; 
                    break;  
                }
                case "albums":{
                    $destination = Configure::read("Album.photo.uploadpath");       
                    $isUploadOnly = true; 
                    break;  
                }
           } 
        }
        $returns["destination"] = $destination;
        $returns["isupload"] = $isUploadOnly;  
        return $returns;
    }
    
    private function uploadPhoto() {
        $totalUpload = 1; 
        $array = "";
        $file = $this->request->data['photo'];
        $returns = $this->uploadDetinations();
       
        $destination = $returns["destination"];
        $isUploadOnly  = $returns["isupload"]; 
        
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
              if(!$isUploadOnly && $this->saveUploadedPhoto($newFile)) {
                    $this->updateUserPhotoInSession($newFile);
                    $this->deleteOldPhotoOnUpload($this->request->data["sourceImage"]);
                    $this->Notifications->setChangedProfilePhoto($newFile);
                    $this->addToProfilePhotoAlbum($newFile);
                    copy($destination . $newFile , Configure::read("Album.photo.uploadpath") . $newFile);
              } else {
                    $fileUploadOnly = $destination . $this->request->data["sourceImage"];   
                    if( $this->request->data["sourceImage"] && fileExistsInPath($fileUploadOnly)) {
                        unlink($fileUploadOnly); 
                    }
              }
			  echo json_encode(array("imagefile"=>$newFile)); 
              exit;
        }else {
             $error = array("error"=>$this->errorList["File"][1]);
             echo json_encode($error);    
        }
       
        exit;       
    }
    
    private function addToProfilePhotoAlbum($fileName) {
        $this->loadModel("UsersAlbum");
        $authUserId = $this->Auth->user('id');
        $arg["conditions"] = array("UsersAlbum.user_id"=>$authUserId, "UsersAlbum.album_name"=>"Profile");
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
             $data["album_name"] = "Profile";
             $this->UsersAlbum->save($data);
             $lastInsertId = $this->UsersAlbum->getLastInsertID();
             $data["album_id"] = $lastInsertId;
             $this->UsersAlbum->UsersAlbumsPhoto->save($data);
        }
    }
    
    private function updateUserPhotoInSession($newFile) {
        $this->Session->write('Auth.User.photo', $newFile);
        
    }
    
    private function deleteOldPhotoOnUpload($sourceImage) {
        $image = $sourceImage;
        if($sourceImage == Configure::read("User.photo.bignone") ) {
            return false;
        }
        $destination = Configure::read("User.photo.uploadpath"); 
        $sourceImage = $destination . $sourceImage;
        
       	$file = strtolower($sourceImage);
        $ext = trim(substr($file,strrpos($file,".")+1,strlen($file)));
            
        $arrReplace = array(".jpg", ".png", ".gif", ".jpeg", ".bmp");
        $cropped = str_replace($arrReplace, "_cropped.$ext", $image);
        $cropped = $destination . $cropped;
       
        if(fileExistsInPath($sourceImage)) {
            unlink($sourceImage);
        }
        if(fileExistsInPath($cropped)) {
            unlink($cropped);
        } 
    }
    
    private function saveCroppedPhoto($imageFile) {
        return $this->saveUploadedPhoto($imageFile);    
    } 
    
    private function updateDeletedPhoto() {
       $imageFile = Configure::read("User.photo.bignone");
       return $this->saveUploadedPhoto($imageFile); 
    }
    
    private function saveUploadedPhoto($newFile) {
        $imageFile = $newFile;
        $this->User->id = $this->Auth->user('id');
        $this->request->data["photo"] = $imageFile;
        return $this->User->save($this->request->data);
    }
    
    private function cropPhoto() {
        $sourcePath = Configure::read("User.photo.uploadpath"); 
        $file = $this->request->data['sourceImage'];
        if (strlen($file)) {  
	       $targetSize["w"] = 200;
           $targetSize["h"] = 200;
           $size["w"] =  $targetSize["w"];
           $size["h"] =  $targetSize["h"];
           $position["x"] = $this->request->data["x"] * 4;
           $position["y"] = $this->request->data["y"] * 4;
           
           $source =  $sourcePath . $this->request->data["sourceImage"];           
           $croppedFile = $this->Upload->crop($source, $targetSize, $size, $position, $quality = 100);
           $imageFile = array("imagefile"=>$croppedFile);
           $this->saveCroppedPhoto( $croppedFile);
           echo json_encode($imageFile); 
        } else {
            $error = array("error"=>$this->errorList["File"][2]);
            echo json_encode($error); 
        }
        exit;       
    }
  
    
    

}
?>
