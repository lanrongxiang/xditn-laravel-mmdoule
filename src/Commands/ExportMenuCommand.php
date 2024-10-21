<?php

namespace Xditn\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Xditn\Support\Tree;
use Xditn\MModule;

class ExportMenuCommand extends XditnCommand
{
    protected $signature = 'xditn:export:menu {module} {table=permissions} {--p}';

    protected $description = '导出表数据';

    /**
     * 执行命令的主逻辑
     *
     * @return void
     */
    public function handle(): void
    {
        $module = $this->argument('module');
        $table = $this->argument('table');
        $processTree = $this->option('p');

        $data = $this->fetchData($table, $module);

        if ($processTree) {
            $data = Tree::done($data);
        }

        $this->exportData($data, $module, $table);
        $this->info('导出成功');
    }

    /**
     * 从数据库中获取数据
     *
     * @param  string  $table
     * @param  string|null  $module
     * @return array
     */
    protected function fetchData(string $table, ?string $module): array
    {
        $query = DB::table($table)->where('deleted_at', 0);
        if ($module) {
            $query->where('module', $module);
        }

        return json_decode($query->get()->toJson(), true);
    }

    /**
     * 导出数据到文件或生成 Seeder 类
     *
     * @param  array  $data
     * @param  string|null  $module
     * @param  string  $table
     * @return void
     */
    protected function exportData(array $data, ?string $module, string $table): void
    {
        $dataExport = 'return '.var_export($data, true).';';

        if ($module) {
            $this->exportSeed($dataExport, $module);
        } else {
            file_put_contents(base_path().DIRECTORY_SEPARATOR.$table.'.php', "<?php\r\n".$dataExport);
        }
    }

    /**
     * 导出 Seeder 文件
     *
     * @param  string  $data
     * @param  string  $module
     * @return void
     */
    protected function exportSeed(string $data, string $module): void
    {
        $stub = File::get(__DIR__.'/stubs/menuSeeder.stub');
        $className = ucfirst($module).'MenusSeeder';

        $stub = str_replace('{CLASS}', $className, $stub);
        File::put(MModule::getModuleSeederPath($module).$className.'.php', str_replace('{menus}', $data, $stub));
    }
}
