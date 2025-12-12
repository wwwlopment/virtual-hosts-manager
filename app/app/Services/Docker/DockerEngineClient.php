<?php

namespace App\Services\Docker;

use GuzzleHttp\Client;
use Illuminate\Http\Client\Response;

class DockerEngineClient
{
    protected string $socket = '/var/run/docker.sock';
    protected Client $guzzle;

    public function __construct()
    {
        $this->guzzle = new Client([
            'base_uri' => 'http://localhost',
            'curl' => [
                CURLOPT_UNIX_SOCKET_PATH => $this->socket,
            ],
        ]);
    }

    public function post(string $uri, array $data = [])
    {
        $response = $this->guzzle->post($uri, [
            'json' => $data,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        return new Response($response);
    }

    public function get(string $uri)
    {
        $response = $this->guzzle->get($uri);

        return new Response($response);
    }

    public function inspectContainer(string $container): array
    {
        $response = $this->get("/containers/{$container}/json");

        return $response->json();
    }

    public function getContainerStatus(string $container): array
    {
        try {
            $info = $this->inspectContainer($container);

            return [
                'exists' => true,
                'running' => $info['State']['Running'] ?? false,
                'status' => $info['State']['Status'] ?? 'unknown',
                'started_at' => $info['State']['StartedAt'] ?? null,
                'name' => $info['Name'] ?? $container,
                'id' => $info['Id'] ?? null,
            ];
        } catch (\Exception $e) {
            return [
                'exists' => false,
                'running' => false,
                'status' => 'not_found',
                'error' => $e->getMessage(),
            ];
        }
    }
}
