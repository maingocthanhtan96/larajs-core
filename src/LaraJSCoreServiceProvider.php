<?php

namespace LaraJS\Core;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use LaraJS\Core\Commands\SetupCommand;

class LaraJSCoreServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     */
    protected bool $defer = false;

    public function boot()
    {
        $this->app->singleton('larajs.setup', function () {
            return new SetupCommand();
        });
        $this->commands('larajs.setup');
        $this->publishes(
            [
                __DIR__.'/../config/generator.php' => config_path('generator.php'),
            ],
            'larajs-core-config',
        );
        $this->publishes(
            [
                __DIR__.'/../config/generator-mono.php' => config_path('generator.php'),
            ],
            'larajs-core-config-mono',
        );
        $this->mergeConfigFrom(__DIR__.'/../config/generator.php', 'generator');
        $this->publishes([
            __DIR__.'/../public' => public_path('vendor'),
        ], 'larajs-core-public');
        $this->publishes(
            [
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ],
            'larajs-core-migrations',
        );
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register()
    {
        $this->_whereLike();
        $this->_paginate();
        $this->_orderByRelationship();
    }

    private function _whereLike()
    {
        // whereLike
        if (! Builder::hasGlobalMacro('whereLike')) {
            Builder::macro('whereLike', function ($attributes, string $searchTerm) {
                $this->where(function (Builder $query) use ($attributes, $searchTerm) {
                    foreach (Arr::wrap($attributes) as $attribute) {
                        $query->when(
                            Str::contains($attribute, '.'),
                            function (Builder $query) use ($attribute, $searchTerm) {
                                [$relationName, $relationAttribute] = explode('.', $attribute);
                                $query->orWhereHas($relationName, function (Builder $query) use (
                                    $relationAttribute,
                                    $searchTerm,
                                ) {
                                    $query->where($relationAttribute, 'LIKE', "%{$searchTerm}%");
                                });
                            },
                            function (Builder $query) use ($attribute, $searchTerm) {
                                $table = $this->getModel()->getTable();
                                $query->orWhere("$table.$attribute", 'LIKE', "%{$searchTerm}%");
                            },
                        );
                    }
                });

                return $this;
            });
        }
    }

    private function _paginate()
    {
        // Enable pagination
        if (! Collection::hasMacro('paginate')) {
            Collection::macro('paginate', function ($perPage = 15, $page = null, $options = []) {
                $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

                return (new LengthAwarePaginator(
                    $this->forPage($page, $perPage)
                        ->values()
                        ->all(),
                    $this->count(),
                    $perPage,
                    $page,
                    $options,
                ))->withPath(Request::url());
            });
        }
    }

    private function _orderByRelationship()
    {
        if (! Builder::hasGlobalMacro('orderByRelationship')) {
            Builder::macro('orderByRelationship', function ($searchColumn, string $direction = 'asc') {
                if (Str::contains($searchColumn, '.')) {
                    [$relation, $column] = explode('.', $searchColumn);
                    $relation = $this->getRelation($relation);
                    if ($relation instanceof BelongsToMany) {
                        $tableThrough = $relation->getTable();
                        $relationForeignKey = $relation->getForeignPivotKeyName();
                        $relationRelatedKey = $relation->getRelatedPivotKeyName();
                        $queryTable = $this->getModel()->getTable();
                        $queryTableRelated = $relation->getModel()->getTable();

                        return $this->select("$queryTable.*")
                            ->leftJoin($tableThrough, "$tableThrough.$relationForeignKey", "$queryTable.id")
                            ->leftJoin($queryTableRelated, "$queryTableRelated.id", "$tableThrough.$relationRelatedKey")
                            ->orderBy("$queryTableRelated.$column", $direction);
                    } else {
                        $relationTable = $relation->getModel()->getTable();
                        $relationForeignKey = $relation->getForeignKeyName();
                        $queryTable = $this->getModel()->getTable();

                        return $this->select("$queryTable.*")
                            ->leftJoin($relationTable, "$queryTable.$relationForeignKey", "$relationTable.id")
                            ->orderBy("$relationTable.$column", $direction);
                    }
                }

                return $this->orderBy($searchColumn, $direction);
            });
        }
    }
}
