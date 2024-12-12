<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ThingController extends Controller
{
    public function index(){
        return 'Prueba index';
    }

    //Displays the form to create a new citizen
    public function create(){
        return 'Prueba create';
    }

    //Handles storing a new citizen in the database
    public function store(){
        return 'Prueba store';
    }

    //Displays the form to edit an existing citizen
    public function show() {
        return 'Prueba edit';
    }

    //Handles updating a citizen's information in the database
    public function update()
    {
        return 'Prueba update';
    }

    // Deletes a citizen from the database
    public function destroy(){
        return 'Prueba destroy';
    }
}