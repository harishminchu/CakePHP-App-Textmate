<?php

class UsersFriendsController extends AppController {
	public $uses = array();
	public $helpers = array("Jquery");
    public $components = array("Auth", "Notifications");
	public $autoLayout = false;
   
	
	function beforeFilter() {
        parent::beforeFilter();
        
    }
    
/**
 * Shows all friend request
 * @return void
 */
    public function friendRequests() {
        $authUserId = $this->Auth->user('id');
        if($authUserId) {
            $param["conditions"] = array("UsersFriend.friend_id"=>$authUserId,"request_granted"=>0);
            $param["fields"] = array("UsersFriend.user_id","User.id",
                                     "User.username", "User.first_name", "User.last_name", "User.photo");
            $dataFriendRequests = $this->UsersFriend->find("all", $param);
            $this->set("dataFriendRequests", $dataFriendRequests);
        }        
    }
    
    
	function addFriend() {
		$this->securityCheck();
        $isSave = false;
        $friendId = $this->request->data["param_id"];
        $data["UsersFriend"]["user_id"] = $this->Auth->user("id");
        $data["UsersFriend"]["friend_id"] = $friendId;
        if ($this->UsersFriend->isUnique(array('UsersFriend.user_id'=>$this->Auth->user("id"), 
           'UsersFriend.friend_id'=>$friendId), false)) {
            $isSave = $this->UsersFriend->save($data);
        }
        if($isSave) {
            $this->jsonResponse(array("success"=>true));
            $this->Notifications->setYouRequested($friendId);
        }else {
           $this->jsonResponse(array("error"=>$this->errorList["User"][3])); 
        }
        exit;
	}    
	function cancelFriend() {
		$this->securityCheck();
        $friendId = $this->request->data["param_id"];
        
        $conditions = array("user_id"=>$this->Auth->user("id"),"friend_id"=>$friendId);
        $this->UsersFriend->deleteAll($conditions);
        
        $conditions = array("user_id"=>$friendId, "friend_id"=>$this->Auth->user("id"));
        $isSDelete = $this->UsersFriend->deleteAll($conditions);
        
        if($isSDelete) {
            $this->jsonResponse(array("success"=>true));
            $this->Notifications->setIgnoreRequest($friendId);
        }else {
            $this->jsonResponse(array("error"=>$this->errorList["User"][4])); 
        }
        exit;
	}    
 
   
    function confirmedRequest() {
        $this->securityCheck();
        $isSaved = false;
        $data["request_granted"] = 1;
        $conditions = array("user_id"=>$this->request->data["friendId"], "friend_id"=>$this->Auth->user("id"));
        $this->UsersFriend->updateAll($data, $conditions);
        
        if ($this->UsersFriend->isUnique(array('UsersFriend.user_id'=>$this->Auth->user("id"), 
           'UsersFriend.friend_id'=>$this->request->data["friendId"]), false)) {
            
            $data["friend_id"] = $this->request->data["friendId"];
            $data["user_id"] = $this->Auth->user("id");
            $data["request_granted"] = 1;
            $isSaved = $this->UsersFriend->save($data);
        }
       
        if($isSaved) {
            $friendId = $this->request->data["friendId"];
            $this->Notifications->setAreNowFriends($friendId);
            $this->jsonResponse(array("success"=>true));   
        } else {
            $this->jsonResponse(array("error"=>$this->errorList["User"][5])); 
        }
        exit;
    }


}
?>
