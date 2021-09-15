<?php

namespace App\Providers;

use App\Admin\Controllers\PostController;
use App\Services\PostService;
use Illuminate\Support\ServiceProvider;

class PostServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //使用singleton绑定单例
        $this->app->singleton('post',function(){
            return new PostService();
        });

        //使用bind绑定实例到接口以便依赖注入
        $this->app->bind(PostController::class,function(){
            return new PostController();
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
