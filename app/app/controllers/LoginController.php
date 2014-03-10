<?php

namespace Controller;

class LoginController extends \Phalcon\Mvc\Controller {
    
    public function indexAction() {
        
        //this action is responsible for logging a user in.
        if($this->request->isPost()) {
            //the form was submitted.
            $user = $this->request->getPost('user', 'string');
            $pass = hash('sha256', $this->request->getPost('pass', 'string'));
            
            
        }
        
    }
    
}

