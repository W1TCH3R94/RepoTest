<?php

namespace App\controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;

class Cliente
{
    protected $container;
    public function __construct(ContainerInterface $c)
    {
        $this->container = $c;
    }

    function create(Request $request, Response $response, $args)
    {
        $body = json_decode($request->getBody(), 1);
        $sql = "INSERT INTO cliente (";
        $values = "VALUES (";
        foreach ($body as $key => $value) {
            $sql .= $key . ', ';
            $values .= ":$key, ";
        }
        $values = substr($values, 0, -2) . "); ";

        $sql = substr($sql, 0, -2) . ") " . $values;

        $data = [];
        foreach ($body as $key => $value) {
            $data[$key] = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
        }

        $con = $this->container->get('bd');
        $con->beginTransaction();

        try {
            $query = $con->prepare($sql);
            $query->execute($data);

            $id = $con->lastInsertId();

            $sql = "INSERT INTO usuario (idUsuario, rol, passw) VALUES (:idUsuario, :rol, :passw);";

            $passw = password_hash($data['idCliente'], PASSWORD_BCRYPT, ['cost' => 10]);

            $query = $con->prepare($sql);

            $query->bindValue(":idUsuario", $id, PDO::PARAM_INT);
            $query->bindValue(":rol", 3, PDO::PARAM_INT);
            $query->bindValue(":passw", $passw);

            $query->execute();
            $con->commit();
            $status = 201;
        } catch (\PDOException $e) {
            $status = $e->getCode() == 23000 ? 409 : 500;
            $con->rollback();
        }

        $query = null;
        $con = null;

        return $response->withStatus($status);
    }

    function read(Request $request, Response $response, $args)
    {
        $sql = "SELECT * FROM cliente ";

        if (isset($args['id'])) {
            $sql .= "WHERE id= :id";
        }

        $con = $this->container->get('bd');
        $query = $con->prepare($sql);

        if (isset($args['id'])) {
            $query->execute(['id' => $args['id']]);
        } else {
            $query->execute();
        }

        $res = $query->fetchAll();

        $status = $query->rowCount() > 0 ? 200 : 204;

        $query = null;
        $con = null;

        $response->getBody()->write(json_encode($res));
        return $response
            ->withHeader('Content-type', 'Application/json')
            ->withStatus($status);
    }

    function update(Request $request, Response $response, $args)
    {

        $body = json_decode($request->getBody());

        if (isset($body->id)) {
            unset($body->id);
        }


        $sql = "UPDATE cliente SET ";
        foreach ($body as $key => $value) {
            $sql .= "$key = :$key, ";
        }

        $sql = substr($sql, 0, -2);
        $sql .= " WHERE id = :id;";

        $con = $this->container->get('bd');
        $query = $con->prepare($sql);

        foreach ($body as $key => $value) {
            $query->bindValue(":$key", $value, PDO::PARAM_STR);
        }

        $query->bindValue(':id', $args['id'], PDO::PARAM_INT);

        $query->execute();

        $status = $query->rowCount() > 0 ? 200 : 204;

        $query = null;
        $con = null;

        return $response->withStatus($status);
    }

    function delete(Request $request, Response $response, $args)
    {

        $sql = "DELETE FROM cliente WHERE id = :id";

        $con = $this->container->get('bd');

        $query = $con->prepare($sql);

        $query->bindValue(":id", $args['id'], PDO::PARAM_INT);

        $query->execute();

        $status = $query->rowCount() > 0 ? 200 : 204;

        $query = null;
        $con = null;

        return $response->withStatus($status);
    }

    function filtrar(Request $request, Response $response, $args)
    {
        $datos = $request->getQueryParams();

        $sql = "SELECT * FROM cliente WHERE ";
        foreach ($datos as $key => $value) {
            $sql .= "$key LIKE :$key AND ";
        }

        $sql = rtrim($sql, 'AND ') . ';';

        $con = $this->container->get('bd');
        $query = $con->prepare($sql);

        foreach ($datos as $key => $value) {
            $query->bindValue(":$key", "%$value%", PDO::PARAM_STR);
        }

        $query->execute();

        $res = $query->fetchAll();

        $status = $query->rowCount() > 0 ? 200 : 204;

        $query = null;
        $con = null;

        $response->getBody()->write(json_encode($res));
        return $response
            ->withHeader('Content-type', 'Application/json')
            ->withStatus($status);
    }
}