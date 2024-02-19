<?php

use WHMCS\Database\Capsule;

function ip_logs_config()
{
    return [
        // Display name for your module
        'name' => 'Client IP Logs',
        // Description displayed within the admin interface
        'description' => 'Records client IP address history',
        // Module author name
        'author' => 'Hive Datacenter',
        // Default language
        'language' => 'english',
        // Version number
        'version' => '1.0',
        // Variables
        'fields' => []
    ];
}

function ip_logs_activate()
{
    // Create custom tables and schema
    try {
        // Create database table
        Capsule::schema()
            ->create(
                'mod_ip_logs',
                function ($table) {
                    $table->increments('id');
                    $table->integer('user_id');
                    $table->string('ip', 64);
                    $table->dateTime('login_datetime');
                }
            );
            // Text displayed when activating the module
            return [
                'status' => 'success',
                'description' => 'mod_ip_logs activated.',
            ];
    } catch (\Exception $e) {
        return [
            'status' => "error",
            'description' => 'Unable to create mod_ip_logs: ' . $e->getMessage(),
        ];
    }
}


function ip_logs_deactivate()
{
    // Undo any database and schema modifications
    try {
        Capsule::schema()
            ->dropIfExists('mod_ip_logs');

        return [
            // Text displayed when disabling the module
            'status' => 'success',
            'description' => 'mod_ip_logs has been deactivated',
        ];
    } catch (\Exception $e) {
        return [
            "status" => "error",
            "description" => "Unable to drop mod_ip_logs: {$e->getMessage()}",
        ];
    }
}
