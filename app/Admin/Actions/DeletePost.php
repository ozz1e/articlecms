<?php

namespace App\Admin\Actions;

use App\Services\PostService;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * 文章列表行按钮 删除文章 row-action
 * Class DeletePost
 * @package App\Admin\Actions
 */
class DeletePost extends RowAction
{

    protected $title = '<i class="feather icon-trash"></i> 删除';

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        if( !is_numeric($this->getKey()) ){
            return $this->response()->warning('文章信息有误');
        }

        try{
            $service = new PostService();
            $service->setId($this->getKey())
                ->delete();
            return $this->response()->success('删除成功')->refresh();
        }catch (\Exception $exception){
            Log::error($exception->getMessage().'发生在文件'.$exception->getFile().'第'.$exception->getLine().'行');
            return $this->response()->error('删除失败');
        }
    }

    /**
     * @return string|array|void
     */
    public function confirm()
    {
         return ['确定删除文章？', '确定将 ID-'.$this->getKey().' 的文章放进回收站？'];
    }

    /**
     * @param Model|Authenticatable|HasPermissions|null $user
     *
     * @return bool
     */
    protected function authorize($user): bool
    {
        //当登录账号不是管理员也不是文章拥有者时 没有操作权限
        return checkPostOwner($this->getKey());

    }

    /**
     * @return array
     */
    protected function parameters()
    {
        return [];
    }
}
