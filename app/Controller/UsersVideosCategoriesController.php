<?php
/* @TODO Security check for the following: addPhoto(), removePhoto()*/ 
class UsersVideosCategoriesController extends AppController {
	public $uses = array();
	public $helpers = array("Jquery");
    public $components = array("Auth");
	public $autoLayout = false;
    public $userId = 0;
	
	function beforeFilter() {
        parent::beforeFilter();
        
    }
    
    public function addCategory () {
        $this->securityCheck();
        if($this->data["category_name"] != "") {
            $authUserId = $this->Auth->user('id');
            $this->request->data["user_id"] = $authUserId; 
            $this->UsersVideosCategory->save($this->request->data);
        }    
        exit;   
    }
    
    public function editSettings($categoryId = 0) {
        $authUserId = $this->Auth->user('id');
        $arg["conditions"] = array("UsersVideosCategory.id"=>$categoryId, "UsersVideosCategory.user_id"=>$authUserId);
        $records = $this->UsersVideosCategory->find("first", $arg);
        $this->Session->write("VideosCategory.id", $categoryId);
        $this->data = $records;
        $this->setLayout(array("title"=>"Video Settings","layout"=>"videos"));     
    }
    
    public function saveSettings() {
        $this->securityCheck();
        $categoryId = $this->Session->read("VideosCategory.id");
        $this->request->data["UsersVideosCategory"]["id"] = $categoryId;
        $this->UsersVideosCategory->save($this->request->data);
        echo json_encode(array("success"=>true));
        exit;
    }
    
    public function deleteCategory() {
        $this->securityCheck();
        $authUserId = $this->Auth->user('id');
        $categoryId = $this->request->data["param_id"];
        $conditions = array("UsersVideosCategory.id"=>$categoryId, "UsersVideosCategory.user_id"=>$authUserId);
        $this->UsersVideosCategory->deleteAll($conditions, true);
        exit;

    }
    
    public function categoryAdd() {
        $this->setLayout(array("title"=>"Category Add","layout"=>"videos"));      
    }

}
?>
