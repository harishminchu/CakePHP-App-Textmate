<?php
class UsersMessagesController extends AppController {
    public $components = array("Auth");
    public $helpers = array("Jquery");
	public $autoLayout = false;
	
    public function beforeFilter() {
        parent::beforeFilter();
    }
	
	public function inbox() {
        $userSent = array();
        $authUserId = $this->Auth->user('id');
        
        $param["conditions"] = array("(UsersMessage.friend_id = $authUserId  AND UsersMessage.sender_deleted = 0)
                                      OR (UsersMessage.user_id = $authUserId AND UsersMessage.recepient_deleted = 0) ");
                                                                                 
        $param["order"] = array("UsersMessage.id"=>"DESC");
        $userSent = $this->UsersMessage->find("all", $param);
     
        $this->set("dataUserDetail", $this->Auth->user);
        $this->set("dataUserSent", $userSent); 
        $this->setLayout(array("title"=>"Inbox","layout"=>"messages"));
	}
    
   	public function read($useId) {
        $userSent = array();
        $authUserId = $this->Auth->user('id');
        $friendId = $useId;
        
        $this->Session->write('Message.friend_id', $friendId);
        
        $param["conditions"] = array("(UsersMessage.friend_id=$authUserId AND UsersMessage.user_id = $friendId 
                                       AND UsersMessage.sender_deleted=0) OR
                                       (UsersMessage.user_id = $authUserId AND UsersMessage.friend_id = $friendId
                                       AND UsersMessage.recepient_deleted=0)
                                      ");                                                   
        $param["order"] = array("UsersMessage.id"=>"ASC");
        $this->UsersMessage->recursive = 1;
        $userSent = $this->UsersMessage->find("all", $param);
        
        if($useId != $authUserId) {
            $this->setRead($useId);
        }
        
        $this->set("dataUserDetail", $this->Auth->user);
        $this->set("dataUserSent", $userSent); 
        $this->setLayout(array("title"=>"Read Messages","layout"=>"messages"));
    }
    
    private function setRead($userId) {
        $authUserId = $this->Auth->user('id');
        $conditions = array("UsersMessage.user_id"=>$userId, "UsersMessage.friend_id"=>$authUserId); 
        $data["read"] = 1;
        $isDeleted = $this->UsersMessage->updateAll( $data, $conditions );       
    }
    
    public function sent() {
        $userSent = array();
        $authUserId = $this->Auth->user('id');
        $param["conditions"] = array("UsersMessage.user_id"=>$authUserId,"UsersMessage.recepient_deleted"=>0); 
        $param["order"] = array("UsersMessage.id"=>"ASC");
        $userSent = $this->UsersMessage->find("all", $param);
        
        $this->set("dataUserDetail", $this->Auth->user);
        $this->set("dataUserSent", $userSent);
        $this->setLayout(array("title"=>"Sent Messages","layout"=>"messages")); 
    }
    
    public function deleted() {
       
        $userSent = array();
        $authUserId = $this->Auth->user('id');
        $param["conditions"] = array("(UsersMessage.user_id = $authUserId AND UsersMessage.recepient_deleted = 1) OR
                                      (UsersMessage.friend_id = $authUserId AND UsersMessage.sender_deleted = 1)"
                                     ); 
        $param["order"] = array("UsersMessage.id"=>"ASC");
        $userSent = $this->UsersMessage->find("all", $param);
        
        $this->set("dataUserDetail", $this->Auth->user);
        $this->set("dataUserSent", $userSent); 
        $this->setLayout(array("title"=>"Deleted Messages","layout"=>"messages"));
    }
    
    
    public function compose() {
        $this->loadModel("UsersFriend");
        $authUserId = $this->Auth->user('id');
        $this->UsersFriend->Behaviors->attach("Containable");
        $this->UsersFriend->contain("Friend");
        $arg["conditions"] = array("UsersFriend.user_id"=>$authUserId);
        $dataFriends = $this->UsersFriend->find("all", $arg);   
        $this->set("dataUserFriends", $dataFriends);
        $this->set("dataUserDetail", $this->Auth->user);
        $this->setLayout(array("title"=>"Compose Message","layout"=>"messages"));
    }
    
   	public function addReply() {
        $this->securityCheck();
        if(!empty($this->data["message"]) && $this->Auth->user('id')) {  
            
            $this->request->data["friend_id"] = $this->Session->read('Message.friend_id');
            $this->request->data["user_id"] = $this->Auth->user('id');
            $this->request->data["type"] = "1";
            $this->UsersMessage->save($this->data); 
            $responseData = $this->UsersMessage->newData;
            $view = new View($this);
            $time = $view->loadHelper('Time');
            
            $created = $responseData["UsersMessage"]["created"];
            $responseData["UsersMessage"]["created"] = $time->timeAgoInWords($created);
            $this->header('Content-Type: application/json');
            echo json_encode($responseData); 	    
		} 
        exit;
	}
    
    public function addMessage() {
        $this->securityCheck();
        $friendIds = $this->data["friend"];
        $this->request->data["user_id"] = $this->Auth->user('id');
        if(!empty($friendIds) && $this->data["message"] != "") {
            foreach($friendIds as $friendId){
                $this->request->data["friend_id"] = $friendId; 
                $exist = $this->parentMessageExist($friendId);
                if($exist) {
                    $this->request->data["parent_id"] = $exist;       
                }
                $this->UsersMessage->create();
                $this->UsersMessage->save($this->data); 
            }
            echo json_encode(array("success"=>true)); 
        }    
        exit;
	}
    
    private function parentMessageExist($senderId) {
        $authUserId =  $this->Auth->user('id');
        $arg["conditions"] = array("(UsersMessage.friend_id=$senderId AND UsersMessage.user_id=$authUserId) OR
                                    (UsersMessage.user_id=$senderId AND UsersMessage.friend_id=$authUserId)
                                   ");
        $records = $this->UsersMessage->find("first", $arg);
        
        if(isset($records["UsersMessage"]["id"])) {
            return $records["UsersMessage"]["id"];
        } else {
            return false;
        }
    }
    
    private function permanentlyDelete() {
         $authUserId = $this->Auth->user('id');
         $messageId = $this->data["param_id"]; 
         $isPermanent = 0;
         
         $records = $this->UsersMessage->find("first",array("conditions"=>array("UsersMessage.id"=>$messageId))); 
         $senderId = $records["UsersMessage"]["user_id"];
         $senderDeleted = $records["UsersMessage"]["sender_deleted"];
         $recepientDeleted = $records["UsersMessage"]["recepient_deleted"];
         if($senderDeleted >= 1) {
            $data["sender_deleted"] = 2;
            $isPermanent = 1;
         }
         if($recepientDeleted >= 1) {
            $data["recepient_deleted"] = 2;
            $isPermanent = 1;
         } 
         
         if($isPermanent == 1) {
             if($senderId == $authUserId) {
                $conditions = array("UsersMessage.id"=>$messageId, "UsersMessage.user_id"=>$authUserId);
             } else {
                $conditions = array("UsersMessage.id"=>$messageId, "UsersMessage.friend_id"=>$authUserId); 
             }
             $isDeleted = $this->UsersMessage->updateAll( $data, $conditions ); 
             echo json_encode(array("success"=>true)); 
             return null;
         } 
         return $senderId;     
    }
    
    public function deleteMessage() {
       $this->securityCheck();
       
       $authUserId = $this->Auth->user('id');
       $messageId = $this->data["param_id"];
       $senderId = $this->permanentlyDelete();
       
       if($senderId) {
           if($senderId == $authUserId) {
              $conditions = array("UsersMessage.id"=>$messageId, "UsersMessage.user_id"=>$authUserId);
              $data["recepient_deleted"] = 1;
           } else {
            
              $conditions = array("UsersMessage.id"=>$messageId, "UsersMessage.friend_id"=>$authUserId);
              $data["sender_deleted"] = 1; 
           }
           
           $isDeleted = $this->UsersMessage->updateAll( $data, $conditions );
           if($isDeleted) {
                $response = array("success"=>true);
            } else {
                $response = array("error"=>true);  
            }            
           echo json_encode($response); 
       }
       exit; 
    }
    
    public function removePhoto() {
        $sourceImage = $this->request->data["sourceImage"];
        $destination = Configure::read("Message.photo.uploadpath") . $sourceImage; 
        if(fileExistsInPath($destination)) {
            unlink($destination);
            echo json_encode(array("success"=>true));
            exit;
        }
        echo json_encode(array("error"=>$errorList["File"][3]));
        exit;   
    }
     
}
?>
