<?php

/**
 * this controller is the root controller and handles /.
 */
class IndexController extends \Phalcon\Mvc\Controller {
    
    /**
     * this action handles / and /index.
     */
    public function indexAction() {
        
        //we need to use the cURL extension in order to process POST requests.
        if(!extension_loaded('curl')) {
            $this->flashSession->error('Curl is not enabled, cannot use api.');
            return;
        }
        
        //check if this client already has a token, in reality this would be saved in
        //a database, or persistant caching mechanism.
        if($this->session->has('token')) {
            return $this->response->redirect('index/callback');
        }
        
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
    
    /**
     * this action handles /index/callback.
     */
    public function callbackAction() {
        
        //check to see if a token is set.
        $token = $this->session->get('token');
        
        //if the token is not set, we shall see if we asked to be authorised.
        if(!isset($token)) {

            //this is the point in the application that handles the callback from the auth process.
            //this either receives a temp code, or an error.
            $e = $this->request->getQuery('error', 'string');
            if(isset($e) && strlen($e) != 0) {
                //an error must have occured.
                $this->flashSession->error($this->request->getQuery('error_description'));
                $this->session->remove('token');
                $this->session->remove('scope');
                return $this->response->redirect('index');
            }

            //get the code value, and check the state.
            $state = $this->request->getQuery('state', 'string');
            if($this->session->get('state') != $state) {
                //something went wrong.
                $this->flashSession->error('the state variable passed to the auth-server was different.');
                $this->session->remove('state');
                $this->session->remove('token');
                $this->session->remove('scope');
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
                $this->flashSession->error('An error occured in the token exchange.');
                return $this->response->redirect('index');
            }

            //get the token and save it in a session.
            $params = json_decode($response, true);
            $token = $params['token'];
            $scope = explode(',',$params['scope']);
            $this->session->set('token', $token);
            $this->session->set('scope', $scope);
           
        } else {
            $scope = $this->session->get('scope');
        }
        
        //call the /user api to get information about the user we just authenticated.
        //we need to check that the user gave us permission, the scope is returned with
        //the token.
        
        if(!in_array('user', $scope)) {
            //the user did not give us permission to view user profile.
            $this->flashSession->error('We dont have permission to use the /user api.');
            $this->session->remove('token');
            $this->session->remove('scope');
            return $this->response->redirect('index');
        }
        
        //check to see if the token is still valid.
        $url = 'http://api.local/user?access_token='.$token;
        $headers = get_headers($url);
        if(preg_match('/(forbidden)/i', $headers[0])) {
            $this->flashSession->error('User has revoked access to user api.');
            $this->session->remove('token');
            $this->session->remove('scope');
            return $this->response->redirect('index');
        }
        
        //token can be passed via Header or querystring.
        //instant array access is only available after php 5.4
        if(version_compare(PHP_VERSION, '5.4.0', '>=')) {
            $this->view->data = json_decode(file_get_contents($url), true)[0];
        } else {
            $d = json_decode(file_get_contents($url), true);
            $this->view->data = $d[0];
        }
        
        
    }
    
    /**
     * generates a random string of a specified length to use as a state.
     * @param int $length [optional] <p>The length of the string to return, default is 10</p>
     * @return string the random string.
     */
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
    
}

