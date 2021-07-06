<?php

use app\brokerage\action\ExtractAction;
use miuxa\App;

return function (App $app) {
    $route = $app->getRouter();

    $route->get('extract', ExtractAction::class);
};
