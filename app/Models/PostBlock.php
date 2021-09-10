<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PostBlock extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'post_block';

    protected $dates = ['deleted_at'];
}
