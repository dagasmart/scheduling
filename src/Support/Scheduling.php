<?php

namespace DagaSmart\Scheduling\Support;

use Exception;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;

class Scheduling
{
    /**
     * @var string out put file for command.
     */
    protected string $sendOutputTo;

    /**
     * Get all events in console kernel.
     *
     * @return array
     * @throws BindingResolutionException
     */
    protected function getKernelEvents(): array
    {
        app()->make('Illuminate\Contracts\Console\Kernel');

        return app()->make('Illuminate\Console\Scheduling\Schedule')->events();
    }

    /**
     * Get all formatted tasks.
     *
     * @throws Exception
     *
     * @return array
     */
    public function getTasks(): array
    {
        $tasks = [];

        foreach ($this->getKernelEvents() as $event) {
            $tasks[] = [
                'task'          => $this->formatTask($event),
                'expression'    => $event->expression,
                'nextRunDate'   => $event->nextRunDate()->format('Y-m-d H:i:s'),
                'description'   => $event->description,
                'readable'      => CronSchedule::fromCronString($event->expression)->asNaturalLanguage(),
            ];
        }

        return $tasks;
    }

    /**
     * Format a giving task.
     *
     * @param $event
     *
     * @return array
     */
    protected function formatTask($event): array
    {
        if ($event instanceof CallbackEvent) {
            return [
                'type' => 'closure',
                'name' => 'Closure',
            ];
        }

        if (Str::contains($event->command, '\'artisan\'')) {
            $exploded = explode(' ', $event->command);

            return [
                'type' => 'artisan',
                'name' => 'artisan '.implode(' ', array_slice($exploded, 2)),
            ];
        }

        if (PHP_OS_FAMILY === 'Windows' && Str::contains($event->command, '"artisan"')) {
            $exploded = explode(' ', $event->command);

            return [
                'type' => 'artisan',
                'name' => 'artisan '.implode(' ', array_slice($exploded, 2)),
            ];
        }

        return [
            'type' => 'command',
            'name' => $event->command,
        ];
    }

    /**
     * Run specific task.
     *
     * @param int $id
     *
     * @return string
     * @throws BindingResolutionException
     * @throws \Throwable
     */
    public function runTask(int $id): string
    {
        set_time_limit(0);

        /** @var Event $event */
        $event = $this->getKernelEvents()[$id - 1];

        if (PHP_OS_FAMILY === 'Windows') {
            $event->command = Str::of($event->command)->replace('php-cgi.exe', 'php.exe');
        }

        $event->sendOutputTo($this->getOutputTo());

        $event->run(app());

        return $this->readOutput();
    }

    /**
     * @return string
     */
    protected function getOutputTo(): string
    {
        if (!$this->sendOutputTo) {
            $this->sendOutputTo = storage_path('app/task-schedule.output');
        }

        return $this->sendOutputTo;
    }

    /**
     * Read output info from output file.
     *
     * @return string
     */
    protected function readOutput(): string
    {
        return file_get_contents($this->getOutputTo());
    }

    /**
     * Bootstrap this package.
     *
     * @return void
     */
//    public static function boot()
//    {
//        static::registerRoutes();
//
//        Admin::extend('scheduling', __CLASS__);
//    }

//    /**
//     * Register routes for laravel-admin.
//     *
//     * @return void
//     */
//    protected static function registerRoutes()
//    {
//        parent::routes(function ($router) {
//            /* @var \Illuminate\Routing\Router $router */
//            $router->get('scheduling', 'Encore\Admin\Scheduling\SchedulingController@index')->name('scheduling-index');
//            $router->post('scheduling/run', 'Encore\Admin\Scheduling\SchedulingController@runEvent')->name('scheduling-run');
//        });
//    }

//    /**
//     * {@inheritdoc}
//     */
//    public static function import()
//    {
//        parent::createMenu('Scheduling', 'scheduling', 'fa-clock-o');
//
//        parent::createPermission('Scheduling', 'ext.scheduling', 'scheduling*');
//    }
}
