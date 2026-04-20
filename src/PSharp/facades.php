<?php

namespace {
    /**
     * Loads all facades declared in the src/facades/ path.
     * 
     * Just accepts .php files - no other file will be loaded.
     */
    $facadesPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '*.php';

    if ($files = glob($facadesPath)) foreach ($files as $file) {
        require $file;
    }
}