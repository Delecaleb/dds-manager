<?php

namespace App\Traits;

use App\Models\Office;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OfficeScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $officeId = Office::getActiveOfficeId();

        if ($officeId !== null) {
            $table = $model->getTable();
            $builder->where("{$table}.office_id", $officeId);
        }
    }
}

trait BelongsToOffice
{
    public static function bootBelongsToOffice(): void
    {
        static::addGlobalScope(new OfficeScope);

        static::creating(function (Model $model) {
            if (empty($model->office_id)) {
                $model->office_id = Office::getActiveOfficeId() ?? 1;
            }
        });
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }
}
