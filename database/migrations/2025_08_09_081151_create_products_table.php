<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->string('sku')->unique();
            $table->string('ean13')->nullable();
            $table->string('upc')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            
            $table->index(['sku']);
            $table->index(['ean13']);
            $table->index(['upc']);
            $table->index(['status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
