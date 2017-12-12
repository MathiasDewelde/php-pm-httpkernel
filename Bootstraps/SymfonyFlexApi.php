<?php

namespace PHPPM\Bootstraps;

use Symfony\Component\HttpKernel\KernelInterface;

/**
 * SymfonyFlexApi
 *
 * Custom Symfony Flex bootstrapper that does nothing for sessions because they are not needed
 * for API only kernels.
 */
class SymfonyFlexApi extends AbstractSymfony
{
    /**
     * Create a Symfony application
     *
     * @return KernelInterface
     */
    public function getApplication()
    {
        $app = $this->createKernelInstance();

        $this->initializeKernel($app);
        $this->bootKernel($app);

        return $app;
    }
}
