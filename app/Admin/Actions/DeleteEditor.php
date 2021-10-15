<?php

namespace App\Admin\Actions;

use App\Services\EditorService;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Admin;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeleteEditor extends RowAction
{
    protected $editorId;
    /**
     * 按钮标题
     * @return string
     */
    protected $title = '<i class="feather icon-trash"></i> 删除';

    public function __construct($id = null)
    {
        parent::__construct();
        $this->editorId = $id;
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
        if( !is_numeric($this->getKey()) ){
            return $this->response()->error('提交作者信息有误');
        }

        try{
            $service = new EditorService();
            $result = $service->setEditorId($this->getKey())
                ->delete();
            if( $result ){
                return $this->response()->success('删除成功')->redirect('/editor');
            }else{
                return $this->response()->error('删除失败');
            }
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
         return ['确定要删除吗?', "ID-{$this->getKey()}的作者"];
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
     * @param Model|Authenticatable|HasPermissions|null $user
     *
     * @return bool
     */
    protected function authorize($user): bool
    {
        //管理员才有权限进行删除
        if( !Admin::user()->inRoles(['administrator', 'manager']) ){
            return false;
        }
        return true;
    }

}
