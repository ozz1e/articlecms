<?php
return [
    'labels' => [
        'Post' => '文章',
        'post' => '文章管理',
    ],
    'fields' => [
        'title' => '文章标题',
        'keywords' => '文章关键字',
        'description' => '文章描述',
        'directory_fullpath' => '生成的静态页面的目录全路径',
        'html_fullpath' => '生成的静态页面的名称',
        'html_name' => 'html_name',
        'summary' => '文章简介',
        'contents' => '文章内容',
        'template_id' => '模板id',
        'template_amp_id' => '是否启用amp页面，0未启用，number表示启用了模板为设置值的amp模板id',
        'post_status' => '文章发布状态，0未发布，1已发布，2删除',
        'editor_json' => '文章作者json数据文本',
        'editor_id' => '用于检测文章属性哪个作者',
        'lang_id' => '文章语言',
        'related_posts' => '相关文章',
        'published_at' => '文章发布时间',
        'structured_data' => '结构化数据',
        'fb_comment' => '默认启用Facebook插件',
        'lightbox' => '默认启用Lightbox插件',
    ],
    'options' => [
    ],
];
