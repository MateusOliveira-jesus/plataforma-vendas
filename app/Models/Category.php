<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_ARCHIVED = 'archived';

    /**
     * Display layouts
     */
    const LAYOUT_GRID = 'grid';
    const LAYOUT_LIST = 'list';
    const LAYOUT_CAROUSEL = 'carousel';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'status',
        'is_featured',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'image',
        'banner_image',
        'icon',
        'sort_order',
        'display_layout',
        'products_count',
        'attributes',
        'filters',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'products_count' => 'integer',
        'attributes' => 'array',
        'filters' => 'array',
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
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
            
            // Garantir que o slug seja único
            $originalSlug = $category->slug;
            $count = 1;
            while (static::where('slug', $category->slug)->exists()) {
                $category->slug = $originalSlug . '-' . $count;
                $count++;
            }
        });

        static::updating(function ($category) {
            // Se o nome mudou, atualizar o slug
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
                
                // Garantir que o slug seja único
                $originalSlug = $category->slug;
                $count = 1;
                while (static::where('slug', $category->slug)
                        ->where('id', '!=', $category->id)
                        ->exists()) {
                    $category->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });

        static::deleting(function ($category) {
            if ($category->isForceDeleting()) {
                // Se for exclusão permanente, atualizar os produtos
                $category->products()->update(['category_id' => null]);
            }
        });
    }
}