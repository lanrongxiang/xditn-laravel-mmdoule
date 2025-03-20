<?php

namespace Xditn\Base\modules\Develop\Support\Generate\Create;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Xditn\MModule;

class Enumer extends Creator
{
    protected array $replace = [
        '{ENUM_CLASS}', '{TYPE}', '{CASE}', '{VALUE}', '{NAME}',
    ];

    /**
     * @param  string  $enumClass
     * @param  array<array<label, value>>  $values
     */
    public function __construct(protected string $enumClass, protected array $values)
    {

    }

    public function getFile(): string
    {
        $enmDir = MModule::makeDir(
            MModule::getModulePath('Common').DIRECTORY_SEPARATOR.'Enums'
        );

        return $enmDir.DIRECTORY_SEPARATOR.$this->enumClass.'.php';
    }

    /**
     * @return array
     */
    protected function getReplaces(): array
    {
        $case = Str::of('');
        $values = Str::of('');
        $name = Str::of('');
        $type = 'string';

        foreach ($this->values as $value) {
            $k = Str::of($value['key'])->snake()->upper()->toString();
            $v = is_numeric($value['value']) ? intval($value['value']) : $value['value'];
            if (is_numeric($v)) {
                $type = 'int';
                $case = $case->append("case {$k} = {$v};\n");
                $values = $values->append("self::{$k} => {$v},\n");
            } else {
                $case = $case->append("case {$k} = '{$v}';\n");
                $values = $values->append("self::{$k} => '{$v}',\n");
            }

            $name = $name->append("self::{$k} => '{$value['label']}',\n");
        }

        return [
            $this->enumClass,
            $type,
            $case->trim("\n")->toString(),
            $values->trim("\n")->toString(),
            $name->trim("\n")->toString(),
        ];
    }

    /**
     * 是否不同
     *
     * @return bool
     */
    protected function isDifferent(): bool
    {
        // 默认命名空间
        $enum = '\Xditn\Base\modules\Common\Enums\\'.$this->enumClass;
        if (class_exists($enum)) {
            $enum = new \ReflectionClass($enum);
            $constants = [];
            foreach ($enum->getConstants() as $constant) {
                $constants[$constant->name] = $constant->value;
            }

            $newValue = [];
            foreach ($this->values as $value) {
                $v = is_numeric($value['value']) ? intval($value['value']) : $value['value'];
                $newValue[Str::of($value['key'])->snake()->upper()->toString()] = $v;
            }

            return $newValue != $constants;
        } else {
            return true;
        }
    }

    /**
     * @return string|bool
     */
    public function getContent(): string|bool
    {
        if (! $this->isDifferent()) {
            return false;
        }

        // TODO: Implement getContent() method.
        return Str::of(File::get($this->getEnumStub()))->replace($this->replace, $this->getReplaces())->toString();
    }

    /**
     * @return string
     */
    protected function getEnumStub(): string
    {
        return dirname(__DIR__).DIRECTORY_SEPARATOR
            .'stubs'
            .DIRECTORY_SEPARATOR
            .'enum.stub';
    }
}
