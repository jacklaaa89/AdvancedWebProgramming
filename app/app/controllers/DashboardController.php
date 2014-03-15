<?php

/**
 * this controller handles requests to /dashboard.
 * @author Jack Timblin - U1051575
 */
class DashboardController extends \Phalcon\Mvc\Controller {
    
    /**
     * this is the action for /dashboard/{userID}.
     * @param string $userID the ID of the user in the dashboard.
     */
    public function indexAction($userID) {
        if(!isset($userID)) {
            return $this->dispatcher->forward(array(
                'controller' => 'error',
                'action' => 'index'
            ));
        }
        
        //get the user from the ID.
        $user = \Model\Auth\User::findFirst(array(
            'conditions' => 'userID = ?1',
            'bind' => array(1 => $userID),
            'bindTypes' => array(1 => \Phalcon\Db\Column::BIND_PARAM_STR)
        ));
        
        //check the user was valid.
        if(!$user) {
             return $this->dispatcher->forward(array(
                'controller' => 'error',
                'action' => 'index'
            ));
        }
        
        //deal with a form submission.
        if($this->request->isPost()) {
            //one of the forms was posted.
            switch($this->request->getPost('type', 'string')) {
                case 'EMAIL_UPDATE':
                    $email = $this->request->getPost('email', 'string');
                    if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        if(!$user->setEmail($email)->save()) {
                            //show error message
                            $this->flashSession->error('Could not update email address');
                        } else {
                            //also update the data model.
                            $this->flashSession->success('Email Updated Successfully');
                            $user->getDataUser()->setEmail($email)->save();
                        }
                    }
                    break;
                case 'PASSWORD_UPDATE':
                    $pass = hash('sha256', $this->request->getPost('oldpass', 'string'));
                    if($pass != $user->getPasswordHash()) {
                        $this->flashSession->error('Old Password was incorrect, please try again.');
                    } else {
                        //check new passwords match.
                        $newPass = $this->request->getPost('pass', 'string');
                        if(strlen($newPass) < 6) {
                            $this->flashSession->error('Passwords is too short.');
                        } else {
                            $newPass = hash('sha256', $newPass);
                            $cPass = hash('sha256', $this->request->getPost('confirmpass', 'string'));
                            if($newPass != $cPass) {
                                $this->flashSession->error('Passwords did not match, try again.');
                            } else {
                                //update the password and save.
                                $this->flashSession->success('Password Updated Successfully');
                                $user->setPasswordHash($newPass)->save();
                            }
                        }
                    }
                    break;
            }
        }
        
        //disable the main layout as this view uses a navbar.
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_LAYOUT);
        
        //send the user through to the view.
        $this->view->user = $user;
        $this->view->permission = '';
    }
    
    /**
     * this action deals with /dashboard/{userID}/permissions
     * @param string $userID the currently logged in user.
     */
    public function permissionsAction($userID) {
        //again check that the userID is set and th user exists.
        if(!isset($userID)) {
            return $this->dispatcher->forward(array(
                'controller' => 'error',
                'action' => 'index'
            ));
        }
        
        //get the user from the ID.
        $user = \Model\Auth\User::findFirst(array(
            'conditions' => 'userID = ?1',
            'bind' => array(1 => $userID),
            'bindTypes' => array(1 => \Phalcon\Db\Column::BIND_PARAM_STR)
        ));
        
        //check the user was valid.
        if(!$user) {
             return $this->dispatcher->forward(array(
                'controller' => 'error',
                'action' => 'index'
            ));
        }
        
        //disable the main layout as this view uses a navbar.
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_LAYOUT);
        
        //send the user over to the view.
        $this->view->user = $user;
        $this->view->permission = 'active';
        
    }
    
    /**
     * this action deals with ajax requests from /dashboard/background
     */
    public function backgroundAction() {
        
        $this->view->disable();
        //check to see if this is a ajax and post request, that is the only valid request.
        if(!$this->request->isPost() || !$this->request->isAjax()) {
            echo json_encode(array('error' => true, 'error_description' => 'only post and ajax commited'));
            return;
        }
        
        //get the permission/application that was revoked access.
        $type = $this->request->getPost('type', 'string');
        $userID = $this->request->getPost('userID', 'string');
        $appID = $this->request->getPost('appID', 'string');
        $app = \Model\Auth\Application::findApplication($appID, $userID);
        if(!$app) {
            echo json_encode(array('error' => 'true', 'error_description' => 'application not found.'));
            return;
        }
        switch(strtoupper($type)) {
            case 'PERMISSION_REVOKED':
                $permission = $this->request->getPost('permission', 'string');
                //remove matching permission from scope array on token, then save token
                if(!is_bool($app)) {
                    $app->removeScope($permission);
                    $app->update();
                }
                break;
            case 'APPLICATION_REVOKED':
                //remove the token associated with this client/user pair.
                if(!is_bool($app)) {
                    $app->delete();
                }
                break;
            default:
                echo json_encode(array('error' => true, 'error_description' => 'type undefined'));
                return;
        }
        //cheat by returning error = false, which I will the use to make page reload instead of returning new HTML.
        echo json_encode(array('error' => false, 'error_description' => ''));
        exit();
    }
    
}
