<?php

namespace App\Admin\Forms;

use App\Models\Editor;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Facades\DB;

class BindEditor extends Form implements LazyRenderable
{
    use LazyWidget; // 使用异步加载功能

    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {
        //选中行的id
        $userId = $this->payload['id'] ?? null;
        $bindData = [];
        foreach ($input['editor_id'] as $item) {
            $bindData[] = ['user_id'=>$userId,'editor_id'=>$item];
        }
        //更新绑定作者的话 首先删除原有的绑定数据 再写入新的绑定数据
        DB::table('user_editor')->where('user_id','=',$userId)->delete();
        $insertNum = DB::table('user_editor')->insert($bindData);
        if( $insertNum > 0 ){
            return $this
                ->response()
                ->success('作者绑定成功')
                ->refresh();
        }
        return $this
				->response()
				->error('作者绑定失败');
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $editors = Editor::with('lang')->get()->toArray();
        $editorBox = [];
        foreach ($editors as $item) {
            $editorBox[$item['id']] = $item['editor_name'].'('.$item['lang']['lang_name'].')';
        }
        $this->checkbox('editor_id','作者')
        ->options($editorBox)
        ->canCheckAll();
    }

    /**
     *弹窗表单的默认数据
     * @return array
     */
    public function default()
    {
        //选中行的id
        $userId = $this->payload['id'] ?? null;
        $editorId = DB::table('user_editor')->where('user_id','=',$userId)->pluck('editor_id')->toArray();
        if( !empty($editorId) ){
            return [
                'editor_id'=>$editorId
            ];
        }
        return [];

    }
}
