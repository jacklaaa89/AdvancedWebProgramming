<?php

class RegisterController extends \Phalcon\Mvc\Controller {
    
    public function indexAction() {
        
        if($this->request->isPost()) {
            //the register form was posted.
            //get the username, password
            $username = $this->request->getPost('user', 'string');
            $pass = $this->request->getPost('pass', 'string');
            $name = $this->request->getPost('name', 'string');
            
            //check that a user does not already exist with that username.
            $user = \Model\Auth\User::checkUserExists($username);
            if(is_bool($user)) {
                $this->flashSession->error('A account already exists with that username');
                return $this->response->redirect('login');
            }
            
            //add the new user, and display if it was successful.
            $user = \Model\Auth\User::addNewUser($username, $pass, $name);
            if(!is_bool($user)) {
                $this->flashSession->success('Your account was successfully created.');
                return $this->response->redirect('login');
            } else {
                $this->flashSession->error('An error occured creating your account, please try again.');
            }
        }
        
        //set view variables as this action is using a recycled view.
        $this->view->action = 'register';
        $this->view->label = 'Register';
        $this->view->name = \Phalcon\Tag::textField(array('name', 'class' => 'form-control', 'required' => 'required', 'placeholder' => 'Name', 'style' => 'margin-bottom:10px;'));
        $this->view->title = 'Please enter an Email Address and Password to create an account.';
        
        //just use the login view to render as
        //it has the same fields.
        $this->view->pick('login/index');
        
    }
    
}

