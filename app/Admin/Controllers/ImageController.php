<?php


namespace App\Admin\Controllers;


use App\Admin\Pages\ImageManage;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;

class ImageController extends  AdminController
{

    public function index(Content $content)
    {
        return $content->body(new ImageManage());
    }
}
