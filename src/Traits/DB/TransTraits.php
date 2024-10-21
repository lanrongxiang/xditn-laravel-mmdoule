<?php

namespace Xditn\Traits\DB;

use Illuminate\Support\Facades\DB;

trait TransTraits
{
    /**
     * 开启数据库事务
     *
     * 该方法使用 Laravel 提供的 DB::beginTransaction() 方法来开启一个新的事务。
     * 所有的数据库操作都将在事务中进行，直到手动提交或回滚。
     */
    public function beginTransaction(): void
    {
        DB::beginTransaction();
    }

    /**
     * 提交数据库事务
     *
     * 当所有的数据库操作都成功执行后，可以调用该方法提交事务，
     * 从而使所有的数据库操作生效。
     */
    public function commit(): void
    {
        DB::commit();
    }

    /**
     * 回滚数据库事务
     *
     * 如果数据库操作中出现错误或异常，调用该方法可以回滚事务，
     * 撤销自上次 `beginTransaction()` 以来的所有数据库操作。
     */
    public function rollback(): void
    {
        DB::rollBack();
    }

    /**
     * 使用闭包执行数据库事务
     *
     * 该方法接收一个闭包参数，并在事务中执行该闭包。Laravel 会自动处理事务的
     * 提交和回滚。如果闭包内的操作执行成功，事务将自动提交；如果发生异常，
     * 则自动回滚事务。
     *
     * @param  \Closure  $closure 执行数据库操作的闭包
     */
    public function transaction(\Closure $closure): void
    {
        DB::transaction($closure);
    }
}
