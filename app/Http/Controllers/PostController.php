<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Helpers\JwtAuth;

class PostController extends Controller{
    
    public function __construct(){
        $this->middleware('api.auth', ['except' => [
            'index',
            'show',
            'getImage',
            'getPostsByCategory',
            'getPostsByUser'
        ]]);
    }

    public function index(Request $request){
        $posts = Post::all()->load('Category');

        return response()->json([
            'code'      => 200,
            'status'    => 'success',
            'posts'=> $posts
        ], 200);
    }

    public function show($id){

        $post = Post::find($id)->load('Category');

        if(is_object($post)){
            $data = array(
                'code'      => 200,
                'status'    => 'success',
                'category'=> $post
            );
        }else{
            $data = array(
                'code' => 404,
                'message' => 'La entrada no existe',
                'status' => 'error'
            );
        }
        return response()->json($data,$data['code']);
    }

    public function store(Request $request){
        ##Recojemos datos por post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if(!empty($params_array)){
            ##Conseguir usuario identificado
            $user = $this->getIdentity($request);

            ##validar los datos
            $validate = \Validator::make($params_array, [
                'title'         => 'required',
                'content'       => 'required',
                'category_id'   => 'required',
                'image'   => 'required'
            ]);

            if($validate->fails()){
                $data = array(
                    'code' => 400,
                    'message' => 'Faltan datos requeridos, no se ha guardado el post',
                    'status' => 'error'
                );
            }else{
                ##Guardar el articulo(post)
                $post = new Post();
                $post->user_id = $user->sub;
                $post->title = $params_array['title'];
                $post->content = $params_array['content'];
                $post->category_id = $params_array['category_id'];
                $post->image = $params_array['image'];
                $post->save();
                
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Post guardado',
                    'post' => $post
                );
                
            }
            
        }else{
            $data = array(
                'code' => 400,
                'message' => 'Envia los datos correctamente',
                'status' => 'error'
            );
        }
        ##devolver resultado
        return response()->json($data,$data['code']);
    }

    public function update($id, Request $request){
        ##Recojer los adtos que llegan por post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        

        if(!empty($params_array)){
            ##validar datos
            $validate = \Validator::make($params_array,[
                'title'         => 'required',
                'content'       => 'required',
                'category_id'   => 'required'
            ]);

            if($validate->fails()){
                 ##devolver respuesta
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => $validate->errors()
                );
            }else{
                ##Eliminar los datos que no queremos actualizar
                unset($params_array['id']);
                unset($params_array['user_id']);
                unset($params_array['created_at']);
                unset($params_array['user']);

                ##Conseguir usuario identificado
                $user = $this->getIdentity($request);

                ##Actualizar el registro en concreto
                if($user->role == 'ROLE_ADMIN'){
                   // $where = ['id' => $id];
                    $post = Post::where([
                        'id' => $id
                        ])->first();
                }else{
                    $post = Post::where([
                        'id' => $id,
                        'user_id' => $user->sub
                        ])->first();
                   /* $where = [
                        'id'        => $id,
                        'user_id'   => $user->sub
                    ];*/
                }


                if(!empty($post) && is_object($post)){

                    $post->update($params_array);

                    ##devolver respuesta
                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Post Actualizado',
                        'changes' => $params_array,
                        'post' => $post
                    );
                    
                }else{
                     ##devolver respuesta
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'No se puede actualizar'
                    );
                }

                
            }
        }else{

             ##devolver respuesta
             $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Faltan datos a enviar'
            );
        }
        ##devolver resultado
        return response()->json($data,$data['code']);
    }
    
    public function destroy($id, Request $request){
        ##Conseguir usuario identificado
        $user = $this->getIdentity($request);

        ##Conseguir el registo
        if($user->role == 'ROLE_ADMIN'){
            $post = Post::where('id', $id)->first();
        }else{
            $post = Post::where([
                'id' => $id,
                'user_id' => $user->sub
                ])->first();
        }
        if(!empty($post)){
            ##Borrarlo
            $post->delete();

            ##devolver respuesta
            $data = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'Post eliminado',
                'post' => $post
            );
        }else{
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El post no existe'
            );
        }
        return response()->json($data,$data['code']);

    }

    private function getIdentity($request){
         ##Conseguir usuario identificado
         $jwtAuth = new JwtAuth();
         $token = $request->header('Authorization', null);
         $user = $jwtAuth->checkToken($token, true);
         return $user;
    }

    public function upload(Request $request){
        ##Recojer la imagen de la peticion
        $image = $request->file('file0');

        ##validar imagen
        $validate = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        ##guardar la imagen
        if(!$image || $validate->fails()){
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Imagen no existe o no tiene el formato aceptado'
            );
        }else{

            $image_name = time().$image->getClientOriginalName();

            \Storage::disk('images')->put($image_name, \File::get($image));

            $data = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'Imagen agregada',
                'image' => $image_name
            );
        }

        ##devoler dato
        return response()->json($data,$data['code']);     
   }

   public function getImage($file_name){
        ##Comprovar si existe le fichero
        $isset = \Storage::disk('images')->exists($file_name);
       
        if($isset){

            ##conseguir imagen
            $file = \Storage::disk('images')->get($file_name);
            ##devolver imagen
            return new Response($file, 200);
        }else{
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Imagen no existe'
            );
            return response()->json($data,$data['code']);
        }
       

    
   }

   public function getPostsByCategory($id){
        $posts = Post::where('category_id', $id)->get();
        if(!empty($posts)){
            $data = array(
                'status' => 'success',
                'code' => 200,
                'posts' => $posts
            );
        }else{
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Categoria no tiene posts'
            );
        }
        return response()->json($data,$data['code']);
    }

    public function getPostsByUser($id){
        $posts = Post::where('user_id', $id)->get();
        if(!empty($posts)){
            $data = array(
                'status' => 'success',
                'code' => 200,
                'posts' => $posts
            );
        }else{
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'El usuario no tiene posts'
            );
        }
        return response()->json($data,$data['code']);
    }

}
