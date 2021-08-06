<?php


namespace App\Services;


use App\Models\Editor;
use App\Models\EditorAttr;
use Illuminate\Support\Facades\DB;

class EditorService
{
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

    public function create()
    {
        DB::beginTransaction();

        $newEditor = Editor::create([
            'editor_name'=>$this->editorName,
            'lang_id'=>$this->langId,
            'editor_intro'=>$this->editorIntro,
            'editor_avatar'=>$this->editorAvatar
        ]);

        $attrArr = $this->editorAttr;

        if( !is_null($attrArr) ){
            $attrInsertArr = [];

            foreach ($attrArr['keys'] as $key=>$item) {
                $attrInsertArr[$key]['editor_id'] = $newEditor->id;
                $attrInsertArr[$key]['key'] = $item;
                $attrInsertArr[$key]['value'] = $attrArr['values'][$key];
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

}
