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
        Schema::create('designs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('printify_product_id');
            $table->string('printify_variant_id');
            $table->string('product_name');
            $table->string('product_size');
            $table->string('product_color');
            $table->json('print_files')->nullable();
            // $table->enum('status', ['active', 'inactive']);
            $table->unsignedTinyInteger('created_by')->nullable();
            $table->unsignedTinyInteger('updated_by')->nullable();
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
        Schema::dropIfExists('designs');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
};
