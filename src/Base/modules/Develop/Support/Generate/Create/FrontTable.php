<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Develop\Support\Generate\Create;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Xditn\MModule;

class FrontTable extends Creator
{
    protected string $columns = '{columns}';

    protected string $search = '{search}';

    protected string $api = '{api}';

    protected string $createForm = '{createForm}';

    protected string $tree = '{row-key}';

    protected string $paginate = '{paginate}';

    protected array $structures;

    public function __construct(
        protected readonly string $controller,
        protected readonly bool $hasPaginate,
        protected readonly string $apiString,
        protected readonly bool $needForm
    ) {
    }

    /**
     * get content
     */
    public function getContent(): string
    {
        // TODO: Implement getContent() method.
        return Str::of(File::get($this->getTableStub()))->replace([
            $this->columns, $this->search, $this->api, $this->tree, $this->paginate,
            $this->createForm,
        ], [
            $this->getTableContent(),
            $this->getSearchContent(),
            $this->apiString,
            $this->isTree() ? 'row-key="id"' : '',
            $this->isTree() ? ':paginate="false"' : ($this->paginate ? '' : ':paginate="false"'),
            $this->getCreateForm(),
        ])->toString();
    }

    /**
     * get file
     */
    public function getFile(): string
    {
        $path = config('xditn.views_path').lcfirst($this->module).DIRECTORY_SEPARATOR;

        return MModule::makeDir($path.Str::of($this->controller)->replace('Controller', '')->lcfirst()).DIRECTORY_SEPARATOR.'index.vue';
    }

    /**
     * get search content
     */
    protected function getSearchContent(): string
    {
        $search = Str::of('[')->append(PHP_EOL);
        foreach ($this->structures as $structure) {
            if ($structure['search']) {
                $search = $search->append("\t{".PHP_EOL)->append("\t")
                    ->append("\ttype: '{$structure['form_component']}'")->append(','.PHP_EOL)->append("\t")
                    ->append("\tname: '{$structure['field']}'")->append(','.PHP_EOL)->append("\t")
                    ->append("\tlabel: '{$structure['label']}'")->append(','.PHP_EOL)->append("\t")
                    ->append('},')->append(PHP_EOL);
            }
        }

        return $search->trim(',')->append(']')->toString();
    }

    /**
     * get list content;
     */
    protected function getTableContent(): string
    {
        $columns = Str::of('[')->append(PHP_EOL);

        foreach ($this->structures as $structure) {
            $columns = $columns->append("\t{".PHP_EOL)->append("\t")
                ->append("\tprop: '{$structure['field']}'")->append(','.PHP_EOL)->append("\t")
                ->append("\tlabel: '{$structure['label']}'")->append(','.PHP_EOL)->append("\t")
                ->append('},')->append(PHP_EOL);
        }

        $columns = $columns->append("\t{".PHP_EOL)->append("\t")
            ->append("\ttype: 'operate'")->append(','.PHP_EOL)->append("\t")
            ->append("\tlabel: '操作'")->append(','.PHP_EOL)->append("\t")
            ->append('},')->append(PHP_EOL);

        return $columns->trim(',')->append(']')->toString();
    }

    /**
     * get table stub
     */
    protected function getTableStub(): string
    {
        return dirname(__DIR__).DIRECTORY_SEPARATOR.'stubs'

            .DIRECTORY_SEPARATOR.'vue'.DIRECTORY_SEPARATOR.'table.stub';
    }

    /**
     * get tree props
     */
    public function isTree(): bool
    {
        return in_array('parent_id', array_column($this->structures, 'field'));
    }

    /**
     * set structures
     *
     * @return $this
     */
    public function setStructures(array $structures): static
    {
        $this->structures = $structures;

        return $this;
    }

    /**
     * get create form
     */
    protected function getCreateForm(): string
    {
        return $this->needForm ? "import Create from './form/create.vue'" : '';
    }
}
