<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CategoryFilter
{
    public function __construct(protected Request $request) {}

    /**
     * Apply filters to the category query builder.
     */
    public function apply(Builder $query): Builder
    {
        return $query
            ->when($this->request->filled('search'), function ($q) {
                $q->where('name', 'like', '%' . $this->request->query('search') . '%');
            })
            ->when($this->request->has('main_only'), function ($q) {
                // If true, returns only main categories (parent_id is null)
                if (filter_var($this->request->query('main_only'), FILTER_VALIDATE_BOOLEAN)) {
                    $q->whereNull('parent_id');
                }
            })
            ->when($this->request->filled('parent_id'), function ($q) {
                $q->where('parent_id', $this->request->query('parent_id'));
            });
    }
}
