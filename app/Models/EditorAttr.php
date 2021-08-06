<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EditorAttr extends Model
{
    use HasFactory;

    protected $table = 'editor_attr';

    public $timestamps = false;

    protected $fillable = ['editor_id','key','value'];

}
