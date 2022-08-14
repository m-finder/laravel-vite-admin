<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->isLocal()) {
            $this->app->register(IdeHelperServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        // HTTPS 访问
        if(config('app.is_https')){
            URL::forceScheme('https');
        }

        //慢查询日志
        DB::listen(function ($query) {
            $sql = $query->sql;
            $bingings = $query->bindings;
            $time = $query->time;
            if (config('app.debug') == 'true' && $time <= 1000) {
                $sql = binging_into_sql($bingings,$sql);
                Log::channel('sql')->info('query', compact('time', 'sql'));
            }
            //超过1秒记录日志
            if ($time > 1000) {
                $sql = binging_into_sql($bingings,$sql);
                Log::channel('sql')->info('slowly query', compact('time', 'sql'));
            }
        });
        /* toRawSql start*/
        \Illuminate\Database\Query\Builder::macro('toRawSql', function () {
            return binging_into_sql($this->getBindings(),$this->toSql());
        });
        \Illuminate\Database\Eloquent\Builder::macro('toRawSql', function () {
            return ($this->getQuery()->toRawSql());
        });
        /* toRawSql end*/
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);


        //自定义token是否过期的方法
        Sanctum::authenticateAccessTokensUsing(function ($accessToken, $isValid){
            $expiration = config('sanctum.expiration');
            $time = $accessToken->last_used_at??$accessToken->created_at;
            return $time->gt(now()->subMinutes($expiration));
        });
    }
}
