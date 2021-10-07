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
            return $this->response()->warning('请选择文章模板');
        }

        $templatePath = Template::query()->find($requestData['template_id'],['file_path'])->toArray();
        if( !$templatePath ){
            return $this->response()->warning('未找到模板信息');
        }
        //获得模板文件的内容
        $templateFilePath = public_path('uploads/').$templatePath['file_path'];
        if( is_file($templateFilePath) ){
            $templateHtmlContent = file_get_contents($templateFilePath);
        }else{
            return $this->response()->warning('模板文件丢失');
        }


        //获得需要替换模板的文章的数据
        $articles = Post::with(['lang','editor','attr'])->whereIn('id',$requestData['_key'])->get();
        $service = new PostService();
        foreach ($articles as $item) {
            $articleObj = $service->setDirFullPath($item->directory_fullpath)->setLangId();

        }


        return $this->response()
            ->success('Processed successfully: '.json_encode($this->getKey()))
            ->redirect('/');
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
		 return ['Confirm?', 'contents'];
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
