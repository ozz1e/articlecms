<?php


namespace App\Admin\Controllers;

use App\Admin\Actions\DeleteEditor;
use App\Admin\Repositories\Editor;
use App\Http\Requests\EditorRequest;
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


            $grid->actions(function (Grid\Displayers\Actions $actions) {
                //禁用默认的删除按钮
                $actions->disableDelete();
                // append一个操作
                $id = $actions->row->id;
                $actions->append(new DeleteEditor($id));
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
                $form->action('editor/createEditor');

                $editorId = $form->model()->id;

            if ($form->isEditing()) {
                $form->model()->attr->toArray();
                $form->action('editor/'.$editorId.'/updateEditor');
            }

            if ($form->isDeleting()) {
                $form->action('editor/'.$editorId.'/deleteEditor');
            }

            $form->table('attr',function ($table)
            {
                $table->text('key','属性名')->required();
                $table->text('value','属性值')->required();
                $table->hidden('id');
            });


        });
    }

    /**
     * 执行添加作者
     * @param EditorRequest $request
     * @param EditorService $service
     * @return \Dcat\Admin\Http\JsonResponse
     */
    public function createEditor(EditorRequest $request, EditorService $service)
    {

        $data = $request->all();
        $form = new Form();
        try{
            $editor = $service->setName($data['editor_name'])
                ->setLangId($data['lang_id'])
                ->setIntro($data['editor_intro'])
                ->setAvatar($data['editor_avatar']);

            //请求数据中有作者属性则增加添加属性操作
            if( array_key_exists('attr',$data)){
                foreach ($data['attr'] as $key=>$item) {
                    //_remove_为1说明动态增加的该键值对被删除
                    if( $item['_remove_'] == '1' ){
                        unset($data['attr'][$key]);
                    }
                    unset($data['attr'][$key]['_remove_']);
                }
                $editor->setAttr($data['attr']);
            }

            $result = $editor->create();
            if( $result ){
                return $form->response()->success('添加成功')->redirect('/editor');
            }else{
                return $form->response()->error('添加失败');
            }

        }catch (\Exception $exception){
            Log::error($exception->getMessage().'发生在文件'.$exception->getFile().'第'.$exception->getLine().'行');
            return $form->response()->error('添加失败');
        }

    }

    /**
     * 执行更新作者
     * @param EditorRequest $request
     * @param EditorService $service
     * @return \Dcat\Admin\Http\JsonResponse
     */
    public function updateEditor(EditorRequest $request, EditorService $service)
    {
        $data = $request->all();
        $editorId = $request->route('id');
        $form = new Form();

        try{
            $editor = $service->setEditorId($editorId)
                ->setName($data['editor_name'])
                ->setLangId($data['lang_id'])
                ->setIntro($data['editor_intro'])
                ->setAvatar($data['editor_avatar']);

            //请求数据中有作者属性则增加添加属性操作
            if( array_key_exists('attr',$data)){
                $editor->setAttr($data['attr']);
            }

            $result = $editor->update();
            if( $result ){
                return $form->response()->success('修改成功')->redirect('/editor');
            }else{
                return $form->response()->error('修改失败');
            }

        }catch (\Exception $exception){
            Log::error($exception->getMessage().'发生在文件'.$exception->getFile().'第'.$exception->getLine().'行');
            return $form->response()->error('修改失败');
        }


    }

    public function deleteEditor(EditorRequest $request, EditorService $service)
    {
        $data = $request->all();
        dd($data);
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
