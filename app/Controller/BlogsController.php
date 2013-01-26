<?php

class BlogsController extends AppController {
	public $uses = array();
	public $helpers = array("Jquery");
    public $components = array("Auth");
	public $autoLayout = false;
    public $userId = 0;

	function beforeFilter() {
        parent::beforeFilter();
    }

    public function index() {
       $this->redirect("browse");
    }

    public function browse() {
        $arg["conditions"] = array();
        $blogEntries = $this->Blog->find("all", $arg);
        app::uses("Sanitize", "Utility");
        $this->set("dataBlogEntries", $blogEntries );
        $this->setLayout(array("title"=>"Entries", "layout"=>"blogs"));
    }

    public function viewEntry() {

        if(isset($this->params["pass"][1])) {
            $blogId = $this->params["pass"][1];
            $arg["conditions"] = array("Blog.id"=>$blogId);
            $blogEntry = $this->Blog->find("first", $arg);
            $this->set("dataBlogEntry", $blogEntry );
            $this->showsBlogComments($blogId );
            $this->Session->write("Blog.id", $blogId);
            $this->setLayout(array("title"=>"View Entry", "layout"=>"blogs"));
        } else {
            exit;
        }
    }

    private function showsBlogComments($blogId) {
        $param["conditions"] = array("BlogsComment.blog_id"=>$blogId);
        $param["order"] = array("BlogsComment.id"=>"ASC");
        $this->loadModel("BlogsComment");
        $dataBlogComments = $this->BlogsComment->find("all", $param);
        $this->set("dataBlogComments", $dataBlogComments);
    }

    public function addComment() {
        $this->securityCheck();
        if(!empty($this->data['comment']) && $this->Auth->user('id')) {
            $this->loadModel("BlogsComment");
            $this->request->data["user_id"] = $this->Auth->user('id');
            $blogId = $this->Session->read("Blog.id");
            $this->request->data["blog_id"] = $blogId;
            $this->BlogsComment->save($this->data);
            $responseData = $this->BlogsComment->newData;

            $view = new View($this);
            $time = $view->loadHelper('Time');

            $created = $responseData["BlogsComment"]["created"];
            $responseData["BlogsComment"]["created"] = $time->timeAgoInWords($created);
            $this->header('Content-Type: application/json');
            echo json_encode($responseData);
		}
       exit;
    }

    public function deleteComment() {
        $this->securityCheck();
        $this->loadModel("BlogsComment");
        $paramId = $this->request->data["param_id"];
        $conditions = array("BlogsComment.user_id"=>$this->Auth->user('id'),
                            "BlogsComment.id"=>$paramId);
        $this->BlogsComment->delete($paramId, true);
        echo json_encode(array("success"=>true));
        exit;
    }

    public function writeNewEntry() {
        $this->setLayout(array("title"=>"Write New Entry", "layout"=>"blogs"));
    }

    public function saveEntry() {
        $this->securityCheck();
        $this->request->data["Blog"]["user_id"] = $this->Auth->user('id');
        $this->Blog->save($this->request->data);
        $lastInserted = $this->Blog->getLastInsertID();
        echo json_encode(array("redirect"=>"preview/$lastInserted"));
        exit;
    }

    public function updateEntry() {
        $this->securityCheck();
        $authUserId = $this->Auth->user('id');
        $blogId = $this->Session->read("Blog.id");
        $this->request->data["Blog"]["id"] = $blogId;

        $this->Blog->save($this->request->data);
        echo json_encode(array("redirect"=>"/blogs/preview/$blogId"));
        exit;
    }

    public function edit($blogId = 0) {
       $arg["conditions"] = array("Blog.id"=>$blogId, "Blog.user_id"=>$this->Auth->user('id'));
       $blogEntry = $this->Blog->find("first", $arg);
       $this->data = $blogEntry;
       $this->Session->write("Blog.id", $blogId);
       $this->setLayout(array("title"=>"Preview New Entry", "layout"=>"blogs"));
    }

    public function myEntries() {
       $arg["conditions"] = array("Blog.user_id"=>$this->Auth->user('id'));
       $blogEntry = $this->Blog->find("all", $arg);
       app::uses("Sanitize", "Utility");
       $this->set("dataBlogEntries", $blogEntry );
       $this->setLayout(array("title"=>"My Entries", "layout"=>"blogs"));
    }

    public function preview($blogId = 0) {
       $arg["conditions"] = array("Blog.id"=>$blogId, "Blog.user_id"=>$this->Auth->user('id'));
       $blogEntry = $this->Blog->find("first", $arg);

       $this->set("dataBlogEntry", $blogEntry);
       $this->setLayout(array("title"=>"Preview New Entry", "layout"=>"blogs"));
    }


}
?>