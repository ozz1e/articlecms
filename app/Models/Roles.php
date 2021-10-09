<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    use HasFactory;

    protected $table = 'admin_roles';

    public function user()
    {
        return $this->belongsToMany(Users::class,'admin_role_users','role_id','user_id');
    }
}
