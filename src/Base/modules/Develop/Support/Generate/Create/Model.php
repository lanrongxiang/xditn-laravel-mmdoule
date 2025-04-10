<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Develop\Support\Generate\Create;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use Illuminate\Support\Str;
use Xditn\MModule;

class Model extends Creator
{
    protected array $replace = [
        '{uses}',
        '{property}',
        '{namespace}',
        '{model}',
        '{traits}',
        '{table}',
        '{fillable}',
        '{searchable}',
        '{fieldsInList}',
        '{isPaginate}', '{form}', '{dateFormat}', '{timestamps}',
    ];

    protected array $structures;

    protected bool $softDelete;

    protected bool $timestamps;

    public function __construct(
        protected string          $modelName,
        protected readonly string $tableName,
        protected readonly bool   $isPaginate
    )
    {
        $model = new class() extends EloquentModel {
            use SoftDeletes;
        };

        $this->softDelete = in_array($model->getDeletedAtColumn(), SchemaFacade::getColumnListing($this->tableName));

        $this->timestamps = in_array($model->getCreatedAtColumn(), SchemaFacade::getColumnListing($this->tableName))
            && in_array($model->getUpdatedAtColumn(), SchemaFacade::getColumnListing($this->tableName));
    }

    /**
     * get file
     */
    public function getFile(): string
    {
        // TODO: Implement getFile() method.
        return MModule::getModuleModelPath($this->module) . $this->getModelName() . $this->ext;
    }

    /**
     * get content
     */
    public function getContent(): string
    {
        $modelStub = File::get($this->getModelStub());
        //
        return Str::of($modelStub)->replace($this->replace, [
            $this->getUses(),
            $this->getProperties(),
            $this->getModelNamespace(),
            $this->getModelName(),
            $this->getTraits(),
            $this->tableName,
            $this->getFillable(),
            $this->getSearchable(),
            $this->getFieldsInList(),
            $this->isPaginate(),
            $this->getInForm(),
            $this->softDelete ? '' : $this->getDateFormat(), // 如果不是软删除，生成 Model 要自动写入 unix 时间戳
            $this->timestamps ? '' : $this->getTimestamps(), // 自动时间戳
        ])->toString();
    }

    /**
     * get model namespace
     */
    public function getModelNamespace(): string
    {
        return Str::of(MModule::getModuleModelNamespace($this->module))->trim('\\')->append(';')->toString();
    }

    /**
     * get model name
     */
    public function getModelName(): string
    {
        $modelName = Str::of($this->modelName);

        if (!$modelName->length()) {
            $modelName = Str::of($this->tableName)->camel();
        }

        return $modelName->ucfirst()->toString();
    }

    /**
     * get uses
     */
    protected function getUses(): string
    {
        if ($this->softDelete) {
            return  Str::of('')
                       ->append('use Illuminate\Database\Eloquent\Model;')
                       ->newLine()
                       ->append('use Xditn\Traits\DB\SoftDeletesTrait;')
                       ->toString();
        }
        return 'use Xditn\Base\XditnModel as Model;';
    }

    /**
     * get traits
     */
    protected function getTraits(): string
    {
        return Str::of('')
                  ->when($this->softDelete, function ($str) {
                      return $str->append('use SoftDeletesTrait;')
                          ;
                  })
                  ->toString()
            ;
    }

    protected function getProperties(): string
    {
        $comment = Str::of('/**')->newLine();

        foreach ($this->getTableColumns() as $column) {
            $comment = $comment->append(sprintf(' * @property $%s', $column))->newLine();
        }

        return $comment->append('*/')->toString();
    }

    /**
     * get fillable
     */
    protected function getFillable(): string
    {
        $fillable = Str::of('');

        foreach ($this->getTableColumns() as $column) {
            $fillable = $fillable->append(" '{$column}'")->append(',');
        }

        return $fillable->rtrim(',')->toString();
    }

    protected function getTableColumns(): array
    {
        return getTableColumns($this->tableName);
    }

    /**
     * get field in list
     */
    protected function getFieldsInList(): string
    {
        $str = Str::of('');
        foreach ($this->structures as $structure) {
            if ($structure['list']) {
                $str = $str->append("'{$structure['field']}'")->append(',');
            }
        }

        return $str->rtrim(',')->toString();
    }

    /**
     * get field in list
     */
    protected function getInForm(): string
    {
        $str = Str::of('');
        foreach ($this->structures as $structure) {
            if ($structure['form']) {
                $str = $str->append("'{$structure['field']}'")->append(',');
            }
        }

        return $str->rtrim(',')->toString();
    }

    /**
     * searchable
     */
    protected function getSearchable(): string
    {
        $searchable = Str::of('');

        foreach ($this->structures as $structure) {
            if ($structure['search'] && $structure['field'] && $structure['search_op']) {
                $searchable = $searchable->append(sprintf("'%s' => '%s'", $structure['field'], $structure['search_op']))->append(',')->newLine();
            }
        }

        return $searchable->toString();
    }

    protected function isPaginate(): string
    {
        return $this->isPaginate ? '' : Str::of('protected bool $isPaginate = false;')->toString();
    }

    /**
     * @return $this
     */
    public function setStructures(array $structures): static
    {
        $this->structures = $structures;

        return $this;
    }

    /**
     * get stub
     */
    protected function getModelStub(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'model.stub';
    }

    protected function getDateFormat(): string
    {
        return "protected \$dateFormat = 'U';";
    }

    protected function getTimestamps(): string
    {
        return 'public $timestamps = false;';
    }
}
