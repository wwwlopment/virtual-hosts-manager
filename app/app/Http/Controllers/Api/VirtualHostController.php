<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVirtualHostRequest;
use App\Http\Requests\UpdateVirtualHostStatusRequest;
use App\Http\Resources\VirtualHostResource;
use App\Models\VirtualHost;
use App\Services\VirtualHostService;

class VirtualHostController extends Controller
{
    public function __construct(
        private VirtualHostService $service
    ) {}

    public function index()
    {
        $hosts = VirtualHost::latest()->get();
        $usedPorts = $hosts->pluck('port')->toArray();

        $allPorts = range(8081, 8100);
        $availablePorts = array_values(array_diff($allPorts, $usedPorts));

        return response()->json([
            'data' => VirtualHostResource::collection($hosts),
            'available_ports' => $availablePorts,
        ]);
    }

    public function store(StoreVirtualHostRequest $request)
    {
        $host = $this->service->create($request->validated());

        return new VirtualHostResource($host);
    }

    public function updateStatus(UpdateVirtualHostStatusRequest $request, VirtualHost $host)
    {
        $host = $this->service->toggle($host, $request->boolean('active'));

        return new VirtualHostResource($host);
    }

    public function destroy(VirtualHost $host)
    {
        $this->service->delete($host);

        return response()->noContent();
    }
}
