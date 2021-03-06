<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\BatchReplaceEditor;
use App\Admin\Actions\BatchReplaceTemplate;
use App\Admin\Actions\CopyHtml;
use App\Admin\Actions\DeletePost;
use App\Admin\Actions\DestroyPost;
use App\Admin\Actions\EditorSelect;
use App\Admin\Actions\ReleasePost;
use App\Admin\Actions\RestorePost;
use App\Admin\Actions\TemplateSelect;
use App\Http\Requests\PostRequest;
use App\Models\Directory;
use App\Models\Editor;
use App\Models\Lang;
use App\Models\Post;
use App\Models\Template;
use App\Services\PostService;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Widgets\Alert;
use Dcat\Admin\Widgets\Modal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PostController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(Post::with(['lang','attr']), function (Grid $grid) {
            $grid->model()->orderBy('id', 'desc');
            $grid->withBorder();
            $grid->addTableClass(['table-text-center']);
            $grid->column('id')->width('55px')->sortable();
//            $grid->column('html_fullpath')->display(function ($html_fullpath){
//                return '<a href="/'.$html_fullpath.'" target="_blank">'.$html_fullpath.'</a>';
//            });
            $grid->column('html_fullpath')->link(function ($value){
               return $value;
            })->setAttributes(['class' => 'text-left']);
            $grid->column('attr','作者')->width('150px')->display(function($attr){
                $tbody = '<div class="table-responsive table-wrapper complex-container table-middle mt-1 table-collapse "><table class="table custom-data-table data-table"><thead><tr><th>属性名</th><th>属性值</th></tr></thead><tbody>';
                foreach ($attr as $item) {
                    $tbody .= '<tr><td>'.$item->key.'</td><td>'.$item->value.'</td></tr>';
                }
                $tbody .= '</tbody></table></div>';
                $modal = Modal::make()
                    ->lg()
                    ->title('作者信息')
                    ->body($tbody)
                    ->button('<button class="btn btn-primary"><i class="feather icon-eye"></i> '.$this->editor->editor_name.'</button>');
                return $modal;
            });
            $grid->column('lang.lang_name','语言')->width('60px');
            $grid->column('template_id')->width('180px')->select($this->templateList(),true)->help('选择即可更换文章模板');
            $grid->column('post_status')->width('95px')->using([0=>'未发布',1=>'已发布',2=>'已删除'])
            ->dot([
                0=>'warning',
                1=>'success',
                2=>'danger'
            ]);

            $grid->column('created_at')->width('210px')->display(function ($created_at){
                return $created_at;
            });
            $grid->column('updated_at')->width('210px')->display(function($updated_at){
                return $updated_at;
            })->sortable();

            // 禁用详情按钮
            $grid->disableViewButton();

            $grid->filter(function (Grid\Filter $filter) {
                // 更改为 panel 布局
                $filter->panel();
                $filter->equal('id')->width(3);
                $filter->equal('lang_id')->select($this->langList())->width(3);
                $filter->equal('editor_id')->select($this->editorList())->width(3);
                $filter->equal('post_status')->select(['未发布','已发布'])->width(3);
                $filter->equal('directory_fullpath')->select($this->directoryList())->width(3);
                $filter->like('html_name','文件名')->width(3);

                // 范围过滤器，调用模型的`onlyTrashed`方法，查询出被软删除的数据。
                $filter->scope('trashed', '回收站')->onlyTrashed();
            });

            $grid->actions(function (Grid\Displayers\Actions $actions){
                $actions->disableDelete();
                $id = $actions->row->id;
                //回收站列表时行按钮只显示 彻底删除和恢复
                if( request('_scope_') != 'trashed' ){
                    //当登录账号不是管理员也不是文章拥有者时 隐藏编辑按钮
                    if( !Admin::user()->inRoles(['administrator', 'manager']) ){
                        $postEditor = Post::query()->find($id,'editor_id');
                        $postUser = DB::table('user_editor')->where('editor_id',$postEditor['editor_id'])->pluck('user_id')->toArray();
                        if( !in_array(Admin::user()->id,$postUser) ){
                            $actions->disableEdit();
                        }
                    }
                    $actions->append(new DeletePost());
                    if( $actions->row->post_status == 0 ){
                        $actions->append(new ReleasePost());
                    }
                }else{
                    $actions->disableEdit();
                    $actions->append(new RestorePost());
                    $actions->append(new DestroyPost());
                }


            });

            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                        $batch->add(new BatchReplaceTemplate());
                        $batch->add(new BatchReplaceEditor());
                });
            });

            //回收站因为权限问题暂时不涉及批量恢复和批量删除
            if (request('_scope_') != 'trashed') {
                $grid->tools(new TemplateSelect());
                $grid->tools(new EditorSelect());
            }else{
                $grid->disableRowSelector();
            }


        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(Post::with(['attr']), function (Form $form) {
            $form->block(8, function (Form\BlockForm $form) {
                $form->text('title')->required()->width(11,1);
                $form->text('html_name')->required()->width(11,1)->placeholder('输入文章 file name，例如 this-is-an-example-file-name-on-05-22-2019.html')->customFormat(function (){
                    return strtr($this->html_name,['--tmp.html'=>'','.html'=>'']);
                });
                $form->ckeditor('contents')->required()->width(11,1)->attribute(['id'=>'normal_mode'])->setFieldClass('include-css')->customFormat(function (){
                    return html_entity_decode(htmlspecialchars_decode($this->contents));
                });
                $form->ckeditor('related_posts')->width(11,1)->attribute(['id'=>'plain_mode'])->label('相关文章');
                admin_css(["assets/css/postAttr.css"]);
                $form->table('attr', function (Form\NestedForm $table) {
                    //$table->select('post_attr','属性名')->attribute(['class'=>'col-md-3'])->options(['cover','next_page','popular_articles','quick_search','read_time','summary_articles','publish_date']);
//                    $table->select('post_attr','属性名')->attribute(['class'=>'col-md-3'])->options(['next_page'=>'next_page','popular_articles'=>'popular_articles','quick_search'=>'quick_search','publish_date'=>'publish_date']);
//                    $table->ckeditor('post_attr_value','属性值')->setElementClass('attr_editor');
                    $table->select('post_key','属性名')->attribute(['class'=>'col-md-3'])->options(['next_page'=>'next_page','popular_articles'=>'popular_articles','quick_search'=>'quick_search','publish_date'=>'publish_date']);
                    $table->ckeditor('post_value','属性值')->setElementClass('attr_editor');
                })->width(11,1)->label('属性');

                // 显示底部提交按钮
                $form->showFooter();
            });
            $form->block(4, function (Form\BlockForm $form) {
                //如果登录账号未绑定作者 弹出提示信息 不允许继续操作
                if( empty($this->editorList()) ){
//                    admin_exit(
//                        Content::make()
//                            ->title('文章')
//                            ->description('列表')
//                            ->body(Alert::make('改账号还未绑定作者，请联系管理绑定！', '提示')->warning())
//                    );
                    admin_warning('提示', '改账号还未绑定作者，请联系管理绑定！');
                }
                $form->select('editor_id')->required()->options($this->editorList())->width(9,3);
                if( $form->isEditing() ){
                    $form->hidden('id');
                    $form->select('directory_fullpath')->options($this->directoryList())->loads(['template_id','template_amp_id'],['post/loadPostList','post/loadAmpList'])->width(9,3)->disable();
                    $form->select('template_id')->width(9,3)->default(1)->disable();
                    $form->select('template_amp_id')->width(9,3)->default(1,true)->disable();
                    //解决在编辑模式下浏览资源时路径错误的未知问题
                    Admin::script(
                        <<<JS
$('#normal_mode').ckeditor({
filebrowserBrowseUrl: '../../../../../0d958d0af15d73beeec6852c13911a700/ckfinder.html',
filebrowserFlashBrowseUrl: "../../../../../0d958d0af15d73beeec6852c13911a700/ckfinder.html",
filebrowserFlashUploadUrl: "../../../../../0d958d0af15d73beeec6852c13911a700/core/connector/php/connector.php?command=QuickUpload&type=Flash",
filebrowserImage2BrowseUrl: "../../../../../0d958d0af15d73beeec6852c13911a700/ckfinder.html",
filebrowserImage2UploadUrl: "../../../../../0d958d0af15d73beeec6852c13911a700/core/connector/php/connector.php?command=QuickUpload&type=screenshot",
filebrowserImageBrowseUrl: "../../../../../0d958d0af15d73beeec6852c13911a700/ckfinder.html",
filebrowserImageUploadUrl: "../../../../../0d958d0af15d73beeec6852c13911a700/core/connector/php/connector.php?command=QuickUpload&type=screenshot",
filebrowserUploadUrl: "../../../../../0d958d0af15d73beeec6852c13911a700/core/connector/php/connector.php?command=QuickUpload&type=Files"
})
JS
                    );
                }else{
                    $form->select('directory_fullpath')->required()->options($this->directoryList())->loads(['template_id','template_amp_id'],['post/loadPostList','post/loadAmpList'])->width(9,3);
                    $form->select('template_id')->required()->width(9,3)->default(1);
                    $form->select('template_amp_id')->required()->width(9,3)->default(1,true);
                }
                $form->textarea('description')->required()->width(9,3);
                //Todo 建立关键词库
//                $form->tags('keywords')->width(9,3)->required()->help('键入关键词后以英文逗号结束，回车后即可生成');
                $form->textarea('keywords')->width(9,3)->required();
//                $form->tags('post_function')->width(9,3)->required()->help('键入功能后以英文逗号结束，回车后即可生成');
                $form->text('post_function')->width(9,3)->required();
                $form->textarea('summary')->required()->width(9,3);

                $form->next(function (Form\BlockForm $form) {
                    $form->title('文章插件');
                    $form->radio('fb_comment','FaceBook评论')->options(['关闭',  '开启'])->default(1)->width(9,3);
                    $form->radio('lightbox','lightBox幻灯')->options(['关闭',  '开启'])->default(1)->width(9,3);
                    $form->radio('article_index','目录索引')->options(['关闭',  '开启'])->default(1)->width(9,3);
                });

                $form->next(function (Form\BlockForm $form) {
                    $form->title('文章Block');
                    $form->row(function (Form\Row $form) {
                        $blockList = $this->postBlockList();
                        foreach ($blockList as $key=>$item) {
                            $_form = new Form();
                            $_form->disableHeader();
                            $_form->disableViewCheck();
                            $_form->disableEditingCheck();
                            $_form->disableCreatingCheck();
                            $_form->disableResetButton();
                            $_form->disableSubmitButton();
                            $_form->tools(function (Form\Tools $tools) {
                                $tools->disableView();
                                $tools->disableList();
                            });

                            $_form->radio($item->title)
                                ->when(1, function ()use($_form,$item){
                                    $_form->html($item->content)->width(12);
                                    admin_css(["assets/css/post_block/{$item->title}.css"]);
                                })
                                ->when(2, function ()use($_form,$item) {
                                    $_form->textarea('content','代码')->rows(10)->width(11,1)->default($item->content);
                                })->options([
                                    1 => '示例',
                                    2 => '源码',
                                ])->default(2);

                            $varName = 'modal'.$key;
                            $cssFilePath = '/assets/css/post_block/'.$item->title.'.css';
                            $$varName  = Modal::make()
                                ->lg()
                                ->title($item->title)
                                ->body($_form)
                                ->button('<button class="btn btn-primary post-block-btn" data-cssFileName="'.$item->title.'" data-cssFilePath="'.admin_asset($cssFilePath).'">'.$item->title.'</button>');
                            $form->width(4)->html($$varName);
                            $copyScript = <<<EOF
var targetNode = $(this).closest('form').find('textarea');
targetNode.select();
document.execCommand("Copy");
layer.msg("复制成功");
EOF;

                            $_form->button("复制代码")->on('click', $copyScript);;
                        }
                    });
                });
            });

            if ($form->isCreating()) {
                $form->action('post/createArticle');
            }
            if ($form->isEditing()) {
                $form->action('post/updateArticle');
            }
        });
    }

    /**
     * 执行创建文章
     * @param PostRequest $request 文章创建请求对象
     * @param PostService $service 文章创建服务对象
     * @return \Dcat\Admin\Http\JsonResponse
     */
    public function createArticle(PostRequest $request,PostService $service)
    {
        $data = $request->post();
        $form = new Form();
           try{
            $article = $service->setTitle($data['title'])
                ->setKeywords($data['keywords'])
                ->setPostFunction($data['post_function'])
                ->setDescription($data['description'])
                ->setDirFullPath($data['directory_fullpath'])
                ->setHtmlName(filterPostFileName($data['html_name']))
                ->setHtmlFullPath()
                ->setAmpFullPath($data['html_name'])
                ->setSummary($data['summary'])
                ->setContents($data['contents'])
                ->setAttr($data['attr']??[])
                ->setTemplateId($data['template_id'])
                ->setTemplateAmpId($data['template_amp_id'])
                ->setEditorId($data['editor_id'])
                ->setEditorInfo()
                ->setEditorJson()
                ->setLangId()
                ->setRelatedPosts($data['related_posts'])
                ->setStructuredData()
                ->setFaceBookComment($data['fb_comment'])
                ->setLightBox($data['lightbox'])
                ->setArticleIndex($data['article_index']);
            array_key_exists('attr',$data) and $article = $article->setAttr($data['attr']);
            $article->generateHtmlFile();
            return $form->response()->success('文章创建成功')->redirect('post');

        }catch (\Exception $exception){
            Log::error($exception->getMessage().'发生在文件'.$exception->getFile().'第'.$exception->getLine().'行');
            return $form->response()->error($exception->getMessage());
        }
    }


    public function updateArticle(PostRequest $request,PostService $service)
    {
        $data = $request->all();
        $form = new Form();
        try{
            $article = $service->setId($data['id'])
                ->setPostObj()
                ->setTitle($data['title'])
                ->setHtmlName(filterPostFileName($data['html_name']))
                ->setKeywords($data['keywords'])
                ->setPostFunction($data['post_function'])
                ->setDescription($data['description'])
                ->setSummary($data['summary'])
                ->setContents($data['contents'])
                ->setAmpFullPath($data['html_name'])
                ->setAttr($data['attr']??[])
                ->setEditorId($data['editor_id'])
                ->setEditorInfo()
                ->setEditorJson()
                ->setRelatedPosts($data['related_posts'])
                ->setStructuredData()
                ->setFaceBookComment($data['fb_comment'])
                ->setLightBox($data['lightbox'])
                ->setArticleIndex($data['article_index']);
            array_key_exists('attr',$data) and $article = $article->setAttr($data['attr']);
            $article->generateHtmlFile(2);
            return $form->response()->success('文章修改成功')->redirect('post');
        }catch (\Exception $exception){
            Log::error($exception->getMessage().'发生在文件'.$exception->getFile().'第'.$exception->getLine().'行');
            return $form->response()->error($exception->getMessage());
        }
    }

    public function deleteArticle(PostService $service)
    {
        $form = new Form();
        $postId = request()->route('id');
        if( !is_numeric($postId) ){
            return $form->response()->error('文章信息有误');
        }

        try{
            $service->setId($postId)
                ->delete();
            return $form->response()->success('删除成功')->redirect('/post');
        }catch (\Exception $exception){
            Log::error($exception->getMessage().'发生在文件'.$exception->getFile().'第'.$exception->getLine().'行');
            return $form->response()->error('删除失败');
        }
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

    /**
     * 获取模板列表api
     * @return array
     */
    public function templateList( $type = 1 )
    {
        return Template::query()->where('type',$type)->pluck('temp_name','id')->toArray();
    }

    /**
     * 获取语言列表api
     * @return array
     */
    public function langList()
    {
        return Lang::query()->pluck('lang_name','id')->toArray();
    }

    /**
     * 获取当前登录账号绑定的作者列表 api
     */
    public function editorList()
    {
        //管理员默认显示所有作者
        //administrator为超级管理员
        //manager为普通管理员
        if( Admin::user()->inRoles(['administrator', 'manager']) ){
            return Editor::query()->pluck('editor_name','id')->toArray();
        }else{
            $editorId = DB::table('user_editor')->where('user_id',Admin::user()->id)->pluck('editor_id')->toArray();
            return Editor::query()->whereIn('id',$editorId)->pluck('editor_name','id')->toArray();
        }

    }

    /**
     * 获取目录列表api
     * @return array
     */
    public function directoryList()
    {
        return Directory::query()->pluck('directory_fullpath','directory_fullpath')->toArray();
    }

    /**
     * 联动加载post列表api
     * @return array|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function loadPostList()
    {
        $path = request()->all('q');
        $dirInfo = Directory::with('lang')->where('directory_fullpath',$path)->first()->toArray();
        if( !$dirInfo ){
            return $this->templateList();
        }
        return Template::query()->select('id','temp_name as text')->where('lang_id',$dirInfo['lang']['id'])->where('type',1)->get();
    }

    /**
     * 联动加载amp模板api
     * @return array|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function loadAmpList()
    {
        $path = request()->all('q');
        $dirInfo = Directory::with('lang')->where('directory_fullpath',$path)->first()->toArray();

        if( !$dirInfo ){
            return $this->templateList(2);
        }
        return Template::query()->select('id','temp_name as text')->where('lang_id',$dirInfo['lang']['id'])->where('type',2)->get();
    }

    /**
     * 获取文章block api
     * @return \Illuminate\Support\Collection
     */
    public function postBlockList()
    {
        return DB::table('post_block')->select(['id','title','content','description'])->get();
    }
}
