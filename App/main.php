<?php
namespace App;

echo '<PRE>';

use App\Controllers\GuestbookController;
use App\Controllers\GuestbookModeratorController;
use PSharp\Http\Router;

$rout = new Router();

$rout->mapController(GuestbookController::class);
$rout->mapController(GuestbookModeratorController::class);

$ends = $rout->getEndpoints();

print_r($ends);
