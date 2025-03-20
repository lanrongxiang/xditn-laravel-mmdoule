<?php

namespace Xditn\Base\modules\Wechat\Enums;

enum WechatOfficialMenuType: string
{
    case CLICK = 'click'; // 匹配规则
    case VIEW = 'view'; // 跳转网页
    case SCANCODE_PUSH = 'scancode_push'; // 扫码推事件
    case SCANCODE_WAITMSG = 'scancode_waitmsg'; // 扫码推事件且弹出“消息接收中”提示框
    case PIC_SYSPHOTO = 'pic_sysphoto'; // 弹出系统拍照发图
    case PIC_PHOTO_OR_ALBUM = 'pic_photo_or_album'; // 弹出拍照或者相册发图
    case PIC_WEIXIN = 'pic_weixin'; // 弹出微信相册发图
    case LOCATION_SELECT = 'location_select'; // 弹出地理位置选择
    case MINIPROGRAM = 'miniprogram'; // 小程序
    case MEDIA = 'media'; // 转客服

    public function value(): string
    {
        // TODO: Implement name() method.
        return match ($this) {
            self::CLICK => 'click',
            self::VIEW => 'view',
            self::SCANCODE_PUSH => 'scancode_push',
            self::SCANCODE_WAITMSG => 'scancode_waitmsg',
            self::PIC_SYSPHOTO => 'pic_sysphoto',
            self::PIC_PHOTO_OR_ALBUM => 'pic_photo_or_album',
            self::PIC_WEIXIN => 'pic_weixin',
            self::LOCATION_SELECT => 'location_select',
            self::MINIPROGRAM => 'miniprogram',
            self::MEDIA => 'media',
        };
    }

    public function name(): string
    {
        // TODO: Implement value() method.
        return match ($this) {
            self::CLICK => '点击',
            self::VIEW => '跳转网页',
            self::SCANCODE_PUSH => '扫码推事件',
            self::SCANCODE_WAITMSG => '扫码推事件且弹出“消息接收中”提示框',
            self::PIC_SYSPHOTO => '弹出系统拍照发图',
            self::PIC_PHOTO_OR_ALBUM => '弹出拍照或者相册发图',
            self::PIC_WEIXIN => '弹出微信相册发图',
            self::LOCATION_SELECT => '弹出地理位置选择器',
            self::MINIPROGRAM => '小程序',
            self::MEDIA => '媒体素材',
        };
    }

    public function assert(string $value): bool
    {
        return $this->value === $value;
    }
}
