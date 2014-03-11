<?php

class IndexController extends \Phalcon\Mvc\Controller {
    
    public function indexAction() {
        //check to see if user has asked to log in with service.
        if($this->request->isPost()) {
            //start the authentication process, we have already registered as 
            //a client with th auth-server.
            //define what permissions we need.
            $scope = join(',', array('user', 'messages'));
            //generate a random state string.
            $state = $this->generateRandomString();
            //store the state in a session so we can check its the same on re-entry.
            $this->session->set('state', $state);
            //build the url.
            $url = 'http://auth-server.local/oauth/authorize?client_id=3otiuyt4mo&scope='.$scope.'&state='.$state
                    .'&'.  http_build_query(array('redirect_uri'=>'http://example.local/index/callback'));
            
            //redirect the user to the auth-server.
            $this->response->redirect($url, true);
            
        }
    }
    
    public function callbackAction() {
        
        //this is the point in the application that handles the callback from the auth process.
        //this either receives a temp code, or an error.
        $e = $this->request->getQuery('error', 'string');
        if(isset($e) && strlen($e) != 0) {
            //an error must have occured.
            return $this->response->redirect('index');
        }
        
        //get the code value, and check the state.
        $state = $this->request->getQuery('state', 'string');
        if($this->session->get('state') != $state) {
            //something went wrong.
            $this->session->remove('state');
            return $this->response->redirect('index');
        }
        
        $code = $this->request->getQuery('code', 'string');
        //now we need to exchange this for an access token.
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POSTFIELDS => json_encode(array('client_id' => '3otiuyt4mo', 'client_secret' => 'fgl;j98o;poiy408v9th560y9pi5095', 'code' => $code)),
            CURLOPT_URL => 'http://auth-server.local/oauth/access_token'
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        
        if(!$response) {
            //an error occured.
            return $this->response->redirect('index');
        }
        
        //get the token and save it in a session.
        $params = json_decode($response, true);
        $token = $params['token'];
        $this->session->set('token', $token);
        
        //call the /user api to get information about the user we just authenticated.
        //we need to check that the user gave us permission, the scope is returned with
        //the token.
        
        if(!in_array('user', explode(',', $params['scope']))) {
            //the user did not give us permission to view user profile.
            return $this->response->redirect('index');
        }
        
        //token can be passed via Header or querystring.
        $data = json_decode(file_get_contents('http://api.local/user?access_token='.$token), true);
        $this->view->data = $data[0];
        
    }
    
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
    
}

