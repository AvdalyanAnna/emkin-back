<?php

namespace App\Traits;

use Carbon\Carbon;

/**
 * Trait TimezoneChangeAble
 * @package App\Traits
 */
trait TimezoneChangeAble
{
    /**
     * @param $value
     * @return null|Carbon
     */
    public function getCreatedAtAttribute($value): null|Carbon {
        if (!$value) return null;
        $date = Carbon::createFromFormat('Y-m-d H:i:s', new Carbon($value), 'UTC');
        $date->setTimezone(config('app.timezone'));
        return $date;
    }

    /**
     * @param $value
     */
    public function setCreatedAtAttribute($value) {
        $this->attributes['created_at'] = Carbon::parse($value, config('app.timezone'))->setTimezone('UTC');
    }

    /**
     * @param $value
     * @return null|Carbon
     */
    public function getDeletedAtAttribute($value): null|Carbon {
        if (!$value) return null;

        $date = Carbon::createFromFormat('Y-m-d H:i:s', new Carbon($value), 'UTC');
        $date->setTimezone(config('app.timezone'));
        return $date;
    }

    /**
     * @param $value
     */
    public function setDeletedAtAttribute($value) {
        $this->attributes['deleted_at'] = Carbon::parse($value, config('app.timezone'))->setTimezone('UTC');
    }

    /**
     * @param $value
     * @return null|Carbon
     */
    public function getUpdatedAtAttribute($value): null|Carbon {
        if (!$value) return null;

        $date = Carbon::createFromFormat('Y-m-d H:i:s', new Carbon($value), 'UTC');
        $date->setTimezone(config('app.timezone'));
        return $date;
    }

    /**
     * @param $value
     */
    public function setUpdatedAtAttribute($value) {
        $this->attributes['updated_at'] = Carbon::parse($value, config('app.timezone'))->setTimezone('UTC');
    }
}
