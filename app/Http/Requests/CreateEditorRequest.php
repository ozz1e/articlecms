<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateEditorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'editor_name'=>'required|unique:editor,editor_name',
            'lang_id'=>'required|numeric|min:1',
            'editor_avatar'=>'required',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息
     *
     * @return array
     */
    public function messages()
    {
        return [
            'editor_name.required' => '请填写作者名称',
            'editor_name.unique'=>'作者名称已存在',
            'lang_id.required'=>'请选择语言',
            'lang_id.numeric'=>'请选择一项语言',
            'lang_id.min'=>'请选择一项语言',
            'editor_avatar.required'=>'请上传作者头像',
        ];
    }
}
