<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\DeleteDirectory;
use App\Admin\Actions\IncludeDirectory;
use App\Admin\Repositories\Directory;
use App\Http\Requests\DirectoryRequest;
use App\Models\Lang;
use App\Models\Template;
use App\Services\DirectoryService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Widgets\Modal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PHPUnit\Exception;
use Dcat\Admin\Layout\Content;

class DirectoryController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(\App\Models\Directory::with(['lang','postTemp','ampTemp']), function (Grid $grid) {
            $grid->withBorder();
            $grid->addTableClass(['table-text-center']);
            $grid->column('id')->sortable();
            $grid->column('lang.lang_name','语言');
            $grid->column('directory_fullpath','目录路径')->display(function ($directory_fullpath){
                $result = \App\Models\Directory::with(['articleNum'])->where('directory_fullpath',$directory_fullpath)->select('id','directory_fullpath')->get()->toArray();
                $num = count($result[0]['article_num'])?:0;
                return $directory_fullpath.'<span class="badge" style="background: #587ea4;display: inline-block;float: right;">'.$num.'</span>';
            });
            $grid->column('info','信息')->display(function (){
                $tbody = '<div class="table-responsive table-wrapper complex-container table-middle mt-1 table-collapse "><table class="table custom-data-table data-table"><tbody>';
                $tbody .= '<tr><td width="150">目录标题</td><td>'.$this->directory_title.'</td></tr>';
                $tbody .= '<tr><td width="150">目录介绍</td><td>'.$this->directory_intro.'</td></tr>';
                $tbody .= '<tr><td width="150">目录首页标题</td><td>'.$this->page_title.'</td></tr>';
                $tbody .= '<tr><td width="150">目录首页描述</td><td>'.$this->page_description.'</td></tr>';
                $tbody .= '<tr><td width="150">目录首页关键字</td><td>'.$this->page_keywords.'</td></tr>';
                $tbody .= '</tbody></table></div>';
                $modal = Modal::make()
                    ->lg()
                    ->title($this->directory_fullpath)
                    ->body($tbody)
                    ->button('<button class="btn btn-primary"><i class="feather icon-eye"></i> 查看</button>');
                return $modal;
            });
            $grid->column('postTemp.temp_name','POST模板');
            $grid->column('ampTemp.temp_name','AMP模板');
            $grid->column('created_at','创建时间')->display(function ($created_at){
                return $created_at;
            });
            $grid->column('updated_at','更新时间')->display(function ($updated_at){
                return $updated_at;
            });

            $grid->filter(function (Grid\Filter $filter) {
                // 更改为 panel 布局
                $filter->panel();
                $filter->equal('lang.lang_name','语言')->width(3);

            });
            // 禁用详情按钮
            $grid->disableViewButton();

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                //禁用默认的删除按钮
                $actions->disableDelete();

                $id = $actions->row->id;
                $dirInfo = $actions->row->toArray();
                $actions->append(new DeleteDirectory($id));
                $actions->append(new IncludeDirectory($dirInfo));
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
        return Form::make(new Directory(), function (Form $form) {
            $form->text('domain')->required()->default('www.multcloud.com');
            $langSearchList = Lang::all(['id','lang_name'])->toArray();
            $langSelectList = [];
            foreach ($langSearchList as $item) {
                $langSelectList[$item['id']] = $item['lang_name'];
            }
            $form->select('lang_id','语言')->required()->required()->options($langSelectList)->loads(['template_id','template_amp_id'],['/directory/tempList?t=1','/directory/tempList?t=2']);
            $form->text('directory_name')->required()->placeholder('请输入目录标题，该标题用于页面显示');
            $form->text('directory_fullpath','目录路径')->required()->placeholder('请输入目录近路，以/开头')->help('修改目录路径只能在同一目录下进行，例如/a→/b,不能/a→/b/c');
            $form->text('directory_title')->required();
            $form->text('directory_intro')->required();
            $form->select('template_id','POST模板')->default(1)->help('选择语言后筛选出相应的模板信息');
            $form->select('template_amp_id','AMP模板')->default(1)->help('同上');
            $form->text('page_title','目录首页页面title');
            $form->textarea('page_description','目录首页页面description');
            $form->text('page_keywords','目录首页页面keywords')->help('多个关键词以英文逗号隔开，例如(备份,分区)');

            $dirId = $form->model()->id;

            if ($form->isCreating()) {
                $form->action('directory/createDirectory');
            }

            if ($form->isEditing()) {
                $form->hidden('id')->value($dirId);
                $form->action('directory/'.$dirId.'/updateDirectory');
            }

            //去掉底部查看按钮
            $form->disableViewCheck();
            //去掉继续编辑
            $form->disableEditingCheck();
            //去掉继续创建
            $form->disableCreatingCheck();

            $form->tools(function (Form\Tools $tools) {
                $tools->disableView();
            });
        });
    }

    /**
     * 采集目录文章到数据库
     * @param DirectoryService $service
     * @param Request $request
     * @return \Dcat\Admin\Http\JsonResponse|\Illuminate\Http\JsonResponse
     */
    public function includeDirectory(DirectoryService $service,Request $request)
    {
        //请求数据为数组 ['template_id'=>'','template_amp_id'=>'','directory_fullpath'=>'','directory_title'=>'','lang_id'=>'']
        $requestData = $request->all('dir');
        try{
            $result = $service->setDirInfo($requestData['dir'])
                ->includeHtmlFiles();
            return response()->json($result);

        }catch (\Exception $exception){
            Log::error($exception->getMessage().'发生在文件'.$exception->getFile().'第'.$exception->getLine().'行');
            return response()->json(['code'=>400,'status'=>'error','msg'=>'采集失败']);
        }
    }

    /**
     * 执行创建目录
     * @param DirectoryRequest $request
     * @param DirectoryService $service
     * @return \Dcat\Admin\Http\JsonResponse
     */
    public function createDirectory(DirectoryRequest $request,DirectoryService $service)
    {
        $data = $request->all();
        $form = new Form();
        try{
            $result = $service->setDomain($data['domain'])
                ->setLangId($data['lang_id'])
                ->setDirectoryName($data['directory_name'])
                ->setDirectoryTitle($data['directory_title'])
                ->setDirectoryFullPath($data['directory_fullpath'])
                ->setDirectoryIntro($data['directory_intro'])
                ->setTemplateId($data['template_id'])
                ->setTemplateAmpId($data['template_amp_id'])
                ->setPageTitle($data['page_title'])
                ->setPageDesc($data['page_description'])
                ->setPageKeywords($data['page_keywords'])
                ->create();
            if( $result ){
                return $form->response()->success('添加成功')->redirect('directory');
            }else{
                return $form->response()->warning('目录创建失败，请检查路径或者目录权限');
            }
        }catch (\Exception $exception){
            Log::error($exception->getMessage().'发生在文件'.$exception->getFile().'第'.$exception->getLine().'行');
            return $form->response()->error('创建失败');
        }

    }

    /**
     * 执行更新目录信息
     * @param DirectoryRequest $request
     * @param DirectoryService $service
     * @return \Dcat\Admin\Http\JsonResponse
     */
    public function updateDirectory(DirectoryRequest $request,DirectoryService $service)
    {
        $data = $request->all();
        $form = new Form();
        try{
            $result = $service->setId($data['id'])
                ->setDomain($data['domain'])
                ->setLangId($data['lang_id'])
                ->setDirectoryName($data['directory_name'])
                ->setDirectoryTitle($data['directory_title'])
                ->setDirectoryFullPath($data['directory_fullpath'])
                ->setDirectoryIntro($data['directory_intro'])
                ->setTemplateId($data['template_id'])
                ->setTemplateAmpId($data['template_amp_id'])
                ->setPageTitle($data['page_title'])
                ->setPageDesc($data['page_description'])
                ->setPageKeywords($data['page_keywords'])
                ->update();
            if( $result ){
                return $form->response()->success('修改成功')->redirect('directory');
            }else{
                return $form->response()->error('修改失败');
            }
        }catch (\Exception $exception){
            Log::error($exception->getMessage().'发生在文件'.$exception->getFile().'第'.$exception->getLine().'行');
            return $form->response()->error('修改失败');
        }
    }

    /**
     * 联动语言加载模板
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function tempList(Request $request)
    {
        $langId = $request->get('q');
        $type = $request->get('t');
        return  Template::query()->select('id','temp_name as text')->where('lang_id',$langId)->where('type',$type)->get();
    }

    public function dialogCreateEditor(Request $request)
    {

    }
}
