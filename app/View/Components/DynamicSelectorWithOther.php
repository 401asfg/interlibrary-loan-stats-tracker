<?php

/*
 * Author: Michael Allan
 */

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use App\View\Components\DynamicSelector;

class DynamicSelectorWithOther extends DynamicSelector
{
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.dynamic-selector-with-other');
    }
}
