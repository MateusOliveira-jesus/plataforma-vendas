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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            
            // Preços
            $table->decimal('price', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            
            // Estoque
            $table->integer('quantity')->default(0);
            $table->string('sku')->unique()->nullable();
            $table->string('barcode')->unique()->nullable();
            $table->boolean('track_quantity')->default(true);
            $table->boolean('allow_out_of_stock_purchase')->default(false);
            
            // Categorização
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            
            // Status
            $table->enum('status', ['draft', 'active', 'inactive', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_bestseller')->default(false);
            $table->boolean('is_new')->default(true);
            
            // Dimensões e peso
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            
            // Imagens
            $table->string('main_image')->nullable();
            $table->json('gallery_images')->nullable();
            
            // Especificações técnicas
            $table->json('specifications')->nullable();
            
            // Tags
            $table->json('tags')->nullable();
            
            // Visitas e estatísticas
            $table->integer('views')->default(0);
            $table->integer('sales_count')->default(0);
            $table->decimal('rating', 3, 2)->nullable();
            $table->integer('reviews_count')->default(0);
            
            // Datas importantes
            $table->timestamp('published_at')->nullable();
            $table->timestamp('featured_until')->nullable();
            
            // Ordenação
            $table->integer('sort_order')->default(0);
            
            // Soft deletes e timestamps
            $table->softDeletes();
            $table->timestamps();
            
            // Índices
            $table->index('name');
            $table->index('price');
            $table->index('status');
            $table->index('category_id');
            $table->index('brand_id');
            $table->index('is_featured');
            $table->index('is_bestseller');
            $table->index(['status', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};