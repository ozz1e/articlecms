<?php


namespace App\Services;


use App\Models\Directory;
use Illuminate\Support\Facades\DB;

class DirectoryService
{
    /**
     * 目录id
     * @var int
     */
    protected $id;
    /**
     * 域名
     * @var string
     */
    protected $domain;
    /**
     * 语言id
     * @var int
     */
    protected $langId;
    /**
     * 目录名
     * @var string
     */
    protected $directoryName;
    /**
     * 目录路径
     * @var string
     */
    protected $directoryFullPath;
    /**
     * 目录标题
     * @var string
     */
    protected $directoryTitle;
    /**
     * 目录介绍
     * @var string
     */
    protected $directoryIntro;
    /**
     * post模板id
     * @var int
     */
    protected $templateId;
    /**
     * amp模板id
     * @var int
     */
    protected $templateAmpId;
    /**
     * 页面标题
     * @var string
     */
    protected $pageTitle;
    /**
     * 页面desc
     * @var string
     */
    protected $pageDesc;
    /**
     * 页面关键字
     * @var string
     */
    protected $pageKeywords;

    public function setId( $id = 1 )
    {
        $this->id = $id;
        return $this;
    }

    public function setDomain( $domain = '' )
    {
        $this->domain = $domain;
        return $this;
    }

    public function setLangId( $langId = 1 )
    {
        $this->langId = $langId;
        return $this;
    }
    public function setDirectoryName( $directoryName = '' )
    {
        $this->directoryName = $directoryName;
        return $this;
    }

    public function setDirectoryFullPath( $directoryFullPath = '' )
    {
        $this->directoryFullPath = $directoryFullPath;
        return $this;
    }

    public function setDirectoryTitle( $directoryTitle = '' )
    {
        $this->directoryTitle = $directoryTitle;
        return $this;
    }

    public function setDirectoryIntro( $directoryIntro = '' )
    {
        $this->directoryIntro = $directoryIntro;
        return $this;
    }

    public function setTemplateId( $templateId = 1 )
    {
        $this->templateId = $templateId;
        return $this;
    }

    public function setTemplateAmpId( $templateAmpId = 1 )
    {
        $this->templateAmpId = $templateAmpId;
        return $this;
    }

    public function setPageTitle( $pageTitle = '' )
    {
        $this->pageTitle = $pageTitle;
        return $this;
    }

    public function setPageDesc( $pageDesc = '' )
    {
        $this->pageDesc = $pageDesc;
        return $this;
    }

    public function setPageKeywords( $pageWords = '' )
    {
        $this->pageKeywords = '';
        return $this;
    }

    public function create()
    {
        Directory::create([
            'domain' => $this->domain,
            'lang_id' => $this->langId,
            'directory_name' => $this->directoryName,
            'directory_fullpath' => $this->directoryFullPath,
            'directory_title' => $this->directoryTitle,
            'directory_intro' => $this->directoryIntro,
            'template_id' => $this->templateId,
            'template_amp_id' => $this->templateAmpId,
            'page_title' => $this->pageTitle,
            'page_description' => $this->pageDesc,
            'page_keywords' => $this->pageKeywords
        ]);

        $dirPath = base_path('../').$this->directoryFullPath;
        if( !is_dir( $dirPath ) ){
            return mkdir($dirPath,0777,true);
        }
        return true;

    }

    public function update()
    {
        DB::beginTransaction();

        $dir = Directory::find(intval($this->id));
        if( !$dir ){
            DB::rollBack();
            return false;
        }
        $oldDir = $dir->directory_fullpath;

        $dir->domain = $this->domain;
        $dir->lang_id = $this->langId;
        $dir->directory_name = $this->directoryName;
        $dir->directory_fullpath = $this->directoryFullPath;
        $dir->directory_title = $this->directoryTitle;
        $dir->directory_intro = $this->directoryIntro;
        $dir->template_id = $this->templateId;
        $dir->template_amp_id = $this->templateAmpId;
        $dir->page_title = $this->pageTitle;
        $dir->page_description = $this->pageDesc;
        $dir->page_keywords = $this->pageKeywords;
        if( !$dir->save() ){
            DB::rollBack();
            return false;
        }
        $oldDirPath = base_path('../').$oldDir;
        $newDirPath = base_path('../').$this->directoryFullPath;
        //如果原目录路径不存在就直接创建一个目录 否则修改目录名称
        if( is_dir($oldDirPath) ){
            if( !rename($oldDirPath,$newDirPath) ){
                DB::rollBack();
                return false;
            }
        }else{
            if( !mkdir($newDirPath,0777,true) ){
                DB::rollBack();
                return false;
            }
        }
        DB::commit();
        return true;

    }

    public function includeHtmlFiles()
    {
        //规定文件夹都是在项目目录外
        $realDir = base_path('../').$this->dir;
    }

}
