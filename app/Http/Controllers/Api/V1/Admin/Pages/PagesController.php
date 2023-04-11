<?php

namespace App\Http\Controllers\Api\V1\Admin\Pages;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Page\PageRequest;
use App\Http\Resources\V1\Admin\Page\PageResource;
use App\Models\Media;
use App\Repositories\V1\Admin\Pages\PageRepository;
use App\Traits\ApiResponseAble;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ItemNotFoundException;

/**
 * Class PagesController
 * @package App\Http\Controllers\Api\V1
 */
class PagesController extends Controller {

    use ApiResponseAble;

    /**
     * PagesController constructor.
     * @param PageRepository $pageRepository
     */
    public function __construct(private PageRepository $pageRepository) { }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return PageResource
     */
    public function getPages(Request $request): PageResource {

        $conditions = [];

        if ($request->query('locale')) {
            $conditions['locale'] = $request->query('locale');
        }

        if ($request->query('type')) {
            $conditions['type'] = $request->query('type');
        }

        if ($request->query('page_key')) {
            $conditions['page_key'] = $request->query('page_key');
        }

        $page = $this->pageRepository->findWhere($conditions)->firstOrFail();

        return new PageResource($page->load($page->getRelations()));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param PageRequest $request
     * @throws \Throwable
     * @return PageResource|JsonResponse
     */
    public function update(PageRequest $request): PageResource|JsonResponse {
        try {
            $page = $this->pageRepository->updatePage($request);

            return new PageResource($page->load('backgrounds'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param Media $media
     * @return JsonResponse
     */
    public function deletePageMedia(Media $media): JsonResponse {
        $media->delete();
        return $this->success('', __('messages.ITEM_DELETED'));
    }
}
