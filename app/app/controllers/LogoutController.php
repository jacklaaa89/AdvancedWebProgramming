<?php

/**
 * this controller handles requests to /logout
 */
class LogoutController extends \Phalcon\Mvc\Controller {
    
    /**
     * this action handles /logout/index or just /logout.
     */
    public function indexAction() {
        
        if($this->session->has('auth')) {
            $this->session->remove('auth');
        }
        //this route logs a authenticated user out.
        //just remove the session and redirect to login page.
        
        return $this->response->redirect('login');
        
    }
    
}

