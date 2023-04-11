<?php

namespace App\Scopes\Category;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Class OrderScope
 * @package App\Scopes\Category
 */
class OrderScope implements Scope
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
      $builder->orderBy('order');
   }
}
