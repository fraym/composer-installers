<?php
namespace Fraym\Registry;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Installer\LibraryInstaller;

class ExtensionInstaller extends LibraryInstaller
{
    /**
     * @var array
     */
    private $symlinks = [];

    /**
     * Installs specific package.
     *
     * @param InstalledRepositoryInterface $repo repository in which to check
     * @param PackageInterface $package package instance
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::install($repo, $package);
        $path = $this->getInstallPath($package);
        $this->addExtension($path, $package);
    }

    /**
     * @param InstalledRepositoryInterface $repo
     * @param PackageInterface $initial
     * @param PackageInterface $target
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        parent::update($repo, $initial, $target);
        $path = $this->getInstallPath($initial);
        $this->removeExtension($path, $initial);
        $this->addExtension($path, $target);
    }

    /**
     * @param $path
     * @param PackageInterface $package
     */
    public function addExtension($path, PackageInterface $package) {
        if($package->getType() === 'fraym-core') {
            $this->createSymlinks($path . DIRECTORY_SEPARATOR . 'Fraym', 'Fraym');
        }
        $this->createSymlinks($path . DIRECTORY_SEPARATOR . 'Extension', 'Extension');
        $this->createSymlinks($path . DIRECTORY_SEPARATOR . 'Public', 'Public');
        $this->createSymlinks($path . DIRECTORY_SEPARATOR . 'Template', 'Template');
        $this->createSymlinks($path . DIRECTORY_SEPARATOR . 'Test', 'Test');
        $this->createSymlinks($path . DIRECTORY_SEPARATOR . 'Hook', 'Hook');
    }

    /**
     * @param $path
     * @param PackageInterface $package
     */
    public function removeExtension($path, PackageInterface $package) {
        if($package->getType() === 'fraym-core') {
            $this->removeSymlinks($path . DIRECTORY_SEPARATOR . 'Fraym', 'Fraym');
        }
        $this->removeSymlinks($path . DIRECTORY_SEPARATOR . 'Extension', 'Extension');
        $this->removeSymlinks($path . DIRECTORY_SEPARATOR . 'Public', 'Public');
        $this->removeSymlinks($path . DIRECTORY_SEPARATOR . 'Template', 'Template');
        $this->removeSymlinks($path . DIRECTORY_SEPARATOR . 'Test', 'Test');
        $this->removeSymlinks($path . DIRECTORY_SEPARATOR . 'Hook', 'Hook');
    }

    /**
     * Uninstalls specific package.
     *
     * @param InstalledRepositoryInterface $repo repository in which to check
     * @param PackageInterface $package package instance
     *
     * @throws \InvalidArgumentException if $initial package is not installed
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $path = $this->getInstallPath($package);
        $this->removeExtension($path, $package);
        parent::uninstall($repo, $package);
    }

    /**
     * @param $path
     * @param $to
     */
    public function createSymlinks($path, $to) {
        foreach($this->getSymlinks($path, $to) as $target => $symlink){
            $this->filesystem->relativeSymlink($target, $symlink);
        }
    }

    /**
     * @param $path
     * @param $to
     */
    public function removeSymlinks($path, $to) {
        foreach($this->getSymlinks($path, $to) as $target => $symlink){
            if(is_link($symlink)) {
                unlink($symlink);
            }
        }
    }

    /**
     * @param $path
     * @param $to
     * @return array
     */
    public function getSymlinks($path, $to) {
        foreach(glob($path . '/*') as $filePath) {
            $symlink = realpath($to).DIRECTORY_SEPARATOR.basename($filePath);
            if(is_dir($symlink) && !is_link($symlink)) {
                $this->getSymlinks($filePath, $symlink);
            } else {
                $this->symlinks[$filePath] = $symlink;
            }
        }
        return $this->symlinks;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return 'fraym-extension' === $packageType || 'fraym-core' === $packageType;
    }
}