/**
 * @license Copyright (c) 2003-2020, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function (config) {
    // Define changes to default configuration here. For example:
    // config.language = 'fr';
    // config.uiColor = '#AADC6E';

    // The toolbar groups arrangement, optimized for two toolbar rows.
    config.toolbarGroups = [
        {name: 'document', groups: ['mode', 'document', 'doctools']},
        {name: 'others'},
        {name: 'editing', groups: ['find', 'selection', 'spellchecker']},
        {name: 'links'},
        {name: 'insert'},
        {name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi']},
        {name: 'basicstyles', groups: ['basicstyles', 'cleanup']},
        {name: 'styles'},
        {name: 'colors'},
        // { name: 'forms' },
        {name: 'clipboard', groups: ['clipboard', 'undo']},
        {name: 'tools'},
    ];

    config.removeButtons = 'Save,Preview,Print';

    config.extraAllowedContent = 'div{*}(*)[*];h2{*}(*)[*];h3{*}(*)[*];h4{*}(*)[*];h5{*}(*)[*];h6{*}(*)[*];p{*}(*)[*];ul{*}(*)[*];li{*}(*)[*];ol{*}(*)[*];a{*}(*)[*];span{*}(*)[*];dl{*}(*)[*];dt{*}(*)[*];dd{*}(*)[*];iframe{*}(*)[*];';

    config.extraPlugins = 'editorplaceholder,image2,uploadimage,language,dialog,insertpre,placeholder';

    config.language_list = ['en:English', 'ar:Arabic:rtl', 'de:German', 'pt:Portuguese', 'es:Spanish', 'it:Italian', 'fr:French', 'ja:Japanese', 'zh:Traditional Chinese', 'zh-cn:Simplified Chinese'];
    config.removePlugins = 'image,easyimage,cloudservices';
    config.baseFloatZIndex = 10005;

    config.filebrowserBrowseUrl = '../0d958d0af15d73beeec6852c13911a700/ckfinder.html';
    config.filebrowserImageBrowseUrl = '../0d958d0af15d73beeec6852c13911a700/ckfinder.html';
    config.filebrowserFlashBrowseUrl = '../0d958d0af15d73beeec6852c13911a700/ckfinder.html';
    config.filebrowserUploadUrl = '../0d958d0af15d73beeec6852c13911a700/core/connector/php/connector.php?command=QuickUpload&type=Files';
    config.filebrowserImageUploadUrl = '../0d958d0af15d73beeec6852c13911a700/core/connector/php/connector.php?command=QuickUpload&type=screenshot';
    config.filebrowserFlashUploadUrl = '../0d958d0af15d73beeec6852c13911a700/core/connector/php/connector.php?command=QuickUpload&type=Flash';

    config.image2_alignClasses = ['align-left', 'align-center', 'align-right'];
    config.image2_captionedClass = 'image-captioned';
    config.image2_altRequired = true;

    // Use `'&'` instead of `'&amp;'`
    config.forceSimpleAmpersand = true;

};
