<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;

class UserController extends Controller
{
    public function pruebas(Request $request){
        return "Pruebillas User";
    }

    public function register(Request $request){
        
        ##REcojer los datos por post
        /*$name = $request -> input('name');
        $surname = $request -> input('surname');
        $user = $request -> input('user');
        $password = $request -> input('password');*/
        $json = $request->input('json',null);
        $params = json_decode($json);
        $params_array = json_decode($json,true);

        if(!empty($params_array)){

            ##limpiamos datos
            $params_array = array_map('trim',$params_array);

            ##validar datos y Validar si el usuario ya existe (unique:users)
            $validate = \Validator::make($params_array,[
                'name'    => 'required|alpha',
                'surname' => 'required|alpha',
                'email'   => 'required|email|unique:users',
                'password'=> 'required',
            ]);

        

            if($validate->fails()){
                ##la validacion ha fallado
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'El usuario no se ha creado, valida los datos',
                    'errors' => $validate->errors()
                );
            }else{

                ##cifrar la contraseña
                $pwd = hash('sha256',$params_array['password']);

                ##Crear el usuario
                $user =  new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';
                

                ##Guardamos datos
                $user->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario se ha guardado correctamente',
                    'user' => $user
                );

            }
        }else{
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'No se enviaron datos correctos'
            );
        }
        return response() -> json($data,$data['code']);
    }

    public function login(Request $request){

        $jwtAuth = new \JwtAuth();

        ##recibimos datos por post
        $json = $request->input('json',null);

        $params = json_decode($json);
        $params_array = json_decode($json,true);

        ##validamos los datos
        $validate = \Validator::make($params_array,[
            'email'   => 'required|email',
            'password'=> 'required',
        ]);

    

        if($validate->fails()){
            ##la validacion ha fallado
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'El usuario no se ha podido identificar',
                'errors' => $validate->errors()
            );
        }else{
            ##ciframos la contraseña
            $pwd =  hash('sha256',$params_array['password']);
            ##devolver token o datos
            $data = $jwtAuth->signout($params_array['email'], $pwd);

            if(!empty($params_array['gettoken'])){
                $data = $jwtAuth->signout($params_array['email'], $pwd, true);
            }
        }

      

        ##devolver token o datos

        /*
        $email = 'crismoraj@gmail.com';
        $password = 'bebehmoso6';
        $pwd =  hash('sha256',$password);
        */
        //var_dump($pwd);
        //die();
        return response()->json($data, 200);
    }

    public function update(Request $request){

        ##comprobamos que el usaurio está identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

         ##recojemos los datos por POST
         $json =  $request->input('json',null);
         $params_array = json_decode($json,true);

        if($checkToken && !empty($params_array)){

           

            ##sacamos a usuario identificado
            $user = $jwtAuth->checkToken($token,true);

            ##validamos los datos
            $validate = \Validator::make($params_array,[
                'name'    => 'required|alpha',
                'surname' => 'required|alpha',
                'email'   => 'required|email|unique:users,'.$user->sub
            ]);

            ##quitar los datos que no voy a actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            ##Actualizar usuario ne BD
            $user_update = User::where('id',$user->sub)->update($params_array);

            ##Devolver array con resultado
            $data = array(
                'status' => 'success',
                'code' => 200,
                'user' => $user,
                'changes' => $params_array,
                'message' => 'El usuario se ha actualizado correctamente'
            );
        }else{
            ##error
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'El usuario no está identificado correctamente'
            );
        }
        return response()->json($data,$data['code']);
    }

    public function upload(Request $request){

        ##recojer los datos de la petición
        $image = $request->file('file0');

        ##Validamos que sea una imagen
        $validator = \Validator::make($request->all(),[
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        if(!$image || $validator->fails()){

             ##error
             $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Falta ingresar una imagen valida'
            );

        }else{
            ##guardar imagen
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));

            $data = array(
                'image' => $image_name,
                'code' => 200,
                'message' => 'Se subio la imagen',
                'status' => 'success'
            );
        }

        //return response($data, $data['code'])->header('Content-Type', 'text/plain');
        return response()->json($data,$data['code']);
    }

    public function getImage($fileName){
        $isset = \Storage::disk('users')->exists($fileName);
        if($isset){
            $file = \Storage::disk('users')->get($fileName);
            return new Response($file, 200);
        }else{
            $data = array(
                'code' => 404,
                'message' => 'No existe la imagen',
                'status' => 'error'
            );
            return response()->json($data,$data['code']);
        }
    }

    public function detail($id){
        $user = User::find($id);

        if(is_object($user)){
            $data = array(
                'code' => 200,
                'user' => $user,
                'status' => 'success'
            );
        }else{
            $data = array(
                'code' => 404,
                'message' => 'El usuario no existe',
                'status' => 'error'
            );
        }
        return response()->json($data,$data['code']);
    }
}
