<?php

namespace Xditn\Traits\DB;

use Closure;

/**
 * WithEvents trait 提供了在特定操作之前和之后执行自定义闭包的方法
 */
trait WithEvents
{
    // 在获取列表数据之前执行的闭包
    protected ?Closure $beforeGetList = null;

    // 在通过特定条件获取单条记录之后执行的闭包
    protected ?Closure $afterFirstBy = null;

    /**
     * 设置获取列表数据之前的处理逻辑
     *
     * 该方法允许在获取列表数据之前执行自定义的逻辑，开发者可以传递一个
     * 闭包，执行某些前置操作。
     *
     * @param  Closure  $closure 自定义的前置闭包
     * @return $this 返回当前实例以支持链式调用
     */
    public function setBeforeGetList(Closure $closure): static
    {
        $this->beforeGetList = $closure;

        return $this;
    }

    /**
     * 设置通过条件获取单条数据后的处理逻辑
     *
     * 该方法允许在通过某个条件获取单条数据之后执行自定义的逻辑，开发者
     * 可以传递一个闭包，执行某些后置操作。
     *
     * @param  Closure  $closure 自定义的后置闭包
     * @return $this 返回当前实例以支持链式调用
     */
    public function setAfterFirstBy(Closure $closure): static
    {
        $this->afterFirstBy = $closure;

        return $this;
    }
}
