<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;


class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'is_admin']);
    }
    // Dashboard del administrador
    public function dashboard()
    {
        // Lógica para el dashboard del administrador
        return response()->json(['message' => 'Dashboard de administrador']);
    }

    // Gestionar usuarios
    public function manageUsers(Request $request)
    {
        // Lógica para gestionar usuarios
        return response()->json(['message' => 'Usuarios gestionados']);
    }

    // Gestionar juegos
    public function manageGames(Request $request)
    {
        // Lógica para gestionar juegos
        return response()->json(['message' => 'Juegos gestionados']);
    }

    public function getUsers()
    {
        // Obtiene todos los usuarios donde 'email_verified_at' no es null
        $users = User::whereNotNull('email_verified_at')->get();

        return response()->json(['users' => $users], 200);
    }

}
