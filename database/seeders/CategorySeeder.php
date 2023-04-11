<?php

namespace Database\Seeders;

use App\Enums\MediaCollections;
use App\Models\Category;
use App\Models\Filter;
use App\Models\FilterAnswer;
use App\Models\Media;
use App\Traits\UploadAble;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class CategorySeeder extends Seeder
{
    use UploadAble;

    /**
     * Run the database seeds.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Throwable
     * @return void
     */
    public function run()
    {
       \DB::transaction(function () {

           $disk = 'public';
           $files = \Storage::disk('local')->files('img/category');

           $countItems = rand(2, 5);
           $categoryFactory = Category::factory();
           $filterFactory = Filter::factory();
           $answerFactory = FilterAnswer::factory();

           foreach ($files as $picture) {
               $category = $categoryFactory
                   ->has(
                       $categoryFactory
                           ->has(
                               $categoryFactory
                                   ->has(
                                       $filterFactory
                                           ->has(
                                               $answerFactory->count($countItems), 'answers'
                                           )
                                           ->count($countItems), 'filters'
                                   )
                                   ->count($countItems), 'subCategories'
                           )
                           ->has(
                               $filterFactory
                                   ->has(
                                       $answerFactory->count($countItems), 'answers'
                                   )
                                   ->count($countItems), 'filters'
                           )
                           ->count($countItems), 'subCategories'
                   )
                   ->has(
                       $filterFactory
                           ->has(
                               $answerFactory->count($countItems), 'answers'
                           )
                           ->count($countItems), 'filters'
                   )
                   ->create();

               $picture = public_path($picture);
               $image = Image::make(File::get($picture));
               $width = $image->width();
               $height = $image->height();

               $customProperties = [];

               foreach (Media::IMAGE_CONVERSIONS as $conversionName => $conversionWidth) {
                   if ($width > $conversionWidth) {
                       $newHeight = ceil($conversionWidth * $height / $width);
                       $customProperties[$conversionName] = [
                           'width' => $conversionWidth,
                           'height' => (int)$newHeight,
                       ];
                   }
               }

               $category
                   ->addMedia($picture)
                   ->preservingOriginal()
                   ->storingConversionsOnDisk($disk)
                   ->withCustomProperties($customProperties)
                   ->toMediaCollection(MediaCollections::PICTURE_COLLECTION, $disk);
           }
       });
    }
}
