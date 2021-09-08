<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Post;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Repositories\EloquentRepository;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class PostController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Post(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('title');
            $grid->column('keywords');
            $grid->column('description');
            $grid->column('directory_fullpath');
            $grid->column('html_fullpath');
            $grid->column('html_name');
            $grid->column('summary');
            $grid->column('contents');
            $grid->column('template_id');
            $grid->column('template_amp_id');
            $grid->column('post_status');
            $grid->column('editor_json');
            $grid->column('editor_id');
            $grid->column('lang_id');
            $grid->column('related_posts');
            $grid->column('published_at');
            $grid->column('structured_data');
            $grid->column('fb_comment');
            $grid->column('lightbox');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');

            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new Post(), function (Show $show) {
            $show->field('id');
            $show->field('title');
            $show->field('keywords');
            $show->field('description');
            $show->field('directory_fullpath');
            $show->field('html_fullpath');
            $show->field('html_name');
            $show->field('summary');
            $show->field('contents');
            $show->field('template_id');
            $show->field('template_amp_id');
            $show->field('post_status');
            $show->field('editor_json');
            $show->field('editor_id');
            $show->field('lang_id');
            $show->field('related_posts');
            $show->field('published_at');
            $show->field('structured_data');
            $show->field('fb_comment');
            $show->field('lightbox');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Post(), function (Form $form) {
            $form->display('id');
            $form->text('title');
            $form->text('keywords');
            $form->text('description');
            $form->text('directory_fullpath');
            $form->text('html_fullpath');
            $form->text('html_name');
            $form->text('summary');
            $form->text('contents');
            $form->text('template_id');
            $form->text('template_amp_id');
            $form->text('post_status');
            $form->text('editor_json');
            $form->text('editor_id');
            $form->text('lang_id');
            $form->text('related_posts');
            $form->text('published_at');
            $form->text('structured_data');
            $form->text('fb_comment');
            $form->text('lightbox');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }

    public function modifyHtmlFile(Content $content)
    {
        //处理提交请求
        if( request()->isMethod('post') ){
            $form = new Form();
            $requestData = request()->post();
            $filePath = base_path('../').$requestData['path'];
            if( !file_put_contents($filePath,$requestData['html'],LOCK_EX)){
                return $form->response()->error($requestData['path'].'编辑失败');
            }
            return $form->response()->success('编辑成功')->script(
                <<<JS
            var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
            setTimeout(function (){
                parent.layer.close(index); //再执行关闭
            },3000);
JS
            );
        }

        //显示表单
        $fileName = request()->get('file');
        $filePath = base_path('../').$fileName;
        $fileContent = '';
        is_file($filePath) and $fileContent = file_get_contents($filePath);
        $html = Form::make(new Post(), function (Form $form)use($fileContent,$fileName) {
            $form->title('编辑');
            $form->hidden('path')->value($fileName);
            $form->textarea('html',$fileName)->width(10)->rows(80)->value($fileContent)->help('文件位置/assets/js/team/作者名称.js');
            //去掉底部查看按钮
            $form->disableViewCheck();
            //去掉继续编辑
            $form->disableEditingCheck();
            //去掉继续创建
            $form->disableCreatingCheck();
            //去掉右上角列表按钮
            $form->tools(function (Form\Tools $tools) {
                $tools->disableList();
            });
            $form->action(url('admin/post/modifyHtmlFile'));
        });
        return $content->breadcrumb( ['text' => '文章管理'],['text' => '编辑文件'])->body($html);
    }
}
