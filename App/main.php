<?php

echo '<PRE>';

use PSharp\Http\Router;

$rout = new Router();

$rout->mapControllers();

$ends = $rout->getEndpoints();

print_r($ends);
