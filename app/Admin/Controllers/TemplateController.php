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
use Illuminate\Support\Facades\Route;

class TemplateController extends  AdminController
{
    protected function grid()
    {
        return Grid::make(\App\Models\Template::with(['lang']),function (Grid $grid){
            $grid->column('id');
            $grid->column('temp_name','模板名称(点击可修改模板名称)')->editable(true);
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
                $actions->prepend('<a href="template/'.$id.'/editOnLine"><i class="fa fa-paper-plane"></i> 编辑</a>');
            });
            // 禁用详情按钮
            $grid->disableViewButton();
        });
    }

    protected function form()
    {
        return Form::make(new Template(), function (Form $form) {
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

    /**
     * 在线编辑模板文件
     * @param Content $content
     * @param Request $request
     * @return Content
     */
    public function editTemplate(Content $content,Request $request)
    {
        $form = new Form();
        $form->title('编辑');
        //去掉底部查看按钮
        $form->disableViewCheck();
        //去掉继续编辑
        $form->disableEditingCheck();
        //去掉继续创建
        $form->disableCreatingCheck();
        //去掉重置创建
        $form->disableResetButton();
        $form->tools(function (Form\Tools $tools) {
            // 去掉跳转列表按钮
            $tools->disableList();
            // 去掉跳转详情页按钮
            $tools->disableView();
            // 去掉删除按钮
            $tools->disableDelete();

            // 添加一个按钮, 参数可以是字符串, 匿名函数, 或者实现了Renderable或Htmlable接口的对象实例
            $tools->append('<a class="btn btn-sm btn-primary" style="color: #fff;" href="'.url('admin/template').'"><i class="feather icon-list"></i>&nbsp;&nbsp;列表</a>');
        });
        $templateId = $request->route()->id;
        $form->action('template/'.$templateId.'/saveTemplate');
        $form->confirm('您确定要保存编辑内容吗？', '保存后会直接覆盖原文件，请谨慎操作！');

        $title = '';
        $fileContent = '';
        $id = $request->route('id');
        if( is_numeric($id) ){
            $template = \App\Models\Template::select(['id','temp_name','file_path'])->find($id);
            if( !is_null($template) ){
                $templateArr = $template->toArray();
                $title = $templateArr['temp_name'];
                $filePath = public_path('uploads'.DIRECTORY_SEPARATOR).$templateArr['file_path'];
                is_file($filePath) and $fileContent = file_get_contents($filePath);
            }
        }


        $form->php('code',$title)->help('若未找到文件则显示空白,点击保存仍会生成相应内容的html文件')->value($fileContent);
        return $content
            ->header('在线修改模板文件')
            ->body($form);
    }

    public function saveTemplate(Request $request)
    {
        $data = $request->post();
        $form = new Form();
        $id = $request->route('id');
        if( is_numeric($id) ){
            $template = \App\Models\Template::select(['file_path'])->find($id);
            if( !is_null($template) ){
                $templateArr = $template->toArray();
                //文件本地上传的路径
                $fileUploadPath = config('filesystems.disks.admin.root').DIRECTORY_SEPARATOR.config('admin.upload.directory.file');
                if( !is_dir($fileUploadPath) ){
                    mkdir($fileUploadPath,0777,true);
                }
                $filePath = public_path('uploads'.DIRECTORY_SEPARATOR).$templateArr['file_path'];
                $result = file_put_contents($filePath,$data['code']?:'',LOCK_EX);
                if( $result >= 0 ){
                    return $form->response()->success('保存成功');
                }else{
                    return $form->response()->error('保存失败')->refresh();
                }
            }else{
                return $form->response()->error('数据有误，请稍后再试')->refresh();
            }
        }else{
            return $form->response()->redirect('template');
        }
    }

}
