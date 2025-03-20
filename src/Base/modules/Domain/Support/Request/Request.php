<?php

namespace Xditn\Base\modules\Domain\Support\Request;

interface Request
{
    public function whois(string $domain);

    public function getList(string $domain, $offset, $limit);

    public function store(array $data);

    public function show($recordId, string $domain);

    public function update($id, array $data);

    public function destroy(string $recordId, string $domain);
}
