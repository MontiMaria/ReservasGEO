<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecursosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection("mysql")
            ->create('recursos', function (Blueprint $table) {
                $table->increments('ID');

                $table->string("Recurso", 100);
                $table->integer("Cantidad"); // Nuevo campo
                $table->text("Descripcion");
                $table->integer("ID_Tipo") // ID tabla recursos_tipos (No exsiste)
                    ->comment("ID tabla recursos_tipos (No exsiste)");
                $table->integer("ID_Nivel") // ID tabla nivel
                    ->comment("ID tabla nivel");
                $table->string("Estado", 1)->default("H") // Propongo eliminarlo
                    ->comment("Estado H=Habilitado, S=Suspendido");
                $table->integer("B")->default(0) // Baja
                    ->comment("Baja 0=No, 1=Si");

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
            ->dropIfExists('recursos');
    }
}
