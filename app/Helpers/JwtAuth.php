<?php

namespace App\Helpers;

use Firebase\JWT\JWT;

use Illuminate\Support\Facades\DB;

use App\User;

class JwtAuth{

    public $key;

    public function __construct(){
        $this->key = 'la_vaca_l0l4_la_vaca_l0l4_tiene_cabeza_y_tiene_c0l4';
    }

    public function signout($email, $password, $getToken = null){

        ##Buscar si el usuario existe con sus credenciales (El metodo fist es para sacar el primer dato)
        $user = User::Where([
            'email' => $email,
            'password' => $password
        ])->first();
    
        
        ##Comprobar si son correctas (objeto)
        $signout = false;

        if(is_object($user)){
            $signout = true;
        }

        ##Generar token con usuario identificado
        if($signout){
            $token = array(
                'sub'       =>  $user->id,
                'email'     =>  $user->email,
                'name'      =>  $user->name,
                'surname'   =>  $user->surname,
                'role'      =>  $user->role,
                'iat'       =>  time(),
                'exp'       =>  time() + (60 * 60 * 24 * 7)
            );

            ##HS256 es el algoritmo de codificacion
            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);

             ##Devolver los datos decodificados o el token, en funcion de un parametro
            if(is_null($getToken)){
                $data = $jwt; 
            }else{
                $data = $decoded;
            }
            

        }else{
            $data = array(
                'status' => 'error',
                'messsage' => 'Login Incorrecto'
            );
        }

        return $data;

    }

    public function checkToken($jwt, $getIdentity = false){
        $auth = false;

        try{
            $jwt = str_replace('"','',$jwt);
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        }catch(\UnespectedValueException $e){
            $auth = false;
        }catch(\DomainException $e){
            $auth = false;
        }

        if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){
            $auth = true;
        }else{
            $auth = false;
        }

        if($getIdentity){
            return $decoded;
        }

        return $auth;
        
    }

}