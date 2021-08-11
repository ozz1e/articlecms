<?php
namespace App\Admin\Pages;

use Illuminate\Contracts\Support\Renderable;

class ImageManage implements Renderable
{
    public function render()
    {
        return admin_view('admin.picManage');
    }
}
