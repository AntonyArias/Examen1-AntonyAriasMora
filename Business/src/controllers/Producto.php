<?php
namespace App\controllers;

use App\Services\DataService;
use App\Validators\ProductoValidator;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Producto
{
    public function __construct(private ContainerInterface $container) {}

    public function read(Request $request, Response $response, array $args): Response
    {
        try {
            $dataService = $this->container->get(DataService::class);
            $path = isset($args['id']) ? 'inventario_productos/' . rawurlencode($args['id']) : 'inventario_productos';
            return $this->forward($response, $dataService->get($path));
        } catch (ConnectException) {
            return $this->json($response, ['error' => 'El servicio no está disponible'], 502);
        } catch (RequestException) {
            return $this->json($response, ['error' => 'No se pudo consultar el servicio de datos'], 502);
        }
    }

    public function create(Request $request, Response $response, array $args): Response
    {
        try {
            $request->getBody()->rewind();
            $data = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($data) || array_is_list($data)) {
                return $this->json($response, ['body' => 'El cuerpo de la solicitud debe ser un objeto JSON válido'], 400);
            }

            $errors = (new ProductoValidator())->validate($data);
            if ($errors !== []) {
                return $this->json($response, ['errors' => $errors], 422);
            }

            $dataService = $this->container->get(DataService::class);
            return $this->forward(
                $response,
                $dataService->post('inventario_productos', json_encode($data, JSON_THROW_ON_ERROR))
            );
        } catch (\JsonException) {
            return $this->json($response, ['body' => 'El cuerpo de la solicitud debe ser un objeto JSON válido'], 400);
        } catch (ConnectException) {
            return $this->json($response, ['error' => 'El servicio no está disponible'], 502);
        } catch (RequestException) {
            return $this->json($response, ['error' => 'No se pudo consultar el servicio de datos'], 502);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $request->getBody()->rewind();
            $data = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($data) || array_is_list($data)) {
                return $this->json($response, ['body' => 'El cuerpo de la solicitud debe ser un objeto JSON válido'], 400);
            }

            $errors = (new ProductoValidator())->validate($data, true);
            if ($errors !== []) {
                return $this->json($response, ['errors' => $errors], 422);
            }

            $dataService = $this->container->get(DataService::class);
            return $this->forward(
                $response,
                $dataService->put('inventario_productos/' . rawurlencode($args['id']), json_encode($data, JSON_THROW_ON_ERROR))
            );
        } catch (\JsonException) {
            return $this->json($response, ['body' => 'El cuerpo de la solicitud debe ser un objeto JSON válido'], 400);
        } catch (ConnectException) {
            return $this->json($response, ['error' => 'El servicio no está disponible'], 502);
        } catch (RequestException) {
            return $this->json($response, ['error' => 'No se pudo consultar el servicio de datos'], 502);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $dataService = $this->container->get(DataService::class);
            return $this->forward(
                $response,
                $dataService->delete('inventario_productos/' . rawurlencode($args['id']))
            );
        } catch (ConnectException) {
            return $this->json($response, ['error' => 'El servicio no está disponible'], 502);
        } catch (RequestException) {
            return $this->json($response, ['error' => 'No se pudo consultar el servicio de datos'], 502);
        }
    }

    private function forward(Response $response, \Psr\Http\Message\ResponseInterface $upstream): Response
    {
        $response->getBody()->write((string) $upstream->getBody());
        return $response
            ->withHeader('Content-Type', $upstream->getHeaderLine('Content-Type') ?: 'application/json; charset=utf-8')
            ->withStatus($upstream->getStatusCode());
    }

    private function json(Response $response, array $payload, int $status): Response
    {
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($status);
    }
}
