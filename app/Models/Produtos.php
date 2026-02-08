<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Produtos extends Model
{
    use HasFactory, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'compare_price',
        'cost_price',
        'quantity',
        'sku',
        'barcode',
        'track_quantity',
        'allow_out_of_stock_purchase',
        'category_id',
        'brand_id',
        'status',
        'is_featured',
        'is_bestseller',
        'is_new',
        'weight',
        'length',
        'width',
        'height',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'main_image',
        'gallery_images',
        'specifications',
        'tags',
        'views',
        'sales_count',
        'rating',
        'reviews_count',
        'published_at',
        'featured_until',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'quantity' => 'integer',
        'track_quantity' => 'boolean',
        'allow_out_of_stock_purchase' => 'boolean',
        'is_featured' => 'boolean',
        'is_bestseller' => 'boolean',
        'is_new' => 'boolean',
        'weight' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'gallery_images' => 'array',
        'specifications' => 'array',
        'tags' => 'array',
        'views' => 'integer',
        'sales_count' => 'integer',
        'rating' => 'decimal:2',
        'reviews_count' => 'integer',
        'published_at' => 'datetime',
        'featured_until' => 'datetime',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            
            // Garantir que o slug seja único
            $originalSlug = $product->slug;
            $count = 1;
            while (static::where('slug', $product->slug)->exists()) {
                $product->slug = $originalSlug . '-' . $count;
                $count++;
            }
        });

        static::updating(function ($product) {
            // Se o nome mudou, atualizar o slug
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = Str::slug($product->name);
                
                // Garantir que o slug seja único
                $originalSlug = $product->slug;
                $count = 1;
                while (static::where('slug', $product->slug)
                        ->where('id', '!=', $product->id)
                        ->exists()) {
                    $product->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }


}