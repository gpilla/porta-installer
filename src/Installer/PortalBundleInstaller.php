<?php

namespace gpilla\PortalInstaller\Installer;

use Composer\Composer;
use Composer\Installer\LibraryInstaller;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Script\Event;
use Composer\Util\Filesystem;
use RuntimeException;

class PortalBundleInstaller extends LibraryInstaller
{
    const PATH_BUNDLES = 'bundles';

    /**
     * Decides if the installer supports the given type.
     *
     * This installer only supports package of type 'portal-bundle'.
     *
     * @return bool
     */
    public function supports($packageType)
    {
        return 'portal-bundle' === $packageType;
    }

    /**
     * {@inheritDoc}
     */
    public function getPackageBasePath(PackageInterface $package)
    {
        $path = str_replace('\\','/',$package->getPrettyName());
        return self::PATH_BUNDLES.'/'.$path;
    }

    /**
     * {@inheritDoc}
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::install($repo, $package);
        $this->updatePortalAutoloadConfig();
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::uninstall($repo, $package);
        $this->updatePortalAutoloadConfig();
    }


    public function determineBundles($path = null)
    {
        $bundles = array();
        $path = (!is_null($path)) ? $path : self::PATH_BUNDLES;
        $vendor_dirs = new \DirectoryIterator($path);
        foreach ($vendor_dirs as $vendor_dir){
            if (!$vendor_dir->isDir() || $vendor_dir->isDot()) {
                continue;
            }
            $vendor = $vendor_dir->getFileName();
            $package_dirs = new \DirectoryIterator($path.'/'.$vendor);
            foreach ($package_dirs as $package_dir) {
                if (!$package_dir->isDir() || $package_dir->isDot()) {
                    continue;
                }
                $package = $package_dir->getFileName();

                // TODO: Agregar mas validaciones
                $composerJsonFile = $package_dir->getPath().'/composer.json';
                if (file_exists($composerJsonFile)) {
                    $json = file_get_contents($composerJsonFile);
                    $data = $json_decode($json);
                    if (isset($data['extra']['class'])) {
                        // TODO: Agregar Exception
                        $bundles["$vendor\\$package"] = $data['extra']['class'];
                    }
                }


            }
        }
        return $bundles;
    }

    public function updatePortalAutoloadConfig($path = null)
    {
        $bundles = $this->determineBundles($path);
        file_put_contents('vendor/portal-autoload.php', "<?php \n\n" );
        file_put_contents('vendor/portal-autoload.php', "//Autoload de Bundles del Portal \n\n", FILE_APPEND );
        foreach ($bundles as $class) {
            $data = '$bundles[] = '."new $class();\n";
            file_put_contents('vendor/portal-autoload.php', $data, FILE_APPEND);
        }
    }

}
