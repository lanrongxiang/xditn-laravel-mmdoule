<?php

namespace Xditn\Base\modules\Domain\Providers;

use Xditn\Providers\XditnModuleServiceProvider;

class DomainServiceProvider extends XditnModuleServiceProvider
{
    /**
     * route path
     *
     * @return string
     */
    public function moduleName(): string
    {
        // TODO: Implement path() method.
        return 'domain';
    }
}
