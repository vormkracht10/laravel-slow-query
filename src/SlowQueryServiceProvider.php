<?php

namespace Vormkracht10\SlowQuery;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Vormkracht10\SlowQuery\Events\QueryExecutedSlowly;
use Vormkracht10\SlowQuery\Notifications\SlowQueryDetected;

class SlowQueryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->setupEvents($this->app->events);
    }

    public function register()
    {
    }

    public function setupEvents($events)
    {
        $events->listen(QueryExecuted::class, function (QueryExecuted $query) {
            if ($this->slowQueryCheck($query)) {
                event(new QueryExecutedSlowly($query));
            }
        });

        $events->listen(QueryExecutedSlowly::class, function (QueryExecutedSlowly $event) {
            Notification::route('discord', '680703864172707841')
                ->notify(new SlowQueryDetected($event->query));
        });
    }

    public function slowQueryCheck(QueryExecuted $query)
    {
        return $query->time >= Config::get('slow-query.slow_query_treshold_in_ms');
    }
}
