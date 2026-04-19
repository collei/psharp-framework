<?php

namespace {
    /**
     * Loads all facades declared in the src/facades/ path.
     * 
     * Just accepts .php files - no other file will be loaded.
     */
    $facadesPath = __DIR__ . DIRECTORY_SEPARATOR . 'facades' . DIRECTORY_SEPARATOR . '*.php';

    if ($files = glob($facadesPath)) foreach ($files as $file) {
        require $file;
    }
}