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

}
