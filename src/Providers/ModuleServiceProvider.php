<?php

namespace Upsoftware\Auth\Providers;

use Upsoftware\Auth\Http\Requests\LoginUser;
use Upsoftware\Auth\Http\Requests\LoginUserOtp;
use Upsoftware\Auth\Http\Requests\RegisterUser;
use Upsoftware\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider {
    protected $models = [

    ];

    protected $requests = [
        LoginUser::class,
        LoginUserOtp::class,
        RegisterUser::class
    ];
}
