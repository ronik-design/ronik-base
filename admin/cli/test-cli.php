<?php
/**
 * Test script for Ronik Base CLI functionality
 * 
 * This file is for testing purposes only and should not be included in production.
 * 
 * @package    Ronik_Base
 * @subpackage Ronik_Base/admin/cli
 */

// Prevent direct access
if (!defined('WPINC')) {
    die;
}

// Test CLI class loading
if (class_exists('Ronik_Base_CLI')) {
    echo "✓ CLI class loaded successfully\n";
    
    // Test CLI class instantiation
    try {
        $cli = new Ronik_Base_CLI();
        echo "✓ CLI class instantiated successfully\n";
    } catch (Exception $e) {
        echo "✗ Error instantiating CLI class: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ CLI class not found\n";
}

// Test WP_CLI availability
if (defined('WP_CLI') && WP_CLI) {
    echo "✓ WP_CLI is available\n";
} else {
    echo "ℹ WP_CLI is not available (this is normal in web context)\n";
}

// Test required dependencies
if (function_exists('is_plugin_active')) {
    echo "✓ is_plugin_active function available\n";
} else {
    echo "ℹ is_plugin_active function not available (may need to include plugin.php)\n";
}

// Test database connection
global $wpdb;
if ($wpdb && $wpdb->db_connect()) {
    echo "✓ Database connection available\n";
} else {
    echo "✗ Database connection not available\n";
}

echo "\nCLI test completed.\n";
