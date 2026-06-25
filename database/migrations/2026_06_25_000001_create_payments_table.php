<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('billing_auto_renew')->default(false);
            $table->string('billing_plan', 20)->nullable();
            $table->string('payment_provider_customer_id')->nullable()->index();
            $table->string('billing_card_brand', 40)->nullable();
            $table->string('billing_card_last_four', 4)->nullable();
            $table->string('billing_card_expires', 10)->nullable();
            $table->timestamp('billing_card_updated_at')->nullable();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 40)->default('flutterwave');
            $table->string('tx_ref')->unique();
            $table->string('provider_transaction_id')->nullable()->unique();
            $table->string('status', 40)->default('pending')->index();
            $table->string('purpose', 40)->default('subscription');
            $table->string('tier', 20);
            $table->string('plan', 20);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('USD');
            $table->boolean('auto_renew_requested')->default(false);
            $table->string('payment_method', 50)->nullable();
            $table->string('card_brand', 40)->nullable();
            $table->string('card_last_four', 4)->nullable();
            $table->string('card_expiry', 10)->nullable();
            $table->string('provider_customer_id')->nullable()->index();
            $table->timestamp('paid_at')->nullable()->index();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'paid_at']);
            $table->index(['paid_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'billing_auto_renew',
                'billing_plan',
                'payment_provider_customer_id',
                'billing_card_brand',
                'billing_card_last_four',
                'billing_card_expires',
                'billing_card_updated_at',
            ]);
        });
    }
};
