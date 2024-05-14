<?php
//Archivo de Endpoints o Rutas

namespace App\controllers;
use Slim\Routing\RouteCollectorProxy;

// Recurso Cliente
$app->group('/cliente',function(RouteCollectorProxy $cliente){
    $cliente->post('', Cliente::class . ':create');
    $cliente->get('/read[/{id}]', Cliente::class . ':read');
});