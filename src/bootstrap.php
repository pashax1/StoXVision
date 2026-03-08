<?php

// Simple autoloader for StoXVision classes
spl_autoload_register(function ($class) {
    // Prefix for the namespace
    $prefix = 'StoXVision\\';
    
    // Base directory for the namespace
    $base_dir = __DIR__ . '/';
    
    // Check if the class uses the prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Get the relative class name
    $relative_class = substr($class, $len);
    
    // Replace the namespace separator with directory separator in the relative class name, 
    // append with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
