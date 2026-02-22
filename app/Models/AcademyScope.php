<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AcademyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (!auth('academy')->check()) {
            return;
        }

        $academy = auth('academy')->user();
        
        // Check if company is an object with id property
        if (!is_object($academy) || !isset($academy->id)) {
            return;
        }

        $academyId = $academy->id;  
        $tableName = $model->getTable();

        // Use qualified column name (table.column) to avoid ambiguity in JOIN queries
        // This ensures the scope works correctly even when multiple tables with company_id are joined
        $builder->where("{$tableName}.academy_id", $academyId);
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        // Add a method to remove the scope if needed
        $builder->macro('withoutAcademyScope', function (Builder $builder) {
            return $builder->withoutGlobalScope(AcademyScope::class);
        });
    }
}