<?php

namespace App\Admin\Actions;

use Dcat\Admin\Actions\Action;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class DeletePost extends Action
{
    protected $postId;

    public function __construct($id = null)
    {
        parent::__construct();
        $this->postId = $id;
    }

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        // dump($this->getKey());

        return $this->response()->success('Processed successfully.')->redirect('/');
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
     * 设置按钮的HTML，这里我们需要附加上弹窗的HTML
     *
     * @return string|void
     */
    public function html()
    {
        // 按钮的html
        $html = parent::html();
        $url = url('admin/post/');

        return <<<HTML
{$html}
<a data-url={$url}/{$this->postId}/deleteArticle data-message="确定将 ID-{$this->postId} 的文章放进回收站？" data-action="delete" data-redirect={$url} style="cursor: pointer" href="javascript:void(0)"><i class="feather icon-trash"></i> 删除 &nbsp;&nbsp;</a>
HTML;
    }

    /**
     * @return array
     */
    protected function parameters()
    {
        return [];
    }
}
