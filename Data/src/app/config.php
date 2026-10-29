<?php
$container->set('config_bd', function() {
    return (object) [        
            'host' => $_ENV["DB_HOST"],
            'username' => $_ENV["DB_USERNAME"],
            'password' => $_ENV["DB_PASSWORD"],
            'database' => $_ENV["DB_NAME"],
            'charset' => 'utf8mb4'
        ];
    
});