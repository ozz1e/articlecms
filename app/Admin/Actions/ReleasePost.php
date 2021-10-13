<?php

namespace App\Admin\Actions;

use App\Models\Post;
use App\Services\PostService;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * 文章列表行按钮 发布文章row-action
 * Class ReleasePost
 * @package App\Admin\Actions
 */
class ReleasePost extends RowAction
{
    /**
     * @return string
     */
	protected $postId;
	protected $title = '<i class="feather icon-navigation"></i> 发布';

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        //1.将即将发布的文章的状态post_status改为1
        //2.将post文件名中的'--tmp'去掉
        //3.恢复搜索引擎对post和amp文件的收录

        $post = Post::query()->find($this->getKey());
        if( !$post ){
            return $this->response()->warning('未找到文章信息');
        }
        $post->post_status = 1;
        $post->html_name = strtr($post->html_name,['--tmp'=>'']);
        $postPath = strtr($post->html_fullpath,['--tmp'=>'']);
        $ampPath = strtr($postPath,['.html'=>'.amp.html']);
        $postFilePath = base_path('../').$postPath;
        $ampFilePath = base_path('../').$ampPath;
        //amp文件未发布状态没有'--tmp'标识 文件名不需要重命名
        rename(base_path('../').$post->html_fullpath,$postFilePath);
        $post->html_fullpath = strtr($post->html_fullpath,['--tmp'=>'']);
        if( !$post->save() ){
            return $this->response()->error('发布失败');
        }

        if( is_file($postFilePath) && is_file($ampFilePath) ){
            $postContent = file_get_contents($postFilePath);
            $ampContent = file_get_contents($ampFilePath);
            $service = new PostService();
            $handledPostContent = $service->toggleSEO($postContent,true);
            $handledAmpContent = $service->toggleSEO($ampContent,true);
            file_put_contents($postFilePath,$handledPostContent);
            file_put_contents($ampFilePath,$handledAmpContent);
            $service->toggleSEO($ampContent,true);
        }else{
            return $this->response()->warning('html文件丢失');
        }

        return $this->response()
            ->success('发布成功')
            ->redirect('post');
    }

    /**
     * @return string|void
     */
    protected function href()
    {
        // return admin_url('auth/users');
    }

    /**
	 * @return string|array|void
	 */
	public function confirm()
	{
		 return ['确认发布这篇文章吗？'];
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
