<?php

namespace App\Traits;

/**
 * Trait MakeStaticInstanceAble
 * @package App\Traits
 */
trait MakeStaticInstanceAble
{
    /**
     * Create a new resource instance.
     *
     * @param  mixed  ...$parameters
     * @return static
     */
    public static function make(...$parameters): static
    {
        return new static(...$parameters);
    }
}
