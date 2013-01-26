<?php
class PagesController extends AppController {
	var $uses = array();
    var $components = array("Auth");
	var $helpers = array("Jquery");
	var $autoLayout = false;
	var $userId = 0;
	
	function beforeFilter() {
        parent::beforeFilter();
    }
	
	
	function index() {
		$arg = array();
        $this->pageTitle = "TextMate";
        /* At the moment lets selects random user and then make an auto login */
        $this->loadModel("User");

        if(isset($this->params->query["name"])) {
            $like = $this->params->query["name"];
            $arg["conditions"] = array("User.first_name LIKE '%$like%'");
        }

        $results = $this->User->find("all", $arg);
        shuffle($results);
        $user = $results[0]["User"];
        $username = $user["username"];
        $password = "11111111"; /* all password from db are 111111 and encrypted */
        $this->request->data["User"]["username"] =  $username;
        $this->request->data["User"]["password"] =  $password;

        $this->Session->destroy();
        $this->Auth->logout();
        if ($this->Auth->login()) {
            $this->redirect($this->Auth->loginRedirect);
        }

    }
    
    

}
?>
