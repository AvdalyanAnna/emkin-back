<?php

namespace App\Models;

use App\Traits\TimezoneChangeAble;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AdvertiseAnswers
 * @package App\Models
 */
class AdvertiseAnswers extends Model
{
    use HasFactory, TimezoneChangeAble;

    protected $table = 'advertise_answers';
}
