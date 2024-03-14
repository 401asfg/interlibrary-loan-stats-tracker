<?php

/*
 * Author: Michael Allan
 */

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DynamicSelector extends Component
{
    public $set;
    public $setName;

    /**
     * Create a new component instance.
     */
    public function __construct($set, $setName)
    {
        $this->set = $set;
        $this->setName = $setName;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.dynamic-selector');
    }
}
