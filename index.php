<?php declare(strict_types = 1);

require_once __DIR__.'/app/classes/core/App.php';

try {
    App::getInstance()->run();
}
catch (Exception $e) {
    echo '<h1 class="my-5 text-center text-danger">'.$e->getMessage().'</h1>';
    exit(1);
}
