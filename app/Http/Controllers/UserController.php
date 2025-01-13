<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getUsers()
    {
        try {
            // Filtramos usuarios que tienen un valor en el campo 'email_verified_at'
            $users = User::whereNotNull('email_verified_at')  // Filtro para verificar que el campo no sea nulo
                        ->select('id', 'name', 'email', 'email_verified_at') // Agregamos 'email_verified_at' al select
                        ->get();
            
            // Retornamos los usuarios filtrados en formato JSON
            return response()->json(['users' => $users], 200);
        } catch (\Exception $e) {
            // En caso de error, devolvemos un mensaje con detalles del error
            return response()->json([
                'message' => 'Error al obtener los usuarios',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}


