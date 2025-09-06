<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */
/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
return $request->user();
});
 */
Route::group([

    'middleware' => 'api',
    'prefix' => 'auth',

], function ($router) {

    Route::post('login', 'AuthController@login');
    Route::post('logincongreso', 'AuthController@logincongreso');
    Route::post('logout', 'AuthController@logout');
    Route ::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    //Route::post('register', 'AuthController@register');

});

//Rutas del Home

Route::get('home/materias_asignadas/{id}', 'HomeController@materias_asignadas');
Route::get('home/novedades/{id}', 'HomeController@novedades');
Route::get('home/search_alumno/{id}', 'HomeController@search_alumno');
Route::get('home/notificaciones/{id}', 'HomeController@notificaciones');
Route::get('home/version_app/{id}', 'HomeController@version_app');
Route::post('home/baja_usuario/{id}', 'HomeController@baja_usuario');
Route::get('home/tutoriales/{id}', 'HomeController@tutoriales');

//Rutas del Home Web
Route::get('home/menu/{id}', 'HomeController@menu');
Route::get('home/perfil/{id}', 'HomeController@perfil');
Route::post('home/modificar_perfil/{id}', 'HomeController@modificar_perfil');
Route::get('home/indicadores/{id}', 'HomeController@indicadores');

//Rutas Comunicados

Route::get('comunicados/show_comunicados/{id}', 'ComunicadosController@show_comunicados');
Route::put('comunicados/lectura_comunicado/{id}', 'ComunicadosController@lectura_comunicado');

//Rutas Notificaciones

Route::get('notificaciones/cantidad_notificaciones/{id}', 'NotificacionesController@cantidad_notificaciones');
Route::put('notificaciones/lectura_notificacion/{id}', 'NotificacionesController@lectura_notificacion');
Route::put('notificaciones/lectura_retiro/{id}', 'NotificacionesController@lectura_retiro');
Route::put('notificaciones/lectura_solicitudes_reunion/{id}', 'NotificacionesController@lectura_solicitudes_reunion');

//Rutas mensajeria

Route::get('mensajeria/show_mensajeria/{id}', 'MensajeriaController@show_mensajeria');
Route::get('mensajeria/show_mensajeria_sl/{id}', 'MensajeriaController@show_mensajeria_sl');
Route::get('mensajeria/historial_mensajes/{id}', 'MensajeriaController@historial_mensajes');
Route::post('mensajeria/enviar_chat/{id}', 'MensajeriaController@enviar_chat');
Route::post('mensajeria/nuevo_chat/{id}', 'MensajeriaController@nuevo_chat');
Route::put('mensajeria/lectura_mensajeria/{id}', 'MensajeriaController@lectura_mensajeria');
Route::put('mensajeria/borrar_mensaje/{id}', 'MensajeriaController@borrar_chat');
Route::get('mensajeria/destinatarios_chats/{id}', 'MensajeriaController@destinatarios_chats');

//Rutas Detalle Comunicados

Route::get('detalle_comunicados/lista_comunicados/{id}', 'DetalleComunicadosController@lista_comunicados');
Route::post('detalle_comunicados/nuevo_comunicado/{id}', 'DetalleComunicadosController@nuevo_comunicado');
Route::get('detalle_comunicados/lista_destinatarios/{id}', 'DetalleComunicadosController@lista_destinatarios');
Route::put('detalle_comunicados/borrar_comunicado/{id}', 'DetalleComunicadosController@borrar_comunicado');
Route::get('detalle_comunicados/texto_predeterminado/{id}', 'DetalleComunicadosController@texto_predeterminado');
Route::get('detalle_comunicados/lista_solicitudes_reunion/{id}', 'DetalleComunicadosController@lista_solicitudes_reunion');
Route::post('detalle_comunicados/nueva_solicitud_reunion/{id}', 'DetalleComunicadosController@nueva_solicitud_reunion');
Route::put('detalle_comunicados/borrar_solicitud_reunion/{id}', 'DetalleComunicadosController@borrar_solicitud_reunion');
Route::post('detalle_comunicados/reagendar_solicitud_reunion/{id}', 'DetalleComunicadosController@reagendar_solicitud_reunion');
Route::get('detalle_comunicados/solicitudes_reunion_alumno/{id}', 'DetalleComunicadosController@solicitudes_reunion_alumno');

//Rutas Asistencia

Route::get('asistencias/alumnos_materias/{id}', 'AsistenciaController@alumnos_materias');
Route::get('asistencias/inasistencias_alumno/{id}', 'AsistenciaController@inasistencias_alumno');
Route::post('asistencias/nuevo_parte_asistencia/{id}', 'AsistenciaController@nuevo_parte_asistencia');
Route::post('asistencias/nuevo_parte_asistencia2/{id}', 'AsistenciaController@nuevo_parte_asistencia2');
Route::get('asistencias/lista_partes/{id}', 'AsistenciaController@lista_partes');

//Rutas Libro de temas

Route::get('libro_temas/registros_libro_temas/{id}', 'LibroTemasController@registros_libro_temas');
Route::get('libro_temas/lista_tipo_clases/{id}', 'LibroTemasController@lista_tipo_clases');
Route::post('libro_temas/agregar_registro_libro_temas/{id}', 'LibroTemasController@agregar_registro_libro_temas');

//Rutas Calificacion

Route::get('calificacion/lista_tipo_calificaciones/{id}', 'CalificacionController@lista_tipo_calificaciones');
Route::get('calificacion/lista_escalas/{id}', 'CalificacionController@lista_escalas');
Route::get('calificacion/lista_calificaciones/{id}', 'CalificacionController@lista_calificaciones');
Route::post('calificacion/agregar_nuevo_instrumento/{id}', 'CalificacionController@agregar_nuevo_instrumento');
Route::post('calificacion/agregar_editar_calificacion_alumno/{id}', 'CalificacionController@agregar_editar_calificacion_alumno');
Route::delete('calificacion/eliminar_instrumento/{id}', 'CalificacionController@eliminar_instrumento');
Route::get('calificacion/lista_calificados/{id}', 'CalificacionController@lista_calificados');
Route::get('calificacion/lista_calificaciones_materia/{id}', 'CalificacionController@lista_calificaciones_materia');
Route::get('calificacion/lista_calificaciones_materia_alumno/{id}', 'CalificacionController@lista_calificaciones_materia_alumno');
Route::get('calificacion/lista_instrumentos_materia/{id}', 'CalificacionController@lista_instrumentos_materia');
Route::get('calificacion/lista_calificaciones_alumno/{id}', 'CalificacionController@lista_calificaciones_alumno');

//Rutas Legajo
Route::get('legajo/legajo_alumno/{id}', 'LegajoController@legajo_alumno');
Route::get('legajo/datos_alumno/{id}', 'LegajoController@datos_alumno');
Route::post('legajo/actualizar_datos_alumno/{id}', 'LegajoController@actualizar_datos_alumno');

//Difusion de actividades

Route::get('difusion_actividades/show_publicaciones/{id}', 'DifusionActividadesController@show_publicaciones');
Route::get('difusion_actividades/show_publicacion_id/{id}', 'DifusionActividadesController@show_publicacion_id');
Route::put('difusion_actividades/eliminar_publicacion/{id}', 'DifusionActividadesController@eliminar_publicacion');
Route::post('difusion_actividades/nueva_publicacion/{id}', 'DifusionActividadesController@nueva_publicacion');
Route::post('difusion_actividades/nueva_imagen/{id}', 'DifusionActividadesController@nueva_imagen');
Route::put('difusion_actividades/borrar_imagen/{id}', 'DifusionActividadesController@borrar_imagen');
Route::patch('difusion_actividades/editar_publicacion/{id}', 'DifusionActividadesController@editar_publicacion');

//Rutas Reservas
Route::get('reservas/pantalla/{id}', 'ReservasController@pantalla');


//Rutas Requerimientos
Route::get('requerimientos/show_requerimientos/{id}', 'RequerimientosController@show_requerimientos');
Route::get('requerimientos/show_consultas/{id}', 'RequerimientosController@show_consultas');
Route::post('requerimientos/nuevo_comentario/{id}', 'RequerimientosController@nuevo_comentario');
Route::post('requerimientos/nueva_entrega/{id}', 'RequerimientosController@nueva_entrega');

//Rutas Rite
Route::get('rite/lista_rites/{id}', 'RiteController@lista_rites');
Route::get('rite/lista_alumnos_curso/{id}', 'RiteController@lista_alumnos_curso');
Route::get('rite/lista_valoracion_final_niveles/{id}', 'RiteController@lista_valoracion_final_niveles');
Route::post('rite/agregar_proceso_valoracion_final_alumno/{id}', 'RiteController@agregar_proceso_valoracion_final_alumno');
Route::post('rite/agregar_nota_cursada/{id}', 'RiteController@agregar_nota_cursada');

//Rutas Cualitativos
Route::get('cualitativos/listado/{id}', 'CualitativosController@listado');
Route::post('cualitativos/modificar/{id}', 'CualitativosController@modificar');
Route::get('cualitativos/detalle/{id}', 'CualitativosController@detalle');
Route::post('cualitativos/guardar/{id}', 'CualitativosController@guardar');
Route::post('cualitativos/comentar/{id}', 'CualitativosController@comentar');
Route::put('cualitativos/borrar_imagen/{id}', 'CualitativosController@borrar_imagen');

//Rutas Planificaciones
Route::get('planificaciones/listado/{id}', 'PlanificacionesController@listado');
Route::post('planificaciones/modificar/{id}', 'PlanificacionesController@modificar');
//Route::post('planificaciones/guardar/{id}', 'PlanificacionesController@guardar');



//Rutas Rite
Route::get('rite/lista_rite/{id}', 'RiteController@lista_rite');
Route::get('rite/ver_rite/{id}', 'RiteController@ver_rite');
Route::get('rite/ver_escala_rite/{id}', 'RiteController@ver_escala_rite');
Route::post('rite/generar_rite_alumno/{id}', 'RiteController@generar_rite_alumno');
Route::post('rite/completar_rite_alumno/{id}', 'RiteController@completar_rite_alumno');

//Rutas Recursos
Route::post('recursos/crear_recurso/{id}', 'RecursosController@crear_recurso');
Route::post('recursos/agregar_bloqueo/{id}', 'RecursosController@agregar_bloqueo');
Route::patch('recursos/modificar_cantidad/{id}', 'RecursosController@modificar_cantidad');
Route::delete('recursos/cancelar_reserva/{id}', 'RecursosController@cancelar_reserva');
Route::delete('recursos/eliminar_bloqueo/{id}', 'RecursosController@eliminar_bloqueo');
Route::get('recursos/ver_lista_recursos/{id}', 'RecursosController@ver_lista_recursos');


//Cursos/Materias
Route::get('cursos/lista_alumnos/{id}', 'CursosController@lista_alumnos');

/******************************************************************/
//Ruteo de los usuarios

//DESDE ACA
Route::group(['middleware' => 'auth'], function () {

});
