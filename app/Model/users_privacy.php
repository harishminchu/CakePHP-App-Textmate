<?php
class UsersPrivacy extends AppModel {
	var $name = 'UsersPrivacy';
	    
    public function getPrivacy($authId) {
        $param = array("conditions"=>array("user_id"=>$authId));
        $results = $this->find("first", $param);
        return $results;
    }
 
}
