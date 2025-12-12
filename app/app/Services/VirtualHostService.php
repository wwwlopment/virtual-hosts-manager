<?php

namespace App\Services;

use App\Models\VirtualHost;
use App\Services\Nginx\NginxManagerService;
use Illuminate\Support\Facades\File;

class VirtualHostService
{
    private string $confPath;
    private string $hostsRoot;

    public function __construct(
        protected NginxManagerService $nginxManagerService,
    )
    {
        $this->confPath = config('nginx.conf_path', '/etc/nginx/conf.d');
        $this->hostsRoot = config('nginx.hosts_root', '/var/www/virtual');
    }

    public function create(array $data): VirtualHost
    {
        $domain = $data['domain'];
        $port   = $data['port'];
        $root   = "{$this->hostsRoot}/{$domain}";
        $conf   = "{$this->confPath}/{$domain}.conf";

        File::ensureDirectoryExists($this->confPath);
        File::ensureDirectoryExists($root);
        File::put("{$root}/index.html", $this->helloPage($domain));

        File::put($conf, $this->configTemplate($domain, $port, $root));

        $this->nginxManagerService->testConfig();
        $this->nginxManagerService->reload();

        return VirtualHost::create([
            'domain'    => $domain,
            'port'      => $port,
            'root_path' => $root,
            'status'    => 'active',
        ]);
    }

    public function toggle(VirtualHost $host, bool $active): VirtualHost
    {
        $conf = "{$this->confPath}/{$host->domain}.conf";
        $disabledConf = "{$conf}.disabled";

        if ($active && File::exists($disabledConf)) {
            File::move($disabledConf, $conf);
            $host->update(['status' => 'active']);
            
            $this->nginxManagerService->testConfig();
            $this->nginxManagerService->reload();
        }

        if (! $active && File::exists($conf)) {
            File::move($conf, $disabledConf);
            $host->update(['status' => 'inactive']);
            
            $this->nginxManagerService->testConfig();
            $this->nginxManagerService->reload();
        }

        return $host->refresh();
    }

    public function delete(VirtualHost $host): void
    {
        File::delete("{$this->confPath}/{$host->domain}.conf");
        File::delete("{$this->confPath}/{$host->domain}.conf.disabled");
        File::deleteDirectory($host->root_path);

        $this->nginxManagerService->testConfig();
        $this->nginxManagerService->reload();

        $host->delete();
    }

    private function configTemplate(string $domain, int $port, string $root): string
    {
        return <<<NGINX
server {
    listen {$port};
    server_name {$domain};

    root {$root};
    index index.html;

    location / {
        try_files \$uri \$uri/ =404;
    }
}
NGINX;
    }

    private function helloPage(string $domain): string
    {
        return <<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{$domain}</title>
</head>
<body>
    HELLO {$domain}
</body>
</html>
HTML;
    }
}
