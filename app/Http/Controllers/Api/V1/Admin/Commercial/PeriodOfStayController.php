<?php

namespace App\Http\Controllers\Api\V1\Admin\Commercial;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Commercial\PeriodOfStayRequest;
use App\Http\Resources\V1\Admin\Commercial\PeriodOfStayResource;
use App\Models\PeriodOfStay;
use App\Repositories\V1\Admin\Commercial\PeriodOfStayRepository;
use App\Traits\ApiResponseAble;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

/**
 * Class PeriodOfStayController
 * @package App\Http\Controllers\Api\V1\Admin\Commercial
 */
class PeriodOfStayController extends Controller {

    use ApiResponseAble;

    /**
     * PeriodOfStayController constructor.
     * @param PeriodOfStayRepository $periodOfStayRepository
     */
    public function __construct(private PeriodOfStayRepository $periodOfStayRepository) { }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function list(Request $request): AnonymousResourceCollection {
        return PeriodOfStayResource::collection(
            $this->periodOfStayRepository->paginate($request->query('per_page'))
        );
    }

    /**
     * @param PeriodOfStayRequest $request
     * @return PeriodOfStayResource|JsonResponse
     */
    public function updateOrCreate(PeriodOfStayRequest $request): PeriodOfStayResource|JsonResponse {
        try {
            return new PeriodOfStayResource(
                $this->periodOfStayRepository->updateOrCreateModel($request->all())
            );
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @param PeriodOfStay $periodOfStay
       * @return JsonResponse|PeriodOfStayResource
     */
    public function details(PeriodOfStay $periodOfStay) : JsonResponse|PeriodOfStayResource{
        return new PeriodOfStayResource($periodOfStay);
    }
    /**
     * @param PeriodOfStay $periodOfStay
     * @return JsonResponse
     */
    public function destroy(PeriodOfStay $periodOfStay): JsonResponse {
        try {
            $periodOfStay->delete();

            return $this->success([], __('messages.ITEM_DELETED'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }
}
