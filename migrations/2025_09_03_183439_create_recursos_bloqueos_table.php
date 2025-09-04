<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecursosBloqueosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection("mysql")
            ->create('recursos_bloqueos', function (Blueprint $table) {
                $table->increments('ID');

                $table->integer("ID_Recurso") // ID tabla recursos
                    ->comment("ID tabla recursos");
                $table->integer("Dia_Semana")
                    ->comment("Donde 1=Lunes ... 5=Viernes");
                $table->time("HI") // Hora inicio
                    ->comment("Hora inicio");
                $table->time("HF") // Hora finalizacion
                    ->comment("Hora finalizacion");
                $table->integer("ID_Nivel") // ID tabla nivel
                    ->comment("ID tabla nivel");
                $table->text("Causa");

                // Nuevos campos
                $table->integer("B")->default(0) // Baja
                    ->comment("Baja 0=No, 1=Si");
                $table->date("Fecha_B");
                $table->time("Hora_B");
                $table->integer("ID_Usuario_B")->default(0)
                    ->comment("ID tabla usuarios");

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
            ->dropIfExists('recursos_bloqueos');
    }
}
