<?php

namespace App\Traits;

use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;

trait BuildsReportQuery
{
    protected function buildReportQuery(string $filter, string $filterBy, array $params): Builder
    {
        $query = match ($filter) {
            'item' => Item::query(),
            'in' => Transaction::with('item')->whereIn('type', ['masuk_mentah', 'masuk_jadi']),
            'out' => Transaction::with('item')->whereIn('type', ['keluar_terpakai', 'keluar_dikirim', 'keluar_mentah']),
            'damaged' => Transaction::with('item')->whereIn('type', ['rusak_mentah', 'rusak_jadi']),
            default => Transaction::with('item')->where('type', $filter),
        };

        if (isset($params['itemType']) && $params['itemType'] !== 'all') {
            if ($filter === 'item') {
                $query->where('item_type', $params['itemType']);
            } else {
                $query->whereHas('item', function ($q) use ($params) {
                    $q->where('item_type', $params['itemType']);
                });
            }
        }

        switch ($filterBy) {
            case 'date':
                $query->whereBetween('created_at', [$params['dateFrom'], $params['dateUntil']]);
                break;
            case 'month':
                $query->whereYear('created_at', $params['selectYear'])
                    ->whereMonth('created_at', '>=', $params['monthFrom'])
                    ->whereMonth('created_at', '<=', $params['monthUntil']);
                break;
            case 'year':
                $query->whereYear('created_at', $params['selectYear']);
                break;
        }

        return $query->orderByDesc('created_at');
    }
}