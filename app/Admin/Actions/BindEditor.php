<?php

namespace App\Admin\Actions;

use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Dcat\Admin\Widgets\Modal;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * 给编辑员绑定作者
 * Class BindEditor
 * @package App\Admin\Actions
 */
class BindEditor extends RowAction
{
    /**
     * @return string
     */
	protected $title = '绑定/解绑作者';

    public function render()
    {
        // 实例化表单类并传递自定义参数
        $form = \App\Admin\Forms\BindEditor::make()->payload(['id' => $this->getKey()]);

        return Modal::make()
            ->lg()
            ->title($this->title)
            ->body($form)
            ->button($this->title);
    }
}
