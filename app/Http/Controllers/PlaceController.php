<?php

namespace App\Http\Controllers;

use App\Place;
use Illuminate\Http\Request;
use \Firebase\JWT\JWT;
use App\User;


class PlaceController extends Controller
{
    public function index()
    {

        $headers = getallheaders();
        $token = $headers['Authorization'];
        if (empty($token)) {
            return $this->createResponse(400, "No estás conectado");
        }
        $key = $this->key;
        $userData = JWT::decode($token, $key, array('HS256'));
        $id = User::where('email', $userData->email)->first()->id;
        $places = Place::where('user_id', $id)->get();
        if ($places->isEmpty()) { 
            return $this->createResponse(400, "No hay lugares guardados.");
        }
        $placeNames = [];
        $placeIDs = [];
        foreach ($places as $place) {
            array_push($placeNames, $place->name);
            array_push($placeIDs, $place->id);
            } 
        return $this->createResponse(200, "Lugares", $places);
    }

     public function store(Request $request)
     {   
        $headers = getallheaders();
        $token = $headers['Authorization'];
        if (empty($token)) {
            return $this->createResponse(400, "No estás conectado");
        }
        $key = $this->key;
        $userData = JWT::decode($token, $key, array('HS256'));
        $placeName = $_POST['placeName'];
        $xCoord = $_POST['xCoordinate'];
        $yCoord = $_POST['yCoordinate'];
        $initialDate = $_POST['initialDate'];
        $endDate = $_POST['endDate'];
        $description = $_POST['description'];
        $id = User::where('email', $userData->email)->first()->id;
        $places = Place::where('user_id', $id)->get();
        foreach ($places as $place) 
        {
            if ($place->name == $placeName) 
            {
                return $this->createResponse(400, 'El nombre del lugar ya existe'); 
            }
        }

        if (!preg_match("/^[a-zA-Z ]*$/",$placeName)) {
            return $this->createResponse(400, 'El nombre del lugar solo puede contener caracteres sin espacios en blanco'); 
        }

        if (empty($placeName)) {
            return $this->createResponse(400, 'Tienes que introducir un nombre para el lugar');
        } 

        if (empty($description)) {
            return $this->createResponse(400, 'Tienes que introducir una descripción para el lugar');
        }
        if (empty($initialDate) || empty($endDate)) {
            return $this->createResponse(400, 'Tienes que introducir ambas fechas');
        } 
        if (empty($yCoord) || empty($xCoord)) {
            return $this->createResponse(400, 'Tienes que introducir ambas coordenadas');
        }

        if ($this->checkLogin($userData->email , $userData->password)) 
        { 
            $place = new Place();
            $place->name = $placeName;
            $place->x_coordinate = $xCoord;
            $place->y_coordinate = $yCoord;
            $place->initial_date = $initialDate;
            $place->end_date = $endDate;
            $place->user_id = $id;
            $place->description = $description;
            $place->save();

            return $this->createResponse(200,'Lugar creado', $place);

        }
        else
        {
            return $this->createResponse(401, "No tienes permisos");
        }
     }

    public function deletePlace()
    {
        $headers = getallheaders();
        $token = $headers['Authorization'];
        if (empty($token)) {
            return $this->createResponse(400, "No estás conectado");
        }
        $key = $this->key;
        $userData = JWT::decode($token, $key, array('HS256'));
        $id_user = User::where('email', $userData->email)->first()->id;
        $id_place = $_POST['idPlace'];
        $id = $id_place;
        $place = Place::find($id);
        if ($userData->rol != 1 && $userData->id != $place->user_id) {
            return $this->createResponse(401, 'No tienes permiso');
        }
        if (empty($id_place)) {
            return $this->createResponse(400, 'Indica la id del lugar');
        }

        if (is_null($place)) {
            return $this->createResponse(400, 'El lugar no existe');
        }else{
            $place_name = Place::where('id', $id_place)->first()->name;
            Place::destroy($id);

        return $this->createResponse(200, 'Lugar Borrado', $place_name);
        }
    }

    public function updatePlace()
    {
        $headers = getallheaders();
        $token = $headers['Authorization'];
        if (empty($token)) {
            return $this->createResponse(400, "No estás conectado");
        }
        $key = $this->key;
        $userData = JWT::decode($token, $key, array('HS256'));
        $id_place = $_POST['idPlace'];
        $newName = $_POST['newName'];
        $newX = $_POST['newXCoord'];
        $newY = $_POST['newYCoord'];
        $newInitial = $_POST['newInitial'];
        $newEnd = $_POST['newEnd'];
        $newDesc = $_POST['newDescription'];
        $id = $id_place;
        $place = Place::find($id);
        if ($userData->rol != 1 && $userData->id != $place->user_id) {
            return $this->createResponse(401, 'No tienes permiso');
        }
        if (is_null($place)) {
            return $this->createResponse(400, 'El lugar no existe');
        }

        if (empty($newName)) {
            return $this->createResponse(400, 'Tienes que introducir un nombre para el lugar');
        } 
        if (empty($newInitial) || empty($newEnd)) {
            return $this->createResponse(400, 'Tienes que introducir ambas fechas');
        } 
        if (empty($newDesc)) {
            return $this->createResponse(400, 'Tienes que introducir una descripción para el lugar');
        }

        if (!empty($_POST['newDescription'])) {
            $place->description = $newDesc;
        }
        if (!empty($_POST['newName']) ) {
            $place->name = $newName;
        }
        if (!empty($_POST['newXCoord']) ) {
            $place->x_coordinate = $newX;
        }
        if (!empty($_POST['newYCoord']) ) {
            $place->y_coordinate = $newY;
        }             
        if (!empty($_POST['newInitial']) ) {
            $place->initial_date = $newInitial;
        }
        if (!empty($_POST['newEnd']) ) {
            $place->end_date = $newEnd;
        }           
            $place->save();
        return $this->createResponse(200, 'Lugar Actualizado', $place);
        

    }

    public function get_place()
    {
        $headers = getallheaders();
        $token = $headers['Authorization'];
        $key = $this->key;
        if (!isset($_GET['id'])) {
             return $this->createResponse(400, 'el Parametro id es obligatorio');
        }
        $id = $_GET['id'];
        try {
            $place = Place::find($id);
            if ($place == null) {
                return $this->createResponse(400, 'No existe el evento');

            }

        return $this->createResponse(200, 'Datos del lugar', $place);
            
        } catch (Exception $e) {
            
            return $this->crateResponse(500, $e->getMessage());
        }
    }

}
