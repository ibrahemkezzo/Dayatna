<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class EntityFilter
{
    public function __construct(protected Request $request) {}

    public function apply(Builder $query): Builder
    {
        return $query
            ->when($this->request->filled('search'), function ($q) {
                $term = $this->request->query('search');
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->when($this->request->filled('category_id'), function ($q) {
                $q->where('category_id', $this->request->query('category_id'));
            })
            ->when($this->request->filled('status'), function ($q) {
                $q->where('status', $this->request->query('status'));
            })
            ->when($this->request->has('verified'), function ($q) {
                $q->where('is_verified', filter_var($this->request->query('verified'), FILTER_VALIDATE_BOOLEAN));
            })
            ->when($this->request->filled('price_range'), function ($q) {
                $q->where('price_range', $this->request->query('price_range'));
            });
    }
}
