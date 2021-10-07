<!--表格工具栏替换模板的selelct模板-->
<div class="btn-group" data-toggle="buttons">
    <select name="r_t_id" id="replace_template" style="width: 150px">
        <option value="" disabled selected>选择替换模板</option>
    @foreach($templateArr as $key => $value)
            <option value="{{$key}}">{{$value}}</option>
    @endforeach
    </select>
</div>
