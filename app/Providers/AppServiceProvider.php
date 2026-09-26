<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\View\Composers\WorkNoteComposer;
use App\Models\User;
use App\Observers\UserObserver;

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
        // Ilalagay ng WorkNoteComposer yung $unreadWorkNotes variable
        // sa bawat layout na may "Work Notes" sa sidebar nila.
        // Tandaan: dapat eksaktong tugma ang pangalan dito sa ginamit
        // sa @extends(...) ng mga worknotes blade files (case-sensitive).
        View::composer('layouts.admin.app', WorkNoteComposer::class);
        View::composer('layouts.Supply.app', WorkNoteComposer::class);
        View::composer('layouts.Inspector.app', WorkNoteComposer::class);
    
        User::observe(UserObserver::class);
    }
}
