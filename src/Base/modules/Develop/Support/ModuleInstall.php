<?php
namespace Modules\Develop\Support;


use Illuminate\Support\Facades\File;
use Xditn\Exceptions\FailedException;
use Xditn\Facade\Zipper;
use Xditn\MModule;

/**
 * module install
 */
class ModuleInstall
{
    public const int NORMAL_INSTALL = 1;
    public const int ZIP_INSTALL    = 2;

    public function __construct(protected readonly int|string $type){}

    /**
     *
     * @param array $params
     */
    public function install(array $params): void
    {
        try {
            if ($this->type === self::NORMAL_INSTALL) {
                $this->installWithTitle($params['title']);
            }

            if ($this->type == self::ZIP_INSTALL) {
                $this->installWithZip($params['title'], $params['file']);
            }
        } catch (\Exception $e) {
            if ($this->type == self::ZIP_INSTALL) {
                MModule::deleteModulePath($params['title']);
            }

            throw new FailedException('安装失败: ' . $e->getMessage());
        }
    }

    /**
     *
     * @param string $title
     */
    protected function installWithTitle(string $title): void
    {
        try {
            $installer = MModule::getModuleInstaller($title);

            $installer->install();
        } catch (\Exception|\Throwable $e) {
            // XditnLaravel::deleteModulePath($title);

            throw new FailedException('安装失败: ' . $e->getMessage());
        }
    }

    /**
     * get
     *
     * @param string $title
     * @param string $zip
     */
    protected function installWithZip(string $title, string $zip): void
    {
        $zipRepository = Zipper::make($zip)->getRepository();

        $zipRepository->getArchive()->extractTo(MModule::getModulePath($title));

        $this->installWithTitle($title);

        File::delete($zip);
    }
}