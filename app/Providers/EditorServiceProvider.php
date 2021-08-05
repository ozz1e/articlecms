<?php

namespace App\Providers;

use App\Admin\Controllers\EditorController;
use App\Services\EditorService;
use Illuminate\Support\ServiceProvider;

class EditorServiceProvider extends ServiceProvider
{

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //使用singleton绑定单例
        $this->app->singleton('editor',function(){
            return new EditorService();
        });

        //使用bind绑定实例到接口以便依赖注入
        $this->app->bind(EditorController::class,function(){
            return new EditorController();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }


}
