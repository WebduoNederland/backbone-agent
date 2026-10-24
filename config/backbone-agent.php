<?php

return [
    /**
     * The full URL of your Backbone instance.
     *
     * Example: https://backbone.acme.com
     */
    'base_url' => env('BACKBONE_AGENT_BASE_URL', ''),

    /**
     * The API key from the created project in Backbone.
     */
    'api_key' => env('BACKBONE_AGENT_API_KEY', ''),

    /**
     * Database dump configuration
     */
    'database_dumps' => [
        /**
         * Queue
         */
        'queue' => 'default',

        /**
         * MySQL dump binary path
         */
        'mysql_dump_binary_path' => env('BACKBONE_AGENT_MYSQL_DUMP_BINARY_PATH', null),

        /**
         * MySQL socket path
         */
        'mysql_socket_path' => env('BACKBONE_AGENT_MYSQL_SOCKET_PATH', null),
    ],

    /**
     * PHP file scanner configuration
     *
     * Scans the public folder (including sub folders) every 15 seconds for PHP files.
     */
    'php_file_scanner' => [
        /**
         * Queue
         */
        'queue' => 'default',

        /**
         * Files (relative to the public folder) which are allowed to exist
         */
        'allowed_files' => [
            'index.php',
        ],

        /**
         * File extensions which are executable as PHP
         */
        'extensions' => [
            'php',
            'phtml',
            'phar',
            'phps',
        ],

        /**
         * Panic mode: shut down the server as soon as a PHP file has been found
         */
        'panic_mode' => env('BACKBONE_AGENT_PANIC_MODE', false),

        /**
         * Command used to shut down the server in panic mode
         */
        'shutdown_command' => env('BACKBONE_AGENT_SHUTDOWN_COMMAND', 'sudo -n shutdown -h now'),
    ],
];
