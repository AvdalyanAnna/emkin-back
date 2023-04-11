<?php

namespace App\Http\Controllers\Api\V1\Admin\Commercial;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Commercial\ClientRequest;
use App\Http\Resources\V1\Admin\Commercial\ClientResource;
use App\Models\Client;
use App\Repositories\V1\Admin\Commercial\ClientRepository;
use App\Traits\ApiResponseAble;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

/**
 * Class ClientController
 * @package App\Http\Controllers\Api\V1\Admin\Commercial
 */
class ClientController extends Controller {

    use ApiResponseAble;

    /**
     * AuthController constructor.
     * @param ClientRepository $clientRepository
     */
    public function __construct(private ClientRepository $clientRepository) { }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function list(Request $request): AnonymousResourceCollection {
        return ClientResource::collection(
            $this->clientRepository
                ->withCount('businesses')
                ->paginate($request->query('per_page'))
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @param Client $client
     * @return ClientResource
     */
    public function client(Client $client): ClientResource {
        $client->load('avatar');
        $client->loadCount(['canceled_publications', 'publications']);
        return new ClientResource($client);
    }

    /**
     * @param ClientRequest $request
     * @return ClientResource|JsonResponse
     */
    public function updateOrCreate(ClientRequest $request): ClientResource|JsonResponse {
        try {
            $client = $this->clientRepository->updateOrCreateModel($request->all());

            return new ClientResource($client->load(['avatar']));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param Client $client
     * @return JsonResponse
     */
    public function destroy(Client $client): JsonResponse {
        try {
            $client->delete();

            return $this->success([], __('messages.ITEM_DELETED'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }
}
