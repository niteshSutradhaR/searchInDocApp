<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('file_id');
            $table->string('filename', 100);
            $table->string('mime_type');
            $table->bigInteger('filesize');
            $table->text('content')->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE files ADD FULLTEXT search(content)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(function($table){
            $table->dropIndex('search');
        });

        Schema::dropIfExists('files');
    }
};
