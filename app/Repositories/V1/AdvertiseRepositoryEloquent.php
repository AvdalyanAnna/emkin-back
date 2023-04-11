<?php

namespace App\Repositories\V1;

use App\Criteria\V1\SearchCriteria;
use App\Criteria\V1\Users\BannedUserCriteria;
use App\Enums\Admin\Moderator\ModeratorStatisticsEnum;
use App\Enums\Advertise\AdvertiseStatus;
use App\Http\Requests\V1\AdvertisesRequest;
use App\Models\Advertise;
use App\Models\ModeratorStatistic;
use App\Repositories\V1\Base\BaseRepository;
use App\Traits\UploadAble;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Repository\Traits\CacheableRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class AdvertiseRepositoryEloquent
 * @package App\Repositories\V1\Admin
 * @method  inRandomOrder()
 */
class AdvertiseRepositoryEloquent extends BaseRepository implements AdvertiseRepository {

    use UploadAble, CacheableRepository;

    /**
     * @var int
     */
    protected int $cacheMinutes = 30;

    /**
     * @var array|string[]
     */
    protected array $cacheOnly = ['paginate'];

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'id' => 'not in',
        'price' => 'between',
        'created_at' => 'between',
        'contact_phone' => 'LIKE',
        'contact_phone_numeric' => 'LIKE',
        'answers.id' => 'in',
        'favorites.advertise_id' => 'in',
        'category_id' => 'in',
        'status' => 'in',
        'type',
        'user_id',
        'city_id',
        'city.country_id'
    ];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string {
        return \App\Models\Advertise::class;
    }

    /**
     * Boot up the repository, pushing criteria
     * @throws RepositoryException
     */
    public function boot() {
        $this->pushCriteria(app(SearchCriteria::class));
        $this->pushCriteria(app(BannedUserCriteria::class));
    }

    /**
     * @param array $fields
     */
    public function setFieldSearchable(array $fields): void {
        $this->fieldSearchable = $fields;
    }

    /**
     * @param string|null $search
     */
    public function updateOrCreateSearchText(?string $search): void {
        $searchData = app(SearchCriteria::class)->parserSearchData($search);
        $searchText = $searchData['search_text'] ?? null;

        $search = DB::table('user_searches')->where('search', '=', $searchText);

        if ($search->exists()) {
            $model = $search->first();
            $search->update(['search_count' => ++$model->search_count]);
        } else {
            if ($searchText) DB::table('user_searches')->insert(['search' => $searchText, 'search_count' => 1]);
        }
    }

    /**
     * @param string|null $search
     * @return LengthAwarePaginator
     */
    public function searchTexts(string $search = null): LengthAwarePaginator {

        $table = DB::table('user_searches as us');

        if ($search) {
            $query = $table
                ->select('search', 'search_count', DB::raw('COUNT(name) as count_advertises'))
                ->where('search', 'LIKE', "%$search%")
                ->leftJoin('advertises', 'advertises.name', 'LIKE', DB::raw("CONCAT('%',us.search,'%')"))
                ->groupBy('search', 'search_count');
        } else {
            $query = $table->select('search', 'search_count');
        }

        return $query
            ->orderBy('search_count', 'DESC')
            ->paginate(request()->query('per_page') ?: 20);
    }

    /**
     * @param array $attributes
     * @throws ValidatorException|\Throwable
     * @return mixed
     */
    public function create(array $attributes): mixed {

        if (user()) {
            if (!isset($attributes['user_id'])) {
                $attributes = array_merge($attributes, ['user_id' => user()->id]);
            }

            if (user()->phone && isset($attributes['phone'])) {
                $attributes = array_merge($attributes, ['phone' => user()->phone]);
            }
        }

        DB::transaction(function () use ($attributes, &$advertise) {
            $isset = isset($attributes['answers']);

            if ($isset) $attributes['filters'] = $attributes['answers'];

            $attributes['inactively_date'] = \Illuminate\Support\Carbon::now()->addDays(30);

            $advertise = parent::create($attributes);

            if ($isset) $advertise->answers()->sync($attributes['answers']);

            $this->addPicturesIfExistsFile($advertise, $attributes);
        });

        return $advertise->fresh();
    }

    /**
     * @param array $attributes
     * @param Advertise $advertise
     * @throws \Throwable
     * @return mixed
     */
    public function updateAdvertise(array $attributes, Advertise $advertise): Advertise {
        DB::transaction(function () use ($attributes, &$advertise) {
            $isset = isset($attributes['answers']);

            if (isset($advertise['auto_renewal']) && +$advertise['auto_renewal']) {
                array_push($attributes, ['inactively_date' => Carbon::now()->addDays(30)]);
            }

            if ($isset) $attributes['filters'] = $attributes['answers'];

            $advertise = parent::update($attributes, $advertise->id);

            if ($isset) $advertise->answers()->sync($attributes['answers']);

            $this->addPicturesIfExistsFile($advertise, $attributes);
        });

        return $advertise;
    }

    /**
     * @param AdvertisesRequest $request
     * @param Advertise $advertise
     * @param int|null $moderator_id
     */
    public function changeStatus(AdvertisesRequest $request, Advertise $advertise, int $moderator_id = null): void {
        $status = $request->get('status');

        $data = [
            'status' => $status,
            'refusal_comment' => $request->get('refusal_comment'),
        ];

        if ($refusal_id = $request->get('refusal_id')) {
            $data['refusal_id'] = $refusal_id;
        }

        $advertise->update($data);

        $changes = $advertise->getChanges();

        /**
         * Если Модератор подтвердил обновление сохраняем статистику модератора
         */
        if ($moderator_id && isset($changes['status'])) {
            $changedStatus = $changes['status'];
            $statistic = [
                'moderator_id' => $moderator_id,
                'advertise_id' => $advertise->id,
            ];

            if (AdvertiseStatus::Active === +$changedStatus) {
                $statistic['type'] = ModeratorStatisticsEnum::APPROVED_ADS;
            }

            if (AdvertiseStatus::Rejected === +$changedStatus) {
                $statistic['type'] = ModeratorStatisticsEnum::REJECTED_ADS;
            }

            ModeratorStatistic::make($statistic);
        }
    }

    /**
     * @param Advertise $advertise
     * @param array $attributes
     * @param bool $withDelete
     */
    private function addPicturesIfExistsFile(Advertise $advertise, array $attributes, bool $withDelete = false): void {

        if (isset($attributes['pictures'])) {

            if ($withDelete) {
                /**
                 * Удаляем старую картинку
                 */
                $advertise->clearMediaCollection(\App\Enums\MediaCollections::ADVERTISE_COLLECTION);
            }

            foreach ($attributes['pictures'] as $picture) {
                /**
                 * Добовляем Новую
                 */
                $this->addResponsiveImageWithOrder($advertise, $picture, \App\Enums\MediaCollections::ADVERTISE_COLLECTION);
            }
        }
    }
}
