<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable =['product_name','measurement','price','description','category_id'];

    public function sales(){
        return $this->belongsToMany(Sale::class);
    }
    public function category(){
        return $this->belongsTo(Category::class);
    }
}
