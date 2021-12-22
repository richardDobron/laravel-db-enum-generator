<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug');
            $table->string('name');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::table('user_roles')->insert([
            [
                'slug' => 'MANAGER',
                'name' => 'Admin',
            ],
            [
                'slug' => 'CONTENT_CREATOR',
                'name' => 'Editor',
            ],
            [
                'slug' => 'MODERATOR',
                'name' => 'Moderator',
            ],
            [
                'slug' => 'ADVERTISER',
                'name' => 'Advertiser',
            ],
            [
                'slug' => 'INSIGHTS_ANALYST',
                'name' => 'Analyst',
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
        Schema::drop('user_roles');
    }
}
