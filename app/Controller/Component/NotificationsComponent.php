<?php
class NotificationsComponent extends Object {
/**
 * 2 = has requested to be your friend
 * 3 = are now friends
 * 4 = changed profile photo
 * 5 = ignore your friend request
 */	

/**
 * Initialize component
 *
 * @param Controller $controller Instantiating controller
 * @return void
 */
	public function initialize($controller) {
		$this->controller = $controller;
	}
	
	function startup () {}
	function beforeRender () {}
	function shutdown () {}
    
    public function setRequestedFriend( $friendId ) {
        $userId = $this->controller->Auth->user('id');
        if($userId) {  
           $profileId = $friendId;
           $notification = "has requested to be your friend";
           $data["message"] =  $notification;
           $data["user_id"] = $userId;
           $data["friend_id"] = $profileId;
           $data["type"] = 2; 
           $this->setNotification($data); 
		} 
       return true;    
    }
    
    public function setAddedFriend( $friendId ) {
        $userId = $this->controller->Auth->user('id');
        if($userId) {  
           $profileId = $friendId;
           $notification = "are now friends";
           $data["message"] =  $notification;
           $data["user_id"] = $userId;
           $data["friend_id"] = $profileId;
           $data["type"] = 3; 
           $this->setNotification($data);
		} 
       return true;    
    }
    
    public function setChangedProfilePhoto($photo = null) {
        $userId = $this->controller->Auth->user('id');
        if($userId) {  
           $notification = "changed profile photo";
           $data["message"] =  $notification;
           $data["user_id"] = $userId;
           $data["friend_id"] = 0;
           $data["photo"] = $photo;
           $data["type"] = 4; 
           $this->setNotification($data);
		}
       return true;   
    }
    
    public function setAddedVideo() {
        $userId = $this->controller->Auth->user('id');
        if($userId) {  
           $profileId = 0;
           $notification = "added video";
           $data["video_url"] = $this->controller->data["UsersVideo"]["url"];
           $data["video_thumb_url"] = $this->controller->data["UsersVideo"]["thumb_url"];
           $data["message"] =  $notification;
           $data["user_id"] = $userId;
           $data["friend_id"] = $profileId;
           $data["type"] = 5; 
           $this->setNotification($data);
		} 
       return true;    
    }
    
    public function setIgnoresRequest( $friendId ) {
        $userId = $this->controller->Auth->user('id');
        if($userId) {  
           $profileId = $friendId;
           $notification = "ignore your friend request";
           $data["message"] =  $notification;
           $data["user_id"] = $userId;
           $data["friend_id"] = $profileId;
           $data["type"] = 6; 
           $this->setNotification($data);
		} 
       return true;    
    }
   	
    
    
    function setNotification($data) {
           $this->controller->loadModel("Notification");
           $this->controller->Notification->create();
           $this->controller->Notification->save($data);  
    }
  			
}
	
?>
