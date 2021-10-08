<?php

namespace App\Admin\Actions;

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

class BatchReplaceTemplate extends BatchAction
{
    /**
     * @return string
     */
	protected $title = '替换模板';

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
        if( !$requestData['template_id'] ){
            return $this->response()->warning('请选择文章替换模板');
        }

        $templatePath = Template::query()->find($requestData['template_id'],['file_path','lang_id'])->toArray();
        if( !$templatePath ){
            return $this->response()->warning('未找到模板信息');
        }
        //获得模板文件的内容
        $templateFilePath = public_path('uploads/').$templatePath['file_path'];
        if( !is_file($templateFilePath) ){
            return $this->response()->warning('模板文件丢失');
        }

        try{
            //首先批量更新文章的template_id
            $affectedNum = Post::query()->whereIn('id',$requestData['_key'])->update(['template_id'=>$requestData['template_id']]);
            if( $affectedNum <= 0 ){
                return $this->response()->error('模板替换失败');
            }
            //获得需要替换模板的文章的数据
            $articles = Post::with(['lang','editor','attr'])->whereIn('id',$requestData['_key'])->get()->toArray();
            $service = new PostService();
            foreach ($articles as $item) {
                if( $item['lang_id'] != $templatePath['lang_id'] ){
                    return $this->response()->warning('模板语言必须与文章语言一致');
                }
                $service->setDirFullPath($item['directory_fullpath'])
                    ->setLangId()
                    ->setEditorId($item['editor_id'])
                    ->setHtmlName($item['html_name'])
                    ->setUpdatedAt($item['updated_at'])
                    ->setPostAttr($item['attr'])
                    ->replaceHtmlFile($item,$templateFilePath);
            }
            return $this->response()
                ->success('模板替换成功')->redirect('post');
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
    data.template_id = $("#replace_template option:selected").val();
}
JS;
}

    /**
	 * @return string|array|void
	 */
	public function confirm()
	{
		 return ['确定替换模板?', '请注意模板文件的语言'];
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
