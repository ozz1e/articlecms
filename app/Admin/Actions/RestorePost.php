<?php

namespace App\Admin\Actions;

use App\Models\Post;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * 回收站恢复文章row-action
 * Class RestorePost
 * @package App\Admin\Actions
 */
class RestorePost extends RowAction
{
    /**
     * @return string
     */
	protected $title = '<i class="feather icon-refresh-ccw"></i> 恢复';


    public function handle(Request $request)
    {
        //手动恢复文章
        //删除文章的删除时间
        //恢复后文章为未发布状态
        //将文章的'--del'标识替换为'--tmp'标识
        $article = Post::onlyTrashed()->find($this->getKey());
        $htmlFilePathWithDel = base_path('../').$article->html_fullpath;
        $htmlFilePathWithoutDel = base_path('../').strtr($article->html_fullpath,['--del'=>'--tmp']);
        $article->deleted_at = null;
        $article->post_status = 0;
        $article->html_name = strtr($article->html_name,['--del'=>'--tmp']);
        $article->html_fullpath = strtr($article->html_fullpath,['--del'=>'--tmp']);
        $article->saveOrFail();

        //取消文章html文件的'--del'标识
        if( is_file($htmlFilePathWithDel) ){
            rename($htmlFilePathWithDel,$htmlFilePathWithoutDel);
        }else{
            return $this->response()->error('html文件丢失');
        }

        return $this->response()->success('文章已恢复')->refresh();
    }

    public function confirm()
    {
        return ['确定恢复文章吗？'];
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

}
