<?php

namespace Application;

use Interfaces\IRun as IRun;

class Micro extends \Phalcon\Mvc\Micro implements IRun {

    
    protected $_noAuthPages;

    public function __construct() {
        $this->_noAuthPages = array();
    }

    public function setConfig($file) {
        if (!file_exists($file)) {
                throw new \Exception('Unable to load configuration file');
        }

        $di = new \Phalcon\DI\FactoryDefault();

        $di->set('config', new \Phalcon\Config(require $file));

        $di->set('db', function() use ($di) {

            $type = strtolower($di->get('config')->database->adapter);

            $creds = array(
                    'host' => $di->get('config')->database->host,
                    'username' => $di->get('config')->database->username,
                    'password' => $di->get('config')->database->password,
                    'dbname' => $di->get('config')->database->name
            );

            if ($type == 'mysql') {

                    $connection =  new \Phalcon\Db\Adapter\Pdo\Mysql($creds);

            } else if ($type == 'postgres') {

                    $connection =  new \Phalcon\Db\Adapter\Pdo\Postgresql($creds);

            } else if ($type == 'sqlite') {

                    $connection =  new \Phalcon\Db\Adapter\Pdo\Sqlite($creds);

            } else {

                    throw new Exception('Bad Database Adapter');
            }

            return $connection;
        });

        $this->setDI($di);
    }

    
    public function setAutoload($file, $dir) {
        
        if (!file_exists($file)) {

                throw new \Exception('Unable to load autoloader file');
        }

        $namespaces = include $file;

        $loader = new \Phalcon\Loader();

        $loader->registerNamespaces($namespaces)->register();
    }

    
    public function setRoutes($file) {

        if (!file_exists($file)) {
            
            throw new \Exception('Unable to load routes file');
            
        }

        $routes = include($file);

        if (!empty($routes)) {
            
            foreach($routes as $obj) {
               
                if (isset($obj['authentication']) && $obj['authentication'] === false) {

                    $method = strtolower($obj['method']);
                   
                    if (! isset($this->_noAuthPages[$method])) {
                        
                        $this->_noAuthPages[$method] = array();
                        
                    }

                    $this->_noAuthPages[$method][] = $obj['route'];
                }
                
                $controllerName = class_exists($obj['handler'][0]) ? $obj['handler'][0] : false;
                
                if (!$controllerName) {
                    
                    throw new \Exception("Wrong controller name in routes ({$obj['handler'][0]})");
                    
                }

                $controller = new $controllerName;
                
                $controllerAction = $obj['handler'][1];

                switch($obj['method']) {
                    case 'get':
                            $this->get($obj['route'], array($controller, $controllerAction));
                            break;
                    case 'post':
                            $this->post($obj['route'], array($controller, $controllerAction));
                            break;
                    case 'delete':
                            $this->delete($obj['route'], array($controller, $controllerAction));
                            break;
                    case 'put':
                            $this->put($obj['route'], array($controller, $controllerAction));
                            break;
                    case 'head':
                            $this->head($obj['route'], array($controller, $controllerAction));
                            break;
                    case 'options':
                            $this->options($obj['route'], array($controller, $controllerAction));
                            break;
                    case 'patch':
                            $this->patch($obj['route'], array($controller, $controllerAction));
                            break;
                    default:
                            break;
                }

            }
        }
    }

    public function setEvents(\Phalcon\Events\Manager $events) {
        
        $this->setEventsManager($events);
        
    }

    public function getUnauthenticated() {

        return $this->_noAuthPages;

    }
   
    public function run() {

        $this->notFound(function () {
            
                $response = new \Phalcon\Http\Response();
                
                $response->setStatusCode(404, 'Not Found')->sendHeaders();
                
                $response->setContent('Page doesn\'t exist.');
                
                $response->send();
                
        });

        $this->handle();

    }

}
