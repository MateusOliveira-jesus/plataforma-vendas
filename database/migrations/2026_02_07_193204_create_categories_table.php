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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            
            // Informações básicas
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            // Hierarquia (categorias aninhadas)
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('categories')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
            //relacionando usuario que criou a categoria
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // Status e visibilidade
        $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->boolean('is_featured')->default(false);
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            
            // Imagens
            $table->string('image')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('icon')->nullable();
            
            // Ordenação e exibição
            $table->integer('sort_order')->default(0);
            $table->string('display_layout')->default('grid')->comment('grid, list, etc.');
            
            // Estatísticas (opcional, pode ser calculado)
            $table->integer('products_count')->default(0);
            
            // Configurações adicionais
            $table->json('attributes')->nullable()->comment('Atributos específicos da categoria');
            $table->json('filters')->nullable()->comment('Filtros para esta categoria');
            
            // Soft deletes e timestamps
            $table->softDeletes();
            $table->timestamps();
            
            // Índices
            $table->index('name');
            $table->index('slug');
            $table->index('parent_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('is_featured');
            $table->index('sort_order');
            $table->index(['parent_id', 'status', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};