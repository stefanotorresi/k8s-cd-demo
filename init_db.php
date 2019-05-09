#!/usr/bin/env php
<?php declare(strict_types=1);

namespace Acme;

require 'vendor/autoload.php';

(static function() {
    $app = ToDoApp::bootstrap();

    $app->toDoRepo->initSchema();
})();


