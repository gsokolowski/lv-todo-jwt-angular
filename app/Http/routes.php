<?php
/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
    //return $environment = App::environment();
});

Route::get('users', 'TokenAuthController@index');

Route::post('api/register', 'TokenAuthController@register');
Route::post('api/authenticate', 'TokenAuthController@authenticate');
Route::get('api/authenticate/user', 'TokenAuthController@getAuthenticatedUser');

// resource generates all get insert delete update - list is below
Route::resource('api/todo', 'TodoController');



/**
php artisan route:list

Method    | URI                   | Name             | Action                                                        | Middleware |
+--------+-----------+-----------------------+------------------+---------------------------------------------------------------+------------+
|        | GET|HEAD  | /                     |                  | Closure                                                       |            |
|        | POST      | api/authenticate      |                  | App\Http\Controllers\TokenAuthController@authenticate         |            |
|        | GET|HEAD  | api/authenticate/user |                  | App\Http\Controllers\TokenAuthController@getAuthenticatedUser |            |
|        | POST      | api/register          |                  | App\Http\Controllers\TokenAuthController@register             |            |
|        | POST      | api/todo              | api.todo.store   | App\Http\Controllers\TodoController@store                     | jwt.auth   |
|        | GET|HEAD  | api/todo              | api.todo.index   | App\Http\Controllers\TodoController@index                     |            |
|        | GET|HEAD  | api/todo/create       | api.todo.create  | App\Http\Controllers\TodoController@create                    | jwt.auth   |
|        | DELETE    | api/todo/{todo}       | api.todo.destroy | App\Http\Controllers\TodoController@destroy                   | jwt.auth   |
|        | PUT|PATCH | api/todo/{todo}       | api.todo.update  | App\Http\Controllers\TodoController@update                    | jwt.auth   |
|        | GET|HEAD  | api/todo/{todo}       | api.todo.show    | App\Http\Controllers\TodoController@show                      | jwt.auth   |
|        | GET|HEAD  | api/todo/{todo}/edit  | api.todo.edit    | App\Http\Controllers\TodoController@edit                      | jwt.auth   |
|        | GET|HEAD  | users                 |                  | App\Http\Controllers\TokenAuthController@index                |            |


 */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
    //
});