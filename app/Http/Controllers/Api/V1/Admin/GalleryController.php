<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\MediaCollections;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Gallery\AddFilesRequest;
use App\Http\Requests\V1\Admin\Gallery\ChangeFilePropertiesRequest;
use App\Http\Requests\V1\Admin\Gallery\MediaFilesRequest;
use App\Http\Resources\V1\MediaResource;
use App\Models\Media;
use App\Repositories\V1\Media\MediaRepository;
use App\Traits\ApiResponseAble;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

/**
 * Class GalleryController
 * @package App\Http\Controllers\Api\V1\Admin
 */
class GalleryController extends Controller {

    use ApiResponseAble;

    /**
     * GalleryController constructor.
     * @param MediaRepository $mediaRepository
     */
    public function __construct(private MediaRepository $mediaRepository) { }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function list(Request $request): AnonymousResourceCollection {
        $media = Media::query()
            ->where('collection_name', '=', MediaCollections::ADMIN_FILES)
            ->orderByDesc('created_at')
            ->paginate($request->query('per_page'));

        return MediaResource::collection($media);
    }

    /**
     * @param Media $media
     * @return MediaResource
     */
    public function show(Media $media): MediaResource {
        $media->load('auditModel.user:id,first_name,last_name,created_at');
        return new MediaResource($media);
    }

    /**
     * @param AddFilesRequest $request
     * @return MediaResource|JsonResponse|AnonymousResourceCollection
     */
    public function store(AddFilesRequest $request): MediaResource|JsonResponse|AnonymousResourceCollection {
        try {
            config(['audit.events' => ['created', 'updated']]);

            $mediaFile = $this->mediaRepository->addFile(
                $request->all(),
                user(),
                MediaCollections::ADMIN_FILES
            );

            $function = is_array($mediaFile) ? 'collection' : 'make';

            return MediaResource::$function($mediaFile);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ChangeFilePropertiesRequest $request
     * @param Media $media
     * @throws \Throwable
     * @return MediaResource|JsonResponse
     */
    public function changeFileProperties(ChangeFilePropertiesRequest $request, Media $media): MediaResource|JsonResponse {
        try {
            if (!$request->hasFile('file')) {
                $media = $this->mediaRepository->updateMedia($request->all(), $media);

                return new MediaResource($media);
            } else {
                \DB::transaction(function () use ($media, $request) {
                    $dataFile = [
                        'custom_details' => array_merge(
                            $media->custom_details,
                            $request->except(['file', '_method']),
                        ),
                        'file' => $request->file('file')
                    ];

                    $currentMediaModel = (new $media->model_type)->find($media->model_id);
                    $this->mediaRepository->addFile($dataFile, $currentMediaModel, $media->collection_name);
                    $media->forceDelete();
                });
            }

            return $this->success('ok', __('messages.SUCCESS_OPERATED'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Media $media
     * @return MediaResource|JsonResponse
     */
    public function destroy(Media $media): MediaResource|JsonResponse {
        try {
            $media->delete();
            return $this->success([], __('messages.ITEM_DELETED'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param MediaFilesRequest $request
     * @throws \Throwable
     * @return MediaResource|JsonResponse
     */
    public function duplicate(MediaFilesRequest $request): MediaResource|JsonResponse {
        try {
            $ids = $request->get('media_ids');

            \DB::transaction(function () use ($ids) {
                $this->mediaRepository->findWhereIn('id', $ids)->map(function ($file) {
                    $this->mediaRepository->duplicateFile($file);
                });
            });

            return $this->success([], __('messages.SUCCESS_OPERATED'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param MediaFilesRequest $request
     * @throws \Throwable
     * @return MediaResource|JsonResponse
     */
    public function deleteMultiple(MediaFilesRequest $request): MediaResource|JsonResponse {
        try {
            $ids = $request->get('media_ids');

            \DB::transaction(function () use ($ids) {
                $this->mediaRepository->findWhereIn('id', $ids)->map(function ($file) {
                    return $file->delete();
                });
            });

            return $this->success([], __('messages.ITEM_DELETED'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }
}
