<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Directory;
use App\Models\Lang;
use App\Models\Template;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Http\Request;

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
            $grid->column('id')->sortable();
            $grid->column('lang.lang_name','语言');
            $grid->column('directory_fullpath','目录路径');
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
            $form->text('domain')->default('www.multcloud.com');
            $langSearchList = Lang::all(['id','lang_name'])->toArray();
            $langSelectList = [];
            foreach ($langSearchList as $item) {
                $langSelectList[$item['id']] = $item['lang_name'];
            }
            $form->select('lang_id','语言')->required()->options($langSelectList)->load('template_id','/directory/postList');
            $form->text('directory_name')->placeholder('请输入目录标题，该标题用于页面显示');
            $form->text('directory_fullpath','目录路径')->placeholder('请输入目录近路，以/开头');
            $form->text('directory_title');
            $form->text('directory_intro');
            $form->select('template_id');
            $form->text('template_amp_id');
            $form->text('page_title');
            $form->text('page_description');
            $form->text('page_keywords');
        });
    }

    protected function detail()
    {
        return 'asdasdasd';
    }

    public function postList(Request $request)
    {
        $postId = $request->get('q');
        return  Template::query()->select('id','temp_name as text')->where('lang_id',$postId)->where('type',1)->get()->toJson();
    }
}
