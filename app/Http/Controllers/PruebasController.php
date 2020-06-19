<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\User;
use App\Category;


class PruebasController extends Controller
{
    public function index(){
        $titulo = 'Animales';
        $animales = ['perro','gato','vaca'];
        return view('pruebas.index', array(
            'titulo' => $titulo,
            'animales' => $animales
        ));
    }
    
    public function testOrm(){

       /// $posts = Post::all();
        
        

        $categories = Category::all();
        
        foreach ($categories as $category){
            echo "<h1>{$category->name}</h1>";
            foreach ($category->posts as $post){
                echo "<h3>".$post->title."</h3>";
                echo "<span style='color:#999;'>{$post->user->name} - {$post->category->name}</span>";
                echo "<p>".$post->content."</p>";
               
            }
            echo "<hr />";
        }
        

        die();
    }
}
