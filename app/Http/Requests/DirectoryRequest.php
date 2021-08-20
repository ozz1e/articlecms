<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DirectoryRequest extends FormRequest
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
        switch($this->method())
        {
            // CREATE
            case 'POST':
            {
                return [
                    'domain'=>'required',
                    'lang_id'=>'required|numeric|min:1',
                    'directory_name'=>'required',
                    'directory_fullpath'=>'required',
                    'directory_title'=>'required',
                    'directory_intro'=>'required',
                    'template_id'=>'required',
                    'template_amp_id'=>'required',
                ];
            }
            // UPDATE
            case 'PUT':
            case 'PATCH':
            {
                return [
                    'id'=>'required|numeric',
                    'domain'=>'required',
                    'lang_id'=>'required|numeric|min:1',
                    'directory_name'=>'required',
                    'directory_fullpath'=>'required',
                    'directory_title'=>'required',
                    'directory_intro'=>'required',
                    'template_id'=>'required',
                    'template_amp_id'=>'required',
                ];
            }
            case 'GET':
            case 'DELETE':
            {

            }
            default:
            {
                return [];
            }
        }
    }
    /**
     * 获取已定义验证规则的错误消息
     *
     * @return array
     */
    public function messages()
    {
        return [
            'domain.required' => '请填写域名',
            'editor_name.unique'=>'作者名称已存在',
            'lang_id.required'=>'请选择语言',
            'lang_id.numeric'=>'请选择一项语言',
            'lang_id.min'=>'请选择一项语言',
            'directory_name.required'=>'请填写目录名',
            'directory_fullpath.required'=>'请填写目录路径',
            'directory_title.required'=>'请填写目录标题',
            'directory_intro.required'=>'请填写目录简介',
            'template_id.required'=>'请选择POST模板',
            'template_amp_id.required'=>'请选择AMP模板',
        ];
    }
}
