<?php
namespace App\Controllers;

use Psharp\Http\Route;
use Psharp\Http\Actions\ControllerBase;
use Psharp\Http\Methods\{HttpGet,HttpPost,HttpDelete};

#[ApiController]
#[Route('/guestbook','guests')]
class GuestbookController extends ControllerBase
{
    #[HttpPost]
    public function moderate()
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

