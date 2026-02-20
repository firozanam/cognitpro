<?php

return [
    App\Providers\AppServiceProvider::class,
    
    // Module Service Providers
    App\Modules\Auth\Providers\AuthModuleServiceProvider::class,
    App\Modules\Core\Providers\CoreModuleServiceProvider::class,
    App\Modules\User\Providers\UserModuleServiceProvider::class,
    App\Modules\Prompt\Providers\PromptModuleServiceProvider::class,
];
