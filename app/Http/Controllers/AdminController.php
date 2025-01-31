<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{
    // No es necesario el constructor para aplicar middleware aquí

    public function dashboard()
    {
        // Lógica para el dashboard del administrador
        return response()->json(['message' => 'Dashboard de administrador']);
    }

    public function manageUsers(Request $request)
    {
        // Lógica para gestionar usuarios
        return response()->json(['message' => 'Usuarios gestionados']);
    }

    public function manageGames(Request $request)
    {
        // Lógica para gestionar juegos
        return response()->json(['message' => 'Juegos gestionados']);
    }

    public function hideUser(Request $request)
    {
        $request->validate([
            'userId' => 'required|exists:users,id',
        ]);
    
        // Encuentra el usuario
        $user = User::findOrFail($request->userId);
    
        // Alterna el valor de 'deleted'
        $newDeletedState = !$user->deleted;
        $user->deleted = $newDeletedState;
        $user->save();
    
        return response()->json([
            'message' => $newDeletedState ? 'Usuario ocultado' : 'Usuario recuperado',
            'user' => $user
        ], 200);
    }    
    
}