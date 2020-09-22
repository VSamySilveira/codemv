<?php
use App\Models\Category\Api;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace' => 'Api', 'as' => 'api.'], function() {
    $exceptRoutes = ['except' => ['create','edit']];
    Route::resource('categories', 'CategoryController', $exceptRoutes);
    Route::resource('genres', 'GenreController', $exceptRoutes);
    Route::resource('cast_members', 'CastMemberController', $exceptRoutes);
    Route::resource('videos', 'VideoController', $exceptRoutes);
});
