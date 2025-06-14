<?php

namespace Xditn\Support\Excel;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\LazyCollection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Csv
{
    protected array $header = [];

    /**
     * csv 头信息
     *
     * @param array $header
     * @return $this
     */
    public function header(array $header): static
    {
        $this->header = $header;

        return $this;
    }

    /**
     * 下载
     *
     * @param string $filename
     * @param $data
     * @return StreamedResponse
     */
    public function download(string $filename,  $data): StreamedResponse
    {
        $responseHeader = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
        ];

        return Response::stream(function () use ($data){
           $output = fopen('php://output', 'w');
           fputcsv($output, $this->header);
           if ($data instanceof LazyCollection) {
               foreach ($data as $item) {
                   fputcsv($output, $item->toArray());
               }
           } else {
               fputcsv($output, $data);
           }
           fclose($output);
        }, 200, $responseHeader);
    }
}
