<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecursosReservasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection("mysql")
            ->create('recursos_reservas', function (Blueprint $table) {
                $table->increments('ID');

                $table->date("Fecha");
                $table->time("Hora");
                $table->integer("ID_Recurso") // ID tabla recursos
                    ->comment("ID tabla recursos");
                $table->date("Fecha_R");
                $table->time("Hora_Inicio");
                $table->time("Hora_Fin");
                $table->integer("ID_Nivel") // ID tabla nivel
                    ->comment("ID tabla nivel");
                $table->integer("ID_Curso") // ID tabla cursos
                    ->comment("ID tabla cursos");
                $table->integer("ID_Materia") // ID tabla materias
                    ->comment("ID tabla materias");
                $table->integer("ID_Docente") // ID tabla personal
                    ->comment("ID tabla personal");
                $table->text("Actividad");
                $table->integer("B")->default(0) // Baja
                    ->comment("Baja 0=No, 1=Si");
                $table->date("Fecha_B");
                $table->time("Hora_B");
                $table->integer("ID_Usuario_B")
                    ->comment("ID tabla usuarios");
                $table->text("B_Motivo");

                $table->charset = "latin1";
                $table->collation = "latin1_swedish_ci";
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection("mysql")
            ->dropIfExists('recursos_reservas');
    }
}
