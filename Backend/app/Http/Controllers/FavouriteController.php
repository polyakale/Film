<?php

namespace App\Http\Controllers;

use App\Models\Favourite;
use App\Http\Requests\StoreFavouriteRequest;
use App\Http\Requests\UpdateFavouriteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FavouriteController extends Controller
{
    public function getByUserId($userId)
    {
        // Option 1: Just get the favourites for the user
        $favourites = Favourite::where('userId', $userId)->get();

        // Option 2: Get favourites with joined user and film info:
        $favourites = DB::table('favourites as fa')
            ->join('users as u', 'fa.userId', '=', 'u.id')
            ->join('films as f', 'fa.filmId', '=', 'f.id')
            ->where('fa.userId', $userId)
            ->select('fa.*', 'u.name as userName', 'f.title as filmTitle')
            ->get();

        return response()->json([
            'message' => 'ok',
            'data' => $favourites,
        ]);
    }

    public function showFavouriteByUserIdAndFilmId($userId, $filmId)
    {
        // $sql = "
        // select id, userId, filmId, evaluation from favourites
        // WHERE userId = ? 
        // AND filmId = ?";
        // $favourites = DB::select($sql, [$userId, $filmId]);
        // return response()->json([
        //     'message' => 'ok',
        //     'data' => $favourites,
        // ]);
        $row = Favourite::where('userId', $userId)
        ->where('filmId', $filmId)
        ->select('id', 'userId', 'filmId', 'evaluation', 'content') 
        ->first();

    if ($row) {
       
        return response()->json([
            'message' => 'ok',
            'data'    => $row
        ]);
    } else {
        return response()->json([
            'message' => 'Not found: userId: ' . $userId . ' filmId: ' . $filmId,
            'data'    => $row
        ]);
    }
    }

    public function patchFavouriteByUserIdAndFilmId(UpdateFavouriteRequest $request, $userId, $filmId)
    {

        $row = Favourite::where('userId', $userId)
            ->where('filmId', $filmId)
            ->first();

        if ($row) {
            $row->update($request->all());
            return response()->json([
                'message' => 'ok',
                'data'    => $row
            ]);
        } else {
            return response()->json([
                'message' => 'Not found',
                'data'    => ['userId' => $userId, 'filmId' =>  $filmId]
            ]);
        }
    }
    public function storeFavouriteByUserIdAndFilmId(StoreFavouriteRequest $request)
    {

        $row = Favourite::create($request->all());
        $data = [
            'message' => 'ok',
            'data' => $row
        ];
        return response()->json($data, options: JSON_UNESCAPED_UNICODE);
    }

    public function destroyFavouriteByUserIdAndFilmId($id ,$userId, $filmId){
        $row = Favourite::where('userId', $userId, 'filmId', $filmId);
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
        };
    }


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

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'userId' => 'required|exists:users,id',
                'filmId' => 'required|exists:films,id',
                'evaluation' => 'required|numeric|min:0.5|max:5',
                'content' => 'string|min:3|max:160',
            ]);

            // Check for existing review
            $exists = Favourite::where('userId', $validated['userId'])
                ->where('filmId', $validated['filmId'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => "You've already reviewed this film."
                ], 409); // Using 409 Conflict status
            }

            $favourite = Favourite::create([
                'userId' => $validated['userId'],
                'filmId' => $validated['filmId'],
                'evaluation' => $validated['evaluation'],
                'content' => $validated['content']
            ]);

            return response()->json([
                'message' => 'Review created successfully',
                'data' => $favourite
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id)
    {
        $row = Favourite::find($id);
        if ($row) {
            return response()->json([
                'message' => 'ok',
                'data'    => $row
            ]);
        } else {
            return response()->json([
                'message' => 'Not found',
                'data'    => ['id' => $id]
            ]);
        }
    }

    public function update(UpdateFavouriteRequest $request, int $id)
    {
        $row = Favourite::find($id);
        if ($row) {
            $row->update($request->validated());
            return response()->json([
                'message' => 'ok',
                'data'    => $row
            ]);
        } else {
            return response()->json([
                'message' => 'Not found',
                'data'    => ['id' => $id]
            ]);
        }
    }

    public function destroy(int $id)
    {
        $row = Favourite::find($id);
        if ($row) {
            $row->delete();
            return response()->json([
                'message' => 'ok',
                'data'    => ['id' => $id]
            ]);
        } else {
            return response()->json([
                'message' => 'Not found',
                'data'    => ['id' => $id]
            ]);
        }
    }
}
