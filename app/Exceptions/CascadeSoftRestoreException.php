<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Str;

/**
 * Class CascadeSoftRestoreException
 * @package App\Exceptions
 */
class CascadeSoftRestoreException extends Exception
{
    /**
     * @param $relationships
     * @return static
     */
    public static function invalidRelation($relationships): static {
        return new static(sprintf(
            '%s [%s] must exist and return an object of type Illuminate\Database\Eloquent\Relations\Relation',
            Str::plural('Relationship', count($relationships)),
            join(', ', $relationships)
        ));
    }
}
