<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'category';

    public function masterItems()
    {
        return $this->belongsToMany(MasterItem::class, 'item_category_pivot', 'category_id', 'master_item_id');
    }
}
