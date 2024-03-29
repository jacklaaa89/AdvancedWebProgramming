<?php

namespace Plugin;

use \Phalcon\Events\Event,
    \Phalcon\Mvc\User\Plugin,
    \Phalcon\Mvc\Dispatcher,
    \Phalcon\Acl,
    \Phalcon\Acl\Role,
    \Phalcon\Acl\Resource,
    \Phalcon\Acl\Adapter\Memory as AclAdapter;

class Security extends Plugin {
    
    public function beforeDispatch(Event $event, Dispatcher $dispatcher) {
        
        $role = (!$this->session->has('auth')) ? 'Guests' : 'Users';
        
        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();
        
        $acl = $this->getAcl();
        
        $allowed = $acl->isAllowed($role, $controller, $action);
        
        if($allowed != Acl::ALLOW) {
            $this->flashSession->error('Please login before trying to access this page.');
            $dispatcher->forward(array(
                'controller' => 'login',
                'action' => 'index'
            ));
            
            return false;
        }
    }
    
    /**
     * defines the Access Control List for this application.
     * the routes are hardcoded in, but these could be loaded from a model
     * so what administrators could just add new entries in the Db table to also
     * add new entries into the ACL.
     * @return \Phalcon\Acl\Adapter\Memory the ACL to use with this application.
     */
    public function getAcl() {
        $acl = new AclAdapter();
        
        //set the default action.
        $acl->setDefaultAction(Acl::DENY);
        
        //register the roles.
        $roles = array(
            'users' => new Role('Users'),
            'guests' => new Role('Guests')
        );
        
        //define roles.
        foreach($roles as $role) {
            $acl->addRole($role);
        }
        
        //define areas.
        $private = array(
            'dashboard' => array('index', 'permissions', 'background')
        );
        
        $public = array(
            'login' => array('index'),
            'logout' => array('index'),
            'register' => array('index'),
            'error' => array('index')
        );
        
        foreach($public as $resource => $actions) {
            $acl->addResource(new Resource($resource), $actions);
        }
        
        foreach ($private as $resource => $actions) {
            $acl->addResource(new Resource($resource), $actions);
        }
        
        foreach($roles as $role) {
            foreach($public as $resource => $actions) {
                $acl->allow($role->getName(), $resource, '*');
            }
        }
        
        foreach($private as $resource => $actions) {
            foreach($actions as $action) {
                $acl->allow('Users', $resource, $action);
            }
        }
        
        //return the acl list.
        return $acl;
    }
    
}

