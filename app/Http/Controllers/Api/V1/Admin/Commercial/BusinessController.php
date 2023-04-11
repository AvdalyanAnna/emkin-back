<?php

namespace App\Http\Controllers\Api\V1\Admin\Commercial;

use App\Enums\Admin\Commercial\CommercialBusinessTypes;
use App\Enums\Admin\Commercial\CommercialStatuses;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Commercial\BusinessRequest;
use App\Http\Resources\V1\Admin\Commercial\BusinessResource;
use App\Models\CommercialBusiness;
use App\Repositories\V1\Admin\Commercial\BusinessRepository;
use App\Traits\ApiResponseAble;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

/**
 * Class BusinessController
 * @package App\Http\Controllers\Api\V1\Admin\Commercial
 */
class BusinessController extends Controller {

    use ApiResponseAble;

    /**
     * AuthController constructor.
     * @param BusinessRepository $businessRepository
     */
    public function __construct(private BusinessRepository $businessRepository) { }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function list(Request $request): AnonymousResourceCollection {
        $vertical = CommercialBusinessTypes::VERTICAL;
        $active = CommercialStatuses::ACTIVE;
        $horizontal = CommercialBusinessTypes::HORIZONTAL;
        $draft = CommercialStatuses::DRAFT;

        $query = $this->businessRepository;

        if ($request->query('randomResult')) $query = $query->inRandomOrder();

        $collection = $query->paginate($request->query('per_page'));

        $resource = BusinessResource::collection($collection);

        if (!$request->query('randomResult')) {
            $resource = $resource->additional([
                'counts' => \DB::table('commercial_businesses as cb')
                    ->when($client = $request->query('client', false), function ($q) use ($client) {
                        $q->where('client_id', '=', $client);
                    })
                    ->selectRaw("count(CASE WHEN cb.type = $vertical AND cb.status = $active THEN 1 END) as vertical_count")
                    ->selectRaw("count(CASE WHEN cb.type = $horizontal AND cb.status = $active THEN 1 END) as horizontal_count")
                    ->selectRaw("count(CASE WHEN cb.status = $draft THEN 1 END) as draft_count")
                    ->first()
            ]);
        }

        return $resource;
    }

    /**
     * @param BusinessRequest $request
     * @return BusinessResource|JsonResponse
     */
    public function updateOrCreate(BusinessRequest $request): BusinessResource|JsonResponse {
        try {
            $business = $this->businessRepository->updateOrCreateModel($request->all());

            return new BusinessResource($business->load(['banner_images']));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param CommercialBusiness $commercialBusiness
     * @return JsonResponse
     */
    public function changeToDraft(CommercialBusiness $commercialBusiness): JsonResponse {
        try {
            $commercialBusiness->update(['status' => CommercialStatuses::DRAFT]);

            return $this->success([], __('messages.ITEM_DELETED'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param CommercialBusiness $commercialBusiness
     * @return JsonResponse|BusinessResource
     */
    public function details(CommercialBusiness $commercialBusiness): JsonResponse|BusinessResource {
        return BusinessResource::make($commercialBusiness->load(['banner_images', 'client.avatar']));
    }

    /**
     * @param CommercialBusiness $commercialBusiness
     * @return JsonResponse
     */
    public function destroy(CommercialBusiness $commercialBusiness): JsonResponse {
        try {
            $commercialBusiness->delete();

            return $this->success([], __('messages.ITEM_DELETED'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }
}
