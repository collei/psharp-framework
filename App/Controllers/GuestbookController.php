<?php
namespace App\Controllers;

use PSharp\Http\Route;
use PSharp\Http\Actions\ControllerBase;
use PSharp\Http\Methods\{HttpGet,HttpPost,HttpDelete};

#[ApiController]
#[Route('/guestbook')]
class GuestbookController extends ControllerBase
{
    #[HttpPost]
    public function moderatePage()
    {
        return "Welcome to the moderator panel.";
    }

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

