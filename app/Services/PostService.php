<?php


namespace App\Services;


use App\Models\Directory;
use App\Models\Editor;

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
     * 文章发布时间
     * @var int
     */
    protected $published_at;
    /**
     * 结构化数据
     * @var string
     */
    protected $structured_data;
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

    public function setId( $id = 1 )
    {
        $this->id = $id;
        return $this;
    }

    public function setTitle( $title = '' )
    {
        $this->title = $title;
        return $this;
    }

    public function setDescription( $desc = '' )
    {
        $this->description = $desc;
        return $this;
    }

    public function setDirFullPath( $dirFullPath = '' )
    {
        $this->directory_fullpath = $dirFullPath;
        return $this;
    }

    public function setHtmlName( $htmlName = '' )
    {
        $this->html_name = $htmlName;
        return $this;
    }

    public function setHtmlFullPath()
    {
        $this->html_fullpath = $this->directory_fullpath.'/'.$this->html_name;
        return $this;
    }

    public function setSummary( $summary = '' )
    {
        $this->summary = $summary;
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

    public function setRelatedPost( $relatedPost = '' )
    {
        $this->related_posts = $relatedPost;
        return $this;
    }

    public function setPublishedAt( $publishedAt = 0 )
    {
        $this->published_at = $publishedAt;
        return $this;
    }

    public function setStructuredData( $structuredData = '' )
    {

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

    public function getStructureJson()
    {

    }

}
