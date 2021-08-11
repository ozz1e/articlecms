<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $table = 'template';

    protected $dateFormat = 'U';

    public function lang()
    {
        return $this->hasOne(Lang::class,'id','lang_id');
    }
}
