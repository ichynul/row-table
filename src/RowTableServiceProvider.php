<?php

namespace Ichynul\RowTable;

use Encore\Admin\Form;
use Encore\Admin\Admin;
use Illuminate\Support\ServiceProvider;

class RowTableServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot(TableExt $extension)
    {
        if (!TableExt::boot()) {
            return;
        }

        if ($this->app->runningInConsole() && $assets = $extension->assets()) {
            $this->publishes(
                [$assets => public_path('vendor/laravel-admin-ext/row-table')],
                'row-table'
            );
        }

        Admin::booting(function () {
            Form::extend('table', Table::class);
        });
    }
}