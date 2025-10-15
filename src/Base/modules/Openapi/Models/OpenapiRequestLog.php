<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Openapi\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Xditn\Traits\DB\BaseOperate;
use Xditn\Traits\DB\ScopeTrait;
use Xditn\Traits\DB\WithAttributes;

/**
 * @property $id
 * @property $request_id
 * @property $app_key
 * @property $data
 * @property $created_at
 * @property $updated_at
 */
class OpenapiRequestLog extends Model
{
    use BaseOperate;
    use scopeTrait;
    use WithAttributes;

    protected $table = 'openapi_request_log';

    protected $dateFormat = 'U';

    protected $fillable = ['id', 'request_id', 'app_key', 'data', 'created_at', 'updated_at'];

    protected array $fields = ['id', 'request_id', 'app_key', 'data', 'created_at', 'updated_at'];

    protected string $timeFormat = 'Y-m-d H:i:s';

    public function __construct()
    {
        parent::__construct();

        $this->setSearchable([
            'app_key' => 'like',
            'request_id' => '=',
        ]);
    }

    /**
     * 重写 serializeDate
     */
    protected function serializeDate(DateTimeInterface|string $date): ?string
    {
        if (is_string($date)) {
            return $date;
        }

        return Carbon::instance($date)->setTimezone(config('app.timezone'))->format($this->timeFormat);
    }
}
