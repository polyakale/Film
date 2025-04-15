<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index()
    {
        $rows = Task::all();
        $data = [
            'message' => 'ok',
            'data' => $rows
        ];
        return response()->json($data, options: JSON_UNESCAPED_UNICODE);
    }
    public function store(StoreTaskRequest $request)
    {
        $row = Task::create($request->all());
        $data = [
            'message' => 'ok',
            'data' => $row
        ];
        return response()->json($data, options: JSON_UNESCAPED_UNICODE);
    }

    public function filmPeopleRoles($filmId)
    {
        //natív SQL
        $query =
           'SELECT f.title, p.name, r.role, f.id filmId, r.id roleId, p.id peopleId from tasks t
            JOIN people p ON t.personId = p.id
            JOIN films f  ON t.filmId = f.id
            JOIN roles r  ON t.roleId = r.id
            where f.id = ?
            ORDER BY p.name
           ';
        try {
            //code...
            $rows = DB::select($query, [$filmId]);

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

    public function show(int $id)
    {
        $row = Task::find($id);

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
    public function update(UpdateTaskRequest $request,  $id)
    {
        //Keresd meg az adott product-ot
        $row = Task::find($id);
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
        $row = Task::find($id);
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
