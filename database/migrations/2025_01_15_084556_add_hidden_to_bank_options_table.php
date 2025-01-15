<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('bank_options', function (Blueprint $table) {
        $table->boolean('hidden')->default(false)->after('bets'); // Agrega la columna 'hidden'
    });
}

public function down()
{
    Schema::table('bank_options', function (Blueprint $table) {
        $table->dropColumn('hidden'); // Elimina la columna 'hidden' en caso de rollback
    });
}

};
