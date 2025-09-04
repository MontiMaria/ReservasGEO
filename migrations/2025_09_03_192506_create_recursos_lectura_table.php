<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecursosLecturaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection("mysql")
            ->create('recursos_lectura', function (Blueprint $table) {
                $table->increments('ID');

                $table->integer("ID_Recurso") // ID tabla recursos
                    ->comment("ID tabla recursos");
                $table->integer("ID_Usuario")
                    ->comment("ID tabla usuarios");
                $table->date("Fecha");
                $table->time("Hora");
                $table->integer("Leido")->default(0);
                $table->date("Fecha_Leido");
                $table->time("Hora_Leido");

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
            ->dropIfExists('recursos_lectura');
    }
}
