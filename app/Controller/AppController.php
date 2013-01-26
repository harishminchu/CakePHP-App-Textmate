<?php

/** @property Blog $Blog
 *  @property User $User
 */

class AppController extends Controller {
    public $components = array('Session');
    public $errorList = array();
    /**
     * Supports .htm files to all controller views but still it supports .ctp
     *
     * @var string
     * @access public
     */
    var $ext = ".htm";

    /**
     * Disable rendering layout automatically to all controllers.
     *
     * @var boolean
     * @access public
     */
    var $autoLayout = false;

    /**
     * Called before the controller action.
     *
     * @access public
     * @link http://book.cakephp.org/view/984/Callbacks
     */
    public function beforeFilter() {
        $this->checkDatabaseConnection();
        $this->initializeAuth();
        $this->initializeErrorList();
    }

    private function initializeErrorList() {
        $errorList["User"][1] = "Invalid username or password, please try again.";
        $errorList["User"][2] = "Invalid trying on non-post login";
        $errorList["User"][3] = "Data has not been saved, please try again later";
        $errorList["User"][4] = "Error deleting, please try again later";
        $errorList["User"][5] = "Error on saving, please try again";
        $errorList["User"][6] = "This email is already registered.Please try another email.";

        $errorList["File"][1] = "Invalid file or format";
        $errorList["File"][2] = "File not found";
        $errorList["File"][3] = "File not found or there is a removing issue";
        $this->errorList = $errorList;
    }

    private function initializeAuth() {
        if (isset($this->Auth)) {
            $this->Auth->allow('index', 'view', 'register', 'activate', "activateUser", "saveNewDetails");
            $this->Auth->allow('publicProfile', 'showFriends', "saveNewDatings");
            $this->Auth->loginRedirect = "/whats-new";
            $this->Auth->logoutRedirect = "/logged-out";
            $this->Auth->authError = "Session expired, please login again.";
            $this->Auth->scope = array('User.active' => 1);
            $this->Auth->ajaxLogin = "invalidSession";
            $this->Auth->fields = array(
                'username' => 'username',
                'password' => 'password'
            );

        }
    }

    /**
     * Performs checking if connected to database and the database configuration file is present.
     * @return void
     * @access private
     */
    private function checkDatabaseConnection() {
        /*@TODO : should have database connection tester here and send email if down */
    }

    function securityCheck() {
        if (!$this->request->is('post') && !$this->request->is('ajax')) {
            /* 'You are not authorized to process this request!' */
            $this->jsonResponse(array("error" => "You are not authorized to process this request"));
            exit;
        } else {
            if (strpos(env('HTTP_REFERER'), trim(env('HTTP_HOST'), '/')) === false) {
                /* 'Invalid referrer detected for this request!' */
                $this->jsonResponse(array("error" => "Invalid referrer detected for this request!"));
                exit;
            }
        }
    }

    public function setLayout($arg) {
        $this->autoLayout = true;
        $this->layout = $arg["layout"];
        $this->set("title_for_layout", $arg["title"]);
    }


    function redirectToErrorPage() {
        /*@TODO : Routes to error message page template */
    }

    function jsonResponse($data) {
        $response = $data;
        echo json_encode($response);
    }


    public function newestUser($model) {
        $param["conditions"] = array();
        $param["limit"] = 15;
        $param["order"] = array("User.id" => "DESC");
        $this->attachUsersFriendBehavior($model);
        $newestUser = $model->find("all", $param);
        $this->set("dataNewestUser", $newestUser);

    }

    public function popularUser($model) {
        $param["limit"] = 15;
        $param["order"] = array("User.views" => "DESC");
        $param["conditions"] = array();
        $this->attachUsersFriendBehavior($model);
        $popularUser = $model->find("all", $param);
        $this->set("dataPopularUser", $popularUser);
    }

    private function attachUsersFriendBehavior($model) {
        $model->Behaviors->attach('Containable');
        $model->contain('UsersFriend');
    }

}

?>
