<?php

namespace App\Admin\Actions;

use App\Models\Editor;
use Dcat\Admin\Admin;
use Dcat\Admin\Grid\Tools\AbstractTool;


class EditorSelect extends AbstractTool
{
    protected function script()
    {
        return <<<JS
$("#replace_editor").select2();
JS;
    }

    public function render()
    {
        Admin::script($this->script());

        $editorArr = Editor::query()->orderBy('id')->pluck('editor_name','id')->toArray();

        return view('admin.tools.editor', compact('editorArr'));

    }
}
