<?php

namespace Xditn\Base\modules\Common\Repository\Options;

use Xditn\Base\modules\Wechat\Enums\WechatOfficialMenuType as Enum;

class WechatOfficialMenuType implements OptionInterface
{
    public function get(): array
    {
        return [
            [
                'label' => Enum::CLICK->name(),
                'value' => Enum::CLICK->value(),
            ],
            [
                'label' => Enum::VIEW->name(),
                'value' => Enum::VIEW->value(),
            ],
            [
                'label' => Enum::MINIPROGRAM->name(),
                'value' => Enum::MINIPROGRAM->value(),
            ],
            [
                'label' => Enum::MEDIA->name(),
                'value' => Enum::MEDIA->value(),
            ],
            [
                'label' => Enum::SCANCODE_PUSH->name(),
                'value' => Enum::SCANCODE_PUSH->value(),
            ],
            [
                'label' => Enum::SCANCODE_WAITMSG->name(),
                'value' => Enum::SCANCODE_WAITMSG->value(),
            ],
            [
                'label' => Enum::PIC_SYSPHOTO->name(),
                'value' => Enum::PIC_SYSPHOTO->value(),
            ],
            [
                'label' => Enum::PIC_PHOTO_OR_ALBUM->name(),
                'value' => Enum::PIC_PHOTO_OR_ALBUM->value(),
            ],
            [
                'label' => Enum::PIC_WEIXIN->name(),
                'value' => Enum::PIC_WEIXIN->value(),
            ],
            [
                'label' => Enum::LOCATION_SELECT->name(),
                'value' => Enum::LOCATION_SELECT->value(),
            ],
        ];
    }
}
