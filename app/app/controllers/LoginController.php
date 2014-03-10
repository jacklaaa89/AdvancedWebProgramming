<?php

namespace Controller;

class LoginController extends \Phalcon\Mvc\Controller {
    
    public function indexAction() {
        
        //check to see if a user is already logged in.
        if($this->session->has('auth')) {
            $userID = $this->session->get('auth');
            //redirect to the dashboard.
            return $this->response->redirect('/dashboard/'.$userID);
        }
        
        //this action is responsible for logging a user in.
        if($this->request->isPost()) {
            //the form was submitted.
            $username = $this->request->getPost('user', 'string');
            $pass = hash('sha256', $this->request->getPost('pass', 'string'));
            
            $user = \Model\Auth\User::checkUsersCredentials($username, $pass);
            
            if(!is_bool($user)) {
                //set the user role in the session.
                $this->session->set('auth', $user->getUserID());
                
                //redirect to the dashboard.
                return $this->response->redirect('/dashboard/'.$user->getUserID());
            } else {
                //flash error and re-render login form.
                $this->view->username = $username;
                $this->flash->error('Username/Password were incorrect.');
            }
            
        }
        
    }
    
}

