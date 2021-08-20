<?php

namespace App\Admin\Actions;

use App\Models\Directory;
use Dcat\Admin\Actions\Action;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\False_;

class DeleteDirectory extends Action
{

    protected $directoryId;

    public function __construct($id = null)
    {
        parent::__construct();
        $this->directoryId = $id;
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
        $url = url('admin/directory/');

        return <<<HTML
{$html}
<a data-url={$url}/{$this->directoryId} data-message="ID-{$this->directoryId}的目录信息，注意：目录下的文件不会删除" data-action="delete" data-redirect={$url} style="cursor: pointer" href="javascript:void(0)"><i class="feather icon-trash"></i> 删除 &nbsp;&nbsp;</a>
HTML;
    }

}
