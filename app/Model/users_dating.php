<?php
/* @TODO optimize all find() options across the controllers */

class UsersDating extends AppModel {
	public $name = 'UsersDating';
    public $foreignKey = "user_id";
    
    public function getDatingsPreferences($userId) {
        $arg["conditions"] = array("UsersDating.user_id"=>$userId);
        return $this->find("first", $arg);
    }
    
}
