$(function (){
    var plainEditorId = 'plain_mode';

    CKEDITOR.replace(plainEditorId, {
        editorplaceholder: '输入相关文章（有序号和无序均可）',
        height: '10em',
        entities: false,
        toolbarGroups: [
            {name: 'document', groups: ['mode', 'document', 'doctools']},
            {name: 'clipboard', groups: ['clipboard', 'undo']},
            {name: 'editing', groups: ['find', 'selection', 'spellchecker', 'editing']},
            {name: 'forms', groups: ['forms']},
            {name: 'basicstyles', groups: ['basicstyles', 'cleanup']},
            {name: 'colors', groups: ['colors']},
            {name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi', 'paragraph']},
            {name: 'links', groups: ['links']},
            {name: 'insert', groups: ['insert']},
            {name: 'styles', groups: ['styles']},
            {name: 'tools', groups: ['tools']},
            {name: 'others', groups: ['others']},
            {name: 'about', groups: ['about']}
        ],

        removeButtons: 'Cut,Copy,Paste,PasteText,PasteFromWord,Print,Preview,ExportPdf,Save,Templates,Replace,Find,SelectAll,Scayt,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Blockquote,CreateDiv,Image,Flash,Table,HorizontalRule,Smiley,PageBreak,Iframe,Maximize,About,Outdent,Indent,Styles,CopyFormatting,RemoveFormat,NewPage,JustifyBlock,Anchor,ShowBlocks,Format,Font,FontSize',
    });


    $('.post-block-btn').on('click',function (){
        var name = $(this).data('cssfilename');
        var path = $(this).data('cssfilepath');
        var blockCssID = 'post-block-css-' + name;
        var $iframe = $('#cke_2_contents').find('iframe').contents();

        if($iframe.find('#'+ blockCssID).length <= 0) {
            var htm = '<link id="'+ blockCssID+'" type="text/css" rel="stylesheet" href="'+ path +'">';
            $iframe.find('head').append(htm);
        }

    });
});
