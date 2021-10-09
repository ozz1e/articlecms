<?php

namespace App\Admin\Actions;

use App\Models\Editor;
use App\Models\Post;
use App\Models\Template;
use App\Services\PostService;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\BatchAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * 文章列表批量替换作者batch-action
 * Class BatchReplaceEditor
 * @package App\Admin\Actions
 */
class BatchReplaceEditor extends BatchAction
{
    /**
     * @return string
     */
	protected $title = '替换作者';

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        $requestData = $request->all();
        if( !$requestData['_key'] ){
            return $this->response()->warning('请选择需要替换模板的文章');
        }
        if( !$requestData['editor_id'] ){
            return $this->response()->warning('请选择文章替换作者');
        }

        $editor = Editor::with(['attr'])->find($requestData['editor_id']);
        if( !$editor ){
            return $this->response()->warning('选择作者不存在');
        }

        try{
            //首先更新文章的作者信息
            $service = new PostService();
            foreach ($requestData['_key'] as $item) {
                 $article = Post::with(['attr','editor'])->find($item);
                 $articleArr = $article->toArray();
                 if( $article ){
                     $service = $service->setEditorId($requestData['editor_id'])
                         ->setEditorInfo()
                         ->setEditorJson()
                         ->setTitle($articleArr['title'])
                         ->setHtmlName($articleArr['html_name'])
                         ->setDirFullPath($articleArr['directory_fullpath'])
                         ->setLangId()
                         ->setKeywords(explode(',',$articleArr['keywords']))
                         ->setDescription($article->description)
                         ->setContents($articleArr['contents'])
                         ->setPostAttr($articleArr['attr'])
                         ->setStructuredData()
                         ->setCreatedAt($articleArr['created_at'])
                         ->setUpdatedAt($articleArr['updated_at']);
                     //更新文章的作者信息
                     $article->editor_id = $requestData['editor_id'];
                     $article->editor_json = $service->getEditorJson();
                     $article->structured_data = $service->getStructureData();
                     if( !$article->save() ){
                         return $this->response()->error('ID为'.$item.'的文章替换失败');
                     }
                     $template = Template::query()->find($article->template_id,['file_path','lang_id'])->toArray();
                     //更新信息时模板的语言被修改与原文章的语言不一致
                     //一般不会出现这种情况 保证健壮加上一层判断
                     if( $articleArr['lang_id'] != $template['lang_id'] ){
                         return $this->response()->warning('模板语言与文章语言不一致');
                     }
                     $templateFilePath = public_path('uploads/').$template['file_path'];
                     if( !is_file($templateFilePath) ){
                         return $this->response()->warning('模板文件丢失');
                     }
                     $articleArr['editor_id'] = $requestData['editor_id'];
                     $articleArr['editor_json'] = $service->getEditorJson();
                     $articleArr['structured_data'] = $service->getStructureData();
                     $service->replaceHtmlFile($articleArr,$templateFilePath);
                 }
            }
            return $this->response()
                ->success('作者信息替换成功')
                ->redirect('post');

        }catch (\Exception $exception){
            Log::error($exception->getMessage().'发生在文件'.$exception->getFile().'第'.$exception->getLine().'行');
            return $this->response()->error($exception->getMessage());
        }


    }

    protected function actionScript()
    {
        return <<<JS
function (data, target, action) {
    action.options.key = {$this->getSelectedKeysScript()};
    data.editor_id = $("#replace_editor option:selected").val();
}
JS;
    }

    /**
	 * @return string|array|void
	 */
	public function confirm()
	{
		 return ['确认替换作者?', '替换作者只会替换文章作者相关的信息'];
	}

    /**
     * @param Model|Authenticatable|HasPermissions|null $user
     *
     * @return bool
     */
    protected function authorize($user): bool
    {
        return true;
    }

    /**
     * @return array
     */
    protected function parameters()
    {
        return [];
    }
}
