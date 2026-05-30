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
        Schema::table('design_elements', function (Blueprint $table) {
            $table->string('font_family')->nullable()->change();
            $table->float('font_size', 8, 2)->nullable()->change();
            $table->double('scale')->nullable()->after('height');
            $table->double('angle')->nullable()->after('scale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('design_elements', function (Blueprint $table) {
            $table->string('font_family')->nullable(false)->change();
            $table->float('font_size', 8, 2)->nullable(false)->change();
            $table->dropColumn(['scale', 'angle']);
        });
    }
};
