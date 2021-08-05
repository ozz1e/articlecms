<?php


namespace App\Services;


use App\Models\Editor;

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

    public function index()
    {
//        Editor::create([
//            'editor_name'=>$this->editorName,
//            'lang_id'=>$this->langId,
//            'editor_intro'=>$this->editorIntro,
//            'editor_avatar'=>$this->editorAvatar,
//            'type'=>0,
//        ]);
        $editor = new Editor();
        $editor->editor_name = $this->editorName;
        $editor->lang_id = $this->langId;
        $editor->editor_intro = $this->editorIntro;
        $editor->editor_avatar = $this->editorAvatar;
        $editor->save();

        return true;
    }

}
