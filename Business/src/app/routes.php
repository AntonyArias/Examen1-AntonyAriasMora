<?php
namespace App\controllers;

use Slim\Routing\RouteCollectorProxy;

$app->group('/api', function (RouteCollectorProxy $api) {
    $api->group('/inventario_productos', function (RouteCollectorProxy $endpoint) {
        $endpoint->get('[/{id}]', Producto::class . ':read');
        $endpoint->post('', Producto::class . ':create');
        $endpoint->put('/{id}', Producto::class . ':update');
        $endpoint->delete('/{id}', Producto::class . ':delete');
    });
});
