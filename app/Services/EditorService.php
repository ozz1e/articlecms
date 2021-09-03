<?php


namespace App\Services;


use App\Models\Editor;
use App\Models\EditorAttr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EditorService
{
    /**
     * 作者id
     * @var int
     */
    protected $editorId;
    /**
     * 作者名称
     * @var string
     */
    protected $editorName;
    /**
     * 语言id
     * @var int
     */
    protected $langId;
    /**
     * 作者简介
     * @var string
     */
    protected $editorIntro;
    /**
     * 作者头像
     * @var string
     */
    protected $editorAvatar;
    /**
     * 作者属性
     * @var array
     */
    protected $editorAttr;
    /**
     * 跟踪文件代码
     * @var string
     */
    protected $gaFileCode;
    /**
     * 作者跟踪文件目录
     * @var string
     */
    protected $gaFileDir = '/assets/js/team/';

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
    public function setGaCode( $code = '' )
    {
        $this->gaFileCode = $code;
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

        //创建作者的跟踪文件
        $this->createOrEditGaFile();

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

        $editor = Editor::find($this->editorId);
        $oldEditName = $editor->editor_name;
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

        //创建或修改追踪文件
        $this->createOrEditGaFile();
        //如果修改作者名称则删除原来的追踪文件
        if( $oldEditName != $this->editorName ){
            $isModifiedEditorName = true;
            $oldGaFilePath = base_path('../').$this->gaFileDir.$oldEditName.'.js';
            is_file($oldGaFilePath) and unlink($oldGaFilePath);
        }

        if( !is_null($this->editorAttr) ){
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
                    //如果修改作者名称则强制修改其ga_code_url属性
                    if( isset($isModifiedEditorName) ){
                        $item['key'] == 'ga_code_url' and $item['value'] = $this->gaFileDir.$this->editorName.'.js';
                    }
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

    /**
     * 执行删除作者及相关信息
     * @return bool
     */
    public function delete()
    {
        DB::beginTransaction();

        $editor = Editor::find($this->editorId);
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
        //删除作者的追踪文件
        $gaFilePath = base_path('../').$this->gaFileDir.$editor->editor_name.'.js';
        is_file($gaFilePath) and unlink($gaFilePath);

        DB::commit();
        return true;
    }

    /**
     * 执行创建跟踪文件
     * @return false
     */
    public function createOrEditGaFile()
    {
        $gaFullDir = base_path('../').$this->gaFileDir;
        if ( !is_dir($gaFullDir) ){
            if( !mkdir($gaFullDir,0777,true) ){
                DB::rollBack();
                return false;
            }
        }
        $editorGaFilePath = $gaFullDir.$this->editorName.'.js';
        if( !file_put_contents($editorGaFilePath,$this->gaFileCode,LOCK_EX) ){
            DB::rollBack();
            return false;
        }
    }

    /**
     * 返回一个随机的作者id
     * @return mixed
     */
    public static function randomEditor()
    {
        $idsArr = Editor::query()->pluck('id')->all();
        $randKey = array_rand($idsArr);
        return $idsArr[$randKey];
    }

}
