<?php

namespace App\Traits;

use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;

trait BuildsReportQuery
{
    /**
     * Membuat instance query berdasarkan filter yang diberikan.
     *
     * @param string $filter Tipe data (item, in, out, damaged)
     * @param string $filterBy Periode (date, month, year)
     * @param array $params Parameter periode (dateFrom, dateUntil, dll.)
     * @return Builder
     */
    protected function buildReportQuery(string $filter, string $filterBy, array $params): Builder
    {
        $query = $filter === 'item'
            ? Item::query()
            : Transaction::with('item')->where('type', $filter);

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