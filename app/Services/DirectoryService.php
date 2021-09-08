<?php


namespace App\Services;


use App\Models\Directory;
use App\Models\Editor;
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

        $postListArr = [];
        $defectionFlag = 0;
        $DefectionList = '';
        $type = 1;
        $fileName = '';
        foreach ($diffFilesArr as $item) {
            $filePath = base_path('../').$this->dirInfo['directory_fullpath'].DIRECTORY_SEPARATOR.$item;
            if( is_file($filePath) ){
                $fileContent = file_get_contents($filePath);
                //采集失败返回错误信息
                $resultArr = $this->transferHtmlToArr($fileContent,$item);
                if( array_key_exists('code',$resultArr) ){
                    $defectionFlag = 1;
                    //code = 1,2,4为编辑html文件
                    //code = 3为添加作者
                    if( in_array($resultArr['code'],[1,2,4])){
                        $type = 1;
                        $fileName = $this->dirInfo['directory_fullpath'].DIRECTORY_SEPARATOR.$item;
                    }else{
                        $type = 2;
                    }
                    $DefectionList .= $resultArr['msg'];
                }else{
                    $postListArr[] = $resultArr;
                }


            }
        }
        if( $defectionFlag ){
            return ['status'=>'failed','type'=>$type,'msg'=>$DefectionList,'file'=>$fileName];
        }

        Post::query()->insert($postListArr);

        return ['code'=>200,'status'=>'success','msg'=>'采集成功'];
    }

    public function transferHtmlToArr( $htmlContent = '', $htmlFileName = '' )
    {
        //编辑html文件的url
        $modifyHtmlUrl = '<span style="color:#237fff;cursor:pointer;" class="update-html" data-file="'.$this->dirInfo['directory_fullpath'].'/'.$htmlFileName.'">  编辑</span>';
        //标题
        $title = $this->matchHtmlDocument($htmlContent,3,'title','title');

        //元标签数据
        $metaArr = $this->matchHtmlDocument($htmlContent,1);

        //结构化数据
        $structureArr = $this->matchHtmlDocument($htmlContent,3,'script[@type="application/ld+json"]','structured_data');
//        $_structureArr = $structureArr;
        //html文件需要含有结构化数据 没有则提示添加
        if( !empty($structureArr['structured_data']) ){
            $structure = json_decode($structureArr['structured_data'],true);
            $timeArr = ['published_at'=>strtotime($structure['datePublished']),'created_at'=>strtotime($structure['dateCreated']),'updated_at'=>strtotime($structure['dateModified'])];
        }else{
            return ['code'=>1,'status'=>'failed','msg'=>'<div><b>'.$htmlFileName.'</b> 缺少结构化数据'.$modifyHtmlUrl.'</div>'];
        }
        //结构化数据需要包含父节点
        $structureArr['structured_data'] = filterHtml('<script type="application/ld+json">'.$structureArr['structured_data'].'</script>');

        $editorName = $this->matchHtmlGaJs($htmlContent);
        //html文件需要含有追踪文件 没有则提示添加
        if( $editorName ){
            $editor['editor_id'] = Editor::with('attr')->where('editor_name','=',$editorName)->pluck('id')->first();
            if( empty($editor['editor_id']) ){
                return ['code'=>3,'status'=>'failed','msg'=>'<div style="margin-bottom: 10px;"><b>'.$htmlFileName.'</b> 缺少名称为[<b style="color: #f00">'.$editorName.'</b>]的作者信息</div>'];
            }
        }else{
            return ['code'=>2,'status'=>'failed','msg'=>'<div><b>'.$htmlFileName.'</b> 缺少追踪文件'.$modifyHtmlUrl.'</div>'];
        }

        //文章内容
        $contentArr['contents'] = $this->matchHtmlContent($htmlContent);
        if( !$contentArr['contents'] ){
            return ['code'=>4,'status'=>'failed','msg'=>'<div><b>'.$htmlFileName.'</b> 缺少文章内容标识'.$modifyHtmlUrl.'</div>'];
        }

        //相关文章数据
        $relatedArr = $this->matchHtmlDocument($htmlContent,2,'dl[@id="am-related-articles"]','related_posts');
        //相关文章需要包含父节点
        $relatedArr['related_posts'] = filterHtml('<dl id="am-related-articles">'.$relatedArr['related_posts'].'</dl>');



        //默认editor_json
        //Todo 生成作者属性后自动填充
        $editorJson['editor_json'] = json_encode(['editor_id'=>$editor['editor_id'],'editor_name'=>'','ga_code_url'=>'','twitter_url'=>'','editor_avatar'=>'']);

        //summary默认使用description的内容
        $summary['summary'] = $metaArr['description'];

        //html_fullpath 文件路径+文件名
        $htmlPath['html_fullpath'] = $this->dirInfo['directory_fullpath'].'/'.$htmlFileName;
        //html_name html文件名
        $htmlName['html_name'] = $htmlFileName;

        return array_merge($title,$metaArr,$structureArr,$contentArr,$relatedArr,$timeArr,$editor,$editorJson,$this->dirInfo,$summary,$htmlPath,$htmlName);


    }
    /**
     * 匹配html文件中的各种元素
     * @param string $htmlContent html文件内容
     * @param int $type 1:元标签;2:匹配元素内的html内容(包含html标签);3:匹配元素的内容(不包含html标签)
     * @param string $queryElement 匹配元素
     * @param string $key 储存于数组的键名
     * @return array
     */
    public function matchHtmlDocument( $htmlContent = '', $type = 1, $queryElement = '', $key = '' )
    {
        $data = [];
        $dom  = new \DOMDocument();
        libxml_use_internal_errors( 1 );
        $dom->loadHTML( $htmlContent );
        $xpath = new \DOMXpath( $dom );

        switch ( $type ){
            case 1:
                $metaDocs = $dom->getElementsByTagName('meta');
                for ($i = 0; $metaDocs->length;$i++){
                    if(  !empty($metaDocs[$i]->getAttribute('name')) && in_array($metaDocs[$i]->getAttribute('name'),['description','keywords'])){
                        $data[$metaDocs[$i]->getAttribute('name')] = $metaDocs[$i]->getAttribute('content')??'';
                        break;
                    }
                }
                break;
            case 2:
                $queryString = '';
                $queryDoc = $xpath->query( '//'.$queryElement );
                foreach ($queryDoc->item(0)->childNodes as $childNode) {
                    $queryString .= $dom->saveHTML($childNode);
                }
                $data[$key] = $queryString??'';
                break;
            case 3:
                $jsonScripts = $xpath->query( '//'.$queryElement  );
                $data[$key] = $jsonScripts->item(0)->nodeValue??'';
                break;
            default:
                break;
        }
        return $data;

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
     * 匹配html文件中的追踪js文件获得作者名称
     * @param string $htmlContent
     * @return boolean
     */
    public function matchHtmlGaJs( $htmlContent = '' )
    {
        $reg = '/src=\".*\/team\/(.*).js\"/i';
        preg_match($reg, $htmlContent, $matches);
        return $matches[1]??false;
    }


}
