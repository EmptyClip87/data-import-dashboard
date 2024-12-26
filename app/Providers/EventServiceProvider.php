<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Event::listen(BuildingMenu::class, function (BuildingMenu $event) {
            $importTypes = config('import_types');

            $files = [];
            foreach ($importTypes as $importType) {
                foreach ($importType['files'] as $key => $file) {
                    $files[$key] = [
                        'file' => $file,
                        'type' => $importType['label'],
                    ];
                }
            }

            $submenuData = [];
            foreach ($files as $fileKey => $file) {
                $submenuData[] = [
                    'text' => $file['type'] . ' - ' . $file['file']['label'],
                    'url'  => 'files/' . str_replace('_', '-', $fileKey),
                    'icon' => 'fas fa-fw fa-file',
                    "href" => url('') . "/files/" . str_replace('_', '-', $fileKey),
                    "search" => false,
                    "active" => false,
                    "classes" => [],
                    "class" => "",
                    "top_nav_classes" => [],
                    "top_nav_class" => ""
                ];
            }

            foreach ($event->menu->menu as &$menuItem) {
                if ($menuItem['text'] === 'Imported Data') {
                    $menuItem['submenu'] = $submenuData;
                    break;
                }
            }
        });
    }
}
