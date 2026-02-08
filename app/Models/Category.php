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

    /**
     * Get the parent category.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get all descendants (children, grandchildren, etc.)
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all ancestors (parent, grandparent, etc.)
     */
    public function ancestors()
    {
        $ancestors = collect();
        $parent = $this->parent;

        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }

        return $ancestors->reverse();
    }

    /**
     * Get the products in this category.
     */
    public function products()
    {
        return $this->hasMany(Product::class)->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get all products including subcategories.
     */
    public function allProducts()
    {
        $categoryIds = $this->getAllDescendantIds();
        $categoryIds[] = $this->id;

        return Product::whereIn('category_id', $categoryIds);
    }

    /**
     * Get all descendant category IDs including self.
     */
    public function getAllDescendantIds(): array
    {
        $ids = [];
        
        $this->getDescendantIdsRecursive($this, $ids);
        
        return $ids;
    }

    /**
     * Recursive function to get descendant IDs.
     */
    private function getDescendantIdsRecursive($category, &$ids): void
    {
        foreach ($category->children as $child) {
            $ids[] = $child->id;
            $this->getDescendantIdsRecursive($child, $ids);
        }
    }

    /**
     * Get the breadcrumb trail.
     */
    public function getBreadcrumbAttribute(): array
    {
        $breadcrumb = [];
        
        // Adicionar ancestrais
        foreach ($this->ancestors() as $ancestor) {
            $breadcrumb[] = [
                'name' => $ancestor->name,
                'slug' => $ancestor->slug,
                'url' => $ancestor->url,
            ];
        }
        
        // Adicionar a categoria atual
        $breadcrumb[] = [
            'name' => $this->name,
            'slug' => $this->slug,
            'url' => $this->url,
            'current' => true,
        ];
        
        return $breadcrumb;
    }

    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope a query to only include featured categories.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->active();
    }

    /**
     * Scope a query to only include root categories (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to only include categories with parent.
     */
    public function scopeChild($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Scope a query to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope a query to search categories.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
    }

    /**
     * Get the image URL.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return asset('images/default-category.jpg');
        }
        
        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }
        
        return Storage::url($this->image);
    }

    /**
     * Get the banner image URL.
     */
    public function getBannerImageUrlAttribute(): ?string
    {
        if (!$this->banner_image) {
            return $this->image_url;
        }
        
        if (Str::startsWith($this->banner_image, ['http://', 'https://'])) {
            return $this->banner_image;
        }
        
        return Storage::url($this->banner_image);
    }

    /**
     * Get the icon URL.
     */
    public function getIconUrlAttribute(): ?string
    {
        if (!$this->icon) {
            return null;
        }
        
        if (Str::startsWith($this->icon, ['http://', 'https://'])) {
            return $this->icon;
        }
        
        return Storage::url($this->icon);
    }

    /**
     * Get the status color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_INACTIVE => 'warning',
            self::STATUS_ARCHIVED => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'Ativo',
            self::STATUS_INACTIVE => 'Inativo',
            self::STATUS_ARCHIVED => 'Arquivado',
            default => $this->status,
        };
    }

    /**
     * Check if category has children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    /**
     * Check if category has products.
     */
    public function hasProducts(): bool
    {
        return $this->products_count > 0 || $this->products()->count() > 0;
    }

    /**
     * Get the URL for the category.
     */
    public function getUrlAttribute(): string
    {
        return route('categories.show', $this->slug);
    }

    /**
     * Get the edit URL for the category.
     */
    public function getEditUrlAttribute(): string
    {
        return route('admin.categories.edit', $this->id);
    }

    /**
     * Increment products count.
     */
    public function incrementProductsCount(int $count = 1): void
    {
        $this->increment('products_count', $count);
    }

    /**
     * Decrement products count.
     */
    public function decrementProductsCount(int $count = 1): void
    {
        $this->decrement('products_count', $count);
    }

    /**
     * Sync products count.
     */
    public function syncProductsCount(): void
    {
        $count = $this->products()->count();
        $this->update(['products_count' => $count]);
    }

    /**
     * Get the full name with parent hierarchy.
     */
    public function getFullNameAttribute(): string
    {
        $names = [];
        
        foreach ($this->ancestors() as $ancestor) {
            $names[] = $ancestor->name;
        }
        
        $names[] = $this->name;
        
        return implode(' > ', $names);
    }

    /**
     * Get the depth level of the category.
     */
    public function getDepthAttribute(): int
    {
        $depth = 0;
        $parent = $this->parent;
        
        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }
        
        return $depth;
    }

    /**
     * Check if category is root.
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if category is child.
     */
    public function isChild(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * Get the layout options.
     */
    public static function getLayoutOptions(): array
    {
        return [
            self::LAYOUT_GRID => 'Grid',
            self::LAYOUT_LIST => 'Lista',
            self::LAYOUT_CAROUSEL => 'Carrossel',
        ];
    }

    /**
     * Get the layout label.
     */
    public function getLayoutLabelAttribute(): string
    {
        return match($this->display_layout) {
            self::LAYOUT_GRID => 'Grid',
            self::LAYOUT_LIST => 'Lista',
            self::LAYOUT_CAROUSEL => 'Carrossel',
            default => $this->display_layout,
        };
    }
}