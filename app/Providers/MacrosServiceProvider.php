<?php

namespace App\Providers;

use App\Chat\Pagination;
use App\Models\User;
use App\Support\Macros\CreateUpdateOrDeleteAnswers;
use App\Support\Macros\RequestPaginationParams;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Spatie\MediaLibrary\Support\File;
use Spatie\MediaLibrary\Support\RemoteFile;

/**
 * Class MacrosServiceProvider
 * @package App\Providers
 */
class MacrosServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @throws \Throwable
     * @return void
     */
    public function boot()
    {
        $this->registerMacros();
    }

    /**
     * Добовляет новую функционал в класс связи много к одному
     *
     * @throws \Throwable
     * @return void
     */
    public function registerMacros(): void {
        HasMany::macro('createUpdateOrDeleteAnswers', function (iterable $records, $model) {

            /** @var HasMany $hasMany */
            $hasMany = $this;

            return (new CreateUpdateOrDeleteAnswers($hasMany, $records, $model))();
        });

        Request::macro('getPaginationParams', function () {

            /** @var Request $request */
            $request = $this;

            return (new RequestPaginationParams($request, app(Pagination::class)))();
        });

        Request::macro('getParticipant', function () {

            /** @var Request $request */
            $request = $this;

            return User::findOrFail($request->get('participant_id'));
        });

        FileAdder::macro('withCustomDetails', function(array $details) {
            $this->custom_details = $details;
            return $this;
        });

        FileAdder::macro('toMediaCollectionCustom', function(string $collectionName = 'default', string $diskName = '') {

            $sanitizedFileName = ($this->fileNameSanitizer)($this->fileName);
            $fileName = app(config('media-library.file_namer'))->originalFileName($sanitizedFileName);
            $this->fileName = $this->appendExtension($fileName, pathinfo($sanitizedFileName, PATHINFO_EXTENSION));

            if ($this->file instanceof RemoteFile) {
                return $this->toMediaCollectionFromRemote($collectionName, $diskName);
            }

            if ($this->file instanceof TemporaryUpload) {
                return $this->toMediaCollectionFromTemporaryUpload($collectionName, $diskName, $this->fileName);
            }

            if (! is_file($this->pathToFile)) {
                throw FileDoesNotExist::create($this->pathToFile);
            }

            if (filesize($this->pathToFile) > config('media-library.max_file_size')) {
                throw FileIsTooBig::create($this->pathToFile);
            }

            $mediaClass = config('media-library.media_model');
            /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media $media */
            $media = new $mediaClass();

            $media->name = $this->mediaName;

            $media->file_name = $this->fileName;

            $media->disk = $this->determineDiskName($diskName, $collectionName);
            $this->ensureDiskExists($media->disk);

            $media->conversions_disk = $this->determineConversionsDiskName($media->disk, $collectionName);
            $this->ensureDiskExists($media->conversions_disk);

            $media->collection_name = $collectionName;

            $media->mime_type = File::getMimeType($this->pathToFile);
            $media->size = filesize($this->pathToFile);

            if (! is_null($this->order)) {
                $media->order_column = $this->order;
            }

            $media->custom_properties = $this->customProperties;
            $media->custom_details = $this->custom_details;

            $media->generated_conversions = [];
            $media->responsive_images = [];

            $media->manipulations = $this->manipulations;

            if (filled($this->customHeaders)) {
                $media->setCustomHeaders($this->customHeaders);
            }

            $media->fill($this->properties);

            $this->attachMedia($media);

            return $media;
        });
    }
}
