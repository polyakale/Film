<?php

namespace App\Http\Controllers;

use App\Models\Favourite;
use App\Http\Requests\StoreFavouriteRequest;
use App\Http\Requests\UpdateFavouriteRequest;
use Illuminate\Http\Request;

class FavouriteController extends Controller
{
    public function addFavourite(Request $request)
    {
        $request->validate([
            'filmId' => 'required|exists:films,id',
            'evaluation' => 'nullable|integer|min:1|max:5',
        ]);

        $favourite = Favourite::create([
            'userId' => $request->user()->id,
            'filmId' => $request->filmId,
            'evaluation' => $request->evaluation,
        ]);

        return response()->json([
            'message' => 'Film added to favourites!',
            'favourite' => $favourite,
        ], 201);
    }

    public function getFavourites(Request $request)
    {
        $favourites = Favourite::where('userId', $request->user()->id)
            ->with('film')
            ->get();

        return response()->json($favourites, 200);
    }

    public function removeFavourite($id)
    {
        $favourite = Favourite::findOrFail($id);
        $favourite->delete();

        return response()->json([
            'message' => 'Favourite removed!',
        ], 200);
    }

    public function index()
    {
        $rows = Favourite::all();
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
        return response()->json($data, options: JSON_UNESCAPED_UNICODE);
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
                'data' => [
                    'id' => $id
                ]
            ];
        }
        return response()->json($data, options: JSON_UNESCAPED_UNICODE);
    }

    public function update(UpdateFavouriteRequest $request,  $id)
    {
        //Keresd meg az adott product-ot
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
                'data' => [
                    'id' => $id
                ]
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
                'data' => [
                    'id' => $id
                ]
            ];
        } else {
            $data = [
                'message' => 'Not found',
                'data' => [
                    'id' => $id
                ]
            ];
        }
        //Írd felül a küldött adatokkal
        //visszaküldjük a módosított rekordot
        return response()->json($data, options: JSON_UNESCAPED_UNICODE);
    }
}
