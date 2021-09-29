<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
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
                    'title'=>'required',
                    'keywords'=>'required',
                    'description'=>'required',
                    'directory_fullpath'=>'required',
                    'html_name'=>'required',
                    'summary'=>'required',
                    'contents'=>'required',
                    'template_id'=>'required|numeric|min:1',
                    'template_amp_id'=>'required|numeric|min:1',
                    'editor_id'=>'required|numeric|min:1',
                    'fb_comment'=>'integer|between:0,1',
                    'lightbox'=>'integer|between:0,1',
                    'article_index'=>'integer|between:0,1',
                ];
            }
            // UPDATE
            case 'PUT':
            case 'PATCH':
            {
                return [
                    'id'=>'required|numeric|min:1',
                    'title'=>'required',
                    'keywords'=>'required',
                    'description'=>'required',
                    'html_name'=>'required',
                    'summary'=>'required',
                    'contents'=>'required',
                    'editor_id'=>'required|numeric|min:1',
                    'fb_comment'=>'integer|between:0,1',
                    'lightbox'=>'integer|between:0,1',
                    'article_index'=>'integer|between:0,1',
                ];
            }
            case 'GET':
            case 'DELETE':
            {
                return [
                    'id'=>'required|numeric|min:1',
                ];
            }
            default:
            {
                return [];
            }
        }
    }

    /**
     * 定义验证规则的错误消息
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.required' => '请填写标题',
            'keywords.required'=>'请填写关键词',
            'description.required'=>'请填写文章描述',
            'directory_fullpath.required'=>'请填写目录路径',
            'html_name.required'=>'请填写html文件名',
            'summary.required'=>'请文章简介',
            'contents.required'=>'请填写文章内容',
            'template_id.required'=>'请选择POST模板',
            'template_id.numeric'=>'POST模板参数异常',
            'template_id.min'=>'POST模板参数异常',
            'template_amp_id.required'=>'请选择AMP模板',
            'template_amp_id.numeric'=>'AMP模板参数异常',
            'template_amp_id.min'=>'AMP模板参数异常',
            'editor_id.required'=>'请选择作者',
            'editor_id.numeric'=>'作者参数异常',
            'editor_id.min'=>'作者参数异常',
            'fb_comment.integer'=>'FaceBook评论参数异常',
            'fb_comment.between'=>'FaceBook评论参数异常',
            'lightbox.integer'=>'LightBox幻灯参数异常',
            'lightbox.between'=>'LightBox幻灯参数异常',
        ];
    }
}
