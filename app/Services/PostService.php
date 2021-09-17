<?php


namespace App\Services;


use App\Models\Directory;
use App\Models\Editor;
use App\Models\EditorAttr;
use App\Models\Lang;
use App\Models\Post;
use App\Models\PostAttr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use League\Flysystem\Exception;

class PostService
{
    /**
     * 文章id
     * @var int
     */
    protected $id;
    /**
     * 文章标题
     * @var string
     */
    protected $title;
    /**
     * 文章关键词
     * @var string
     */
    protected $keywords;
    /**
     * 文章描述
     * @var string
     */
    protected $description;
    /**
     * 目录路径
     * @var string
     */
    protected $directory_fullpath;
    /**
     * 页面文件路径
     * @var string
     */
    protected $html_fullpath;
    /**
     * 页面文件名称
     * @var string
     */
    protected $html_name;
    /**
     * 文章简介
     * @var string
     */
    protected $summary;
    /**
     * 文章内容
     * @var string
     */
    protected $contents;
    /**
     * POST模板id
     * @var int
     */
    protected $template_id;
    /**
     * AMP模板id
     * @var int
     */
    protected $template_amp_id;
    /**
     * 文章状态
     * @var int
     */
    protected $post_status;
    /**
     * 文章作者json数据文本
     * @var string
     */
    protected $editor_json;
    /**
     * 作者id
     * @var int
     */
    protected $editor_id;
    /**
     * 作者名称
     * @var string
     */
    protected $editor_name;
    /**
     * 语言id
     * @var int
     */
    protected $lang_id;
    /**
     * 相关文章
     * @var string
     */
    protected $related_posts;
    /**
     * 文章创建时间
     * @var int
     */
    protected $created_at;
    /**
     * 文章更新时间
     * @var int
     */
    protected $updated_at;
    /**
     * 文章发布时间
     * @var int
     */
    protected $published_at;
    /**
     * 结构化数据(存入数据库)
     * @var string
     */
    protected $structured_data;
    /**
     * 结构化数据(临时存放)
     * @var array
     */
    protected $structured_json;
    /**
     * 是否开启Facebook插件
     * @var int
     */
    protected $fb_comment;
    /**
     * 是否开启Lightbox插件
     * @var int
     */
    protected $lightbox;
    /**
     * 文章属性
     * @var array
     */
    protected $attr;
    /**
     * Post模板的结构化数据模板
     * @var string
     */
    static public $articleJsonTpl = '<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "{<title>}",
  "url": "{<url>}",
  "keywords": {<keywords-array>},
  "description": "{<description>}",
  "image": {<image-url-array>},
  "mainEntityOfPage": "True",
  "publisher":{
      "@type": "Organization",
      "name": "MultCloud",
      "url": "https://www.multcloud.com/",
      "logo": {
          "@type": "ImageObject",
          "url": "https://www.multcloud.com/images/front/comma/logo.png",
          "width": "230",
          "height": "59"
      }
  },
  "dateCreated": "{<created_at>}",
  "datePublished": "{<published_at>}",
  "dateModified": "{<updated_at>}",
  "author": "{<editor_name>}",
  "creator": "{<editor_name>}",
  "articleSection":"{<directory_title>}"
}

</script>';

    /**
     * Amp模板的结构化数据模板
     * @var string
     */
    static public $ampJsonTpl = '<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": "{<directory_fullpath>}"
  },
  "headline": "{<title>}",
  "image": {<image-url-array>},
  "dateCreated": "{<created_at>}",
  "datePublished": "{<published_at>}",
  "dateModified": "{<updated_at>}",
  "author": {
    "@type": "Person",
    "name": "{<editor_name>}"
  },
   "publisher": {
    "@type": "Organization",
    "name": "MultCloud",
    "logo": {
      "@type": "ImageObject",
      "url": "https://www.multcloud.com/images/front/comma/logo.png"
    }
  },
  "description": "{<description>}"
}
</script>';
    /**
     * 生成的文件名后缀
     */
    protected const fileExt = '.html';
    /**
     * 临时文件的标识
     */
    protected const fileTempTag = '--tmp';

    public function setId( $id = 1 )
    {
        $this->id = $id;
        return $this;
    }

    public function setTitle( $title = '' )
    {
        $this->title = htmlentities($title);
        return $this;
    }

    public function setKeywords( $keywords = '' )
    {
        $this->keywords = implode(',',$keywords);
        return $this;
    }

    public function setDescription( $desc = '' )
    {
        $this->description = htmlentities($desc);
        return $this;
    }

    public function setDirFullPath( $dirFullPath = '' )
    {
        $this->directory_fullpath = $dirFullPath;
        return $this;
    }

    public function setHtmlName( $htmlName = '' )
    {
        $this->html_name = htmlentities($htmlName.self::fileTempTag.self::fileExt);
        return $this;
    }

    public function setHtmlFullPath()
    {
        $this->html_fullpath = $this->directory_fullpath.'/'.$this->html_name;
        return $this;
    }

    public function setSummary( $summary = '' )
    {
        $this->summary = htmlentities($summary);
        return $this;
    }

    public function setContents( $contents = '' )
    {
        $this->contents = filterHtml($contents);
        return $this;
    }

    public function setTemplateId( $template_id = 1 )
    {
        $this->template_id = $template_id;
        return $this;
    }

    public function setTemplateAmpId( $template_amp_id = 1 )
    {
        $this->template_amp_id = $template_amp_id;
        return $this;
    }

    public function setPostStatus( $status = 1 )
    {
        $this->post_status = $status;
        return $this;
    }

    public function setEditorId( $editorId = 1 )
    {
        $this->editor_id = $editorId;
        return $this;
    }

    public function setEditorName()
    {
        $editorInfo = Editor::query()->select('editor_name')->find($this->editor_id);
        if( !$editorInfo ){
            throw new \Exception('未找到作者信息');
        }
        $this->editor_name = $editorInfo->lang_name;
        return $this;
    }

    /**
     * 根据作者id查询作者信息以及属性即时生成json
     * @return $this|false
     */
    public function setEditorJson()
    {
        $editorInfo = Editor::with('attr')->find($this->editor_id)->toArray();
        if( $editorInfo ){
            $editorArr = [];
            $editorArr['editor_id'] = $this->editor_id;
            $editorArr['editor_name'] = $editorInfo['editor_name'];
            $editorArr['editor_avatar'] = $editorInfo['editor_avatar'];
            if( $editorInfo['attr'] ){
                foreach ($editorInfo['attr'] as $key=>$item) {
                    if( $key == 0 ) continue;
                    $editorArr[$item['key']] = $item['value'];
                }
            }
            $this->editor_json = json_encode($editorArr);
            return $this;
        }else{
            return false;
        }
    }

    /**
     * 语言id为选择目录绑定的语言id
     * @return $this|false
     */
    public function setLangId()
    {
        $langId = Directory::query()->where('directory_fullpath',$this->directory_fullpath)->pluck('lang_id')->first();
        if( $langId ){
            $this->lang_id = $langId;
            return $this;
        }else{
            return false;
        }
    }

    public function setRelatedPosts( $relatedPost = '' )
    {
        $this->related_posts = htmlentities($relatedPost);
        return $this;
    }

    public function setPublishedAt( $publishedAt = 0 )
    {
        $this->published_at = $publishedAt;
        return $this;
    }

    public function setCreatedAt( $createdAt = 0 )
    {
        $this->created_at = $createdAt;
        return $this;
    }

    public function setUpdatedAt( $updatedAt = 0 )
    {
        $this->updated_at = $updatedAt;
        return $this;
    }

    public function setStructuredData()
    {
        $jsonArr = [
            '{<title>}' => $this->title,
            '{<url>}' => Request::server('REQUEST_SCHEME'). '://' . Request::server('SERVER_NAME').$this->html_fullpath,
            '{<keywords-array>}' => $this->keywords,
            '{<description>}' => $this->description,
            '{<image-url-array>}' => json_encode($this->matchElementInContent(htmlentities($this->contents),'//img/@src'),JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT),
            '{<created_at>}' => !is_null($this->created_at) ? $this->created_at : date('F j, Y', time()),
            '{<published_at>}' => !is_null($this->published_at) ? $this->published_at : date('F j, Y', time()),
            '{<updated_at>}' => !is_null($this->updated_at) ? $this->updated_at : date('F j, Y', time()),
            '{<editor_name>}' => $this->editor_name,
            '{<directory_title>}' => $this->getDirectoryTitle($this->directory_fullpath),
            '{<directory_fullpath>}' => Request::server('REQUEST_SCHEME'). '://' . Request::server('SERVER_NAME').$this->directory_fullpath.'/',
        ];
        $this->structured_json = $jsonArr;
        $this->structured_data = $this->getStructureJson($jsonArr);
        return $this;
    }

    public function setFaceBookComment( $fbComment = 1 )
    {
        $this->fb_comment = $fbComment;
        return $this;
    }

    public function setLightBox( $lightBox = 1 )
    {
        $this->lightbox = $lightBox;
        return $this;
    }

    public function setAttr( $attr = [] )
    {
        $postAttr = [];
        $i = 0;
        foreach ($attr as $item) {
            //_remove_=1为移除项
            if( $item['_remove_'] == 1 ){
                continue;
            }
            $postAttr[$i ]['post_htmlpath'] = $this->html_fullpath;
            $postAttr[$i ]['post_key'] = $item['post_attr'];
            $postAttr[$i ]['post_value'] = htmlentities($item['post_attr_value']);
            $postAttr[$i ]['post_html'] = 1;
            $i++;
        }
        $this->attr = $postAttr;
        return $this;
    }

    /**
     * 将结构化数据模板中的变量具象化
     * @param array $data 需要替换的变量数组
     * @param string $type post json模板 amp json模板
     * @return string
     */
    public function getStructureJson(array $data = [], string $type = 'post')
    {
        $tpl = ($type == 'post') ? static::$articleJsonTpl : static::$ampJsonTpl;
        return htmlentities(strtr($tpl,$data));
    }

    /**
     * 获取目录标题
     * @param string $dirPath 目录路径
     * @return string
     * @throws \Exception
     */
    public function getDirectoryTitle( $dirPath = '' )
    {
        $dirTitle = Directory::query()->where('directory_fullpath','=',$dirPath)->pluck('directory_title')->first();
        if( !$dirTitle ){
            throw new \Exception('未找到目录信息');
        }
        return $dirTitle;
    }

    /**
     * 获取文章的语言名称
     * @return mixed
     */
    public function getArticleLang()
    {
        $lang = Lang::find($this->lang_id);
        if( !$lang ){
            throw new \Exception('未找到语言信息');
        }
        return $lang->lang_name;
    }

    /**
     * 匹配内容中指定的元素集合
     * @param string $content html内容字符串
     * @param string $query 需要匹配的字符串 例如//script[@type="application/ld+json"]
     * @return array
     */
    public function matchElementInContent( $content = '',$query = '' )
    {
        $dom  = new \DOMDocument();
        libxml_use_internal_errors( 1 );
        $dom->loadHTML( $content );

        $xpath = new \DOMXpath( $dom );
        $jsonScripts = $xpath->query($query);
        $arr = [];
        for ($i = 0;$i < $jsonScripts->length; $i++){
            //图片地址需要使用绝对路径
            $arr[] =  Request::server('REQUEST_SCHEME'). '://' . Request::server('SERVER_NAME').$jsonScripts[$i]->nodeValue;
        }
        return $arr;
    }

    /**
     * 保存新建文章的数据以及属性
     * @return bool
     */
    public function create()
    {
        DB::beginTransaction();
        Post::create([
            'title' => $this->title,
            'keywords' => $this->keywords,
            'description' => $this->description,
            'directory_fullpath' => $this->directory_fullpath,
            'html_fullpath' => $this->html_fullpath,
            'html_name' => $this->html_name,
            'summary' => $this->summary,
            'contents' => $this->contents,
            'template_id' => $this->template_id,
            'template_amp_id' => $this->template_amp_id,
            'post_status' => 0,//默认生成的文件都是未发布状态
            'editor_json' => $this->editor_json,
            'editor_id' => $this->editor_id,
            'lang_id' => $this->lang_id,
            'related_posts' => $this->related_posts,
            'published_at' => 0,//生成文章仅为预览模式，上线后再更新发布时间
            'structured_data' => $this->structured_data,
            'fb_comment' => $this->fb_comment,
            'lightbox' => $this->lightbox,
        ]);
        $postAttr = PostAttr::insert($this->attr);
        !$postAttr and DB::rollBack();
        DB::commit();
        return true;
    }

    public function generateHtmlFile()
    {
        //1.生成的html文件有'--tmp'标识为预览文件
        //2.预览文件不会进入目录的文章列表
        //3.预览文件禁止搜索引擎抓取
        //4.生成的html文件同时会生成一个'.amp.html'结尾的移动端文件
        //5.生成文件图片会加lazyload的效果
        //6.文章属性的标签显示不同的语言


        //模板中需要替换的变量
        $replaceVarArr = [
            '{{language}}' => $this->getArticleLang(),
            '{{title}}' => html_entity_decode($this->title),
            '{{description}}' => html_entity_decode($this->description),
            '{{keywords}}' => html_entity_decode($this->keywords),
            '{{editor-name}}' => $this->editor_name,
            '{{html-fullpath}}' => Request::server('REQUEST_SCHEME'). '://' . Request::server('SERVER_NAME').$this->html_fullpath,
            '<!--{{amp-html-path}}-->' => $this->getAmpHtmlPath(),
            '<!--{{structrued-data}}-->' => html_entity_decode($this->structured_data),
            '<!--{{ga-code-url}}-->' => $this->getGaUrl(),
        ];
    }

    /**
     * 获取 amphtml 标签内容
     * @return string
     */
    public function getAmpHtmlPath()
    {
        $fileName = substr_replace($this->html_name,'.amp',-5,0);
        return '<link rel="amphtml" href="'.$fileName.'">';
    }

    /**
     * 获取作者Google追踪文件路径
     * @return string
     */
    public function getGaUrl()
    {
        $attr = EditorAttr::query()->where('editor_id',$this->editor_id)->where('key','ga_code_url')->select('value')->first();
        if( $attr && is_file(base_path('..').$attr->value) ){
            return '<script src="'.$attr->value.'"></script>';
        }else{
            return '';
        }
    }


}
