<?php

namespace App\Traits;

use App\Scopes\Category\OrderScope;

/**
 * Trait OrderGlobalScopeAble
 * @package App\Traits
 */
trait OrderGlobalScopeAble
{
   /**
    * The "booted" method of the model.
    *
    * @return void
    */
   protected static function booted()
   {
      static::addGlobalScope(new OrderScope());
   }
}
