<?php

namespace App\Admin\Actions;

use App\Models\Post;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

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
        //2.将文件名中的'--tmp'去掉
        //3.恢复搜索对此文件的收录

        $post = Post::query()->find($this->getKey());
        if( !$post ){
            return $this->response()->warning('未找到文章信息');
        }
        $post->post_status = 1;
        $post->html_name = strtr($post->html_name,['--tmp'=>'']);
        $postPath = strtr($post->html_fullpath,['--tmp'=>'']);
        $filePath = base_path('../').$postPath;
        rename(base_path('../').$post->html_fullpath,$filePath);
        $post->html_fullpath = strtr($post->html_fullpath,['--tmp'=>'']);
        if( !$post->save() ){
            return $this->response()->error('发布失败');
        }


        if( is_file($filePath) ){
            $postContent = file_get_contents($filePath);
            $this->toggleSEO($postContent);
        }else{
            return $this->response()->warning('html文件丢失');
        }


        return $this->response()
            ->success('发布成功')
            ->redirect('post');
    }

    /**
     * 开启/关闭页面被搜索引擎抓取
     * @param string $content 页面内容
     * @param boolean $toggle 开关
     */
    public function toggleSEO( $content = '' ,$toggle = false )
    {
        $replace = !$toggle ? ['/<meta\s+name="robots"\s+content="index,.*follow,.*all".*\/?>/ismU', '<meta name="robots" content="noindex,nofollow,none"/>'] : ['/<meta\s+name="robots"\s+content="noindex,.*nofollow,.*none".*\/?>/ismU', '<meta name="robots" content="index,follow,all"/>'];
        return preg_replace($replace[0], $replace[1], $content);
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
		// return ['Confirm?', 'contents'];
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
