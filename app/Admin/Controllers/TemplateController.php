<?php


namespace App\Admin\Controllers;


use App\Models\Lang;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Admin\Repositories\Template;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TemplateController extends  AdminController
{
    protected function grid()
    {
        return Grid::make(\App\Models\Template::with(['lang']),function (Grid $grid){
            $grid->column('id');
            $grid->column('temp_name','模板名称(可直接修改模板名称)')->editable(true);
            $grid->column('lang.lang_name','语言');
            $grid->column('type')->display(function($type){
                if( $type == 2 ){
                    return 'AMP';
                }else{
                    return '文章';
                }
            });
            $grid->column('file_path');
            $grid->column('created_at')->display(function ($created_at){
                return $created_at;
            });
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                // 获取当前行主键值
                $id = $actions->getKey();
                // prepend一个操作
                $actions->prepend('<a href="template/1/editOnLine"><i class="fa fa-paper-plane"></i> 编辑</a>');
            });
            // 禁用详情按钮
            $grid->disableViewButton();
        });
    }

    protected function form()
    {
        return Form::make(new Template(), function (Form $form) {
            $form->php('code')->value('11111111111111111111');
            $langSearchList = Lang::all(['id','lang_name'])->toArray();
            $langSelectList = [];
            foreach ($langSearchList as $item) {
                $langSelectList[$item['id']] = $item['lang_name'];
            }
            $form->select('lang_id','语言')->required()->options($langSelectList);
            $form->text('temp_name')->required()->help('名称只能包含数字、字母、下划线、中横线或点的组合');
            $form->select('type')->required()->options([1=>'文章模板',2=>'AMP模板']);
            $form->file('file_path')->required()->accept('html')->help('只能上传.html格式的文件，如果是上传AMP模板请以.amp.html结尾');


            //去掉底部查看按钮
            $form->disableViewCheck();
            //去掉继续编辑
            $form->disableEditingCheck();
            //去掉继续创建
            $form->disableCreatingCheck();

        });
    }

    protected function build()
    {
        Form::dialog('编辑角色')
            ->click('.edit-form')
            ->success('Dcat.reload()'); // 编辑成功后刷新页面

        // 当需要在同个“class”的按钮中绑定不同的链接时，把链接放到按钮的“data-url”属性中即可
        $editPage = admin_base_path('auth/roles/1/edit');

        return "
<div style='padding:30px 0'>
    <span class='btn btn-success create-form'> 新增表单弹窗 </span> &nbsp;&nbsp;
    <span class='btn btn-blue edit-form' data-url='{$editPage}'> 编辑表单弹窗 </span>
</div>
";
    }

    public function dialogForm(Content $content,Request $request)
    {
        dd($request->route('id'));
        $form = new Form();
        $form->php('code','样式文件代码')->value('sfsdfsdfsdf');
        return $content
            ->header('Modal Form')
            ->body($form);
    }

}
