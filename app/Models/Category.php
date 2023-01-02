<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    protected $table = "categories";
    protected $relation_table = "user_category";
    use HasFactory;

    public function parent()
    {
        return $this->belongsTo('Post', 'parent_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, $this->relation_table, 'category_id', 'user_id');
    }
}
