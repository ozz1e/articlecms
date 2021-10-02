<div class="{{$viewClass['form-group']}}">

    <label class="{{$viewClass['label']}} control-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        <textarea name="{{ $name}}" placeholder="{{ $placeholder }}" {!! $attributes !!} >{!! $value !!}</textarea>

        @include('admin::form.help-block')

    </div>
</div>

<script require="@ckeditor" init="{!! $selector !!}">
    //初始化文章内容的textarea
    $("#normal_mode").ckeditor();
    //初始化相关文章的textarea
    $('#plain_mode').ckeditor({
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

    //文章内容的富文本编辑初始化时加载block的css
    Dcat.init('.cke_contents', function ($this, id) {
        var htm = '';
        $(".post-block-btn").each(function(index){
            htm = '<link type="text/css" rel="stylesheet" href="'+$(this).data('cssfilepath')+'">';
            $this.find('iframe').contents().find('head').append(htm);
        })
    });


    //初始化属性textarea
    $(".field_attr_editor").ckeditor({
        editorplaceholder: '输入相关文章（有序号和无序均可）',
        height:'5em',
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

    CKEDITOR.on('instanceReady', function (ev) {
        ev.editor.on('paste', function (evt) {
            evt.data.dataValue = evt.data.dataValue
                .replace(/&lt;/g, '<')
                .replace(/&gt;/g, '>')
                .replace(/><br \/>/g, '>')
                .replace(/&nbsp;/g, '')
                .replace(/<p><!--/g, '<!--')
                .replace(/--><br \/><\/p>/g, '-->')
                .replace(/<br\s*\/?><\/div>/g, '<\/div>')
                .replace(/"<br\s*\/>/g, '"')
                .replace(/<p><div/g, '<div')
                .replace(/<\/div><\/p>/g, '<\/div>')
                .replace(/<br\s*\/>\s*/g, ' ') // 把 <br /> 替换成 空格
                .replace(/<p>&nbsp;<\/p>/g, '');
        });
    });


</script>
