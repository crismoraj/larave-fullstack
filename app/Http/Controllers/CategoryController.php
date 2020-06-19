<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;


class CategoryController extends Controller{

    public function __construct(){
        $this->middleware('api.auth', ['except' => ['index','show']]);
    }

    public function index(Request $request){

        $categories = Category::all();

        return response()->json([
            'code'      => 200,
            'status'    => 'success',
            'categories'=> $categories
        ]);
    }

    public function show($id){

        $category = Category::find($id);

        if(is_object($category)){
            $data = array(
                'code'      => 200,
                'status'    => 'success',
                'category'=> $category
            );
        }else{
            $data = array(
                'code' => 404,
                'message' => 'La categoria no existe',
                'status' => 'error'
            );
        }
        return response()->json($data,$data['code']);
    }

    public function store(Request $request){
        
        ##recojer los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if(!empty($params_array)){
            ##validar los datos
            $validate = \Validator::make($params_array, [
                'name' => 'required'
            ]);

            ##guardar la categoria
            if($validate->fails()){
                ##la validacion ha fallado
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Error validando los datos',
                    'errors' => $validate->errors()
                );
            }else{

                $category = new category();
                $category->name = $params_array['name'];
                $category->save();
                
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Categoria guardada',
                    'category' => $category
                );
            }
        }else{
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'No se ha enviado nombre de la categoria'
            );
        }
        ##devolver resultado
        return response()->json($data,$data['code']);
    }

    public function update($id, Request $request){

        ##recojer los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if(!empty($params_array)){
            ##validar los datos
            $validate = \Validator::make($params_array, [
                'name' => 'required'
            ]);

            unset($params_array['id']);
            unset($params_array['created_at']);

            ##guardar la categoria
            if($validate->fails()){
                ##la validacion ha fallado
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Error validando los datos en update',
                    'errors' => $validate->errors()
                );
            }else{

                //Actualizar el registro categoria
                $category = Category::where('id', $id)->update($params_array);

                
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Categoria Actualizada',
                    'category' => $category
                );
            }
        }else{
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'No has enviado el nombre de la categoria'
            );
        }
        ##devolver resultado
        return response()->json($data,$data['code']);

    }
}
