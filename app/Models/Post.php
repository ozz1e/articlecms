<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Post extends Model
{
	use HasDateTimeFormatter;

    protected $table = 'post';

    protected $dateFormat = 'U';

    public function articleNum()
    {
        return $this->belongsTo(Directory::class,'directory_fullpath','directory_fullpath')->select(DB::raw('count(*) as total'));
    }

    public function lang()
    {
        return $this->hasOne(Lang::class,'id','lang_id');
    }

    public function editor()
    {
        return $this->hasOne(Editor::class,'id','editor_id');
    }

    public function attr()
    {
        return $this->hasMany(EditorAttr::class,'editor_id','editor_id');
    }

}
