<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AccesoController extends Controller
{
    public function mostrarFormulario(){
        return view('auth.acceso');
    }
    public function iniciarSesion(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255' ,
            'password' => 'required|string|min:6' 
        ],[
            'email.requiered' => 'El correo electronico es obligatorio',
            'email.email' => 'Debe ingresar un correo electronico valido',
            'email.max' => 'El correo electronico no debe de tener mas de 255 caracteres',
            'password.requiered' => 'La contraseña es obligatoria',
            'password.string' => 'La contraseña debe ser una cadena de texto',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
        ]);

        if( $validator->fails() ){
            if( $request->ajax() ){
                return response()->json([
                    'success' => false,
                    'erros' => $validator->errors()
                ], 422);
            }
            return redirect()
        ->back()
        ->withErrors( $validator )
        ->withInput($request->only('email') );

        }

        $credenciales = $request->only('email', 'password');

        if( Auth::attempt( $credenciales ) ){
            $request->sesion()->regenerate();
            $usuario = Auth::user();

            if( $request->ajax() ){
                return response()->json([
                    'success' => true,
                    'message' => 'Inicio de sesion exitoso',
                    'redirect' => ''

                ]);
            }
        }

    }
}