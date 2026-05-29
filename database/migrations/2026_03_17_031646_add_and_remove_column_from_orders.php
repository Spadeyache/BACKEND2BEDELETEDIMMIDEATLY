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
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->string('stripe_payment_id')->nullable()->change();                          // charge ID after success
            // $table->string('stripe_payment_intent_id')->nullable()->after('stripe_payment_id'); // for webhook verification
            $table->string('printify_order_id')->nullable()->after('stripe_payment_id');        // filled after Stripe webhook
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->string('stripe_payment_id')->nullable(false)->change();
            // $table->dropColumn('stripe_payment_intent_id');
            $table->dropColumn('printify_order_id');
        });
    }
};
