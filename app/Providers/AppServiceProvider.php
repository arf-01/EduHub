<?php

namespace App\Providers;

use App\Http\Livewire\QuizTimer;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        // URL::forceScheme('https');
        // Uncomment above line for production deployment

        Livewire::component('quiz-timer', QuizTimer::class);
    }
}
