<?php

namespace Controller;

class DashboardController extends \Phalcon\Mvc\Controller {
    
    public function indexAction($userID) {
        if(!isset($userID)) {
            return $this->response->redirect('error/index');
        }
        
        //get the user from the ID.
        $user = \Model\Auth\User::findFirst(array(
            'conditions' => 'userID = ?1',
            'bind' => array(1 => $userID),
            'bindTypes' => array(1 => \Phalcon\Db\Column::BIND_PARAM_STR)
        ));
        
        //check the user was valid.
        if(!$user) {
            return $this->response->redirect('error/index');
        }
        
        //send the user through to the view.
        $this->view->user = $user;
    }
    
    public function permissionsAction($userID) {
        //again check that the userID is set and th user exists.
        if(!isset($userID)) {
            return $this->response->redirect('error/index');
        }
        
        //get the user from the ID.
        $user = \Model\Auth\User::findFirst(array(
            'conditions' => 'userID = ?1',
            'bind' => array(1 => $userID),
            'bindTypes' => array(1 => \Phalcon\Db\Column::BIND_PARAM_STR)
        ));
        
        //check the user was valid.
        if(!$user) {
            return $this->response->redirect('error/index');
        }
        
        //send the user over to the view.
        $this->view->user = $user;
        
    }
    
    public function backgroundAction() {
        //check to see if this is a ajax and post request, that is the only valid request.
        if(!$this->request->isPost() || !$this->request->isAjax()) {
            echo json_encode(array('error' => true, 'error_description' => 'only post and ajax commited'));
            return;
        }
        
        //get the permission/application that was revoked access.
        $type = $this->request->getPost('type', 'string');
        $userID = $this->request->getPost('userID', 'string');
        $appID = $this->request->getPost('appID', 'string');
        switch(strtoupper($type)) {
            case 'PERMISSION_REVOKED':
                $permission = $this->request->getPost('permission', 'string');
                //remove matching permission from scope array on token, then save token
                break;
            case 'APPLICATION_REVOKED':
                //remove the token associated with this client/user pair.
                break;
            default:
                echo json_encode(array('error' => true, 'error_description' => 'type undefined'));
                return;
        }
        //cheat by returning error = false, which will make page reload instead of returning new HTML.
        echo json_encode(array('error' => false, 'error_description' => ''));
        exit();
    }
    
}
