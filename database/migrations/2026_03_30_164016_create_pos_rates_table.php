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
        Schema::create('pos_rates', function (Blueprint $table) {
            $table->id();
            $table->string('pos_name');
            $table->enum('card_type', ['credit', 'debit', 'unknown']);
            $table->string('card_brand');
            $table->integer('installment');
            $table->enum('currency',['TRY','USD','EUR']);
            $table->decimal('commission_rate', 10, 3);
            $table->decimal('min_fee', 10, 2)->nullable();
            $table->integer('priority')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->unique(
                ['pos_name', 'card_type', 'card_brand', 'installment', 'currency'],
                'pos_rates_unique'
            );
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_rates');
    }
};
