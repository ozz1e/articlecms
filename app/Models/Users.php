<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    use HasFactory;

    protected $table = 'admin_users';

    public function roles()
    {
        return $this->belongsToMany('App\Models\Roles','admin_role_users','user_id','role_id');
    }
}
