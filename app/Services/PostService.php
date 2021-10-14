<?php


namespace App\Services;


use App\Models\Directory;
use App\Models\Editor;
use App\Models\EditorAttr;
use App\Models\Lang;
use App\Models\Post;
use App\Models\PostAttr;
use App\Models\Template;
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
     * post页面文件路径
     * @var string
     */
    protected $html_fullpath;
    /**
     * amp页面文件路径
     * @var string
     */
    protected $amp_fullpath;
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
     * 作者信息(名称和头像)
     * @var string
     */
    protected $editor_info;
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
     * 是否开启页面索引
     * @var int
     */
    protected $article_index;
    /**
     * 文章属性
     * @var array
     */
    protected $attr;
    /**
     * 文章的对象实例
     * @var object
     */
    protected $postObj;
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

    public function setKeywords( $keywords = [] )
    {
        $this->keywords = rtrim(implode(',',$keywords),',');
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

    public function setAmpFullPath( $htmlName = '' )
    {
        $this->amp_fullpath = $this->directory_fullpath.'/'.$htmlName.'.amp'.self::fileExt;
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

    public function setEditorInfo()
    {
        $editorInfo = Editor::query()->select('editor_name','editor_avatar')->find($this->editor_id);
        if( !$editorInfo ){
            throw new \Exception('未找到作者信息');
        }
        $this->editor_info = $editorInfo;
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
        //文章修改时没有设置directory_fullpath 需要通过文章对象来获取
        $dirFullPath = $this->directory_fullpath?:$this->postObj->directory_fullpath;
        $jsonArr = [
            '{<title>}' => $this->title,
            '{<url>}' => Request::server('REQUEST_SCHEME'). '://' . Request::server('SERVER_NAME').$this->html_fullpath,
            '{<keywords-array>}' => $this->keywords,
            '{<description>}' => $this->description,
            '{<image-url-array>}' => json_encode($this->matchElementInContent(htmlentities($this->contents),'//img/@src'),JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT),
            '{<created_at>}' => !is_null($this->created_at) ? $this->created_at : date('F j, Y', time()),
            '{<published_at>}' => !is_null($this->published_at) ? $this->published_at : date('F j, Y', time()),
            '{<updated_at>}' => !is_null($this->updated_at) ? $this->updated_at : date('F j, Y', time()),
            '{<editor_name>}' => ($this->editor_info)->editor_name,
            '{<directory_title>}' => $this->getDirectoryTitle($dirFullPath),
            '{<directory_fullpath>}' => Request::server('REQUEST_SCHEME'). '://' . Request::server('SERVER_NAME').$dirFullPath.'/',
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

    public function setArticleIndex( $index = 1 )
    {
        $this->article_index = $index;
        return $this;
    }

    public function setPostObj()
    {
        $this->postObj = Post::query()->find($this->id);
        return $this;
    }

    /**
     * 新增或更新时设置文章的属性
     * @param array $attr 提交的属性值
     * @return $this
     */
    public function setAttr( $attr = [] )
    {
        $postAttr = [];
        $i = 0;
        foreach ($attr as $item) {
            //_remove_=1为移除项
            //键值或内容为空的属性不进行添加
            if( $item['_remove_'] == 1 ||empty($item['post_key']) || empty($item['post_value'])){
                continue;
            }
            $postAttr[$i]['post_htmlpath'] = $this->html_fullpath?:$this->postObj->html_fullpath;
            $postAttr[$i]['post_key'] = $item['post_key'];
            $postAttr[$i]['post_value'] = htmlentities($item['post_value']);
            $postAttr[$i]['post_html'] = 1;
            $i++;
        }
        $this->attr = $postAttr;
        return $this;
    }

    /**
     * 替换模板时设置文章的属性
     * @param array $arr 从数据库取出的属性值
     * @return $this
     */
    public function setPostAttr( $arr = [] )
    {
        $this->attr = $arr;
        return $this;
    }

    public function getEditorJson()
    {
        return $this->editor_json;
    }

    public function getStructureData()
    {
        return $this->structured_data;
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

    /**
     * 获取文章作者属性
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object
     * @throws \Exception
     */
    public function getEditorAttr( $attrName = '' )
    {
        $attr = EditorAttr::query()->where('editor_id',$this->editor_id)->where('key','=',$attrName)->select('value')->first();
        if( !$attr ){
            throw new \Exception('未找到作者属性');
        }
        return $attr->value;
    }

    /**
     * 根据语言生成相应时间格式并返回
     * @return false|string
     * @throws \Exception
     */
    public function getUpdatedAt()
    {
        $timeStamp = $this->updated_at??time();
        switch ($this->getArticleLang()){
            case 'de':
            case 'it':
                return date('d.m.Y',$timeStamp);
            case 'es':
            case 'fr':
            case 'pt':
                return date('d/m/Y',$timeStamp);
            case 'cn':
            case 'tw':
            case 'jp':
            case 'ja':
                return date('Y年m月d日',$timeStamp);
            default:
                return date('F j,Y',$timeStamp);
        }
    }

    /**
     * 获取指定的文章属性值
     * @param string $attrKey 文章属性键名
     * @return mixed|string
     */
    public function getPostAttr( $attrKey = '' )
    {
        $value = '';
        foreach ($this->attr as $item) {
            if( $attrKey == $item['post_key'] ){
                $value =  $item['post_value'];
                break;
            }else{
                $value = '';
            }
        }
        return $value;
    }

    /**
     * 给相关文章内容跟包裹一层dl标签
     * @param string $relatedPost 提交的相关文章内容
     * @return mixed|string
     * @throws \Exception
     */
    public function getRelatedPost( $relatedPost = '' )
    {
        $pageTitle = $this->getYMALTitle();
        $result = preg_match_all('/(<a.*>.*<\/a>)/ismU', html_entity_decode($relatedPost), $matches);
        if( $result ){
            $dl = '<dl id="am-related-articles"><dt>' . $pageTitle . '</dt>';
            foreach ($matches[1] as $m) {
                $dl .= '<dd>' . $m . '</dd>';
            }
            $dl .= '</dl>';
            $handledContent = $dl;
        }else{
            $handledContent = html_entity_decode($relatedPost);
        }
        return $handledContent;
    }

    /**
     * 获取语言名称
     * @param int $langId 语言id
     * @return \Illuminate\Database\Eloquent\HigherOrderBuilderProxy|\Illuminate\Support\HigherOrderCollectionProxy|mixed
     * @throws \Exception
     */
    public function getLangName()
    {
        $lang = Lang::query()->find($this->lang_id);
        if( !$lang ){
            throw new \Exception('未找到语言信息');
        }
        return $lang->lang_name;
    }

    /**
     * 获取模板的路径
     * @param int $tempId 模板id
     * @param int $type 模板类型 1：post模板 2：amp模板
     * @return \Illuminate\Database\Eloquent\HigherOrderBuilderProxy|\Illuminate\Support\HigherOrderCollectionProxy|mixed
     * @throws \Exception
     */
    public function getTempPath( $tempId = 1, $type = 1 )
    {
        $temp = Template::query()->select('file_path')->where('type','=',$type)->find($tempId);
        if( !$temp ){
            throw new \Exception('未找到模板信息');
        }
        return public_path('uploads').DIRECTORY_SEPARATOR.$temp->file_path;
    }

    /**
     * 获取Facebook的插件代码
     * @return array
     * @throws \Exception
     */
    public function getFaceBookCommentPlugin()
    {
        $facebookCommentConfig = config('facebook.fb_cfg');
        $lang = $facebookCommentConfig['mapping'][$this->getLangName($this->lang_id)]??'en_US';
        //该段放置在 </body> 之前
        $facebook_comment = <<<FACEBOOKSCRIPT
<div id="fb-root"></div>
<script>
var __qt = {
    apperVisualArea: function(dom) {
        var windowHeigth = window.screen.height;
        var isHidden = dom.getBoundingClientRect().top > 0 && dom.getBoundingClientRect().top < windowHeigth;
        return isHidden;
    }
};
window.addEventListener("scroll",function () {
    __qt.apperVisualArea(document.getElementById('fb-comments')) && (function() {
        (function(d, s, id) {
          var js, fjs = d.getElementsByTagName(s)[0];
          if (d.getElementById(id)) return;
          js = d.createElement(s); js.id = id;
          js.src = "//connect.facebook.net/{$lang}/sdk.js#xfbml=1&version={$facebookCommentConfig['api_ver']}&appId={$facebookCommentConfig['app_id']}";
          fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    })();
});
</script>
FACEBOOKSCRIPT;
        $facebook_replacement = '<div id="fb-comments" class="fb-comments"
                    data-href="' . $this->html_fullpath . '"
                    data-order-by="social"
                    data-width="100%"
                    data-numposts="8"></div>';
        return ['</body>' => $facebook_comment . '</body>', '<!--{{comment-system}}-->' => $facebook_replacement,];
    }

    /**
     * 获取目录索引的代码
     * @return array
     */
    public function getArticleIndex()
    {
        $tocbot_style = '<style>.toc{overflow-y:auto}.toc>.toc-list{overflow:hidden;position:relative}.toc>.toc-list li{list-style:none}.toc-list{margin:0;padding-left:10px}a.toc-link{color:currentColor;height:100%}a.toc-link.is-active-link,a.toc-link:hover{text-decoration:none;font-weight:700}a.toc-link.is-active-link{color:#249efc;position:relative}a.toc-link.is-active-link::before{content:\'\'}.is-collapsible{max-height:1000px;overflow:hidden;transition:all 300ms ease-in-out}.is-collapsed{max-height:0}.is-position-fixed{position:fixed!important;top:0}.toc-link::before{background-color:#eee;content:\' \';height:inherit;left:-10px;margin-top:-1px;position:absolute;width:3px}.is-active-link::before{background-color:#54bc4b}.js-toc .toc-list,.js-toc-box .toc-list{padding-right:10px}.js-toc-box{border:1px solid #249efc;position:fixed;z-index:2009;top:120px;width:202px;background:#fff;-webkit-box-shadow:0 0 3px rgba(3,3,3,.4);-moz-box-shadow:0 0 3px rgba(3,3,3,.4);box-shadow:0 0 3px rgba(3,3,3,.4);-webkit-border-radius:0 3px 3px 0;-moz-border-radius:0 3px 3px 0;border-radius:0 3px 3px 0}.js-toc-box h3{position:relative;cursor:pointer;padding:8px 0 6px 10px;word-break:keep-all;white-space:nowrap;font-size:18px;font-size:1.8rem;background-color:#f8f8f8;margin-bottom: 0;}.js-toc{padding: 10px 0;}.js-toc-box h3::before{content:\'\';vertical-align:middle;display:inline-block;width:22px;height:22px;background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABwAAAAcCAYAAAByDd+UAAACu0lEQVRIS62WSaiPURjGfzd0MyYbmRYyLQyRhbBxjRsUxULJkCELlKyRWNmxwl0YyoKiyMI13LsiJRRShiilUJIxcaNHz8m5f+d8/+/g1Fdf3/ee93mn5zmnhd9rFDAIeBB9+x+vE4H3wAs5a7HHDcBRvz8CzgHnget/iTgLWAosA8bbx0agPQB2AnMSzl8a/BJwFficCaAfMA9YZJDhCbsuoC0F+CPKPN6nslwArhhc/wQyH1jidjTixL6ygG2A6r7QEbcmopWjuCWxyVdAFenwPKh6WpWA+qnVC1jlfqhcAzMl/WAQ9f0U0G07takIsNH/OOBI1G8Ftgl4nAnknwHlNx6wX2WqmOQiwPvAM+AEcCZy2gxwATAb2ONK1C6pADU8WjfsQIOQA1wObI3KvQO4U9LDEcA2O+kbpiwDqGx2O7gvwHFgS2mGoYpTAEV/DHieAZwO3AL2uwVSK62iHh4AJgAHTfYQQLMe9nEPNVBFgIHcAlJ2+4CnFT0cBux0CwQqfQ72tYgvuVIPJV1aEnSVNpXhCuAQMNS27cAuV6j2lIYSrgXWeBCUaQpQMig5O2vgoFRFJV0NjHEP39bgoZwHoGBeBBh6+MSgKlmV0uiIWu9nGrASeFPCQ3FLRB5iIKmNnKRKKupcBEba9rZ5+bEEUHvHRuRX/9ZVDM1p81SV0POtlBY5Tc7xUP16CLyKNtbq4WbgJCCJSq1mxA97JIcavMP+kD2AwwaJt/h0DbhXY0plMhmYa9JPaoi2B6B4tBeYkclKkyri69SQGIQLl5xI+mb68qSep9ZNC0FHuEQFo6lWFp1pehr/y06TN8AbPgH9Ewii1GU/unTdDTYph+HfaINK2gQ+ODdF/v4uAhCYDu8/VhVgbCwuhqwXR5r52lfHkE2sSMn46gLGm3v74qTbwHbge5PMe/z+Cejq5x1oGRqVAAAAAElFTkSuQmCC) no-repeat center;-webkit-background-size:contain;background-size:contain;margin-right:4px;position:relative;top:-2px}.js-toc-box .toc-list li{margin:0.66rem 0}.js-toc-box .toc-list li a{font-size:12px;font-size:1.2rem;}.js-toc-box.closed h3::after{content:"➔";-webkit-transform:rotate(90deg);-moz-transform:rotate(90deg);-ms-transform:rotate(90deg);-o-transform:rotate(90deg);transform:rotate(90deg);margin-top:4px}.js-toc-box h3::after{width:22px;height:22px;content:"➔";display:block;margin-right:10px;-webkit-transform:rotate(-90deg);-moz-transform:rotate(-90deg);-ms-transform:rotate(-90deg);-o-transform:rotate(-90deg);transform:rotate(-90deg);position:absolute;top:6px;right:-5px;}.js-toc .toc-list-item:last-child{margin-bottom: 0}.js-toc-box h3 {margin-top: 0;}</style>';
        $tocbot_script = '<script>!function(e){var t={};function n(o){if(t[o])return t[o].exports;var l=t[o]={i:o,l:!1,exports:{}};return e[o].call(l.exports,l,l.exports,n),l.l=!0,l.exports}n.m=e,n.c=t,n.d=function(e,t,o){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:o})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var o=Object.create(null);if(n.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var l in e)n.d(o,l,function(t){return e[t]}.bind(null,l));return o},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=0)}([function(e,t,n){(function(o){var l,r,i;!function(o,s){r=[],l=function(e){"use strict";var t,o,l,r=n(2),i={},s={},c=n(3),a=n(4),u=!!(e&&e.document&&e.document.querySelector&&e.addEventListener);if("undefined"==typeof window&&!u)return;var d=Object.prototype.hasOwnProperty;function f(e,t,n){var o,l;return t||(t=250),function(){var r=n||this,i=+new Date,s=arguments;o&&i<o+t?(clearTimeout(l),l=setTimeout(function(){o=i,e.apply(r,s)},t)):(o=i,e.apply(r,s))}}return s.destroy=function(){if(!i.skipRendering)try{document.querySelector(i.tocSelector).innerHTML=""}catch(e){console.warn("Element not found: "+i.tocSelector)}i.scrollContainer&&document.querySelector(i.scrollContainer)?(document.querySelector(i.scrollContainer).removeEventListener("scroll",this._scrollListener,!1),document.querySelector(i.scrollContainer).removeEventListener("resize",this._scrollListener,!1),t&&document.querySelector(i.scrollContainer).removeEventListener("click",this._clickListener,!1)):(document.removeEventListener("scroll",this._scrollListener,!1),document.removeEventListener("resize",this._scrollListener,!1),t&&document.removeEventListener("click",this._clickListener,!1))},s.init=function(e){if(u&&(i=function(){for(var e={},t=0;t<arguments.length;t++){var n=arguments[t];for(var o in n)d.call(n,o)&&(e[o]=n[o])}return e}(r,e||{}),this.options=i,this.state={},i.scrollSmooth&&(i.duration=i.scrollSmoothDuration,i.offset=i.scrollSmoothOffset,s.scrollSmooth=n(5).initSmoothScrolling(i)),t=c(i),o=a(i),this._buildHtml=t,this._parseContent=o,s.destroy(),null!==(l=o.selectHeadings(i.contentSelector,i.headingSelector)))){var m=o.nestHeadingsArray(l).nest;i.skipRendering||t.render(i.tocSelector,m),this._scrollListener=f(function(e){t.updateToc(l);var n=e&&e.target&&e.target.scrollingElement&&0===e.target.scrollingElement.scrollTop;(e&&(0===e.eventPhase||null===e.currentTarget)||n)&&(t.updateToc(l),i.scrollEndCallback&&i.scrollEndCallback(e))},i.throttleTimeout),this._scrollListener(),i.scrollContainer&&document.querySelector(i.scrollContainer)?(document.querySelector(i.scrollContainer).addEventListener("scroll",this._scrollListener,!1),document.querySelector(i.scrollContainer).addEventListener("resize",this._scrollListener,!1)):(document.addEventListener("scroll",this._scrollListener,!1),document.addEventListener("resize",this._scrollListener,!1));var h=null;return this._clickListener=f(function(e){i.scrollSmooth&&t.disableTocAnimation(e),t.updateToc(l),h&&clearTimeout(h),h=setTimeout(function(){t.enableTocAnimation()},i.scrollSmoothDuration)},i.throttleTimeout),i.scrollContainer&&document.querySelector(i.scrollContainer)?document.querySelector(i.scrollContainer).addEventListener("click",this._clickListener,!1):document.addEventListener("click",this._clickListener,!1),this}},s.refresh=function(e){s.destroy(),s.init(e||this.options)},e.tocbot=s,s}(o),void 0===(i="function"==typeof l?l.apply(t,r):l)||(e.exports=i)}(void 0!==o?o:this.window||this.global)}).call(this,n(1))},function(e,t){var n;n=function(){return this}();try{n=n||Function("return this")()||(0,eval)("this")}catch(e){"object"==typeof window&&(n=window)}e.exports=n},function(e,t){e.exports={tocSelector:".js-toc",contentSelector:".js-toc-content",headingSelector:"h1, h2, h3",ignoreSelector:".js-toc-ignore",hasInnerContainers:!1,linkClass:"toc-link",extraLinkClasses:"",activeLinkClass:"is-active-link",listClass:"toc-list",extraListClasses:"",isCollapsedClass:"is-collapsed",collapsibleClass:"is-collapsible",listItemClass:"toc-list-item",activeListItemClass:"is-active-li",collapseDepth:0,scrollSmooth:!0,scrollSmoothDuration:420,scrollSmoothOffset:0,scrollEndCallback:function(e){},headingsOffset:1,throttleTimeout:50,positionFixedSelector:null,positionFixedClass:"is-position-fixed",fixedSidebarOffset:"auto",includeHtml:!1,onClick:function(e){},orderedList:!0,scrollContainer:null,skipRendering:!1,headingLabelCallback:!1,ignoreHiddenElements:!1,headingObjectCallback:null,basePath:""}},function(e,t){e.exports=function(e){var t=[].forEach,n=[].some,o=document.body,l=!0,r=" ";function i(n,o){var l=o.appendChild(function(n){var o=document.createElement("li"),l=document.createElement("a");e.listItemClass&&o.setAttribute("class",e.listItemClass);e.onClick&&(l.onclick=e.onClick);e.includeHtml&&n.childNodes.length?t.call(n.childNodes,function(e){l.appendChild(e.cloneNode(!0))}):l.textContent=n.textContent;return l.setAttribute("href",e.basePath+"#"+n.id),l.setAttribute("class",e.linkClass+r+"node-name--"+n.nodeName+r+e.extraLinkClasses),o.appendChild(l),o}(n));if(n.children.length){var c=s(n.isCollapsed);n.children.forEach(function(e){i(e,c)}),l.appendChild(c)}}function s(t){var n=e.orderedList?"ol":"ul",o=document.createElement(n),l=e.listClass+r+e.extraListClasses;return t&&(l+=r+e.collapsibleClass,l+=r+e.isCollapsedClass),o.setAttribute("class",l),o}return{enableTocAnimation:function(){l=!0},disableTocAnimation:function(t){var n=t.target||t.srcElement;"string"==typeof n.className&&-1!==n.className.indexOf(e.linkClass)&&(l=!1)},render:function(e,t){var n=s(!1);t.forEach(function(e){i(e,n)});var o=document.querySelector(e);if(null!==o)return o.firstChild&&o.removeChild(o.firstChild),0===t.length?o:o.appendChild(n)},updateToc:function(i){var s;s=e.scrollContainer&&document.querySelector(e.scrollContainer)?document.querySelector(e.scrollContainer).scrollTop:document.documentElement.scrollTop||o.scrollTop,e.positionFixedSelector&&function(){var t;t=e.scrollContainer&&document.querySelector(e.scrollContainer)?document.querySelector(e.scrollContainer).scrollTop:document.documentElement.scrollTop||o.scrollTop;var n=document.querySelector(e.positionFixedSelector);"auto"===e.fixedSidebarOffset&&(e.fixedSidebarOffset=document.querySelector(e.tocSelector).offsetTop),t>e.fixedSidebarOffset?-1===n.className.indexOf(e.positionFixedClass)&&(n.className+=r+e.positionFixedClass):n.className=n.className.split(r+e.positionFixedClass).join("")}();var c,a=i;if(l&&null!==document.querySelector(e.tocSelector)&&a.length>0){n.call(a,function(t,n){return function t(n){var o=0;return n!==document.querySelector(e.contentSelector&&null!=n)&&(o=n.offsetTop,e.hasInnerContainers&&(o+=t(n.offsetParent))),o}(t)>s+e.headingsOffset+10?(c=a[0===n?n:n-1],!0):n===a.length-1?(c=a[a.length-1],!0):void 0});var u=document.querySelector(e.tocSelector).querySelectorAll("."+e.linkClass);t.call(u,function(t){t.className=t.className.split(r+e.activeLinkClass).join("")});var d=document.querySelector(e.tocSelector).querySelectorAll("."+e.listItemClass);t.call(d,function(t){t.className=t.className.split(r+e.activeListItemClass).join("")});var f=document.querySelector(e.tocSelector).querySelector("."+e.linkClass+".node-name--"+c.nodeName+\'[href="\'+e.basePath+"#"+c.id.replace(/([ #;&,.+*~\':"!^$[\]()=>|/@])/g,"\\$1")+\'"]\');-1===f.className.indexOf(e.activeLinkClass)&&(f.className+=r+e.activeLinkClass);var m=f.parentNode;m&&-1===m.className.indexOf(e.activeListItemClass)&&(m.className+=r+e.activeListItemClass);var h=document.querySelector(e.tocSelector).querySelectorAll("."+e.listClass+"."+e.collapsibleClass);t.call(h,function(t){-1===t.className.indexOf(e.isCollapsedClass)&&(t.className+=r+e.isCollapsedClass)}),f.nextSibling&&-1!==f.nextSibling.className.indexOf(e.isCollapsedClass)&&(f.nextSibling.className=f.nextSibling.className.split(r+e.isCollapsedClass).join("")),function t(n){return-1!==n.className.indexOf(e.collapsibleClass)&&-1!==n.className.indexOf(e.isCollapsedClass)?(n.className=n.className.split(r+e.isCollapsedClass).join(""),t(n.parentNode.parentNode)):n}(f.parentNode.parentNode)}}}}},function(e,t){e.exports=function(e){var t=[].reduce;function n(e){return e[e.length-1]}function o(t){if(!(t instanceof window.HTMLElement))return t;if(e.ignoreHiddenElements&&(!t.offsetHeight||!t.offsetParent))return null;var n={id:t.id,children:[],nodeName:t.nodeName,headingLevel:function(e){return+e.nodeName.split("H").join("")}(t),textContent:e.headingLabelCallback?String(e.headingLabelCallback(t.textContent)):t.textContent.trim()};return e.includeHtml&&(n.childNodes=t.childNodes),e.headingObjectCallback?e.headingObjectCallback(n,t):n}return{nestHeadingsArray:function(l){return t.call(l,function(t,l){var r=o(l);return r&&function(t,l){for(var r=o(t),i=r.headingLevel,s=l,c=n(s),a=i-(c?c.headingLevel:0);a>0;)(c=n(s))&&void 0!==c.children&&(s=c.children),a--;i>=e.collapseDepth&&(r.isCollapsed=!0),s.push(r)}(r,t.nest),t},{nest:[]})},selectHeadings:function(t,n){var o=n;e.ignoreSelector&&(o=n.split(",").map(function(t){return t.trim()+":not("+e.ignoreSelector+")"}));try{return document.querySelector(t).querySelectorAll(o)}catch(e){return console.warn("Element not found: "+t),null}}}}},function(e,t){function n(e,t){var n=window.pageYOffset,o={duration:t.duration,offset:t.offset||0,callback:t.callback,easing:t.easing||d},l=document.querySelector(\'[id="\'+decodeURI(e).split("#").join("")+\'"]\'),r=typeof e==="string"?o.offset+(e?l&&l.getBoundingClientRect().top||0:-(document.documentElement.scrollTop||document.body.scrollTop)):e,i=typeof o.duration==="function"?o.duration(r):o.duration,s,c;function a(e){c=e-s;window.scrollTo(0,o.easing(c,n,r,i));if(c<i){requestAnimationFrame(a)}else{u()}}function u(){if(window.scrollTo(0,n+r),"function"==typeof o.callback){o.callback()}}function d(e,t,n,o){return(e/=o/2)<1?n/2*e*e+t:-n/2*(--e*(e-2)-1)+t}requestAnimationFrame(function(e){s=e;a(e)})}t.initSmoothScrolling=function(e){document.documentElement.style;var t=e.duration,o=e.offset,l=location.hash?r(location.href):location.href;function r(e){return e.slice(0,e.lastIndexOf("#"))}!function(){document.body.addEventListener("click",function(i){if(!function(e){return"a"===e.tagName.toLowerCase()&&(e.hash.length>0||"#"===e.href.charAt(e.href.length-1))&&(r(e.href)===l||r(e.href)+"#"===l)}(i.target)||i.target.className.indexOf("no-smooth-scroll")>-1||"#"===i.target.href.charAt(i.target.href.length-2)&&"!"===i.target.href.charAt(i.target.href.length-1)||-1===i.target.className.indexOf(e.linkClass))return;n(i.target.hash,{duration:t,offset:o,callback:function(){!function(e){var t=document.getElementById(e.substring(1));t&&(/^(?:a|select|input|button|textarea)$/i.test(t.tagName)||(t.tabIndex=-1),t.focus())}(i.target.hash)}})},!1)}()}}]);</script>';
        $tocbot_script .= <<<DOCBOT
            <script>
                $(function() {
                    $('#post-contents-area').find('h2,h3').each(function() {
                        $(this).attr('id', 'toc.'+Math.random());
                    });
                    var toctrans = {en: 'Table of Contents',jp: '目次',de: 'Inhaltsverzeichnis',fr: 'Table des matières',es: 'Tabla de contenido',it: 'Sommario',tw: '目錄'};

                    $('body').append('<div class="js-toc-box">' +
                     '<h3><span>'+ ((typeof toctrans !== 'undefined' && toctrans[$('html').attr('lang')]) || 'INDEX') +'</span></h3><div class="js-toc"></div>' +
                     '</div>');

                    var rtop = ($('#header.scrolling').length > 0 ? $('#header.scrolling') : $('#header')).height() + ($('.am-promotion-entry').length > 0 ? $('.am-promotion-entry').height() : 0);

                    tocbot.init({
                      // Where to render the table of contents.
                      tocSelector: '.js-toc',
                      // Where to grab the headings to build the table of contents.
                      contentSelector: '#post-contents-area',
                      // Which headings to grab inside of the contentSelector element.
                      headingSelector: 'h2, h3',
                      // For headings inside relative or absolute positioned containers within content.
                      hasInnerContainers: true,

                      collapseDepth: 0,
                      scrollSmooth: true,
                      scrollSmoothDuration: 50,
                      headingsOffset: rtop,
                      scrollSmoothOffset: -rtop
                    });

                    $('.js-toc-box > h3').on('click', function() {
                        var menu = $(this).parent().find('.js-toc');

                        if(menu.is(":visible")) {
                            menu.parent().addClass('closed').width(62).find('h3>span').hide();
                            menu.hide();
                        } else {
                            menu.parent().removeClass('closed').width(202).find('h3>span').show();
                            menu.show();
                        }
                    });
                });
            </script>
DOCBOT;
        return ['</body>'=>'</body>' . $tocbot_script,'</head>'=>$tocbot_style . '</head>'];
    }

    /**
     * 获取页面标签的属性
     * @param string $str 包含标签的字符串
     * @param string $attrName 属性名
     * @return false|mixed
     */
    public function getPropertyAttrOfTag( $str = '', $attrName= '' )
    {
        if (preg_match('/' . $attrName . '=("|\')(.*)\1/ismU', $str, $result)) {
            return $result[2];
        } else {
            return false;
        }
    }

    /**
     * 返回对应语言的[相关文章]的页面显示标题文字
     * @return string
     * @throws \Exception
     */
    public function getYMALTitle()
    {
        $langName = $this->getLangName();
        switch ( $langName ){
            case 'en':
            default:
                return 'You May Also Like';
            case 'de':
                return 'Folgende Artikel könnten Sie auch interessieren';
            case 'jp':
            case 'ja':
                return '人気記事';
            case 'it':
                return 'Potrebbe piacerti anche';
            case 'es':
                return 'También le puede interesar';
            case 'fr':
                return 'Autres Articles Connexes';
            case 'pt':
                return 'Você Tambêm Gostar';
            case 'tw':
                return '相關閱讀';
        }
    }

    /**
     * 生成文章需要的阅读时间
     * @param string $content 文章内容
     * @param string $lang 语言
     * @return string
     */
    public function getReadTime( $content,$lang )
    {
        $wordcount = round(str_word_count(strip_tags($content)), -2);
        $minutes = ceil($wordcount / 300);

        if ($wordcount <= 265) {
            $_out = 1;
        } else {
            $_out = $minutes;
        }

        switch($lang) {
            case 'en':
                $output = $_out.' minutes read';
                break;
            case 'de':
                $output = $_out.' Min. Lesezeit';
                break;
            case 'fr':
                $output = $_out.' minutes pour lire';
                break;
            case 'jp':
                $output = $_out.'分くらいかかる';
                break;
            case 'it':
                $output = $_out.' minuti per la lettura';
                break;
            case 'es':
                $output = $_out.' minutos para leer';
                break;
            case 'tw':
                $output = '閱讀本文需要'.$_out.'分鐘';
                break;
            default:
                $output = $_out.' minutes read';
        }

        return $output;
    }

    /**
     * 翻译热门文章的标题以及处理图片的属性
     * @param string $content 热门文章的html内容
     * @return array|string|string[]|null
     * @throws \Exception
     */
    public function handlePopularArticles( $content = '' )
    {
        switch($this->getLangName($this->lang_id)) {
            case 'en':
                $title = 'Popular Articles';
                break;
            case 'de':
                $title = 'Was ist bei anderen beliebt?';
                break;
            case 'fr':
                $title = 'Articles populaires';
                break;
            case 'jp':
                $title = '人気記事';
                break;
            case 'it':
                $title = 'Cosa è popolare tra gli altri?';
                break;
            case 'es':
                $title = 'Los más vistos';
                break;
            case 'tw':
                $title = '熱門文章';
                break;
            default:
                $title = 'Popular Articles';
        }

        $popular_articles_html = preg_replace('/<img\s*(src="[^">]+")(style="[^"]+")?[^>]*>/ismU', '<img \1 width="300" height="200">', $content);
        return preg_replace('/<div class="xl-may">[^<]*<ul>/ismU', '<div class="xl-may"><h3>'.$title.'</h3><ul>', $popular_articles_html);

    }

    /**
     * 文章html引入block需要的css和js资源
     * @param string $content 文章内容
     * @return mixed|string
     * @throws \Exception
     */
    public function includeBlockResource( $content = '' )
    {
        $result = preg_match_all('/data-block-name="(.+)"/ismU', $content, $matches);
        //文章内容没有block的话 则不进行资源引入
        if( !$result ){
            return $content;
        }
        $css = '';
        $js = '';
        foreach(array_unique($matches[1]) as $block_name) {
            // 获取区块css样式
            $css_path = public_path('assets/css/post_block/').$block_name.'.css';
            if( !is_file($css_path) ){
                throw new \Exception('缺少文章block样式文件'.$block_name);
            }
            $_css = file_get_contents($css_path);

            !empty($_css) and $css .= '<style id="post-block-css-'.$block_name.'">'.$_css.'</style>';

            // 获取区块 js 脚本
            $js_path = public_path('assets/js/post_block/').$block_name.'.js';
            //个别block需要需要引入js
            if( is_file($js_path) ){
                $_js = file_get_contents($js_path);
                !empty($_js) and $js .= '<script id="post-block-js-'.$block_name.'">'.$_js.'</script>';
            }
        }
        return strtr($content,['</head>'=>$css.PHP_EOL.'</head>','</body>'=>'</body>'.PHP_EOL.$js]);

    }

    /**
     * 开启/关闭页面被搜索引擎抓取
     * @param string $content 页面内容
     * @param boolean $toggle 开关
     */
    public function toggleSEO( $content = '' ,$toggle = false )
    {
        $replace = !$toggle ? ['/<meta\s+name="robots"\s+content="index,.*follow,.*all".*\/?>/ismU', '<meta name="robots" content="noindex,nofollow,none"/>'] : ['/<meta\s+name="robots"\s+content="noindex,.*nofollow,.*none".*\/?>/ismU', '<meta name="robots" content="index,follow,all"/>'];
        return preg_replace($replace[0], $replace[1], $content);
    }

    public function imgLazyLoad( $content = '' )
    {
        //获取页面中的文章内容
        $result = preg_match("/<!--ART_CONTENT-->(.*)<!--ART_CONTENT-->/imsU", $content, $article);
        if( $result == 0 ){
            throw new \Exception('文章内容缺少内容标识符');
        }

        $regex1 = '/(<a[^>]+>(<img([^>]+)\/?>)<\/a>)/imsU';
        $result1 = preg_match_all($regex1, $article[1], $matches1);
        $regex2 = '/(<img([^>]+)\/?>)/imsU';
        $result2 = preg_match_all($regex2, $article[1], $matches2);

        if( $result1 || $result2 ){
            //替换带有链接的图片
            $handledContent = $this->wrapsTagWithImg($content,$matches1[3],$matches1[0]);
            //不带链接的图片
            $imgArr = array_diff($matches2[2],$matches1[3]); //alt="uio" height="183" src="/de/articles/images/avatar1.jpg" width="183"
            $imgWithTagArr = array_diff($matches2[0],$matches1[2]); //<img alt="uio" height="183" src="/de/articles/images/avatar1.jpg" width="183" />
            //替换不带链接的图片
            $handledContent = $this->wrapsTagWithImg($handledContent,$imgArr,$imgWithTagArr);
            return $handledContent;
        }
        return $content;

    }

    /**
     * 给文章内容的图片外层加上a标签
     * @param string $content
     * @param array $imgArr
     * @param array $tagArr
     * @return string
     * @throws \Exception
     */
    public function wrapsTagWithImg( $content = '', $imgArr = [], $tagArr = [] )
    {
        $handledArr = [];
        foreach ($imgArr as $item) {
            $handledImg = '<a target="_blank" class="artimg" href="';
            $imgSrc = $this->getPropertyAttrOfTag($item,'src');
            if( !$imgSrc ){
                throw new \Exception('图片缺少src属性');
            }
            $imgTitle = $this->getPropertyAttrOfTag($item,'alt')?:'';

            $handledImg .= $imgSrc.'" title="'.$imgTitle.'"><img '.$item.' title="'.$imgTitle.'" class="lazyload';
            if( $this->lightbox === 1 ){
                $handledImg .= ' img-gallery-control" /></a>';
            }else{
                $handledImg .= '" /></a>';
            }
            $handledArr[] = $handledImg;
        }
        $replaceArr = array_combine($tagArr,$handledArr);
        return strtr($content,$replaceArr);
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
     * 创建文章的数据
     * @throws \Exception
     */
    public function create()
    {
        if( Post::query()->where('html_fullpath','=',$this->html_fullpath)->first() ){
            DB::rollBack();
            throw new \Exception('请勿重新创建相同名称的文章');
        }
        $this->postObj = Post::create([
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
        !empty($this->attr) and PostAttr::insert($this->attr);
    }

    /**
     * 更新文章
     * @throws \Exception
     */
    public function update()
    {
        $articleInfo = Post::with(['attr','lang'])->findOrFail($this->id);
        //由于修改不允许修改目录 post/amp模板 所以需要从数据库拿数据
        $this->lang_id = $articleInfo->lang_id;
        //如果修改已发布的文章 则需要增加文件名的'--tmp'标识
        if( $articleInfo->post_status == 1 ){
            if( is_file( base_path('../').$articleInfo->html_fullpath ) ){
                $tempPath = strtr($articleInfo->html_fullpath,['.html'=>'--tmp.html']);
                rename(base_path('../').$articleInfo->html_fullpath,base_path('../').$tempPath);
                $this->html_fullpath = $tempPath;
            }else{
                throw new \Exception('html文件丢失');
            }
        }else{
            $this->html_fullpath = $articleInfo->html_fullpath;
        }
        $this->directory_fullpath = $articleInfo->directory_fullpath;
        $this->template_id = $articleInfo->template_id;
        $this->template_amp_id = $articleInfo->template_amp_id;

        $articleInfo->title = $this->title;
        $articleInfo->keywords = $this->keywords;
        $articleInfo->description = $this->description;
        $articleInfo->summary = $this->summary;
        $articleInfo->html_name = strpos($articleInfo->html_name,'--tmp')?$articleInfo->html_name:strtr($articleInfo->html_name,['.html'=>'--tmp.html']);
        $articleInfo->html_fullpath = strpos($articleInfo->html_fullpath,'--tmp')?$articleInfo->html_fullpath:strtr($articleInfo->html_fullpath,['.html'=>'--tmp.html']);
        $articleInfo->contents = $this->contents;
        $articleInfo->post_status = 0;
        $articleInfo->editor_id = $this->editor_id;
        $articleInfo->editor_json = $this->editor_json;
        $articleInfo->related_posts = $this->related_posts;
        $articleInfo->published_at = 0;
        $articleInfo->structured_data = $this->structured_data;
        $articleInfo->fb_comment = $this->fb_comment;
        $articleInfo->lightbox = $this->lightbox;
        $articleInfo->article_index = $this->article_index;
        if( !$articleInfo->save() ){
            DB::rollBack();
            throw new \Exception('文章修改失败');
        }
        //文章属性更新采用逻辑为先删除原有的属性 再插入更新的属性
        PostAttr::query()->where('post_htmlpath','=',$this->html_fullpath)->delete();
        !empty($this->attr) and PostAttr::insert($this->attr);
    }

    /**
     * 删除文章
     * @return bool
     * @throws \Throwable
     */
    public function delete()
    {
        //1.文章进行软删除
        //2.html文件不删除，文件名加入'--del'进行标识
        //3.由于没有删除html文件，所以禁止搜索引擎收录
        //4.为了兼容以前的逻辑，amp类型文件不加'--del'标识，只禁止收录


        $post = Post::query()->findOrFail($this->id);
        $filePath = base_path('../').$post->html_fullpath;
        if( strpos($post->html_fullpath,'--tmp') ){
            $postPath = strtr($post->html_fullpath,['--tmp'=>'--del']);
            $postName = strtr($post->html_name,['--tmp'=>'--del']);
            //之前的文章变动没有为amp类型加标识
            $ampFilePath = strtr($filePath,['--tmp.html'=>'.amp.html']);
        }else{
            $postPath = strtr($post->html_fullpath,['.html'=>'--del.html']);
            $postName = strtr($post->html_name,['.html'=>'--del.html']);
            $ampFilePath = strtr($filePath,['.html'=>'.amp.html']);
        }

        if( is_file($filePath) && is_file($ampFilePath) ){
            $postContent = file_get_contents($filePath);
            $ampContent = file_get_contents($ampFilePath);
            $handlePostContent = $this->toggleSEO($postContent);
            //amp类型文章禁止收录即可
            $handleAmpContent = $this->toggleSEO($ampContent);
            file_put_contents($filePath,$handlePostContent);
            file_put_contents($ampFilePath,$handleAmpContent);
        }else{
            throw new \Exception('文章html文件丢失');
        }

        rename($filePath, base_path('../').$postPath);
        //更新文件名和文件路径带有--del标识
        $post->html_name = $postName;
        $post->html_fullpath = $postPath;
        $post->post_status = 2;
        $post->saveOrFail();
        $post->delete();

        return true;
    }

    /**
     * 创建/更新 时生成html文件
     * @param int $type 1:创建; 2:更新
     * @return bool
     * @throws \Exception
     */
    public function generateHtmlFile( $type = 1 )
    {
        //1.新建或修改生成的html文件有'--tmp'标识为预览文件
        //2.预览文件不会进入目录的文章列表
        //3.预览文件禁止搜索引擎抓取
        //4.新建或修改生成的html文件同时会生成一个'.amp.html'结尾的移动端文件
        //5.生成文件图片会加lazyload的效果
        //6.文章属性的标签显示不同的语言

        DB::beginTransaction();
        $type == 1 and $this->create();
        $type == 2 and $this->update();

        //模板中需要替换的变量
        $replaceVarArr = [
            '{{language}}' => $this->getArticleLang(),
            '{{title}}' => $this->title,
            '{{description}}' => $this->description,
            '{{keywords}}' => $this->keywords,
            '{{editor-name}}' => ($this->editor_info)->editor_name,
            '{{html-fullpath}}' => Request::server('REQUEST_SCHEME'). '://' . Request::server('SERVER_NAME').$this->html_fullpath,
            '<!--{{amp-html-path}}-->' => $this->getAmpHtmlPath(),
            '<!--{{structrued-data}}-->' => html_entity_decode($this->structured_data),
            '<!--{{ga-code-url}}-->' => '<script src="'.$this->getEditorAttr('ga_code_url').'"></script>',
            '{{directory-fullpath}}' => $this->directory_fullpath.'/',
            '{{directory-title}}' => $this->getDirectoryTitle($this->directory_fullpath),
            '{{summary}}' => $this->summary,
            '{{editor-twitter-url}}' => $this->getEditorAttr('twitter_url'),
            '{{editor-url}}' => $this->getEditorAttr('editor_url'),
            '{{editor-avatar}}' => ($this->editor_info)->editor_avatar,
            '{{updated-at}}' => $this->getUpdatedAt(),
            '{{read-time}}' => !empty($this->getPostAttr('read_time'))?:$this->getReadTime($this->contents,$this->getLangName($this->lang_id)),//如果没有填写阅读时间则系统自动生成
            '<!--{{quick-search}}-->' => !empty($this->getPostAttr('quick_search'))?html_entity_decode($this->getPostAttr('quick_search')):'',
            '{{post-id}}' => $this->id??($this->postObj)->id,
            '{{contents}}' => deCodeHtml($this->contents),
            '<!--{{next-page}}-->' => !empty($this->getPostAttr('next_page'))?html_entity_decode($this->getPostAttr('next_page')):'',
            '{{html-pathname}}' => $this->html_fullpath,
            '<!--{{related-articles}}-->' => $this->getRelatedPost($this->related_posts),
            '<!--{{popular-articles}}-->' => !empty($this->getPostAttr('popular_articles'))?$this->handlePopularArticles(html_entity_decode($this->getPostAttr('popular_articles'))):'',
            '<!--{{comment-system}}-->' => '',
            '{{date-year}}' => date('Y',time()),
        ];
        //获取post amp模板内容
        $tplPath = $this->getTempPath($this->template_id,1);
        $amptplPath = $this->getTempPath($this->template_amp_id,2);
        if( !is_file($tplPath) ){
            DB::rollBack();
            throw new \Exception('未找到POST模板文件');
        }
        if( !is_file($amptplPath) ){
            DB::rollBack();
            throw new \Exception('未找到AMP模板文件');
        }
        //关闭搜索引擎对POST页面的收录
        $tplHtmlContent = $this->toggleSEO(file_get_contents($tplPath));
        if( !$tplHtmlContent ){
            DB::rollBack();
            throw new \Exception('模板文件缺少meta标签');
        }
        //开启FaceBook评论后在页面插入相应的代码
        if( $this->fb_comment == 1 ){
            $faceBookComment = $this->getFaceBookCommentPlugin();
            $replaceVarArr = array_merge($replaceVarArr,$faceBookComment);
        }
        //开启目录索引后在页面插入相应的代码
        if( $this->article_index == 1 ){
            $articleIndex = $this->getArticleIndex();
            $replaceVarArr = array_merge($replaceVarArr,$articleIndex);
        }
        //将模板文件中的标签替换成文章相关内容
        $htmlContent = strtr($tplHtmlContent,$replaceVarArr);
        $ampHtmlContent = strtr(file_get_contents($amptplPath),$replaceVarArr);
        if( !$htmlContent || !$ampHtmlContent ){
            DB::rollBack();
            throw new \Exception('文章生成失败');
        }
        //对POST类型的文章的图片进行懒加载处理
        $htmlContent = $this->imgLazyLoad($htmlContent);
        //html文件中引入文章block相关资源
        $htmlContent = $this->includeBlockResource($htmlContent);
        $tempFilePath = base_path('../').$this->html_fullpath;
        $ampFilePath = base_path('../').$this->amp_fullpath;
        !is_dir(base_path('../').$this->directory_fullpath) and mkdir(base_path('../').$this->directory_fullpath);
        if( !file_put_contents($tempFilePath,$htmlContent) || !file_put_contents($ampFilePath,$ampHtmlContent)){
            DB::rollBack();
            throw new \Exception('文章生成失败');
        }
        DB::commit();

        return true;
    }

    /**
     * 文章替换模板/作者
     * @param array $article 文章属性
     * @param string $tplPath 替换模板的路径
     * @throws \Exception
     */
    public function replaceHtmlFile( $article,$tplPath )
    {
        //1.使用数据库的数据进行模板替换
        //2.替换模板不会影响是否在列表中显示、收录状态以及发布状态
        //3.替换模板暂时只替换post模板
        //4.替换模板生成文件的图片依然会加lazyload的效果
        //5.文章属性的标签显示不同的语言

        //模板中需要替换的变量
        $replaceVarArr = [
            '{{language}}' => $this->getArticleLang(),
            '{{title}}' => $article['title'],
            '{{description}}' => $article['description'],
            '{{keywords}}' => $article['keywords'],
            '{{editor-name}}' => $article['editor']['editor_name'],
            '{{html-fullpath}}' => Request::server('REQUEST_SCHEME'). '://' . Request::server('SERVER_NAME').$article['html_fullpath'],
            '<!--{{amp-html-path}}-->' => $this->getAmpHtmlPath(),
            '<!--{{structrued-data}}-->' => html_entity_decode($article['structured_data']),
            '<!--{{ga-code-url}}-->' => '<script src="'.$this->getEditorAttr('ga_code_url').'"></script>',
            '{{directory-fullpath}}' => $article['directory_fullpath'].'/',
            '{{directory-title}}' => $this->getDirectoryTitle($article['directory_fullpath']),
            '{{summary}}' => $article['summary'],
            '{{editor-twitter-url}}' => $this->getEditorAttr('twitter_url'),
            '{{editor-url}}' => $this->getEditorAttr('editor_url'),
            '{{editor-avatar}}' => $article['editor']['editor_avatar'],
            '{{updated-at}}' => $this->getUpdatedAt(),
            '{{read-time}}' => !empty($this->getPostAttr('read_time'))?:$this->getReadTime($article['contents'],$this->getLangName()),//如果没有填写阅读时间则系统自动生成
            '<!--{{quick-search}}-->' => !empty($this->getPostAttr('quick_search'))?html_entity_decode($this->getPostAttr('quick_search')):'',
            '{{post-id}}' => $article['id'],
            '{{contents}}' => deCodeHtml($article['contents']),
            '<!--{{next-page}}-->' => !empty($this->getPostAttr('next_page'))?html_entity_decode($this->getPostAttr('next_page')):'',
            '{{html-pathname}}' => $article['html_fullpath'],
            '<!--{{related-articles}}-->' => $this->getRelatedPost($article['related_posts']),
            '<!--{{popular-articles}}-->' => !empty($this->getPostAttr('popular_articles'))?$this->handlePopularArticles(html_entity_decode($this->getPostAttr('popular_articles'))):'',
            '<!--{{comment-system}}-->' => '',
            '{{date-year}}' => date('Y',time()),
        ];

        //如果文章状态为待发布 则关闭搜索引擎对文章的收录
        if( $article['post_status'] != 1 ){
            $tplHtmlContent = $this->toggleSEO(file_get_contents($tplPath));
        }else{
            $tplHtmlContent = file_get_contents($tplPath);
        }
        //开启FaceBook评论后在页面插入相应的代码
        if( $article['fb_comment'] == 1 ){
            $faceBookComment = $this->getFaceBookCommentPlugin();
            $replaceVarArr = array_merge($replaceVarArr,$faceBookComment);
        }
        //开启目录索引后在页面插入相应的代码
        if( $article['article_index'] == 1 ){
            $articleIndex = $this->getArticleIndex();
            $replaceVarArr = array_merge($replaceVarArr,$articleIndex);
        }
        //将模板文件中的标签替换成文章相关内容
        $htmlContent = strtr($tplHtmlContent,$replaceVarArr);
        if( !$htmlContent ){
            throw new \Exception('文章生成失败');
        }
        //对POST类型的文章的图片进行懒加载处理
        $htmlContent = $this->imgLazyLoad($htmlContent);
        //html文件中引入文章block相关资源
        $htmlContent = $this->includeBlockResource($htmlContent);
        $filePath = base_path('../').$article['html_fullpath'];
        !is_dir(base_path('../').$article['directory_fullpath']) and mkdir(base_path('../').$article['directory_fullpath']);
        if( !file_put_contents($filePath,$htmlContent) ){
            throw new \Exception('文章生成失败');
        }
    }

    public function replaceArticleEditor()
    {

    }

}
