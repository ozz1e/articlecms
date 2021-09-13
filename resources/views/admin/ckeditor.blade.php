<div class="{{$viewClass['form-group']}}">

    <label class="{{$viewClass['label']}} control-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        <textarea name="{{ $name}}" placeholder="{{ $placeholder }}" {!! $attributes !!} >{!! $value !!}</textarea>

        @include('admin::form.help-block')

    </div>
</div>

<script require="@ckeditor" init="{!! $selector !!}">
    $this.ckeditor();

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
