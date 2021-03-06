<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\DefaultDatetimeFormat;

class Editor extends Model
{
    use HasFactory;

    protected $table = 'editor';

    protected $dateFormat = 'U';

    protected $fillable = ['editor_name','lang_id','editor_intro','editor_avatar','type'];

    public function lang()
    {
        return $this->belongsTo(Lang::class,'lang_id','id');
    }

    public function attr()
    {
        return $this->hasMany(EditorAttr::class,'editor_id','id');
    }
}
