<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Trait SlugAble
 *
 * @package App\Traits
 * @method static whereSlug(string $param)
 * @method static whereName($title)
 */
trait SlugAble {

    /**
     * Boot the model.
     */
    protected static function bootSlugAble() {

        static::creating(function ($model) {
            $slugName = static::$slugName;

            $model->callGenerateSlug($model, $slugName);
        });

        static::updating(function ($model) {
            $slugName = static::$slugName;

            if ($model->isDirty($slugName)) {
                $model->callGenerateSlug($model, $slugName);
            }
        });
    }

    /**
     * @param $model
     * @param $slugName
     */
    function callGenerateSlug($model, $slugName) {

        if (property_exists(static::class, 'translatable')) {

            $locale = app()->getLocale();

            if (user() && user()->isAdmin() && app()->getLocale() != 'en') $locale = 'en';

            $slugName = $model->getTranslations($slugName)[$locale] ?? Str::uuid();
        } else {
            $slugName = $model->{$slugName};
        }

        $model->slug = $model->createSlug($slugName);
    }

    /**
     * @param $slugName
     * @return array|string|null
     */
    private function createSlug($slugName): array|string|null {
        $slug = Str::slug($slugName);

        if ($this->isUniqSlug($slug)) {

            $locale = app()->getLocale();

            if (property_exists(static::class, 'translatable')) {
                $max = static::where("name->$locale", $slugName)->latest('id')->value('slug');
            } else {
                $max = static::whereName($slugName)->latest('id')->value('slug');
            }

            if ($max && is_numeric($max[-1])) {
                return preg_replace_callback('/(\d+)$/', function ($mathCes) {
                    return $mathCes[1] + 1;
                }, $max);
            }

            return "$slug-2";
        }

        return $slug;
    }

    /**
     * @param string $slugName
     * @return mixed
     */
    function isUniqSlug(string $slugName): bool {
        return static::whereSlug($slugName)->exists();
    }
}
