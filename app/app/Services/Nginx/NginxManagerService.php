<?php

namespace App\Services\Nginx;

use App\Services\Docker\DockerEngineClient;

class NginxManagerService
{
    public function __construct(
        protected DockerEngineClient $docker,
    ) {}

    protected function container(): string
    {
        return config('nginx.container');
    }

    protected function confPath(): string
    {
        return rtrim(config('nginx.conf_path'), '/');
    }

    public function start(): void
    {
        $this->docker->post("/containers/{$this->container()}/start");
    }

    public function stop(): void
    {
        $this->docker->post("/containers/{$this->container()}/stop");
    }

    public function restart(): void
    {
        $this->docker->post("/containers/{$this->container()}/restart");
    }

    public function reload(): void
    {
        $this->exec(['nginx', '-s', 'reload']);
    }

    public function testConfig(): void
    {
        $this->exec(['nginx', '-t']);
    }

    protected function exec(array $cmd): void
    {
        $response = $this->docker->post(
            "/containers/{$this->container()}/exec",
            [
                'AttachStdout' => true,
                'AttachStderr' => true,
                'Cmd' => $cmd,
            ]
        );

        $execId = $response->json('Id');

        $start = $this->docker->post("/exec/{$execId}/start", [
            'Detach' => false,
            'Tty' => false,
        ]);

        if (!$start->successful()) {
            throw new \RuntimeException('Nginx command failed');
        }
    }
}
