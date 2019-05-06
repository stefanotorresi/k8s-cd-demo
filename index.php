<?php declare(strict_types=1);

namespace Acme;

use Zend\Diactoros\ServerRequestFactory;
use Zend\HttpHandlerRunner\Emitter\SapiStreamEmitter;

require 'vendor/autoload.php';

(static function() {
    $app = ToDoApp::bootstrap();

    $request = ServerRequestFactory::fromGlobals();
    $response = $app->handle($request);

    (new SapiStreamEmitter())->emit($response);
})();


