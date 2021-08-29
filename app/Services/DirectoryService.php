<?php


namespace App\Services;


use App\Models\Directory;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

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
    /**
     * 目录信息
     * @var array
     */
    protected $dirInfo;
    /**
     * 文章内容的tag标签
     */
    const CONTENT_TAG = '<!--ART_CONTENT-->';

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

    public function setDirInfo( $info = [] )
    {
        $this->dirInfo = $info;
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
        $dirInfo = $this->dirInfo;
        $realDir = base_path('../').$dirInfo['directory_fullpath'];
        if( !is_dir($realDir) ){
            return ['status' => 'error','code'=>Response::HTTP_PRECONDITION_FAILED,'msg'=>'请确认目录路径'];
        }
        $allFileInDir = getDir($realDir);
        $htmlInDir = [];
        foreach ($allFileInDir as $item) {
            //筛选出不含'--tmp'和'amp'的html文件
            if( pathinfo($item)['extension'] == 'html' && !stripos($item,'--tmp') && !stripos($item,'amp')){
                $htmlInDir[] = $item;
            }
        }
        $htmlInDb = Post::query()->where('directory_fullpath',$dirInfo['directory_fullpath'])->pluck('html_name')->toArray();
        //即将入库的文件
        $diffFilesArr = array_diff($htmlInDir,$htmlInDb);
        if( empty($diffFilesArr) ){
            return ['status' => 'success','code'=>Response::HTTP_ACCEPTED,'msg'=>'没有合适的文件需要采集'];
        }


        return true;
    }

    public function transferHtmlToArr( $htmlArr = [] )
    {

    }

    /**
     * 匹配html文件中的文章内容并返回
     * @param string $htmlContent
     * @return false|string
     */
    public function matchHtmlContent( $htmlContent = '' )
    {
        $reg = "/".self::CONTENT_TAG."(.*)".self::CONTENT_TAG."/imsU";
        $result = preg_match($reg, $htmlContent, $matches);
        if(!$result) {
            return false;
        }
        //Todo
        //removeH1   removeLazyImage
        return filterHtml($matches[1]);
    }


    /**
     * 匹配html文件中的文章标题并返回
     * @param string $htmlContent
     * @return false|string
     */
    public function matchHtmlTitle( $htmlContent = '' )
    {
        $reg = "/<title>(.*)<\/title>/ismU";
        $result = preg_match($reg, $htmlContent, $matches);
        if(!$result) {
            return false;
        }
        return filterHtml($matches[1]);
    }

    /**
     * 匹配html文件中的页面描述并返回
     * @param string $htmlContent
     * @return false|string
     */
    public function matchHtmlDescription( $htmlContent = '' )
    {
        $reg = "/<meta\s+name=\"description\"\s+content=\"(.*)\"\s*\/?>/ismU";
        $result = preg_match($reg, $htmlContent, $matches);
        if(!$result) {
            return false;
        }
        return filterHtml($matches[1]);
    }

    public function matchHtmlDocument( $htmlContent = '', $type = 1, $node = '' )
    {
        $data = [];
        $dom  = new \DOMDocument();
        libxml_use_internal_errors( 1 );
        $dom->loadHTML( $htmlContent );

        switch ( $type ){
            case 1:
                $metaDocs = $dom->getElementsByTagName('meta');
                for ($i = 0; $metaDocs->length;$i++){
                    if( in_array($metaDocs[$i]->getAttribute('name'),['title','description','keywords']) ){
                        $data[$metaDocs[$i]->getAttribute('name')] = $metaDocs[$i]->getAttribute('content');
                        break;
                    }
                }
                break;
            case 2:
                //Todo
                break;
            case 3:
                //Todo
                break;
            default:
                break;
        }


    }

    /**
     * 匹配html文件中的关键词并返回
     * @param string $htmlContent
     * @return false|string
     */
    public function matchHtmlKeywords( $htmlContent = '' )
    {
        $reg = "/<meta\s+name=\"keywords\"\s+content=\"(.*)\"\s*\/?>/ismU";
        $result = preg_match($reg, $htmlContent, $matches);
        if(!$result) {
            return false;
        }
        return filterHtml($matches[1]);
    }

    public function matchHtmlSummary(  $htmlContent = ''  )
    {

    }

    public function matchHtmlRelated(  $htmlContent = '' )
    {

    }




}
