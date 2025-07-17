<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; // <--- 1. ADD THIS LINE
use Illuminate\Filesystem\Filesystem;
use Barryvdh\Snappy\PdfWrapper;


class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('files', function () {
            return new Filesystem;
        });

        $this->app->bind('snappy.pdf.wrapper', function ($app) {
            $pdf = new PdfWrapper(
                new \Knp\Snappy\Pdf(config('snappy.pdf.binary')),
                $app['files'],
                $app['view']
            );

            return $pdf;
        });
    }

    public function boot()
    {
          Paginator::useBootstrapFive(); 
    }
}