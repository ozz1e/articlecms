<div class="{{$viewClass['form-group']}}">

    <label class="{{$viewClass['label']}} control-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        <textarea name="{{ $name}}" placeholder="{{ $placeholder }}" {!! $attributes !!} >{!! $value !!}</textarea>

        @include('admin::form.help-block')

    </div>
</div>

<script require="@ckeditor" init="{!! $selector !!}">
    $("#normal_mode").ckeditor();
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
