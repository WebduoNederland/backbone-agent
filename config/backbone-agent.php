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
];
