<?php

namespace App\Http\Controllers;

use App\Models\Favourite;
use App\Http\Requests\StoreFavouriteRequest;
use App\Http\Requests\UpdateFavouriteRequest;
use Illuminate\Support\Facades\DB;

class FavouriteController extends Controller
{
    public function index()
    {
        $favourites = DB::table('favourites as fa')
            ->join('users as u', 'fa.userId', '=', 'u.id')
            ->join('films as f', 'fa.filmId', '=', 'f.id')
            ->select('fa.*', 'u.name as userName', 'f.title as filmTitle')
            ->get();
    
        return response()->json([
            'message' => 'ok',
            'data' => $favourites,
        ]);
    }

    public function store(StoreFavouriteRequest $request)
    {
        $validated = $request->validated();
        
        $favourite = Favourite::create([
            'filmId' => $validated['filmId'],
            'userId' => auth()->id(),
            'evaluation' => $validated['evaluation']
        ]);
    
        return response()->json([
            'message' => 'Rating submitted successfully',
            'data' => $favourite
        ], 201);
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
