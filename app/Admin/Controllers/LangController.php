<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Lang;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;

class LangController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Lang(), function (Grid $grid) {
            $grid->column('id');
            $grid->column('lang_name');
            $grid->column('created_at')->display(function ($created_at){
                return $created_at;
            });
            $grid->column('updated_at')->display(function($updated_at){
                return $updated_at;
            });
            // 禁用行选择器
            $grid->disableRowSelector();
            // 禁用过滤器按钮
            $grid->disableFilterButton();
            // 禁用批量删除按钮
            $grid->disableBatchDelete();
            // 禁用详情按钮
            $grid->disableViewButton();
            // 禁用编辑按钮
            $grid->disableEditButton();
            // 显示快捷编辑按钮
            $grid->showQuickEditButton();
            //弹窗创建
            $grid->enableDialogCreate();
            // 设置弹窗宽高，默认值为 '700px', '670px'
            $grid->setDialogFormDimensions('50%', '30%');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');

            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Lang(), function (Form $form) {
            $form->text('lang_name')->rules('required|min:2|regex:/^[a-zA-Z\d]+$/|unique:lang,lang_name',[
                'required'=>'语言不能为空',
                'min'=>'语言至少需要两个字符',
                'regex'=>'语言必须为字母',
                'unique'=>'语言已存在'
            ]);
        });
    }

}
