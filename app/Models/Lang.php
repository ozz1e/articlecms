<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\DefaultDatetimeFormat;

class Lang extends Model
{
    use HasFactory;

    protected $table = 'lang';

    protected $dateFormat = 'U';
}
