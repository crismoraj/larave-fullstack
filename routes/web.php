<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


##cargando clases
use App\Http\Middleware\ApiAuthMiddleware;


##Rutas de pruebas
Route::get('/', function () {
    return view('welcome');
});

Route::get('/pruebas/{var?}', function ($var = null) {
       if($var == null){
          $var = 'Inicio'; 
       }
       
       $title = '<h1>'.$var.'</h1>';
       $description = 'Texto desde una ruta';
       $text = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec vel libero fringilla turpis sodales eleifend id sed dui. Curabitur auctor vehicula sapien, vel faucibus velit fringilla sit amet. Integer at eros sed augue ultrices cursus. Suspendisse sagittis risus ut luctus fringilla. Pellentesque commodo fringilla arcu vitae placerat. Sed feugiat est quis metus laoreet dictum. Ut metus dui, consectetur in tellus ut, faucibus accumsan tortor. Sed sapien nibh, mollis eu euismod sit amet, imperdiet vitae nulla. Proin quis arcu et turpis suscipit finibus. Maecenas congue tincidunt massa sit amet scelerisque. Donec tempor sem metus, quis tincidunt nisl cursus ut. Mauris posuere malesuada magna, sed facilisis augue eleifend vel. Nam euismod pharetra faucibus. Mauris ac dolor vitae est tempor condimentum.';
    return view('pruebas',array(
        'title' => $title,
        'description' => $description,
        'text' => $text
    ));
});

Route::get('/animales','PruebasController@index');
Route::get('/test-orm','PruebasController@testOrm');

/**************************************************************
*************       METODOS HTTP COMUNES      *****************
***************************************************************

    * GET  ->  Conseguir datos o recursos
    * POST ->  Guardar datos o recursos o hacer logica desde un formulario
    * PUT  ->  Actualizar recursos o datos
    * DELETE-> Eliminar datos o recursos

***************************************************************
***************************************************************
***************************************************************/

##RUTAS DEL API

    //rutas de pruebas
    //Route::get('/usuario/pruebas','UserController@pruebas');
    //Route::get('/categoria/pruebas','CategoryController@pruebas');
    //Route::get('/entrada/pruebas','PostController@pruebas');

    //rutas de controlador de usuarios
    Route::post('/api/register','UserController@register');
    Route::post('/api/login','UserController@login');
    Route::put('/api/user/update','UserController@update');
    Route::post('/api/user/upload', 'UserController@upload')->middleware(ApiAuthMiddleware::class);
    Route::get('/api/user/avatar/{fileName}','UserController@getImage');
    Route::get('/api/user/detail/{id}','UserController@detail');

    //rutas de controlador de categorias
    Route::resource('/api/category', 'CategoryController');

    //rutas de controlador de post
    Route::resource('/api/post', 'PostController');

    //subir imagenes
    Route::post('/api/post/upload','PostController@upload');

    //obtener imagen
    Route::get('/api/post/image/{file_name}','PostController@getImage');

    //Obtener posts por categoria
    Route::get('/api/post/category/{id}','PostController@getPostsByCategory');

    //Obtener posts por usuario
    Route::get('/api/post/user/{id}','PostController@getPostsByUser');