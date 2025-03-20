<?php

declare(strict_types=1);

namespace Xditn\Base\modules\System\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Xditn\Base\modules\System\Models\PersonalAccessTokens;
use Xditn\Base\modules\User\Models\User;
use Xditn\Base\XditnController as Controller;

class PersonalAccessTokensController extends Controller
{
    public function __construct(
        protected readonly PersonalAccessTokens $model
    )
    {
    }

    public function index(): mixed
    {
        return $this->model->setBeforeGetList(function ($query) {
            return $query->addSelect([
                                         'username' => User::whereColumn('id', $this->model->getTable() . '.tokenable_id')
                                             ->select(DB::raw('username')),
                                     ])
                ->join('log_login', 'log_login.token_id', $this->model->getTable() . '.id')
                ->addSelect('log_login.login_ip', 'log_login.location')
            ;
        })->getList();
    }

    /**
     * @return mixed
     */
    public function destroy($id)
    {
        return $this->model->deletesBy($id);
    }
}
