<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\DeleteDirectory;
use App\Admin\Repositories\Directory;
use App\Models\Lang;
use App\Models\Post;
use App\Models\Template;
use App\Services\DirectoryService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Widgets\Modal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                // append一个操作
                $id = $actions->row->id;
                $actions->append(new DeleteDirectory($id));
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
            $form->text('directory_fullpath','目录路径')->required()->placeholder('请输入目录近路，以/开头');
            $form->text('directory_title')->required();
            $form->text('directory_intro')->required();
            $form->select('template_id','POST模板')->default(1)->help('选择语言后筛选出相应的模板信息');
            $form->select('template_amp_id','AMP模板')->default(1)->help('同上');
            $form->text('page_title','目录首页页面title');
            $form->textarea('page_description','目录首页页面description');
            $form->text('page_keywords','目录首页页面keywords')->help('多个关键词以英文逗号隔开，例如(备份,分区)');

            //去掉底部查看按钮
            $form->disableViewCheck();
            //去掉继续编辑
            $form->disableEditingCheck();
            //去掉继续创建
            $form->disableCreatingCheck();


        });
    }

    public function deleteDirectory(DirectoryService $service,Request $request)
    {
            $id = $request->all('id');
            try{
                $result = $service->setId($id)
                    ->delete();

            }catch (\Exception $exception){

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
}
