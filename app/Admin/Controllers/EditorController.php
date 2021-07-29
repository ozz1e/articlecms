<?php


namespace App\Admin\Controllers;

use App\Admin\Repositories\Editor;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;

class EditorController extends  AdminController
{
    protected function grid()
    {
        return Grid::make(new Editor(), function (Grid $grid) {
            $grid->column('id');

            $grid->column('created_at')->display(function ($created_at){
                return $created_at;
            });
            $grid->column('updated_at')->display(function($updated_at){
                return $updated_at;
            });


            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');

            });
        });
    }

    protected function form()
    {
        return Form::make(new Editor(), function (Form $form) {
            $form->ckeditor('content');
        });
    }
}
