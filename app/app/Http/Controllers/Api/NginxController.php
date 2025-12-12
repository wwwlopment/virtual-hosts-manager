<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Docker\DockerEngineClient;
use App\Services\Nginx\NginxManagerService;
use Illuminate\Http\JsonResponse;

class NginxController extends Controller
{
    public function __construct(
        protected NginxManagerService $nginxManager,
        protected DockerEngineClient $docker
    ) {}

    public function start(): JsonResponse
    {
        try {
            $this->nginxManager->start();
            
            return response()->json([
                'message' => 'Nginx успішно запущено',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Помилка запуску Nginx',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function stop(): JsonResponse
    {
        try {
            $this->nginxManager->stop();
            
            return response()->json([
                'message' => 'Nginx успішно зупинено',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Помилка зупинки Nginx',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function restart(): JsonResponse
    {
        try {
            $this->nginxManager->restart();
            
            return response()->json([
                'message' => 'Nginx успішно перезапущено',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Помилка перезапуску Nginx',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function reload(): JsonResponse
    {
        try {
            $this->nginxManager->testConfig();
            $this->nginxManager->reload();
            
            return response()->json([
                'message' => 'Конфігурація Nginx успішно перезавантажена',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Помилка перезавантаження конфігурації',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function status(): JsonResponse
    {
        try {
            $container = config('nginx.container');
            $containerStatus = $this->docker->getContainerStatus($container);
            
            return response()->json([
                'status' => $containerStatus['running'] ? 'running' : $containerStatus['status'],
                'container' => $container,
                'details' => $containerStatus,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
