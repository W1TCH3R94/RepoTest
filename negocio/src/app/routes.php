<?php
namespace App\controllers;
use Slim\Routing\RouteCollectorProxy;

// Recurso Cliente
$app->group('/cliente',function(RouteCollectorProxy $cliente){
    $cliente->post('', Cliente::class . ':create');
    $cliente->get('/read[/{id}]', Cliente::class . ':read');
    // $cliente->get('/filtro', Cliente::class . ':filtrar');
    $cliente->put('/{id}', Cliente::class . ':update');
    $cliente->delete('/{id}', Cliente::class . ':delete');
    // $cliente->get('/{id}', Cliente::class . ':buscar');
});