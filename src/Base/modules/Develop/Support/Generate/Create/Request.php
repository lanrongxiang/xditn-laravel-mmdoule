<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Develop\Support\Generate\Create;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Xditn\MModule;

class Request extends Creator
{
    protected array $structures;

    /**
     * @var array|string[]
     */
    protected array $replace = ['{namespace}', '{request}', '{rule}'];

    public function __construct(public readonly string $controller)
    {
    }

    public function getFile(): string
    {
        return MModule::getModuleRequestPath($this->module).$this->getRequestName().$this->ext;
    }

    /**
     * get content
     */
    public function getContent(): string|bool
    {
        $rule = $this->getRulesString();

        if (! $rule) {
            return false;
        }

        return Str::of(
            File::get(dirname(__DIR__).DIRECTORY_SEPARATOR.'stubs'.DIRECTORY_SEPARATOR.'request.stub')
        )->replace($this->replace, [$this->getNamespace(), $this->getRequestName(), $rule])->toString();
    }

    /**
     * get namespace
     */
    protected function getNamespace(): string
    {
        return Str::of(MModule::getModuleRequestNamespace($this->module))->rtrim('\\')->append(';')->toString();
    }

    /**
     * get request name
     *
     * @return ?string
     */
    public function getRequestName(): ?string
    {
        if ($this->getRules()) {
            return Str::of($this->controller)->remove('Controller')->append('Request')->ucfirst()->toString();
        }

        return null;
    }

    /**
     * get rule
     */
    public function getRulesString(): string|bool
    {
        $rules = $this->getRules();

        if (! count($rules)) {
            return false;
        }

        $rule = Str::of('');

        foreach ($rules as $field => $validates) {
            $rule = $rule->append("'{$field}'")
                ->append(' => ')
                ->append('\'')
                ->append(Arr::join($validates, '|'))
                ->append('\',')
                ->newLine();
        }

        return $rule->toString();
    }

    /**
     * get rules
     */
    protected function getRules(): array
    {
        $rules = [];

        foreach ($this->structures as $structure) {
            if ($structure['field'] && count($structure['validates'])) {
                $rules[$structure['field']] = $structure['validates'];
            }
        }

        return $rules;
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
}
