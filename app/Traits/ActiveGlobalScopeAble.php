<?php

namespace App\Traits;

use App\Scopes\Category\ActiveScope;

/**
 * Trait ActiveScopeAble
 * @package App\Traits
 */
trait ActiveGlobalScopeAble
{
   /**
    * The "booted" method of the model.
    *
    * @return void
    */
   protected static function booted()
   {
      static::addGlobalScope(new ActiveScope());
   }
}
