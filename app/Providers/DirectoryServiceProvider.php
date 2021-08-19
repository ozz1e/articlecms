<?php

namespace App\Providers;

use App\Admin\Controllers\DirectoryController;
use App\Services\DirectoryService;
use Illuminate\Support\ServiceProvider;

class DirectoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //使用singleton绑定单例
        $this->app->singleton('directory',function(){
            return new DirectoryService();
        });

        //使用bind绑定实例到接口以便依赖注入
        $this->app->bind(DirectoryController::class,function(){
            return new DirectoryController();
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
