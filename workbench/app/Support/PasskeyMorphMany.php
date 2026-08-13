<?php

declare(strict_types=1);

namespace Workbench\App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PasskeyMorphMany extends HasMany
{
    public function __construct(
        Builder $query,
        Model $parent,
        protected string $morphType,
        string $foreignKey,
        string $localKey,
        protected string $morphClass,
    ) {
        parent::__construct($query, $parent, $foreignKey, $localKey);
    }

    public function addConstraints()
    {
        if (static::$constraints) {
            $this->getRelationQuery()->where($this->morphType, $this->morphClass);

            parent::addConstraints();
        }
    }

    protected function setForeignAttributesForCreate(Model $model)
    {
        $model->{$this->getForeignKeyName()} = $this->getParentKey();
        $model->{$this->morphType} = $this->morphClass;
    }
}
