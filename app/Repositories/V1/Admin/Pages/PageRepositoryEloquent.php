<?php

namespace App\Repositories\V1\Admin\Pages;

use App\Http\Requests\V1\Admin\Page\PageRequest;
use App\Models\Page;
use App\Traits\UploadAble;
use Illuminate\Support\Facades\DB;
use App\Repositories\V1\Base\BaseRepository;
use Prettus\Repository\Traits\CacheableRepository;

/**
 * Class PageRepositoryEloquent
 * @package App\Repositories\V1\Admin
 */
class PageRepositoryEloquent extends BaseRepository implements PageRepository {

    use UploadAble, CacheableRepository;

    /**
     * @var int
     */
    protected int $cacheMinutes = 30;


    /**
     * @var string
     */
    public string $collection_name = \App\Enums\MediaCollections::BACKGROUND_COLLECTION;

    /**
     * @var array|string[]
     */
    protected array $cacheOnly = ['findWhere'];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string {
        return \App\Models\Page::class;
    }

    public function boot() {
        config(['audit.events' => ['created', 'updated']]);
    }

    /**
     * @param PageRequest $request
     * @throws \Throwable
     * @return Page
     */
    public function updatePage(PageRequest $request): Page {
        DB::transaction(function () use ($request, &$page) {
            $page = parent::updateOrCreate([
                'id' => $request->get('id'),
                'locale' => $request->get('locale'),
                'type' => $request->get('type'),
                'page_key' => $request->get('page_key'),
            ], $request->only(['content', 'name']));

            $files = $request->get('files', []);

            foreach ($files as $file) $this->setExistsMediaFileToModel($page, $file);
        });

        return $page;
    }
}
