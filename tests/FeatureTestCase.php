<?php

namespace Laravel\Telescope\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse as LegacyTestResponse;
use Illuminate\Queue\Queue;
use Illuminate\Testing\TestResponse;
use Laravel\Telescope\Contracts\EntriesRepository;
use Laravel\Telescope\Storage\DatabaseEntriesRepository;
use Laravel\Telescope\Storage\EntryModel;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeServiceProvider;
use Orchestra\Testbench\TestCase;

class FeatureTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (Application::VERSION === '7.x-dev' || version_compare(Application::VERSION, '7.0', '>=')) {
            TestResponse::macro('terminateTelescope', [$this, 'terminateTelescope']);
        } else {
            LegacyTestResponse::macro('terminateTelescope', [$this, 'terminateTelescope']);
        }

        Telescope::flushEntries();
    }

    protected function tearDown(): void
    {
        Telescope::flushEntries();

        Queue::createPayloadUsing(null);

        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [
            TelescopeServiceProvider::class,
        ];
    }

    protected function resolveApplicationCore($app)
    {
        parent::resolveApplicationCore($app);

        $app->detectEnvironment(function () {
            return 'self-testing';
        });
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $config = $app->get('config');

        $config->set('logging.default', 'errorlog');

        $config->set('database.default', 'testbench');

        $config->set('telescope.storage.database.connection', 'testbench');

        $config->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app->when(DatabaseEntriesRepository::class)
            ->needs('$connection')
            ->give('testbench');
    }

    protected function loadTelescopeEntries()
    {
        $this->terminateTelescope();

        return EntryModel::all();
    }

    public function terminateTelescope()
    {
        Telescope::store(app(EntriesRepository::class));
    }
}
