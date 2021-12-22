<?php

namespace dobron\LaravelDatabaseEnum;

use dobron\LaravelDatabaseEnum\Providers\LaravelDatabaseEnumServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTest;

abstract class TestCase extends BaseTest
{
    /**
     * Application object
     *
     * @var Application
     */
    protected $app;

    /**
     * Define environment setup
     *
     * @param Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $this->app = $app;

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('i18n.driver', 'json');
        $app['config']->set('i18n.language_datasource', realpath(__DIR__ . '/../storage/i18n/languages.json'));
    }

    /**
     * Define package service provider
     *
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        $this->app = $app;

        return [
            LaravelDatabaseEnumServiceProvider::class,
        ];
    }

    /**
     * Invoke protected / private method of the given object
     *
     * @param mixed $object
     * @param string $methodName
     * @param array $parameters
     * @return mixed
     * @throws \ReflectionException
     */
    protected function invokeMethod($object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Get any protected / private property value
     *
     * @param mixed $object
     * @param string $propertyName
     * @return mixed
     * @throws \ReflectionException
     */
    public function getPropertyValue($object, string $propertyName)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * Prepare database requirements
     * to perform any tests.
     *
     * @param string $migrationPath
     * @return void
     */
    protected function prepareDatabase(string $migrationPath)
    {
        $this->loadMigrationsFrom($migrationPath);
    }

    /**
     * Setup the test environment
     *
     * @return void
     */
    public function setUp() :void
    {
        parent::setUp();

        $this->prepareDatabase(
            realpath(__DIR__ . '/../database/migrations')
        );

        Factory::guessFactoryNamesUsing(function (string $modelName) {
            return 'Database\\Factories\\' . class_basename($modelName) . 'Factory';
        });
    }
}
