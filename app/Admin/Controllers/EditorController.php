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
        return Grid::make(\App\Models\Editor::with(['lang']), function (Grid $grid) {
            $grid->column('id');
            $grid->column('lang.lang_name','语言');
            $grid->column('editor_name');
            $grid->column('editor_avatar')->image('',50,50);
            $grid->column('editor_attr')
                ->display('查看') // 设置按钮名称
                ->modal(function ($modal) {
                    // 设置弹窗标题
                    $modal->title('标题 '.$this->username);

                    // 自定义图标
                    $modal->icon('feather icon-eye');


                    return "<div style='padding:10px 10px 0'>asdasdasd</div>";
                });

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
