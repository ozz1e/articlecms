<?php

namespace App\Admin\Actions;

use App\Models\Template;
use Dcat\Admin\Admin;
use Dcat\Admin\Grid\Tools\AbstractTool;

class TemplateSelect extends AbstractTool
{

    protected function script()
    {
        return <<<JS
$("#replace_template").select2();
JS;
    }

    public function render()
    {
        Admin::script($this->script());

        $templateArr = Template::query()->orderBy('id')->pluck('temp_name','id')->toArray();

        return view('admin.tools.template', compact('templateArr'));

    }

}
