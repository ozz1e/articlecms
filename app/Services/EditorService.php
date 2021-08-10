<?php


namespace App\Services;


use App\Models\Editor;
use App\Models\EditorAttr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EditorService
{
    //作者id
    private $editorId;
    //作者名称
    private $editorName;
    //语言id
    private $langId;
    //作者简介
    private $editorIntro;
    //作者头像
    private $editorAvatar;
    //作者属性
    private $editorAttr;

    public function setEditorId($id = 1)
    {
        $this->editorId = $id;
        return $this;
    }

    public function setName($name = '')
    {
        $this->editorName = $name;
        return $this;
    }

    public function setLangId($lang_id = 1)
    {
        $this->langId = $lang_id;
        return $this;
    }

    public function setIntro($intro = '')
    {
        $this->editorIntro = $intro;
        return $this;
    }

    public function setAvatar($avatar = '')
    {
        $this->editorAvatar = $avatar;
        return $this;
    }

    public function setAttr($attr = [])
    {
        $this->editorAttr = $attr;
        return $this;
    }

    /**
     * 执行创建作者
     * @return bool
     */
    public function create()
    {
        DB::beginTransaction();

        //创建并获取新建的作者对象
        $newEditor = Editor::create([
            'editor_name'=>$this->editorName,
            'lang_id'=>$this->langId,
            'editor_intro'=>$this->editorIntro,
            'editor_avatar'=>$this->editorAvatar
        ]);

        $attrArr = $this->editorAttr;
        //如果提交数据包含作者属性则在作者属性表插入对应的数据
        if( !is_null($attrArr) ){
            $attrInsertArr = array_values($attrArr) ;
            foreach ( $attrInsertArr as $key=>$item) {
                $attrInsertArr[$key]['editor_id'] = $newEditor->id;
            }
            $newEditorAttr = EditorAttr::insert($attrInsertArr);
            if( !$newEditorAttr ){
                DB::rollBack();
                return false;
            }
        }

        DB::commit();

        return true;
    }

    /**
     * 执行更新作者
     * @return bool
     */
    public function update()
    {
        DB::beginTransaction();

        $editor = Editor::find(intval($this->editorId));
        if( !$editor ){
            Log::info('未找到ID为'.$this->editorId.'的作者信息');
            return false;
        }
        $editor->editor_name = $this->editorName;
        $editor->lang_id = $this->langId;
        $editor->editor_intro = $this->editorIntro;
        $editor->editor_avatar = $this->editorAvatar;
        if( !$editor->save() ){
            DB::rollBack();
            return false;
        }

        if( !is_null($this->editorAttr) ){
//            $orginAttr = EditorAttr::where('editor_id','=',$this->editorId)->get()->toArray();
            $attrArr = array_values($this->editorAttr);
            //_remove_为1的表示前端已删除改键值对
            foreach ($attrArr as $item) {
                if( $item['_remove_'] == 1 && $item['id'] != null){ //删除属性
                    if( !EditorAttr::destroy($item['id']) ){
                        Log::info('ID为'.$this->editorId.'的作者属性 '.$item['key'].' 删除失败');
                        DB::rollBack();
                        return false;
                    }
                }else if( $item['_remove_'] != 1 && $item['id'] == null){ //添加属性
                    if( !EditorAttr::create(['editor_id'=>$this->editorId,'key'=>$item['key'],'value'=>$item['value']]) ){
                        Log::info('ID为'.$this->editorId.'的作者属性 '.$item['key'].' 添加失败');
                        DB::rollBack();
                        return false;
                    }
                }else{ //修改属性
                    $attrInfo = EditorAttr::findOrFail($item['id']);
                    $attrInfo->key = $item['key'];
                    $attrInfo->value = $item['value'];
                    if( !$attrInfo->save() ){
                        Log::info('ID为'.$this->editorId.'的作者属性 '.$item['key'].' 修改失败');
                        DB::rollBack();
                        return false;
                    }
                }

            }
        }
        DB::commit();
        return true;
    }

    public function delete()
    {
        DB::beginTransaction();

        $editor = Editor::find(intval($this->editorId));
        $avatarFilePath = public_path('uploads').DIRECTORY_SEPARATOR.$editor->editor_avatar;

        if( !$editor ){
            Log::info('未找到ID为'.$this->editorId.'的作者信息');
            return false;
        }
        if( !$editor->delete() ){
            DB::rollBack();
            return false;
        }
        //删除作者的属性
        if( !EditorAttr::where('editor_id','=',$this->editorId)->delete() ){
            DB::rollBack();
            return false;
        }
        //删除作者头像的图片文件
        if( is_file($avatarFilePath) ){
            unlink($avatarFilePath);
        }
        DB::commit();
        return true;
    }

}
