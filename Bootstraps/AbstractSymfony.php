<?php
namespace PHPPM\Bootstraps;

use PHPPM\Bootstraps\Symfony as BaseSymfony;
use PHPPM\Utils;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * SymfonyFlex
 *
 * Custom bootstrapper to handle the changes introduced in Symfony Flex.
 * Attempts to locate the kernel in the default src/ folder.
 */
class AbstractSymfony extends BaseSymfony
{
    /**
     * @return string
     */
    public function getStaticDirectory()
    {
        return 'public/';
    }
    /**
     * @return KernelInterface
     */
    protected function createKernelInstance()
    {
        // include applications autoload
        require './vendor/autoload.php';
        // attempt to preload the environment vars
        $this->loadEnvironmentVariables();
        // locate and attempt to boot the kernel in the current project folder
        $kernel = $this->locateApplicationKernel();
        return new $kernel($this->appenv, $this->debug);
    }
    /**
     * @param KernelInterface $app
     */
    protected function initializeKernel($app)
    {
        // we need to change some services, before the boot, because they would otherwise
        // be instantiated and passed to other classes which makes it impossible to replace them.
        Utils::bindAndCall(function () use ($app) {
            $app->initializeBundles();
            $app->initializeContainer();
        }, $app);
    }
    /**
     * @param KernelInterface $app
     */
    protected function bootKernel($app)
    {
        Utils::bindAndCall(function () use ($app) {
            foreach ($app->getBundles() as $bundle) {
                $bundle->setContainer($app->container);
                $bundle->boot();
            }
            $app->booted = true;
        }, $app);
    }
    /**
     * Attempt to load the env vars from .env, only if Dotenv exists
     */
    protected function loadEnvironmentVariables()
    {
        if (!getenv('APP_ENV') && class_exists('Symfony\Component\Dotenv\Dotenv')) {
            (new \Symfony\Component\Dotenv\Dotenv())->load(realpath('./.env'));
        }
    }
    /**
     * Based on getNamespace from Illuminate\Foundation\Application
     *
     * @return string
     */
    protected function locateApplicationKernel()
    {
        $composer = json_decode(file_get_contents(realpath('./composer.json')), true);
        if (!isset($composer['autoload']['psr-4'])) {
            return 'AppKernel';
        }
        foreach ((array) $composer['autoload']['psr-4'] as $namespace => $path) {
            foreach ((array) $path as $pathChoice) {
                if (realpath('./src/') == realpath('./'.$pathChoice)) {
                    return $namespace . 'Kernel';
                }
            }
        }
        return 'AppKernel';
    }
}
