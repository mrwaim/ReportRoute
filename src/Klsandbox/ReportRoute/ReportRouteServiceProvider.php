<?php namespace Klsandbox\ReportRoute;

use Illuminate\Support\ServiceProvider;
use Klsandbox\ReportRoute\Console\Commands\SiteDeleteReport;
use Klsandbox\ReportRoute\Console\Commands\SiteMakeReport;
use Klsandbox\ReportRoute\Console\Commands\SiteUpdateReport;
use Klsandbox\ReportRoute\Services\ReportService;

class ReportRouteServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register() {
		$this->app->singleton('command.klsandbox.sitedeletereport', function() {
			return new SiteDeleteReport();
		});

		$this->commands('command.klsandbox.sitedeletereport');

		$this->app->singleton('command.klsandbox.sitemakereport', function() {
			return new SiteMakeReport(new ReportService());
		});

		$this->commands('command.klsandbox.sitemakereport');

		$this->app->singleton('command.klsandbox.siteupdatereport', function() {
			return new SiteUpdateReport(new ReportService());
		});

		$this->commands('command.klsandbox.siteupdatereport');
	}


	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [];
	}

	public function boot() {
		if (!$this->app->routesAreCached()) {
			require __DIR__ . '/../../../routes/routes.php';
		}

		$this->loadViewsFrom(__DIR__ . '/../../../views/', 'report-route');

		$this->publishes([
			__DIR__ . '/../../../views/' => base_path('resources/views/vendor/report-route')
		], 'views');

		$this->publishes([
			__DIR__ . '/../../../database/migrations/' => database_path('/migrations')
		], 'migrations');
	}
}
