<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset\Enums;

enum FrontendStack: string
{
    case Blade = 'blade';
    case Livewire = 'livewire';
    case InertiaVue = 'inertia-vue';
    case InertiaReact = 'inertia-react';
}
