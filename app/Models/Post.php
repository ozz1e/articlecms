<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'post';

    protected $dateFormat = 'U';

    public $timestamps = true;

    protected $fillable = ['title','keywords','description','directory_fullpath','html_fullpath','html_name','summary','contents','template_id','template_amp_id','post_status','editor_json','editor_id','lang_id','related_posts','published_at','structured_data','fb_comment','lightbox'];

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
        return $this->hasMany(PostAttr::class,'post_htmlpath','html_fullpath');
    }

}
