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
        //
        Schema::table('designs', function (Blueprint $table) {
            $table->string('mockup_image')->after('product_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('designs', function (Blueprint $table) {
            $table->dropColumn('mockup_image');
        });
    }
};
