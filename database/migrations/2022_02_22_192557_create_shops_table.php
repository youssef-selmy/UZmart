<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id()->from(501);
            $table->uuid('uuid')->index();
            $table->foreignId('user_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->double('tax', 22, 2)->default(0);
            $table->double('percentage', 22, 0)->default(0);
            $table->jsonb('lat_long')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('open')->default(1);
            $table->boolean('visibility')->default(1);
            $table->string('background_img', 191)->nullable();
            $table->string('logo_img', 191)->nullable();
            $table->double('min_amount', 12)->default(0.1);
            $table->enum('status', ['new', 'edited', 'approved', 'rejected', 'inactive'])->default('new');
            $table->text('status_note')->nullable();
            $table->json('delivery_time');
            $table->tinyInteger('type');
            $table->boolean('verify')->default(0);
            $table->double('r_count')->nullable()->default(0);
            $table->double('r_avg')->nullable()->default(0);
            $table->double('r_sum')->nullable()->default(0);
            $table->double('o_count')->nullable()->default(0);
            $table->double('od_count')->nullable()->default(0);
            $table->timestamps();
        });

        Schema::create('shop_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->string('locale')->index();
            $table->string('title', 191);
            $table->text('description')->nullable();
            $table->text('address')->nullable();

            $table->unique(['shop_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_translations');
        Schema::dropIfExists('shops');
    }
};
