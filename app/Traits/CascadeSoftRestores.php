<?php

namespace App\Traits;

use App\Exceptions\CascadeSoftRestoreException;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Trait CascadeSoftRestores
 * @package App\Traits
 */
trait CascadeSoftRestores
{
    /**
     * Boot the trait.
     *
     * Listen for the deleting event of a soft deleting model, and run
     * the delete operation for any configured relationship methods.
     *
     * @throws \LogicException
     */
    protected static function bootCascadeSoftRestores()
    {
        static::restoring(function ($model) {
            $model->validateCascadingSoftRestore();

            $model->runCascadingRestore();
        });
    }

    /**
     * Validate that the calling model is correctly setup for cascading soft restore.
     *
     * @throws CascadeSoftRestoreException
     */
    protected function validateCascadingSoftRestore()
    {
        if ($invalidCascadingRelationships = $this->hasInvalidRelationships()) {
            throw CascadeSoftRestoreException::invalidRelation($invalidCascadingRelationships);
        }
    }

    /**
     * Run the cascading soft delete for this model.
     *
     * @return void
     */
    protected function runCascadingRestore(): void
    {
        foreach ($this->getActiveCascadingRestore() as $relationship) {
            $this->cascadeSoftRestores($relationship);
        }
    }

    /**
     * Cascade restore the given relationship on the given mode.
     *
     * @param string $relationship
     * @return void
     */
    protected function cascadeSoftRestores(string $relationship): void
    {
        $participants = $this->{$relationship}()->withTrashed()->get();

        foreach ($participants as $model) $model->pivot ? $model->pivot->restore() : $model->restore();
    }

    /**
     * Determine if the current model implements soft restore.
     *
     * @return bool
     */
    protected function implementsSoftRestore(): bool {
        return method_exists($this, 'runSoftRestore');
    }

    /**
     * Determine if the current model has any invalid cascading relationships defined.
     *
     * A relationship is considered invalid when the method does not exist, or the relationship
     * method does not return an instance of Illuminate\Database\Eloquent\Relations\Relation.
     *
     * @return array
     */
    protected function hasInvalidRelationships(): array {
        return array_filter($this->getCascadingRestore(), function ($relationship) {
            return ! method_exists($this, $relationship) || ! $this->{$relationship}() instanceof Relation;
        });
    }

    /**
     * Fetch the defined cascading soft restore for this model.
     *
     * @return array
     */
    protected function getCascadingRestore(): array {
        return property_exists($this, 'cascadeRestore')
            ? $this->cascadeRestore
            : [];
    }

    /**
     * For the cascading restore defined on the model, return only those that are not null.
     *
     * @return array
     */
    protected function getActiveCascadingRestore(): array {
        return array_filter($this->getCascadingRestore(), function ($relationship) {
            return $this->{$relationship}()->withTrashed()->exists();
        });
    }
}
