<?php

namespace App\Http\Controllers;

use App\Models\Favourite;
use App\Http\Requests\StoreFavouriteRequest;
use App\Http\Requests\UpdateFavouriteRequest;

class FavouriteController extends Controller
{
    public function index()
    {
        $rows = Favourite::orderBy('id', 'asc')->get();
        $data = [
            'message' => 'ok',
            'data' => $rows
        ];
        return response()->json($data, options: JSON_UNESCAPED_UNICODE);
    }

    public function store(StoreFavouriteRequest $request)
    {
        $row = Favourite::create($request->all());
        $data = [
            'message' => 'ok',
            'data' => $row
        ];
        return response()->json($data, 201, options: JSON_UNESCAPED_UNICODE);
    }

    public function show(int $id)
    {
        $row = Favourite::find($id);
        if ($row) {
            $data = [
                'message' => 'ok',
                'data' => $row
            ];
        } else {
            $data = [
                'message' => 'Not found',
                'data' => ['id' => $id]
            ];
        }
        return response()->json($data, options: JSON_UNESCAPED_UNICODE);
    }

    public function update(UpdateFavouriteRequest $request, int $id)
    {
        $row = Favourite::find($id);
        if ($row) {
            $row->update($request->all());
            $data = [
                'message' => 'ok',
                'data' => $row
            ];
        } else {
            $data = [
                'message' => 'Not found',
                'data' => ['id' => $id]
            ];
        }
        return response()->json($data, options: JSON_UNESCAPED_UNICODE);
    }

    public function destroy(int $id)
    {
        $row = Favourite::find($id);
        if ($row) {
            $row->delete();
            $data = [
                'message' => 'ok',
                'data' => ['id' => $id]
            ];
        } else {
            $data = [
                'message' => 'Not found',
                'data' => ['id' => $id]
            ];
        }
        return response()->json($data, options: JSON_UNESCAPED_UNICODE);
    }
}