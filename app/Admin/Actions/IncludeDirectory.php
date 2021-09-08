<?php

namespace App\Admin\Actions;

use Dcat\Admin\Actions\Action;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Form;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class IncludeDirectory extends Action
{
    protected $directoryInfo;


    public function __construct($info = null)
    {
        parent::__construct();
        $this->directoryInfo = $info;
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
       $createEditorUrl = url('admin/editor/create');
       $modifyHtmlUrl = url('admin/post/modifyHtmlFile');
        return <<<JS
$(".collect-article").on('click',function (){
    $.ajax({
    type:'post',
    data:{dir:$(this).data('info')},
    url:'directory/includeDirectory',
    beforeSend:function (){
      Dcat.loading();
    },
    complete:function (){
      Dcat.loading(false);
    },
    success:function (res){
        if( res.code !== 200 ){
            if( res.status === 'error'){
                Dcat.warning(res.msg);
            }else if( res.status === 'failed' ){
                    var addButtonHtml = '';
                if( res.type === 2 ){
                    addButtonHtml = '<span style="display:block;width:100px;margin:10px auto;" class="btn btn-primary create-editor">点击添加</span>';
                }
                layer.open({
                          type: 1 //Page层类型
                          ,area: ['800px','auto']
                          ,title: '提示(添加完所需信息后需要重新采集)'
                          ,shade: 0.6 //遮罩透明度
                          ,maxmin:false
                          ,anim: 5 //0-6的动画形式，-1不开启
                          ,content: '<div style="padding:20px;">'+res.msg+' </div>'+addButtonHtml
                        });
            }
        }else{
            Dcat.success(res.msg);
             setTimeout(function () {
                Dcat.reload();
            }, 3000);
        }
    }

    })
});

$(document).on('click','.create-editor',function (){
    layer.open({
          type: 2,
          title: '创建作者',
          shadeClose: true,
          shade: 0.8,
          area: ['800px', '90%'],
          content: "{$createEditorUrl}",
          end:function (){
              layer.closeAll();
          }
        });
})
$(document).on('click','.update-html',function (){
    var file = $(this).data('file');
    layer.open({
          type: 2,
          title: '编辑文件',
          shadeClose: true,
          shade: 0.8,
          area: ['50%', '90%'],
          content: "{$modifyHtmlUrl}?file="+file,
        });
})
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
        $dirInfo = [
            'template_id'=>$this->directoryInfo['template_id'],
            'template_amp_id'=>$this->directoryInfo['template_amp_id'],
            'directory_fullpath'=>$this->directoryInfo['directory_fullpath'],
            'lang_id'=>$this->directoryInfo['lang_id'],
        ];
        $info = json_encode($dirInfo);

        return <<<HTML
{$html}
<a class="collect-article" data-info='{$info}' style="cursor: pointer" href="javascript:void(0)"><i class="feather icon-command"></i> 采集 &nbsp;&nbsp;</a>
HTML;

    }

    public function confirm()
    {
        return '文件过多时采集需要一定时间，请勿中断操作！';
    }

}
