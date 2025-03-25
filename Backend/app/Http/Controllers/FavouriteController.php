<?php

namespace App\Http\Controllers;

use App\Models\Favourite;
use App\Http\Requests\StoreFavouriteRequest;
use App\Http\Requests\UpdateFavouriteRequest;
use Illuminate\Http\Request; // ✅ Correct import
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'userId' => 'required|exists:users,id',
                'filmId' => 'required|exists:films,id',
                'evaluation' => 'required|numeric|min:0.5|max:5'
                // Removed content validation
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
                'evaluation' => $validated['evaluation']
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
            $row->update($request->validated()); // ✅ Only validated data
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
