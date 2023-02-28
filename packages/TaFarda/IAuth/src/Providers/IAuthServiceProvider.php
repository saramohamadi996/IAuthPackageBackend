<?php

namespace TaFarda\IAuth\Providers;

use TaFarda\IAuth\app\Contracts\Interfaces\AdminAuthRepositoryInterface;
use TaFarda\IAuth\app\Contracts\Interfaces\AdminRepositoryInterface;
use TaFarda\IAuth\app\Contracts\Interfaces\BaseRepositoryInterface;
use TaFarda\IAuth\app\Contracts\Interfaces\LogRepositoryInterface;
use TaFarda\IAuth\app\Contracts\Interfaces\ProductRepositoryInterface;
use TaFarda\IAuth\app\Contracts\Interfaces\UserVerifyRepositoryInterface;
use TaFarda\IAuth\app\Contracts\Interfaces\UserRepositoryInterface;
use TaFarda\IAuth\app\Contracts\Repositories\AdminAuthRepository;
use TaFarda\IAuth\app\Contracts\Repositories\AdminRepository;
use TaFarda\IAuth\app\Contracts\Repositories\BaseRepository;
use TaFarda\IAuth\app\Contracts\Repositories\LogRepository;
use TaFarda\IAuth\app\Contracts\Repositories\ProductRepository;
use TaFarda\IAuth\app\Contracts\Repositories\UserVerifyRepository;
use TaFarda\IAuth\app\Contracts\Repositories\UserRepository;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
class IAuthServiceProvider extends ServiceProvider
{
    /**Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(AdminAuthRepositoryInterface::class, AdminAuthRepository::class);
        $this->app->bind(AdminRepositoryInterface::class, AdminRepository::class);
        $this->app->bind(UserVerifyRepositoryInterface::class, UserVerifyRepository::class);
        $this->app->bind(BaseRepositoryInterface::class, BaseRepository::class);
        $this->app->bind(LogRepositoryInterface::class, LogRepository::class);
        $this->app->bind(UserRepositoryInterface::class,UserRepository::class);
        $this->mergeConfigFrom($this->getConfig(), 'tafarda_iauth');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    public function boot()
    {
        $this->publishConfig();
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadJsonTranslationsFrom(__DIR__ . "/../lang");

        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });
    }

    /**
     * get Config.
     *
     * @return string
     */
    private function getConfig(): string
    {
        return __DIR__ . "/../config/tafarda_iauth.php";
    }

    /**
     * publish Config.
     *
     * @return void
     */
    private function publishConfig(): void
    {
        $this->publishes([$this->getConfig() => App::configPath('tafarda_iauth.php')]);
    }

}
