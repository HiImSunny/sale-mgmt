<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'ean13',
        'upc',
        'price',
        'sale_price',
        'stock',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_values');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    // Accessors
    public function getFinalPriceAttribute()
    {
        return $this->sale_price ?? $this->price;
    }

    public function getVariantNameAttribute()
    {
        $attrs = $this->attributeValues->map(function($attrValue) {
            return $attrValue->attribute->name . ': ' . $attrValue->value;
        })->implode(', ');
        
        return $this->product->name . ($attrs ? " ({$attrs})" : '');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('sku', $code)
                    ->orWhere('ean13', $code)
                    ->orWhere('upc', $code);
    }
}
