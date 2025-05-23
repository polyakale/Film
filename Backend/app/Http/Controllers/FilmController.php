<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Http\Requests\StoreFilmRequest;
use App\Http\Requests\UpdateFilmRequest;
use Illuminate\Support\Facades\DB;

class FilmController extends Controller
{
    public function queryFilmsWithEvaluation()
    {
        //natív SQL
        $rows = DB::select(
            '
            SELECT fi.id, 
            fi.title, 
            fi.production, 
            fi.length, 
            fi.presentation, 
            fi.imdbLink, 
            ROUND(AVG(fa.evaluation),1) AS evaluation,
            GROUP_CONCAT(fa.userId) usersId
        FROM films fi
        LEFT JOIN favourites fa ON fi.id = fa.filmId
        GROUP BY fi.id,
                fi.title, 
            fi.production, 
            fi.length, 
            fi.presentation, 
            fi.imdbLink  
            '
        );


        $data = [
            'message' => 'ok',
            'data' => $rows
        ];

        return response()->json($data, options: JSON_UNESCAPED_UNICODE);
    }
    public function queryFilmCasts($id)
    {
        //natív SQL
        $query =
            '
            SELECT f.title, p.name, r.role from tasks t
            JOIN people p ON t.personId = p.id
            JOIN films f  ON t.filmId = f.id
            JOIN roles r  ON t.roleId = r.id
            where f.id = ?;
        ';
        try {
            //code...
            $rows = DB::select($query, [$id]);

            $data = [
                'message' => 'ok',
                'data' => $rows
            ];
        } catch (\Throwable $th) {
            //throw $th;
            $data = [
                'message' => 'Query Error',
                'data' => []
            ];
        }


        return response()->json($data, options: JSON_UNESCAPED_UNICODE);
    }


    public function index()
    {
        $rows = Film::all();
        $data = [
            'message' => 'ok',
            'data' => $rows
        ];
        return response()->json($data, options: JSON_UNESCAPED_UNICODE);
    }
    public function store(StoreFilmRequest $request)
    {
        $row = Film::create($request->all());
        $data = [
            'message' => 'ok',
            'data' => $row
        ];
        return response()->json($data, options: JSON_UNESCAPED_UNICODE);
    }
    public function show(int $id)
    {
        $row = Film::find($id);

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
    public function update(UpdateFilmRequest $request,  $id)
    {
        //Keresd meg az adott product-ot
        $row = Film::find($id);
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
        $row = Film::find($id);
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
