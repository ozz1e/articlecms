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
</script>
