<?php

namespace App\Http\Controllers;

use App\Models\Favourite;
use App\Http\Requests\StoreFavouriteRequest;
use App\Http\Requests\UpdateFavouriteRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
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
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'userId'    => 'required|exists:users,id',
            'filmId'    => 'required|exists:films,id',
            'evaluation' => 'required|numeric|min:0.5|max:5',
            'content'   => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }
        // Check if review already exists for the user and film
        $exists = Favourite::where('userId', $request->userId)
            ->where('filmId', $request->filmId)
            ->exists();
        if ($exists) {
            return response()->json([
                'message' => "You've already reviewed this film."
            ], 422);
        }

        // Create the review
        $favourite = Favourite::create($request->all());

        return response()->json([
            'message' => 'Review created successfully',
            'data'    => $favourite
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
