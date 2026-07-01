<?php

namespace App\Http\Controllers;

use App\Services\DataQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecordsController extends Controller
{
    public function __construct(private readonly DataQueryService $service) {}

    public function index(Request $request): JsonResponse
    {
        $cursor  = $request->query('cursor');
        $limit   = min(max((int) $request->query('limit', 500), 1), 50000);
        $sort    = $request->query('sort', 'desc');
        $idFrom  = $request->query('id_from') ? (int) $request->query('id_from') : null;
        $idTo    = $request->query('id_to') ? (int) $request->query('id_to') : null;
        $search  = $request->query('search');

        return response()->json(
            $this->service->listRecords($cursor ? (int) $cursor : null, $limit, $sort, $idFrom, $idTo, $search)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'field_1' => 'required|string|max:255',
            'field_2' => 'required|email|max:255',
            'field_3' => 'required|string|max:50',
        ]);

        $id = DB::table('records')->insertGetId([
            'field_1' => $data['field_1'],
            'field_2' => $data['field_2'],
            'field_3' => $data['field_3'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->service->invalidateCaches();

        return response()->json(['id' => $id, 'message' => 'Created'], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'field_1' => 'required|string|max:255',
            'field_2' => 'required|email|max:255',
            'field_3' => 'required|string|max:50',
        ]);

        $updated = DB::table('records')->where('id', $id)->update([
            'field_1' => $data['field_1'],
            'field_2' => $data['field_2'],
            'field_3' => $data['field_3'],
            'updated_at' => now(),
        ]);

        if (!$updated) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $this->service->invalidateCaches();

        return response()->json(['message' => 'Updated']);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = DB::table('records')->where('id', $id)->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $this->service->invalidateCaches();

        return response()->json(['message' => 'Deleted']);
    }
}
