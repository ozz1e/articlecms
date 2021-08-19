<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Directory extends Model
{
	use HasDateTimeFormatter;

    protected $table = 'directory';

    protected $dateFormat = 'U';

    public $timestamps = true;

    protected $fillable = ['domain','lang_id','directory_name','directory_fullpath','directory_title','directory_intro','directory_intro','template_amp_id','page_title','page_description','page_keywords'];

    public function lang()
    {
        return $this->hasOne(Lang::class,'id','lang_id');
    }

    public function postTemp()
    {
        return $this->hasOne(Template::class,'id','template_id');
    }

    public function ampTemp()
    {
        return $this->hasOne(Template::class,'id','template_amp_id');
    }

    public function articleNum()
    {
        return $this->hasMany(Post::class,'directory_fullpath','directory_fullpath')->select('directory_fullpath');
    }
}
