<?php

namespace App\Admin\Actions;

use App\Models\Post;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 *回收站彻底删除文章row-action
 * Class DestroyPost
 * @package App\Admin\Actions
 */
class DestroyPost extends RowAction
{
    /**
     * @return string
     */
	protected $title = '<i class="feather icon-trash-2"></i> 彻底删除';

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        //删除文章在数据库中的记录
        //删除文章的post和amp文件

        $article = Post::onlyTrashed()->find($this->getKey());
        $postFilePath = base_path('../').$article->html_fullpath;
        $ampFilePath = base_path('../').strtr($article->html_fullpath,['--del'=>'.amp']);

        $article->forceDelete();
        is_file( $postFilePath ) and unlink($postFilePath);
        is_file( $ampFilePath ) and unlink($ampFilePath);

        return $this->response()
            ->success('彻底删除成功')->refresh();
    }

    /**
	 * @return string|array|void
	 */
	public function confirm()
	{
		 return ['确定要彻底删除吗？', '删除后文章的记录和html文件都将被清除，不可找回！'];
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

}
