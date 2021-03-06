<?php

namespace Fraym\Registry;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class ExtensionInstallerPlugin implements PluginInterface
{
    /**
     * @param Composer $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $installer = new \Fraym\Registry\ExtensionInstaller($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }
}
