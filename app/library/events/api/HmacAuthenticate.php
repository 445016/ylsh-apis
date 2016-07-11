<?php

namespace Events\Api;

use Interfaces\IEvent as IEvent;

class HmacAuthenticate extends \Phalcon\Events\Manager implements IEvent {

    protected $_msg;


    protected $_privateKey;


    protected $_maxRequestDelay = 300;


    public function __construct($message, $privateKey) {
        
            $this->_msg = $message;
            
            $this->_privateKey = $privateKey;

            $this->handleEvent();
    }
    
    public function handleEvent() {

        $this->attach('micro', function ($event, $app) {
            
            if ($event->getType() == 'beforeExecuteRoute') {

                $iRequestTime = $this->_msg->getTime();
                $msgData = is_array($this->_msg->getData()) ? http_build_query($this->_msg->getData(), '', '&') : $this->_msg->getData();
                $data = $iRequestTime . $this->_msg->getId() . $msgData;
                $serverHash = hash_hmac('sha256', $data, $this->_privateKey);
                $clientHash = $this->_msg->getHash();

                $allowed = false;
                if ($clientHash === $serverHash) {

                    if ((time() - $iRequestTime) <= $this->_maxRequestDelay) {

                        $allowed = true; 

                    }

                }

                if (!$allowed) {

                    $method = strtolower($app->router->getMatchedRoute()->getHttpMethods());
                    $unAuthenticated = $app->getUnauthenticated();
                    if (isset($unAuthenticated[$method])) {

                            $unAuthenticated = array_flip($unAuthenticated[$method]);

                            if (isset($unAuthenticated[$app->router->getMatchedRoute()->getPattern()])) {
                                    return true; 
                            }
                    }

                    $app->response->setStatusCode(401, "Unauthorized");
                    $app->response->setContent("Access denied");
                    $app->response->send();

                    return false;

                }
            }
        });
    }
}
