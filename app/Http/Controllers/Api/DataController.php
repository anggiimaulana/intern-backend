<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DataQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataController extends Controller
{
    public function __construct(private readonly DataQueryService $service) {}

    /**
     * List records with cursor pagination and optional filters.
     *
     * Query params:
     *   - cursor  (int|null): ID of the last record from previous page
     *   - limit   (int, default 500, max 50000): Records per page
     *   - sort    (string, 'asc'|'desc', default 'desc')
     *   - id_from (int|null): Minimum ID filter
     *   - id_to   (int|null): Maximum ID filter
     *   - search  (string|null): Search term (min 2 chars) for field_1/2/3
     */
    public function index(Request $request): JsonResponse
    {
        $cursor  = $request->query('cursor');
        $limit   = min(max((int) $request->query('limit', 500), 1), 50000);
        $sort    = $request->query('sort', 'desc');
        $idFrom  = $request->query('id_from') ? (int) $request->query('id_from') : null;
        $idTo    = $request->query('id_to') ? (int) $request->query('id_to') : null;
        $search  = $request->query('search');

        return response()->json(
            $this->service->listRecords(
                $cursor ? (int) $cursor : null,
                $limit,
                $sort,
                $idFrom,
                $idTo,
                $search
            )
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'field_1' => 'required|string|max:255',
            'field_2' => 'required|email|max:255',
            'field_3' => 'required|string|max:50',
        ]);

        $id = DB::table('records')->insertGetId([
            'field_1' => $validated['field_1'],
            'field_2' => $validated['field_2'],
            'field_3' => $validated['field_3'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->service->invalidateCaches();

        $record = DB::table('records')->where('id', $id)->first();

        return response()->json([
            'data' => [
                'id'      => $record->id,
                'field_1' => $record->field_1,
                'field_2' => $record->field_2,
                'field_3' => $record->field_3,
            ],
            'message' => 'Record created successfully',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'field_1' => 'required|string|max:255',
            'field_2' => 'required|email|max:255',
            'field_3' => 'required|string|max:50',
        ]);

        $record = DB::table('records')->where('id', $id)->first();
        if (!$record) {
            return response()->json(['message' => 'Not found'], 404);
        }

        DB::table('records')->where('id', $id)->update([
            'field_1' => $validated['field_1'],
            'field_2' => $validated['field_2'],
            'field_3' => $validated['field_3'],
            'updated_at' => now(),
        ]);

        $this->service->invalidateCaches();

        $record = DB::table('records')->where('id', $id)->first();

        return response()->json([
            'data' => [
                'id'      => $record->id,
                'field_1' => $record->field_1,
                'field_2' => $record->field_2,
                'field_3' => $record->field_3,
            ],
            'message' => 'Record updated successfully',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $exists = DB::table('records')->where('id', $id)->exists();
        if (!$exists) {
            return response()->json(['message' => 'Not found'], 404);
        }

        DB::table('records')->where('id', $id)->delete();
        $this->service->invalidateCaches();

        return response()->json(['message' => 'Record deleted successfully']);
    }
}
