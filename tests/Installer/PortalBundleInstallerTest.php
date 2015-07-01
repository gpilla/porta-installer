<?php
namespace gpilla\PortalInstaller\Test\Installer;

use gpilla\PortalInstaller\Installer\PortalBundleInstaller;
use Composer\Composer;
use Composer\Config;
use Composer\Package\Package;
use Composer\Package\RootPackage;
use Composer\Repository\RepositoryManager;


class PortalBundleInstallerTest extends \PHPUnit_Framework_TestCase
{
    public $package;

    public $installer;
    /**
     * Directories used during tests
     *
     * @var string
     */
    protected $testDirs = ['', 'vendor', 'plugins'];

    public function setUp()
    {
        $this->package = new Package('Vendor\CamelCased', '1.0', '1.0');
        $this->package->setType('cakephp-plugin');

        $this->path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'portal-bundle-installer-test';

        foreach ($this->testDirs as $dir) {
            if (!is_dir($this->path . '/' . $dir)) {
                mkdir($this->path . '/' . $dir);
            }
        }

        $composer = new Composer();
        $config = $this->getMock('Composer\Config');
        $config->expects($this->any())
                ->method('get')
                ->will($this->returnValue($this->path . '/vendor'));

        $composer->setConfig($config);

        $this->io = $this->getMock('Composer\IO\IOInterface');

        $rm = new RepositoryManager(
            $this->io,
            $config
        );

        $composer->setRepositoryManager($rm);

        $this->installer = new PortalBundleInstaller($this->io, $composer);
    }

    public function testInstallPath()
    {
        $this->assertEquals('src/Vendor/CamelCased', $this->installer->getInstallPath($this->package));
    }

    public function testSupports()
    {
        $this->assertTrue($this->installer->supports('portal-bundle'));
        $this->assertFalse($this->installer->supports(md5(rand(0,100))));
        $this->assertFalse($this->installer->supports('portal-'.md5(rand(0,100))));

//        var_dump($this->installer->determineBundles('temp/src'));
        $this->installer->updatePortalAutoloadConfig('temp/src');
    }


}
