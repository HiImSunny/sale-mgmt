<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('sku')->unique();
            $table->string('ean13')->nullable();
            $table->string('upc')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->index(['sku']);
            $table->index(['ean13']);
            $table->index(['upc']);
            $table->index(['product_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_variants');
    }
};
