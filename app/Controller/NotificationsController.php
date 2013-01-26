<?php

class NotificationsController extends AppController {
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
    public function recentUpdates() {
       $authUserId = $this->Auth->user('id');
       if($authUserId) {
            $types = "2,3,4,5";
            $arg["conditions"] = array("(Notification.user_id = $authUserId OR Notification.friend_id = $authUserId) AND Notification.type IN($types)");
            $dataNotification = $this->Notification->find("all", $arg);
            
            $param["conditions"] = array("UsersFriend.friend_id"=>$authUserId,"request_granted"=>0);
            $this->loadModel("UsersFriend");
            $totalFriendRequest = $this->UsersFriend->find("count", $param);
        }  
      
       $this->set("dataNotification", $dataNotification); 
       $this->set("dataTotalFriendRequest", $totalFriendRequest); 
       $this->setLayout(array("title"=>"Notifications", "layout"=>"notifications"));
    }
         
    public function friendRequests() {
       $authUserId = $this->Auth->user('id');
       if($authUserId) {
           $param["conditions"] = array("UsersFriend.friend_id"=>$authUserId,"request_granted"=>0);
            $param["fields"] = array("UsersFriend.user_id","UsersFriend.id","User.id",
                                     "User.username", "User.first_name", "User.last_name", "User.photo");
            $this->loadModel("UsersFriend");
            $dataFriendRequests = $this->UsersFriend->find("all", $param);
            
        }  
       $this->set("dataTotalFriendRequest", count($dataFriendRequests)); 
       $this->set("dataFriendRequests", $dataFriendRequests); 
       $this->setLayout(array("title"=>"Friend Requests", "layout"=>"notifications"));
    }
}
?>
