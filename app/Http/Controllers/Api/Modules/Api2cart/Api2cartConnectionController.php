<?php

namespace App\Http\Controllers\Api\Modules\Api2cart;

use App\Http\Controllers\Controller;
use App\Modules\Api2cart\src\Http\Requests\Api2cartConnectionDestroyRequest;
use App\Modules\Api2cart\src\Http\Requests\Api2cartConnectionIndexRequest;
use App\Modules\Api2cart\src\Http\Requests\Api2cartConnectionStoreRequest;
use App\Modules\Api2cart\src\Jobs\DispatchImportOrdersJobs;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

/**
 * Class Api2cartConnectionController.
 */
class Api2cartConnectionController extends Controller
{
    public function index(Api2cartConnectionIndexRequest $request): AnonymousResourceCollection
    {
        return JsonResource::collection(Api2cartConnection::all());
    }

    public function store(Api2cartConnectionStoreRequest $request): JsonResource
    {
        $config = new Api2cartConnection;
        $config->fill($request->only($config->getFillable()));
        $config->save();

        DispatchImportOrdersJobs::dispatch();

        return new JsonResource($config);
    }

    /**
     * @return Application|ResponseFactory|Response
     *
     * @throws Exception
     */
    public function destroy(Api2cartConnectionDestroyRequest $request, Api2cartConnection $connection): Response
    {
        $connection->delete();

        return response('ok');
    }
}
