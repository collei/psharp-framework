<?php
namespace PSharp\Core\DI;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Exception thrown when class not found.
 */
class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{
    //
}