<?php

namespace Xditn\Base\modules\Common\Repository\Options;

class RecordTypes implements OptionInterface
{
    public function get(): array
    {
        $types = [];

        foreach ($this->types() as $key => $value) {
            $types[] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        return $types;
    }

    protected function types()
    {
        return [
            'A' => 'A记录-将域名指向IPV4地址',
            'CNAME' => 'CNAME记录-将域名指向另一个域名',
            'MX' => 'MX记录-将域名指向邮件服务器地址',
            'TXT' => 'TXT记录-文本长度限制512，通常做SPF记录（反垃圾邮件）',
            'NS' => 'NS记录-将子域名指定其他DNS服务器解析',
            'AAAA' => 'AAAA记录-将域名指向IPV6地址',
            'CAA' => 'CAA记录-CA证书颁发机构授权校验',
            'SRV' => 'SRV记录-记录提供特定的服务的服务器',
        ];
    }
}
