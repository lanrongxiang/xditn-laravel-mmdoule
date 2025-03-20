<?php

namespace Xditn\Base\modules\System\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $task
 * @property $params
 * @property $start_at
 * @property $status
 * @property $error
 * @property $time_taken
 * @property $result
 * @property $retry
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class AsyncTask extends Model
{
    protected $table = 'system_async_task';

    protected $fillable = [
        'id',
        'task',
        'params',
        'start_at',
        'status',
        'time_taken',
        'error',
        'result',
        'retry',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const UN_RUNNING = 1;

    public const RUNNING = 2;

    public const ERROR = 3;

    public const FINISHED = 4;

    protected $casts = [
        'start_at' => 'date:Y-m-d H:i',
    ];

    protected int $maxRetryTimes = 3;

    public function getUnRunning(): ?AsyncTask
    {
        return self::whereIn('status', [self::UN_RUNNING, self::ERROR])
            ->where('retry', '<', $this->maxRetryTimes)
            ->first();
    }

    /**
     * @return bool
     */
    public function start(): bool
    {
        $this->start_at = time();

        $this->retry();

        $this->setRunningStatus();

        return $this->save();
    }

    /**
     * @param  string|array  $result
     * @return bool
     */
    public function finished(string|array $result): bool
    {
        $this->setFinishedStatus();

        if (is_array($result)) {
            $result = json_encode($result);
        }

        $this->result = $result;

        return $this->save();
    }

    /**
     * @param  string  $error
     * @return bool
     */
    public function error(string $error): bool
    {
        $this->error = $error;

        $this->setErrorStatus();

        return $this->save();
    }

    /**
     * @return $this
     */
    public function setRunningStatus(): static
    {
        $this->status = self::RUNNING;

        return $this;
    }

    /**
     * @return $this
     */
    public function setErrorStatus(): static
    {
        $this->status = self::ERROR;

        $this->setTimeTaken();

        return $this;
    }

    /**
     * @return $this
     */
    public function setFinishedStatus(): static
    {
        $this->status = self::FINISHED;

        $this->setTimeTaken();

        // 成功设置错误为空
        $this->error = '';

        return $this;
    }

    public function isRunning(): bool
    {
        return $this->status === self::RUNNING;
    }

    public function isError(): bool
    {
        return $this->status === self::ERROR;
    }

    public function isFinished(): bool
    {
        return $this->status === self::FINISHED;
    }

    /**
     * @return $this
     */
    public function setTimeTaken(): mixed
    {
        $this->time_taken = time() - $this->start_at->timestamp;

        return $this;
    }

    /**
     * @return $this
     */
    public function retry(): static
    {
        if ($this->isError()) {
            $this->retry += 1;
        }

        return $this;
    }

    public function getRunningTaskNum(): int
    {
        return self::where('status', self::RUNNING)->count();
    }

    protected function result(): Attribute
    {
        return new Attribute(
            get: fn ($value) => Str::startsWith($value, 'http') ? $value : config('app.url').'/'.$value
        );
    }
}
