<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\RefusalRequest;
use App\Http\Resources\V1\RefusalResource;
use App\Models\Refusal;
use App\Repositories\V1\Admin\RefusalRepository;
use App\Traits\ApiResponseAble;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

/**
 * Class RefusalController
 * @package App\Http\Controllers\Api\V1\Admin\Commercial
 */
class RefusalController extends Controller {

    use ApiResponseAble;

    /**
     * RefusalController constructor.
     * @param RefusalRepository $refusalRepository
     */
    public function __construct(private RefusalRepository $refusalRepository) { }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function list(Request $request): AnonymousResourceCollection {
        return RefusalResource::collection(
            $this->refusalRepository->paginate($request->query('per_page'))
        );
    }

    /**
     * @param RefusalRequest $request
     * @return RefusalResource|JsonResponse
     */
    public function updateOrCreate(RefusalRequest $request): RefusalResource|JsonResponse {
        try {
            return new RefusalResource(
                $this->refusalRepository->updateOrCreateModel($request->all())
            );
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param Refusal $refusal
     * @return JsonResponse
     */
    public function destroy(Refusal $refusal): JsonResponse {
        try {
            $refusal->delete();

            return $this->success([], __('messages.ITEM_DELETED'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }
}
