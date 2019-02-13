<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use \Firebase\JWT\JWT;
use App\Role;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function register (Request $request)
    {
        if (!isset($_POST['name']) or !isset($_POST['email']) or !isset($_POST['password'])) 
        {
            return $this->createResponse(401, 'Debes rellenar todos los campos');
        }

       

        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $users = User::where('email', $email)->get();
        foreach ($users as $user) 
        {
            if ($user->email == $email) 
            {
                return $this->createResponse(400, 'El email ya existe'); 
            }
        }

        if (preg_match('/\s/',$name)){
            return $this->createResponse(400, 'El nombre no puede contener espacios en blanco');
        }
        

        if (!preg_match("/^[a-zA-Z ]*$/",$name)) {
            return $this->createResponse(400, 'El nombre solo puede contener caracteres sin espacios en blanco'); 
        } 

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->createResponse(400, 'Introduzca un email valido'); 
        }

        if (strlen($password) < 8){
            return $this->createResponse(400, 'La contraseña debe tener al  menos 8 caracteres');
        }

    
        
    

        if (!empty($name) && !empty($email) && !empty($password))
        {
            try
            {
                $users = new User();
                $users->name = $name;
                $users->password = encrypt($password);
                $users->email = $email;
                $users->status = 1;
                $users->role_id = 2;

                $users->save();
            }
            catch(Exception $e)
            {
                return $this->createResponse(2, $e->getMessage());
            }
            
            return $this->createResponse(200, 'Usuario registrado correctamente', $users);
        }
        else
        {
            return $this->createResponse(401, 'Debes rellenar todos los campos');
        }
    }

    public function post_insertUser()
    {
        $headers = getallheaders();
        $token = $headers['Authorization'];
        $key = $this->key;
        $userData = JWT::decode($token, $key, array('HS256'));
        $id_user = $userData->id;
        $user = User::find($id_user);
        if ($user->id !== 1) {
            return $this->error(401, 'No tienes permiso');
        } 
        if (empty($_POST['email']) || empty($_POST['id_rol']) || empty($_POST['name'])) {
            return $this->createResponse(400, 'Falta parametro email, id_rol, name');
        }
        if (!preg_match("/^[a-zA-Z ]*$/",$_POST['name'])) {
            return $this->createResponse(400, 'El nombre solo puede contener caracteres sin espacios en blanco'); 
        } 

        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->createResponse(400, 'Introduzca un email valido'); 
        }



        $email = $_POST['email'];
        $role_id = $_POST['id_rol'];
        $name = $_POST['name'];

        try {

            $userDB = User::where('email', $email)->first();

            if ($userDB != null) {
                return $this->createResponse(400, 'El email ya existe');
            }

            $newUser = new User();
            $newUser->email = $email;
            $newUser->role_id = $role_id;
            $newUser->password = encrypt("temporal");
            $newUser->name = $name;
            $newUser->status = 1;

            $newUser->save();


            
            return $this->createResponse(200, 'Usuario insertado con exito');

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }
    
    public function login(Request $request)
    {
        $email = $_POST['email'];
        $password = $_POST['password'];
        

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->createResponse(401, 'Introduzca un email válido'); 
        }

        if (empty($email)) {
            return $this->createResponse(401, 'Introduce un email');
        }
        if (empty($password)) {
            return $this->createResponse(401, 'Introduce el password');
        }


        $userDecrypt = User::where('email', $email)->first();
        $passwordHold = $userDecrypt->password;
        $key = $this->key;
        if (self::checkLogin($email, $password))
        {
            $decryptedPassword = decrypt($passwordHold);
            $userSave = User::where('email', $email)->first();


            $array = $arrayName = array
            (
                'id' => $userSave->id,
                'email' => $email,
                'password' => $password,
                'name' => $userSave->name,
                'rol' => $userSave->role_id
            );
            $token= JWT::encode($array, $key);
            return $this->createResponse(200, 'login correcto', ['token'=>$token, 'user' => $userSave]);

        }
        else
        {
            return $this->createResponse(401, "Los datos no son correctos");
        }
    }

    public function post_recover(Request $request)
    {
        if (!isset($_POST['email'])) 
        {
            return $this->createResponse(401, 'Introduzca su email');
        }    
        $email = $_POST['email'];
        if (self::recoverPassword($email)) {
            $userRecover = User::where('email', $email)->first();
            $id = $userRecover->id;
            $pwdDB = User::where('email', $userRecover->email)->first()->password;
            $pwdDecrypted = decrypt($pwdDB);
            $dataEmail = array(
                'pwd' => $pwdDecrypted,
            );
            Mail::send('emails.welcome', $dataEmail, function($message){
                $emailRecipient = $_POST['email'];
                $message->from('proyectogpass@gmail.com', 'Recuperación contraseña');
                $message->to($emailRecipient)->subject('Recuperación contraseña');
            });
            return $this->createResponse(200, "Contraseña Enviada", $email);


        }
        else
        {
            return createResponse(403, "Los datos no son correctos");
        }

    }

    public function post_update()
    {
        $headers = getallheaders();
        $token = $headers['Authorization'];
        $key = $this->key;
        $userData = JWT::decode($token, $key, array('HS256'));
        $id_user = $userData->id;
        $id = $_POST['id'];
        $user = User::find($id_user);

        if ($user->role_id != 1 && $userData->id != $id) {
            return $this->createResponse(401, 'No tienes permiso');
        }


        if (empty($_POST['id'])) {
            return $this->createResponse(400, 'Introduce la id del usuario');
        }
        try {
            $userBD = User::find($id);
            if ($userBD == null) {
                return $this->createResponse(400, 'El usuario no existe');
            }
            if (!empty($_POST['email']) ) {
                $userBD->email = $_POST['email'];
            }
            if (!empty($_POST['name']) ) {
                $userBD->name = $_POST['name'];
            }
            if (!empty($_POST['status']) ) {
                $userBD->status = $_POST['status'];
            }
            if (!empty($_POST['password']) ) {
                $userBD->password = encrypt($_POST['password']);
            }

            if (!empty($_POST['role_id']) ) {
                $userBD->role_id = $_POST['role_id'];
            }
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->createResponse(400, 'Introduzca un email valido'); 
            }

            if (strlen($_POST['password']) < 8){
            return $this->createResponse(400, 'La contraseña debe tener al  menos 8 caracteres');
        }
            $userBD->save();

            return $this->createResponse(200, 'Usuario actualizado');

            
        } catch (Exception $e) {
           
           return $this->createResponse(500, $e->getMessage());

        }
    }
    public function deactivateAccount()
    {
        $headers = getallheaders();
        $token = $headers['Authorization'];
        $key = $this->key;
        $userData = JWT::decode($token, $key, array('HS256'));
        $id = $_POST['id']; 
        $user = User::find($id);


        if ($userData->rol != 1) {
            return $this->createResponse(401, 'No autorizado', $userData);
        }

        if ($user->status != 1) {
            return $this->createResponse(401, 'La cuenta ya está desactivada', $user);
        }
        $user->status = 0;
        $user->save();
        return $this->createResponse(200, 'La cuenta ha sido desactivada', $user);

    }

    public function reactivateAccount()
    {
        $headers = getallheaders();
        $token = $headers['Authorization'];
        $key = $this->key;
        $userData = JWT::decode($token, $key, array('HS256'));
        $id = $_POST['id']; 
        $user = User::find($id);


        if ($userData->rol != 1) {
            return $this->createResponse(401, 'No autorizado');
        }

        if ($user->status != 0) {
            return $this->createResponse(401, 'La cuenta está activa');
        }
        $user->status = 1;
        $user->save();
        return $this->createResponse(200, 'La cuenta ha sido reactivada');
    }

    public function get_allusers()
    {
        $headers = getallheaders();
        $token = $headers['Authorization'];
        $key = $this->key;
        $userData = JWT::decode($token, $key, array('HS256'));
        $id_user = $userData->id;
        $user = User::find($id_user);
        if ($user->role_id !== 1) {
            return $this->createResponse(401, 'No tienes permiso', $user);
        }
        $users = User::all();
            return $this->createResponse(200, 'Listado de usuarios', $users);
    }

    public function get_user()
    {
        $headers = getallheaders();
        $token = $headers['Authorization'];
        $key = $this->key;
        $userData = JWT::decode($token, $key, array('HS256'));
        $id_user = $userData->id;
        $user = User::find($id_user);
        if ($user->id !== 1) {
            return $this->createResponse(401, 'No tienes permiso');
        }


        $searchTerm = $_GET['name'];
        $userDB = User::query()
            ->where('name', 'LIKE', $searchTerm) 
            ->get();

        return $this->createResponse(200, 'Listado de usuarios', $userDB);
            }   

    public function post_delete()
    {
        $headers = getallheaders();
        $token = $headers['Authorization'];
        $key = $this->key;
        $userData = JWT::decode($token, $key, array('HS256'));
        $id_user = $userData->id;
        $user = User::find($id_user);
        if ($user->id !== 1) {
            return $this->createResponse(401, 'No tienes permiso');
        }
        $id = $_POST['id'];
        if (empty($_POST['id'])) {
            return $this->createResponse(400, 'Introduce la id del usuario');
        }
        try {
            $userBD = User::find($id);
            if ($userBD == null) {
                return $this->createResponse(400, 'El usuario no existe');
            }

            $userBD->delete();

            // return $this->error(200, 'Usuario borrado');
            return $this->createResponse(200, 'Usuario borrado');
        } catch (Exception $e) {
            return $this->createResponse(500, $e->getMessage());
        }
    }


    public function get_userById()
    {
        try {

            $headers = getallheaders();
            $token = $headers['Authorization'];
            $key = $this->key;

            
            if ($token == null) 
            {
                return $this->createResponse(400, 'Permiso denegado');
            }
            
            $userData = JWT::decode($token, $key, array('HS256'));
            $id_user = $userData->id;
           
            $id = $_GET['id'];


            $userDB = User::find($id);

            if ($userDB == null) 
            {
                return $this->createResponse(400, 'El usuario no existe');
            }

            return $this->createResponse(200, 'Usuario devuelto', $userDB);


        } catch (Exception $e) {
            
            return $this->error(500, $e->getMessage());
        }
    }
}
