<?php

use App\Models\Bonus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBonusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('bonuses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shop_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('stock_id')
                ->nullable()
                ->constrained('stocks')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->integer('bonus_quantity');

            $table->foreignId('bonus_stock_id')
                ->nullable()
                ->constrained('stocks')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->integer('value');
            $table->enum('type', Bonus::TYPES);
            $table->dateTime('expired_at');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('bonuses');
    }
}
