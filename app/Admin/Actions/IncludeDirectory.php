<?php

namespace App\Admin\Actions;

use Dcat\Admin\Actions\Action;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class IncludeDirectory extends Action
{
    protected $directoryPath;

    protected $modalId = 'show-current-user';

    public function __construct($path = null)
    {
        parent::__construct();
        $this->directoryPath = $path;
    }


    /**
     * 处理响应的HTML字符串，附加到弹窗节点中
     *
     * @return string
     */
    protected function handleHtmlResponse()
    {
        return <<<'JS'
function (target, html, data) {
    var $modal = $(target.data('target'));

    $modal.find('.modal-body').html(html)
    $modal.modal('show')
}
JS;
    }

    protected function actionScript()
    {
        return <<<JS
function (data, target, action) {
    console.log('发起请求之前', data, target, action);

    // return false; 在这里return false可以终止执行后面的操作

    // 更改传递到接口的主键值
    action.options.key = 123;
}
JS;
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

    protected function script()
    {
        return <<<JS
console.log($('.collect-article').length);
$(".collect-article").on('click',function (){
    // console.log($(this).data('path'));
    $.ajax({
    type:'post',
    url:'directory/includeDirectory',
    success:function (res){

    }

    })
});
JS;
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
<a class="collect-article" data-path="{$this->directoryPath}" style="cursor: pointer" href="javascript:void(0)"><i class="feather icon-command"></i> 采集 &nbsp;&nbsp;</a>
HTML;

    }

    public function confirm()
    {
        return '文件过多时采集需要一定时间，请勿中断操作！';
    }

}
