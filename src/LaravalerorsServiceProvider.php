<?php

namespace AHAbid\Laravalerors;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class LaravalerorsServiceProvider extends ServiceProvider
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
        Validator::resolver(function ($translator, $data, $rules, $messages, $customAttributes) {
            $validator = new \Illuminate\Validation\Validator($translator, $data, $rules, $messages, $customAttributes);
            $validator->setException(LaravalerorsException::class);
            return $validator;
        });
    }
}
