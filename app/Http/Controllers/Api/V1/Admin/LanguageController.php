<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\LanguageRequest;
use App\Http\Resources\V1\LanguageResource;
use App\Models\Language;
use App\Repositories\V1\LanguageRepositoryEloquent;
use App\Traits\ApiResponseAble;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class LanguageController
 * @package App\Http\Controllers\Api\V1\Admin
 */
class LanguageController extends Controller {

    use ApiResponseAble;

    /**
     * LanguageController constructor.
     * @param LanguageRepositoryEloquent $languageRepository
     */
    public function __construct(private LanguageRepositoryEloquent $languageRepository) { }

    /**
     * @return AnonymousResourceCollection
     */
    public function getLocales(): AnonymousResourceCollection {
        $languages = $this->languageRepository->all();
        return LanguageResource::collection($languages);
    }

    /**
     * @param LanguageRequest $request
     * @throws ValidatorException
     * @return LanguageResource
     */
    public function addLocale(LanguageRequest $request): LanguageResource {
        $language = $this->languageRepository->create($request->all());
        return new LanguageResource($language);
    }

    /**
     * @param LanguageRequest $request
     * @throws ValidatorException
     * @return LanguageResource
     */
    public function updateLocale(LanguageRequest $request): LanguageResource {
        $language = $this->languageRepository->update($request->all(), $request->get('id'));
        return new LanguageResource($language);
    }

    /**
     * @param Language $language
     * @return JsonResponse
     */
    public function destroy(Language $language): JsonResponse
    {
        $language->delete();
        return $this->success('ok'. __('messages.ITEM_DELETED'));
    }
}
