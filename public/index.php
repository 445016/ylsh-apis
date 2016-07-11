<?php


$dir = dirname(__DIR__);
$appDir = $dir . '/app';

require $appDir . '/library/utilities/debug/PhpError.php';
require $appDir . '/library/interfaces/IRun.php';
require $appDir . '/library/application/Micro.php';

register_shutdown_function(['Utilities\Debug\PhpError','runtimeShutdown']);

$configPath = $appDir . '/config/';
$config = $configPath . 'config.php';
$autoLoad = $configPath . 'autoload.php';
$routes = $configPath . 'routes.php';

use \Models\Api as Api;

try {
    $app = new Application\Micro();

    set_error_handler(['Utilities\Debug\PhpError','errorHandler']);

    $app->setAutoload($autoLoad, $appDir);
    $app->setConfig($config);

    // 通过HTTP的HEADER头获取
    $clientId = $app->request->getHeader('API_ID');
    $time = $app->request->getHeader('API_TIME');
    $hash = $app->request->getHeader('API_HASH');

    $privateKey = Api::findFirst($clientId)->private_key;
    switch ($_SERVER['REQUEST_METHOD']) {

        case 'GET':
            $data = $_GET;
            unset($data['_url']); // clean for hashes comparison
            break;

        case 'POST':
            $data = $_POST;
            break;

        default: 
            parse_str(file_get_contents('php://input'), $data);
            break;
    }
    
    $message = new \Micro\Messages\Auth($clientId, $time, $hash, $data);

    $app->setEvents(new \Events\Api\HmacAuthenticate($message, $privateKey));	

    $app->setRoutes($routes);

    $app->run();

} catch(Exception $e) {
    
    $app->response->setStatusCode(500, "Server Error");
    $app->response->setContent($e->getMessage());
    $app->response->send();
    
}
