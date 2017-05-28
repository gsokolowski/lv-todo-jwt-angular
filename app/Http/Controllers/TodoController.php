<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Todo;
use Tymon\JWTAuth\Facades\JWTAuth as JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use DateTime;


class TodoController extends Controller
{

    public function __construct()
    {
        // Apply the jwt.auth middleware to all methods in this controller
        // except for the authenticate method. We don't want to prevent
        // the user from retrieving their token if they don't already have it
        $this->middleware('jwt.auth', ['except' => ['index']]);

        // create a log channel
        $log = new Logger('name');
        $log->pushHandler(new StreamHandler('storage/logs/app.log', Logger::WARNING));

        // add records to the log
        $log->addWarning('todo rest called');
    }

    // Show all per token (user)
    // GET http://localhost:8000/api/todo?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjMsImlzcyI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC9hcGlcL2F1dGhlbnRpY2F0ZSIsImlhdCI6MTQ5NTk4NjEyNSwiZXhwIjoxNDk1OTg5NzI1LCJuYmYiOjE0OTU5ODYxMjUsImp0aSI6InNSOFBYUzNnaU9DUTl1SHIifQ.NGp-gi9d3WLPdL2ci6c2IhoVfhHsLVcCeYmKI-gMf2g
    public function index()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            } else {
                // PDO mode --------------------------------
                // $todos is an array of objects (each row is separate object)
                // get all todas for user
                //$todos = DB::select('select * from todos where id < ?', [37]);

                // DB
                $todos = DB::select('select * from todos');

                // Laravel
                //$todos = Todo::where('owner_id', $user->id)->get();

                return response()->json(compact('todos'));
            }
        } catch (TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }
    }

    // GET|HEAD  | api/todo/{todo}       | api.todo.show    | App\Http\Controllers\TodoController@show
    // GET http://localhost:8000/api/todo/2?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjMsImlzcyI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC9hcGlcL2F1dGhlbnRpY2F0ZSIsImlhdCI6MTQ5NTk4OTgwMSwiZXhwIjoxNDk2MDI1ODAxLCJuYmYiOjE0OTU5ODk4MDEsImp0aSI6IlFJSGF0NFM1U0huM1VoWGEifQ.sKO7uz8aQ1rz4T7qhvZXE5_nETfF9hxqbpP5fcesY1s
    public function show($id) {
        $user = JWTAuth::parseToken()->authenticate();

        // todos table not todo

        // DB - works
        // $todo = DB::select('select * from todos where id = ?', [$id]);

        // PDO - works
        $pdo = DB::connection()->getPdo();
        $sql = "select * from todos where id = ?";
        $query = $pdo->prepare($sql);
        $query->execute(array($id)); // need to be an array
        $todo = $query->fetchAll();

        // laravel - works
        // $todo = Todo::where('owner_id', $user->id)->where('id',$id)->first();

        return response()->json(compact('todo'));

    }


    // POST      | api/todo              | api.todo.store   | App\Http\Controllers\TodoController@store
    // POST http://localhost:8000/api/todo?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjMsImlzcyI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC9hcGlcL2F1dGhlbnRpY2F0ZSIsImlhdCI6MTQ5NTk4OTgwMSwiZXhwIjoxNDk2MDI1ODAxLCJuYmYiOjE0OTU5ODk4MDEsImp0aSI6IlFJSGF0NFM1U0huM1VoWGEifQ.sKO7uz8aQ1rz4T7qhvZXE5_nETfF9hxqbpP5fcesY1s
    // Payload
    // x-www-form-encoded {'description': 'Zrob relations na Eloquent', 'is_done': 0}
    // owner_id comes from token
    public function store(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $now = new DateTime();
        $dateTime =  $now->format("Y-m-d H:i:s");


        // DB query builder - works
//        $data=array(
//            'description' => $request->description,
//            'owner_id' => $user->id,
//            'is_done' => $request->is_done,
//            'created_at' => $dateTime,
//            'updated_at' => $dateTime
//        );
//        $response = DB::table('todos')->insert($data);

        //$response = DB::insert('insert into numbers (number, created_at, updated_at ) values (?, ?, ?)', [$numberToAdd, $dateTime, $dateTime]);


        // PDO - works
        $pdo = DB::connection()->getPdo();
        $description = $request->description;
        $owner_id = $user->id;
        $is_done = $request->is_done;
        $created_at = $dateTime;
        $updated_at = $dateTime;

        // todos table not todo
        $sql = "insert into todos (description, owner_id, is_done, created_at, updated_at ) values (?, ?, ?, ?,?)";
        $query = $pdo->prepare($sql);
        $response = $query->execute(array($description, $owner_id, $is_done, $created_at, $updated_at));


        // laravel
        // get all passed parameters
//         $newTodo= $request->all();
//         $newTodo['owner_id']=$user->id;
//         $response = Todo::create($newTodo);

        if ($response) {
            return response()->json(['todo created'], 201);
        } else {
            return response()->json(['todo not created'], 500);
        }
    }

    // PUT update all columns
    // PATCH update only a part
    // PUT | api/todo/{todo}       | api.todo.update  | App\Http\Controllers\TodoController@update
    public function update(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $now = new DateTime();
        $dateTime =  $now->format("Y-m-d H:i:s");


        // DB query builder - works
//        $data=array(
//            'description' => $request->description,
//            'owner_id' => $user->id,
//            'is_done' => $request->is_done,
//            'updated_at' => $dateTime
//        );
//        $response = DB::table('todos')
//            ->where('id', $id)
//            ->update($data);


        // PDO - works
        $pdo = DB::connection()->getPdo();
        $description = $request->description;
        $owner_id = $user->id;
        $is_done = $request->is_done;
        $updated_at = $dateTime;

        // todos table not todo
        $sql = "update todos SET description = ?, owner_id = ?, is_done = ?,  updated_at = ? WHERE id = ?";
        $query = $pdo->prepare($sql);
        $response = $query->execute(array($description, $owner_id, $is_done, $updated_at, $id));


        // laravel
//        $todo = Todo::where('owner_id', $user->id)->where('id',$id)->first();
//        $todo->description=$request->description;
//        $todo->owner_id = $user->id;
//        $todo->is_done=$request->is_done;
//        $todo->updated_at=$dateTime;
//        $response = $todo->save();
        //var_dump($response);

        if($response){
            return response('Resource updated successfully',204);
        } else {
            return response('Unauthoraized',403);
        }
    }
    // works
    public function destroy($id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // DB query builder - works
//        $response = DB::table('todos')
//            ->where('id', $id)
//            ->delete();

        // PDO - works
        $pdo = DB::connection()->getPdo();
        // todos table not todo
        $sql = "delete from todos WHERE id = ? limit 1";
        $query = $pdo->prepare($sql);
        $response = $query->execute(array($id));

        // laravel
//        $todo = Todo::where('owner_id', $user->id)->where('id',$id)->first();
//        $response = Todo::destroy($todo->id);

        if($response){
            return  response('Success',204);
        }else{
            return response('Unauthoraized',403);
        }
    }
}