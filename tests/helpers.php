<?php

namespace Tests;

/**
 * Test helper functions
 */

if (!function_exists('config')) {
    /**
     * Mock config helper for testing
     */
    function config($key = null, $default = null)
    {
        // Return null by default to trigger the exception in FirebaseNotification
        return $default;
    }
}

namespace Cofa\NotificationViaFirebaseAndDatabase\Contracts;

if (!function_exists('Cofa\NotificationViaFirebaseAndDatabase\Contracts\config')) {
    /**
     * Mock config helper for FirebaseNotification testing
     */
    function config($key = null, $default = null)
    {
        // Return null by default to trigger the exception in FirebaseNotification
        return $default;
    }
}

