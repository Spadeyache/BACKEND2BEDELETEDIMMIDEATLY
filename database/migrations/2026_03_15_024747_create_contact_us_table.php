<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contact_us', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('address');
            $table->text('comment');
            $table->boolean('read')->default(0);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('phone')->after('avatar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_us');

        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('phone');
        });
    }
};
