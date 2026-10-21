<?php

namespace App\Providers;

use App\Repositories\Contracts\EbookRepositoryInterface;
use App\Repositories\Eloquent\EbookRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EbookRepositoryInterface::class, EbookRepository::class);
    }

    public function boot(): void
    {
    }
}
