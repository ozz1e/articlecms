<!--表格工具栏替换作者的selelct模板-->
<div class="btn-group" data-toggle="buttons">
    <select name="r_e_id" id="replace_editor" style="width: 150px">
        <option value="" disabled selected>选择替换作者</option>
        @foreach($editorArr as $key => $value)
            <option value="{{$key}}">{{$value}}</option>
        @endforeach
    </select>
</div>
