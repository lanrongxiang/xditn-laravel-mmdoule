<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Cms\Http\Controllers;

use Illuminate\Http\Request;
use Xditn\Base\modules\Cms\Models\Option;
use Xditn\Base\XditnController as Controller;

/**
 * @group 内容管理
 *
 * @subgroup 内容设置
 *
 * @subgroupDescription MModule 后台内容管理->内容设置
 */
class SettingController extends Controller
{
    /**
     * 获取设置
     *
     * @queryParam key string 设置名称
     *
     * @responseField data object 设置内容
     * @responseField data.is_simple_url int 是否开启简短链接:1 开启 2 关闭
     * @responseField data.site_comment_avatar string 评论的头像: 默认identicon
     * @responseField data.site_comment_avatar_proxy string 评论头像代理，默认https://gravatar.loli.net
     * @responseField data.site_comment_check boolean 是否开启评论审核:1 开启 2 关闭
     * @responseField data.site_comment_limit string 是否开启评论验证码:1 开启 2 关闭
     * @responseField data.site_comment_need_email string 评论是否需要邮箱
     * @responseField data.site_comment_order_desc string 评论倒叙配列
     * @responseField data.site_comment_per_page string 评论每页显示数量
     * @responseField data.site_date_format string 站点日期格式
     * @responseField data.site_logo string 站点 logo 图
     * @responseField data.site_time_format string 站点时间格式
     * @responseField data.site_title string 站点标题
     * @responseField data.site_url_struct string 是否开启评论举报:1 开启 2 关闭
     *
     * @param  string  $key
     * @return array|int|mixed|string
     */
    public function index(string $key = '*')
    {
        return Option::getValues($key);
    }

    /**
     * 保存配置
     *
     * @param  Request  $request
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function store(Request $request)
    {
        $optionKeys = Option::pluck('key');

        foreach ($request->all() as $key => $value) {
            if ($optionKeys->contains($key)) {
                Option::where('key', $key)->update([
                    'value' => $value,
                    'creator_id' => $this->getLoginUserId(),
                ]);
            } else {
                app(Option::class)->storeBy([
                    'key' => $key,
                    'value' => $value,
                    'creator_id' => $this->getLoginUserId(),
                ]);
            }
        }
    }
}
