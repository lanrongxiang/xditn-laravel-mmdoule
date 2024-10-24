<?php

namespace Xditn\Modules\Develop\Http\Controllers;

use Xditn\Base\XditnController as Controller;
use Exception;
use Illuminate\Http\Request;
use Xditn\Modules\Develop\Support\Generate\Generator;

class GenerateController extends Controller
{
    /**
     * @param Request $request
     * @param Generator $generator
     * @throws Exception
     */
    public function index(Request $request, Generator $generator)
    {
        $generator->setParams($request->all())->generate();
    }
}
