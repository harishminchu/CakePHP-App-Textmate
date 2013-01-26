<?php

class UsersDatingsController extends AppController {
	public $uses = array();
	public $helpers = array();
    public $components = array("Auth", "Notifications");
	public $autoLayout = false;
   
	
	function beforeFilter() {
        parent::beforeFilter();  
    }
    
    public function savePreferences() {
        $this->securityCheck();
        $authUserId = $this->Auth->user('id');  
        if($authUserId) {
            
            $this->loadModel("User");
            $this->request->data["User"]["id"] = $authUserId;  
            $privacy = $this->getUserPrivacy(); 
            $this->request->data["UsersDating"]["user_id"] = $authUserId;
            $this->request->data["UsersPrivacy"]["id"] = $privacy["id"];
        
            $this->User->saveAll($this->request->data);
            $isSuccess = $this->UsersDating->save($this->request->data);
            
            if($isSuccess) {
                $success = array("success"=>true);
                echo json_encode($success);   
            }else {
                $error = array("error"=>$this->errorList["User"][3]); 
                echo json_encode($error);   
            }
        }
        exit;        
    }

    public function saveNewDatings() {
        $newUserId = $this->Session->read("NewUser.id");
        if($newUserId ) {
            $this->loadModel("User");
            $this->request->data["User"]["id"] = $newUserId;
            $this->request->data["UsersDating"]["user_id"] = $newUserId;

            $this->User->recursive = -1;
            $this->User->bindDatingsModel();
            $this->User->saveAll($this->request->data);


        }
        exit;
    }

    /********** user privacy start ************/   
    public function getUserPrivacy() {
        $dataPrivacy = $this->User->UsersPrivacy->getPrivacy($this->Auth->user('id'));
        return $dataPrivacy["UsersPrivacy"];
    }
}
?>
