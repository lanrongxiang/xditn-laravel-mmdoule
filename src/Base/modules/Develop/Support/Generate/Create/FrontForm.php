<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Develop\Support\Generate\Create;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Xditn\Base\modules\System\Models\DictionaryValues;
use Xditn\MModule;

class FrontForm extends Creator
{
    protected string $label = '{label}';

    protected string $prop = '{prop}';

    protected string $modelValue = '{model-value}';

    protected string $table = '{table}';

    protected string $search = '{search}';

    protected string $api = '{api}';

    protected string $options = '{options}';

    protected string $formItems = '{formItems}';

    protected string $paginate = '{paginate}';

    protected string $useList = '{useList}';

    protected array $structures;

    public function __construct(protected readonly string $controller)
    {
    }

    /**
     * get content
     */
    public function getContent(): string
    {
        // TODO: Implement getContent() method.
        return Str::of(File::get($this->getFormStub()))->replace($this->formItems, $this->getFormContent())->toString();
    }

    /**
     * get file
     */
    public function getFile(): string
    {
        $path = config('xditn.views_path').lcfirst($this->module).DIRECTORY_SEPARATOR;

        // TODO: Implement getFile() method.
        return MModule::makeDir($path.Str::of($this->controller)->replace('Controller', '')->lcfirst().DIRECTORY_SEPARATOR.'form').DIRECTORY_SEPARATOR.'create.vue';
    }

    /**
     * get form content
     */
    protected function getFormContent(): string
    {
        $form = Str::of('');

        $formComponents = $this->formComponents();

        foreach ($this->structures as $structure) {
            if ($structure['label'] && $structure['form_component'] && $structure['form']) {
                if (isset($formComponents[$structure['form_component']])) {
                    $dictionaryId = $structure['dictionary'] ?? null;
                    // 如果存在字典ID
                    if ($dictionaryId) {
                        $structure['options'] = $this->getDictionaryValues($dictionaryId);
                    }

                    $form = $form->append(
                        Str::of($formComponents[$structure['form_component']])
                            ->replace(
                                [$this->label, $this->prop, $this->modelValue],
                                [$structure['label'], $structure['field'], sprintf('formData.%s', $structure['field'])]
                            )
                            ->when(isset($structure['options']), function ($content) use ($structure) {
                                return $content->replace($this->options, $this->parseOptions2JsObject($structure['options']));
                            })
                            // switch 组件
                            ->when($structure['form_component'] == 'switch' && isset($structure['options']), function ($content) use ($structure) {
                                $active = $structure['options'][0]['value'];
                                $inactive = $structure['options'][1]['value'];

                                return $content->replace(['{active}', '{inactive}'], [
                                    ! is_numeric($active) ? sprintf("'%s'", $active) : $active,
                                    ! is_numeric($inactive) ? sprintf("'%s'", $inactive) : $inactive,
                                ]);
                            })
                            // 规则
                            ->when(count($structure['validates']), function ($content) use ($structure) {
                                return $content->replace('{rule}', $this->rules($structure['label'], $structure['validates']));
                            }, function ($content) {
                                return $content->replace('{rule}', '');
                            })
                    );
                }
            }
        }

        return $form->trim(PHP_EOL)->toString();
    }

    /**
     * form components
     */
    protected function formComponents(): array
    {
        $components = [];

        foreach (File::glob(
            $this->getFormItemStub()
        ) as $stub) {
            $components[File::name($stub)] = File::get($stub);
        }

        return $components;
    }

    /**
     * get formItem stub
     */
    protected function getFormItemStub(): string
    {
        return dirname(__DIR__).DIRECTORY_SEPARATOR.'stubs'

            .DIRECTORY_SEPARATOR.'vue'.DIRECTORY_SEPARATOR

            .'formItems'.DIRECTORY_SEPARATOR.'*.stub';
    }

    /**
     * get form stub
     */
    public function getFormStub(): string
    {
        return dirname(__DIR__).DIRECTORY_SEPARATOR.'stubs'

            .DIRECTORY_SEPARATOR.'vue'.DIRECTORY_SEPARATOR.'form.stub';
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
     * array to jsObject
     *
     * @param  array  $options
     * @return string
     */
    protected function parseOptions2JsObject(array $options): string
    {
        $jsObject = Str::of('[');

        foreach ($options as $option) {
            $jsObject = $jsObject->append('{');

            if (is_numeric($option['value'])) {
                $jsObject = $jsObject->append(sprintf('label:\'%s\',value: %s,', $option['label'], intval($option['value'])));
            } else {
                $jsObject = $jsObject->append(sprintf('label:\'%s\',value:\'%s\',', $option['label'], intval($option['value'])));
            }

            $jsObject = $jsObject->trim(',')->append('},');
        }

        return $jsObject->trim(',')->append(']')->toString();
    }

    /**
     * @param  string  $label
     * @param  array  $validates
     * @return string
     */
    protected function rules(string $label, array $validates): string
    {
        $rules = [
            'string' => '必须是字符串类型',
            'number' => '必须是数字类型',
            'url' => 'URL 格式不正确',
            'email' => '邮箱格式不正确',
            'boolean' => '必须是布尔类型',
            'date' => '日期格式不正确',
            'required' => sprintf('%s字段必须填写', $label),
        ];

        $required = '{ required: true, message: \'%s\' },';

        $type = '{ type: \'%s\', message: \'%s\' },';

        $formRules = Str::of(':rules="[');

        foreach ($validates as $validate) {
            if ($validate === 'numeric') {
                $validate = 'number';
            }

            if ($validate == 'required') {
                $formRules = $formRules->append(sprintf($required, $validate));
            } else {
                if(in_array($validate, array_keys($rules))) {
                    $formRules = $formRules->append(sprintf($type, $validate, $rules[$validate]));
                }
            }
        }

        return $formRules->trim(',')->append(']"')->toString();
    }

    /**
     * @param $id
     * @return array
     */
    protected function getDictionaryValues($id): array
    {
        return DictionaryValues::getEnabledValues($id)->toArray();
    }
}
