<?php
namespace App;

echo '<PRE>';

use App\Controllers\GuestbookController;
use PSharp\Http\Router;

$gbcter = new GuestbookController();
$rout = new Router();

$rout->mapController(GuestbookController::class);

$ends = $rout->getEndpoints();

print_r($ends);
