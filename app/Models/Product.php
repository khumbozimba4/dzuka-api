<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable =['product_name'];

    public function sales(){
        return $this->belongsToMany(Sale::class);
    }
    public function category(){
        return $this->belongsToMany(Category::class);
    }
}
