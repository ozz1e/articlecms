<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostAttr extends Model
{
    use HasFactory;

    protected $table = 'post_attr';

    public $timestamps = false;

    protected $fillable = ['post_htmlpath','post_key','post_value','post_html'];
}
