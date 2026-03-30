<?php
namespace App\Controllers;

use PSharp\Http\Route;
use PSharp\Http\Actions\ControllerBase;
use PSharp\Http\Methods\{HttpGet,HttpPost};

#[ApiController]
#[Route('/guestbook')]
class GuestbookController extends ControllerBase
{
    #[HttpGet(name: 'home')]
    public function home()
    {
        return "Hello, world!";
    }

    #[HttpPost('/sign-me','sign')]
    public function sign(string $name, string $message)
    {
        return "You had posted some work here";
    }
}

