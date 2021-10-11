<?php


namespace App\Admin\Controllers;


use App\Admin\Actions\BindEditor;
use App\Models\Editor;
use App\Models\Roles;
use App\Models\Users;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\DB;

class UserEditorController extends AdminController
{
    // 页面标题翻译，默认会自动读取，不需要指定
    public function title()
    {
        // labels翻译示例，最终翻译成 “用户中心”
        return admin_trans_label('Usereditor ');
    }

    protected function grid()
    {
        return Grid::make(new Users(), function (Grid $grid) {
            $role = Roles::with('user')->where('slug','=','operator')->first()->toArray();
            $showUsers = [];
            foreach ($role['user'] as $item) {
                $showUsers[] = $item['user_id'];
            }
            $grid->model()->whereIn('id',$showUsers);

            $grid->setActionClass(Grid\Displayers\Actions::class);

            $grid->column('username');
            $grid->column('created_at')->display(function ($created_at){
                return $created_at;
            });
            $grid->column('updated_at')->display(function($updated_at){
                return $updated_at;
            });

            $grid->disableCreateButton();

            $grid->actions(function (Grid\Displayers\Actions $actions){
                $actions->disableView();
                $actions->disableEdit();
                $actions->disableDelete();
                $actions->append(new BindEditor());
            });

        });


    }

}
