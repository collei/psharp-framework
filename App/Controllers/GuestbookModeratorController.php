<?php
namespace App\Controllers;

use PSharp\Http\Route;
use PSharp\Http\Actions\ControllerBase;
use PSharp\Http\Methods\{HttpGet,HttpPost,HttpDelete};

#[ApiController]
#[Route('/guestbook/mod-panel','guestbook-mod')]
class GuestbookModeratorController extends ControllerBase
{
    #[HttpGet()]
    public function index()
    {
        return "Index of moderator panel.";
    }

    #[HttpGet('messages')]
    public function messages()
    {
        return "Message listing.";
    }

    #[HttpGet('messages/{id}')]
    public function messageDetails(int $id)
    {
        return "Message $id in detail.";
    }

    #[HttpPost('messages/{id}/edit')]
    public function editMessage(int $id, string $newMessage)
    {
        return "Message $id edited to $mewMessage.";
    }
}

