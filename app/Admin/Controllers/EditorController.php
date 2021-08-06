<?php


namespace App\Admin\Controllers;

use App\Admin\Repositories\Editor;
use App\Http\Requests\CreateEditorRequest;
use App\Models\Lang;
use App\Services\EditorService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Modal;
use Dcat\Admin\Traits\HasUploadedFile;
use Illuminate\Support\Facades\Log;

class EditorController extends  AdminController
{
    use HasUploadedFile;

    protected function grid()
    {
        return Grid::make(\App\Models\Editor::with(['lang','attr']), function (Grid $grid) {
            $grid->column('id');
            $grid->column('lang.lang_name','语言');
            $grid->column('editor_name');
            $grid->column('editor_avatar')->image('',50,50);
            $grid->column('attr')->display(function($attr){
                $tbody = '<div class="table-responsive table-wrapper complex-container table-middle mt-1 table-collapse "><table class="table custom-data-table data-table"><thead><tr><th>属性名</th><th>属性值</th></tr></thead><tbody>';
                foreach ($attr as $item) {
                    $tbody .= '<tr><td>'.$item->key.'</td><td>'.$item->value.'</td></tr>';
                }
                $tbody .= '</tbody></table></div>';
                $modal = Modal::make()
                    ->lg()
                    ->title('作者信息')
                    ->body($tbody)
                    ->button('<button class="btn btn-primary">查看</button>');
                return $modal;
            });

            $grid->column('created_at')->display(function ($created_at){
                return $created_at;
            });
            $grid->column('updated_at')->display(function($updated_at){
                return $updated_at;
            });

            // 禁用行选择器
            $grid->disableRowSelector();
            // 禁用批量删除按钮
            $grid->disableBatchDelete();
            // 禁用详情按钮
            $grid->disableViewButton();


            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');

            });
        });
    }

    protected function form()
    {
        return Form::make(new Editor(), function (Form $form) {
//            $form->ckeditor('content');
                $form->text('editor_name')->required()->rules('regex:/^[a-zA-Z\d]+$/|unique:editor,editor_name',[
                    'regex'=>'作者名称必须为字母',
                    'unique'=>'作者名称已存在'
                ]);
                $langSearchList = Lang::all(['id','lang_name'])->toArray();
                $langSelectList = [];
                foreach ($langSearchList as $item) {
                    $langSelectList[$item['id']] = $item['lang_name'];
                    }
                $form->select('lang_id','语言')->required()->options($langSelectList);
                $form->textarea('editor_intro','简介');
                $form->image('editor_avatar')->required()->url('editor/uploadAvatar');
//                $form->image('editor_avatar')->required()->autoUpload();
                $form->keyValue('attr')->setKeyLabel('属性名')->setValueLabel('属性值');;

                $form->action('editor/createEditor');

            if ($form->isEditing()) {
                $form->action('editor/updateEditor');
            }
        });
    }

    public function createEditor(CreateEditorRequest $request,EditorService $service)
    {

        $data = $request->all();

        try{
            $editor = $service->setName($data['editor_name'])
                ->setLangId($data['lang_id'])
                ->setIntro($data['editor_intro'])
                ->setAvatar($data['editor_avatar']);

            if( array_key_exists('keys',$data['attr'])){
                foreach ($data['attr']['keys'] as $key=>$item) {
                    if( empty($item) ){
                        unset($data['attr']['keys'][$key]);
                        unset($data['attr']['values'][$key]);
                    }
                }

                $editor->setAttr($data['attr']);
            }
            $result = $editor->create();

            $form = new Form();
            if( $result ){
                return $form->response()->success('添加成功')->redirect('/editor');
            }else{
                return $form->response()->error('添加失败');
            }

        }catch (\Exception $exception){
            Log::error($exception->getMessage());
        }


    }
    public function uploadAvatar()
    {
        $disk= $this->disk('admin');
        $file = $this->file();
        $dir = 'images';


        $result = $disk->putFileAs($dir,$file,$file->getClientOriginalName());
        $path = "{$dir}/".$file->getClientOriginalName();

        return $result
            ? $this->responseUploaded($path, $disk->url($path))
            : $this->responseErrorMessage('文件上传失败');


    }
}
