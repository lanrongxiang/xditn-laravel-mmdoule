<?php

namespace Xditn\Base\modules\Cms\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component as BaseComponent;

/**
 * 导航栏组件
 */
class Component extends BaseComponent
{
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('cms::'.$this->theme().'.components.'.$this->getComponentName());
    }

    /**
     * @return string
     */
    protected function theme(): string
    {
        return 'default';
    }

    /**
     * @return string
     */
    protected function getComponentName(): string
    {
        return Str::of(strtolower(class_basename(static::class)))->snake();
    }
}
