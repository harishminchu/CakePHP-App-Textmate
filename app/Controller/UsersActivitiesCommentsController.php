<?php
class UsersActivitiesCommentsController extends AppController {
    public $components = array("Auth");
    public $helpers = array();
	public $autoLayout = false;
	public $userId = 0;
	
    public function beforeFilter() {
        parent::beforeFilter();
        $actionAjax = array("addComment");
        if(in_array($this->action,$actionAjax)) {
            $this->securityCheck();
        }
    }
	
	public function addComment() {
        $this->securityCheck();
        if(!empty($this->data['comment']) && $this->Auth->user('id')) {  
            $this->request->data["user_id"] = $this->Auth->user('id');
            $this->UsersActivitiesComment->save($this->data); 
            $responseData = $this->UsersActivitiesComment->newData;
            
            $view = new View($this);
            $time = $view->loadHelper('Time');
            
            $created = $responseData["UsersActivitiesComment"]["created"];
            $responseData["UsersActivitiesComment"]["created"] = $time->timeAgoInWords($created);
            $this->header('Content-Type: application/json');
            echo json_encode($responseData); 	    
		} 
       exit;
	}
    
    public function deleteComment() {
        $paramId = $this->request->data["param_id"];
        $conditions = array("UsersActivitiesComment.user_id"=>$this->Auth->user('id'), 
                                    "UsersActivitiesComment.id"=>$paramId);                
        $this->UsersActivitiesComment->delete($paramId, true); 
        echo json_encode(array("success"=>true)); 
        exit;
    }
   
    public function lookLiked() {
        $postIds = $this->request->data["postIds"];
        $this->loadModel("UsersLike");
        $param["conditions"] = array("UsersLike.like_id IN($postIds)", "UsersLike.type"=>2);
        $this->UsersLike->recursive = 0;
        $responseData = $this->UsersLike->find("all", $param);
        echo json_encode($responseData); 
        exit;
   }
   
	

}
?>
