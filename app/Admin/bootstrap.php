<?php

use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use App\Admin\Extensions\Form\CKEditor;
use App\Admin\Extensions\Form\PHPEditor;


/**
 * Dcat-admin - admin builder based on Laravel.
 * @author jqh <https://github.com/jqhph>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 *
 * extend custom field:
 * Dcat\Admin\Form::extend('php', PHPEditor::class);
 * Dcat\Admin\Grid\Column::extend('php', PHPEditor::class);
 * Dcat\Admin\Grid\Filter::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */
Admin::js('/assets/js/viewImg.js?v='.time());

// 注册前端组件别名
Admin::asset()->alias('@ckeditor', [
    'js' => [
        '/packages/ckeditor/ckeditor.js',
        '/packages/ckeditor/adapters/jquery.js',
    ],
]);

Form::extend('ckeditor', CKEditor::class);

Admin::asset()->alias('@phpeditor', [
    'js' => [
        '/packages/codemirror-5.62.2/lib/codemirror.js',
        '/packages/codemirror-5.62.2/addon/edit/matchbrackets.js',
        '/packages/codemirror-5.62.2/mode/htmlmixed/htmlmixed.js',
        '/packages/codemirror-5.62.2/mode/xml/xml.js',
        '/packages/codemirror-5.62.2/mode/javascript/javascript.js',
        '/packages/codemirror-5.62.2/mode/css/css.js',
        '/packages/codemirror-5.62.2/mode/clike/clike.js',
        '/packages/codemirror-5.62.2/mode/php/php.js',
    ],
    'css' => '/packages/codemirror-5.62.2/lib/codemirror.css',
]);

Form::extend('php', PHPEditor::class);
