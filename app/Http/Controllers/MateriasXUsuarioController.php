<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\user;
use App\Models\Materias;
use App\Models\MateriaXUsuario;
use App\Models\Calificacion;

class MateriasXUsuarioController extends Controller
{
    function index($id){
        $usuario = User::with('tipo')->findOrFail($id);
        $materiasAsignadas = MateriaXUsuario::with(['materias', 'calificaciones'])
        ->where('users_id', $id)
        ->get();
        foreach( $materiasAsignadas as $asignacion ){
            $promedio = $asignacion->calificaciones->avg('calificaciones');
            $asignacion->promedio = $promedio ? round( $promedio, 2) : 0;
        }
        $materiasAsignadasIds = $materiasAsignadas->pluck('materias_id')->toArray();
        $materiasDisponibles = Materia::WhereNotIn('id', $materiasAsignadasIds)->get();
        return view('materiasxusuario.index', compact('usuario', 'materiasAsignadas', 'materiasDisponibles'));
    }
    function asignar(Request $request, $id){
        $validator = validator::make($request->all, [
            'materia_id' => 'required|exist:materias,id'
        ]);
        if( $validator->fails() ){
            return response()->json([
                'succes' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        try{
            $existe = MateriasXUsuario::where('materias_id', $request->$materia_id)
            ->where('users_id', $id)
            ->exists();
            if( $existe ){
                return response()->json([
                  'succes' => false,
                  'message' => 'Esta materias ya esta asignada al usuario.'  
                ], 400);
            }
            MateriasXUsuarios::create([
                'materias_id'=>$request->materia_id,
                'users_id'=> $id
            ]);
            return response()->json([
                  'succes' => true,
                  'message' => 'Materia Asignada correctamente.'  
                ]);
        }
        catch(\Exception $e) {
            return response()->json([
                'succes' => false,
                'message' => 'Error al asignar la materia'  
            ], 500);
        }
    }
    function desasignar($asignacion_id){
        try{
            $asignacion = MateriasXUsuario::findOrFail($asignacion_id);
            $usuario_id = $asignacion->users_id;

            Calificacion::where('materias_x_usuario_id', $asignacion_id)->delete();
            $asignacion->delete();
            return redirect()
            ->route('materiasxusuario.index', $usuario_id)
            ->with('succes', 'Materia desasignada correctamente');
        }
        catch(\Exception $e) {
            return redirect()
            ->back()
            ->with('error al desasignar la materia');
        }
    }
}