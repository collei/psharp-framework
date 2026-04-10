<?php
namespace PSharp\Core\Providers;

use PSharp\Support\Interfaces\Deferrable;

/**
 * Marks a service provider as a deferrable.
 */
interface DeferrableProvider extends ProviderInterface, Deferrable 
{
    //
}