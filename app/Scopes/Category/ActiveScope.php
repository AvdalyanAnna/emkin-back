<?php

namespace App\Scopes\Category;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Class ActiveScope
 * @package App\Scopes\Category
 */
class ActiveScope implements Scope
{
   /**
    * Apply the scope to a given Eloquent query builder.
    *
    * @param Builder $builder
    * @param Model $model
    * @return void
    */
   public function apply(Builder $builder, Model $model)
   {
      $builder->where('status','=', true);
   }
}
