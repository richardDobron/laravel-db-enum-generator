<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('language', 4);
            $table->string('region', 25);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::table('languages')->insert([
            [
                'name' => 'Slovenčina',
                'language' => 'sk_SK',
                'region' => 'Eastern Europe',
            ],
            [
                'name' => 'Čeština',
                'language' => 'cs_CZ',
                'region' => 'Eastern Europe',
            ],
            [
                'slug' => 'Afrikaans',
                'name' => 'af_za',
                'region' => 'Africa and Middle East',
            ],
            [
                'slug' => 'English (US)',
                'name' => 'en_US',
                'region' => 'Americas',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('languages');
    }
}
