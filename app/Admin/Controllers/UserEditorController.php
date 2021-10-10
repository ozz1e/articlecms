<?php


namespace App\Admin\Controllers;


use App\Models\Users;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;

class UserEditorController extends AdminController
{
    protected function grid()
    {
        return Grid::make(new Users(), function (Grid $grid) {

            $grid->model()->where('id','<>',1);
            $grid->column('username');
            $grid->column('role')->display(function ($role){

            });
        });


    }

}
