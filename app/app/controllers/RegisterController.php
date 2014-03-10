<?php

namespace Controller;

class RegisterController extends \Phalcon\Mvc\Controller {
    
    public function indexAction() {
        
        if($this->request->isPost()) {
            //the register form was posted.
            //get the username, password
            $username = $this->request->getPost('user', 'string');
            $pass = $this->request->getPost('pass', 'string');
            
            //check that a user does not already exist with that username.
            $user = \Model\Auth\User::checkUserExists($username);
            if(!is_bool($user)) {
                $this->flashSession->error('A account already exists with that username');
                return $this->response->redirect('login/index');
            }
            
            //add the new user, and display if it was successful.
            $user = \Model\Auth\User::addNewUser($username, $pass);
            if(!is_bool($user)) {
                $this->flashSession->success('Your account was successfully created.');
                return $this->response->redirect('login/index');
            } else {
                $this->flash->error('An error occured creating your account, please try again.');
            }
        }
        
    }
    
}

