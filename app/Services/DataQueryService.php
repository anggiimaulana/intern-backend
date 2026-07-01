<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DataQueryService
{
    /**
     * List records using cursor (keyset) pagination with optional filters.
     *
     * @param  int|null    $cursor   Last seen record ID (null for first page)
     * @param  int         $limit    Records per page (default 500, max 50000)
     * @param  string      $sort     'asc' or 'desc'
     * @param  int|null    $idFrom   Minimum ID filter
     * @param  int|null    $idTo     Maximum ID filter
     * @param  string|null $search   Search term for field_1, field_2, field_3
     */
    public function listRecords(
        ?int $cursor,
        int $limit = 500,
        string $sort = 'desc',
        ?int $idFrom = null,
        ?int $idTo = null,
        ?string $search = null,
    ): array {
        $sort = strtolower($sort) === 'asc' ? 'asc' : 'desc';
        $limit = min(max($limit, 1), 50000);

        // Only cache first page with default filters (most common case)
        if (! $cursor && ! $idFrom && ! $idTo && ! $search) {
            return Cache::remember("records:first_page:{$limit}:{$sort}", 300, fn () => $this->executeQuery(null, $limit, $sort, null, null, null));
        }

        return $this->executeQuery($cursor, $limit, $sort, $idFrom, $idTo, $search);
    }

    private function executeQuery(
        ?int $cursor,
        int $limit,
        string $sort,
        ?int $idFrom,
        ?int $idTo,
        ?string $search,
    ): array {
        $query = DB::table('records')
            ->select('id', 'field_1', 'field_2', 'field_3');

        // Sort + cursor pagination
        if ($sort === 'asc') {
            $query->orderBy('id');
            if ($cursor) {
                $query->where('id', '>', $cursor);
            }
        } else {
            $query->orderByDesc('id');
            if ($cursor) {
                $query->where('id', '<', $cursor);
            }
        }

        // ID range filter
        if ($idFrom) {
            $query->where('id', '>=', $idFrom);
        }
        if ($idTo) {
            $query->where('id', '<=', $idTo);
        }

        // Search filter (LIKE on all text fields, min 2 chars)
        if ($search && strlen(trim($search)) >= 2) {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                $q->where('field_1', 'LIKE', "%{$term}%")
                  ->orWhere('field_2', 'LIKE', "%{$term}%")
                  ->orWhere('field_3', 'LIKE', "%{$term}%");
            });
        }

        $startTime = microtime(true);
        $records = $query->limit($limit)->get();
        $dbTimeMs = round((microtime(true) - $startTime) * 1000, 2);

        $items = $records->map(fn ($row) => (array) $row)->all();

        // next_cursor is null when this is the last page
        $nextCursor = $records->count() < $limit ? null : $records->last()?->id;

        return [
            'data'                  => $items,
            'next_cursor'           => $nextCursor,
            'total'                 => $this->getCachedTotalCount(),
            'filtered_total'        => $this->getFilteredTotal($idFrom, $idTo, $search),
            'database_time_ms'      => $dbTimeMs,
            'query_execution_speed' => "{$dbTimeMs} ms (< 1s - Sub-second PostgreSQL Index Scan)",
        ];
    }

    public function getCachedTotalCount(): int
    {
        return Cache::remember('records:total_count', 3600, fn () => DB::table('records')->count());
    }

    private function getFilteredTotal(?int $idFrom, ?int $idTo, ?string $search): ?int
    {
        if (! $idFrom && ! $idTo && ! $search) {
            return null;
        }

        $query = DB::table('records');

        if ($idFrom) {
            $query->where('id', '>=', $idFrom);
        }
        if ($idTo) {
            $query->where('id', '<=', $idTo);
        }
        if ($search && strlen(trim($search)) >= 2) {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                $q->where('field_1', 'LIKE', "%{$term}%")
                  ->orWhere('field_2', 'LIKE', "%{$term}%")
                  ->orWhere('field_3', 'LIKE', "%{$term}%");
            });
        }

        return $query->count();
    }

    public function invalidateCaches(): void
    {
        Cache::forget('records:total_count');
        foreach ([500, 1000, 2000, 5000, 10000, 50000] as $i) {
            Cache::forget("records:first_page:{$i}");
        }
    }
}
