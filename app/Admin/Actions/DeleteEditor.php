<?php

namespace App\Admin\Actions;

use Dcat\Admin\Actions\Action;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class DeleteEditor extends Action
{
    protected $editorId;

    public function __construct($id = null)
    {
        parent::__construct();
        $this->editorId = $id;
    }

    /**
     * 按钮标题
     * @return string
     */
//	protected $title = '删除';

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
//    public function handle(Request $request)
//    {
//        // dump($this->getKey());
//
//        return $this->response()->success('Processed successfully.')->redirect('/');
//    }

    /**
     * @return string|array|void
     */
    public function confirm()
    {
        // return ['Confirm?', 'contents'];
    }

    /**
     * 设置HTML标签的属性
     *
     * @return void
     */
    protected function setupHtmlAttributes()
    {
        parent::setupHtmlAttributes();
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
        $url = url('admin/editor');

        return <<<HTML
{$html}
<a data-url={$url}/{$this->editorId}/deleteEditor data-message="ID-{$this->editorId}的作者" data-action="delete" data-redirect={$url} style="cursor: pointer" href="javascript:void(0)"><i class="feather icon-trash"></i> 删除 &nbsp;&nbsp;</a>
HTML;
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
//    protected function parameters()
//    {
//        return [];
//    }
}
