<?php

namespace TaFarda\IAuth\app\Contracts\Repositories;

use TaFarda\IAuth\app\Contracts\Interfaces\BaseRepositoryInterface;
use Carbon\Carbon;
class BaseRepository implements BaseRepositoryInterface
{
    /**
     * paginates and search items.
     *
     * @param $modelName
     * @param $query
     * @return array
     */
    public function index($modelName, $query): array
    {
        $query = $query->when($keyword = request('search'), function ($query) use ($modelName, $keyword) {
            $query->where(function ($query) use ($modelName, $keyword) {
                foreach ($modelName::$SEARCH_ITEMS as $searchItem)
                    $query->OrWhere($searchItem, 'like', "%{$keyword}%");
            });
        })->when((request('status') && request('status') != 'all'), function ($query) {
            $query->where('status', 'like', request('status'));
        })->when($fromDate = request('from_date'), function ($query) use ($fromDate) {
            $query->where('created_at', '>=',);
        })->when($toDate = request('to_date'), function ($query) use ($toDate) {
            $query->where('created_at', '<=', Carbon::createFromTimestamp($toDate));
        });

        $sort = request('sort') ?? 'id';
        $sortOrder = request('sort_order') ?? 'desc';
        $page = request('page') ?? 1;
        $perPage = request('per_page') ?? config('tafarda_iauth.pagination_size');
        $cloneItems = clone $query;

        return [
            $query->orderBy($sort, $sortOrder)->skip(($page - 1) * $perPage)->limit($perPage)->get(),
            $cloneItems->count()
        ];
    }

}
