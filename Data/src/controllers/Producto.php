<?php
namespace App\controllers;

use App\Models\Producto as ProductoModel;
use Illuminate\Database\QueryException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Producto
{
    public function __construct(private ContainerInterface $container) {}

    public function read(Request $request, Response $response, array $args): Response
    {
        $this->container->get('eloquent');

        if (isset($args['id'])) {
            $id = filter_var($args['id'], FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1, 'max_range' => 2147483647],
            ]);
            if ($id === false) {
                return $this->json($response, ['error' => 'El id debe ser un entero positivo válido.'], 422);
            }

            $producto = ProductoModel::find($id);
            if ($producto === null) {
                return $this->json($response, ['error' => 'Producto no encontrado.'], 404);
            }

            return $this->json($response, ['data' => $producto->toArray()], 200);
        }

        $productos = ProductoModel::query()->get()->toArray();
        return $this->json($response, ['data' => $productos], 200);
    }

    public function create(Request $request, Response $response, array $args): Response
    {
        $this->container->get('eloquent');
        $data = $request->getParsedBody();
        $data = is_array($data) ? $data : [];

        try {
            $producto = ProductoModel::create([
                'sku' => trim($data['sku']),
                'nombre_producto' => trim($data['nombre_producto']),
                'descripcion' => $data['descripcion'] ?? null,
                'precio_unitario' => $data['precio_unitario'],
                'stock_actual' => $data['stock_actual'],
                'categoria' => trim($data['categoria']),
            ]);
        } catch (QueryException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
                return $this->json($response, ['error' => 'El SKU ya está registrado.'], 409);
            }

            throw $exception;
        }

        return $this->json($response, ['data' => $producto->refresh()->toArray()], 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $this->container->get('eloquent');
        $id = filter_var($args['id'], FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 2147483647],
        ]);
        if ($id === false) {
            return $this->json($response, ['error' => 'El id debe ser un entero positivo válido.'], 422);
        }

        $producto = ProductoModel::find($id);
        if ($producto === null) {
            return $this->json($response, ['error' => 'Producto no encontrado.'], 404);
        }

        $data = $request->getParsedBody();
        $data = is_array($data) ? $data : [];
        foreach (['sku', 'nombre_producto', 'descripcion', 'precio_unitario', 'stock_actual', 'categoria'] as $field) {
            if (array_key_exists($field, $data)) {
                $producto->$field = in_array($field, ['sku', 'nombre_producto', 'categoria'], true)
                    ? trim($data[$field])
                    : $data[$field];
            }
        }

        try {
            $producto->save();
        } catch (QueryException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
                return $this->json($response, ['error' => 'El SKU ya está registrado.'], 409);
            }

            throw $exception;
        }

        return $this->json($response, ['data' => $producto->refresh()->toArray()], 200);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->container->get('eloquent');
        $id = filter_var($args['id'], FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 2147483647],
        ]);
        if ($id === false) {
            return $this->json($response, ['error' => 'El id debe ser un entero positivo válido.'], 422);
        }

        $producto = ProductoModel::find($id);
        if ($producto === null) {
            return $this->json($response, ['error' => 'Producto no encontrado.'], 404);
        }

        $producto->delete();
        return $response->withStatus(204);
    }

    private function json(Response $response, array $payload, int $status): Response
    {
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($status);
    }
}
