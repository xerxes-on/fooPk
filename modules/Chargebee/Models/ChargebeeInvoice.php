<?php

declare(strict_types=1);

namespace Modules\Chargebee\Models;

use App\Models\User;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Modules\Chargebee\Models\ChargebeeInvoice
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $assigned_user_id
 * @property array $data
 * @property string $invoice_id
 * @property int $invoice_date
 * @property string $customer_id
 * @property string $status
 * @property bool $processed
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User|null $assignedClient
 * @property-read User|null $owner
 * @method static Builder|Modules\Chargebee\Models\ChargebeeInvoice newModelQuery()
 * @method static Builder|ChargebeeInvoice newQuery()
 * @method static Builder|ChargebeeInvoice query()
 * @method static Builder|ChargebeeInvoice whereAssignedUserId($value)
 * @method static Builder|ChargebeeInvoice whereCreatedAt($value)
 * @method static Builder|ChargebeeInvoice whereCustomerId($value)
 * @method static Builder|ChargebeeInvoice whereData($value)
 * @method static Builder|ChargebeeInvoice whereId($value)
 * @method static Builder|ChargebeeInvoice whereInvoiceDate($value)
 * @method static Builder|ChargebeeInvoice whereInvoiceId($value)
 * @method static Builder|ChargebeeInvoice whereProcessed($value)
 * @method static Builder|ChargebeeInvoice whereStatus($value)
 * @method static Builder|ChargebeeInvoice whereUpdatedAt($value)
 * @method static Builder|ChargebeeInvoice whereUserId($value)
 * @mixin Eloquent
 */
class ChargebeeInvoice extends Model
{
    // TODO: @NickMost need to refactor it with correct form of enum
    public const STATUS_PAID     = 'paid';
    public const STATUS_REFUNDED = 'refunded';
    public const PROCESSED       = true;
    public const NOT_PROCESSED   = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chargebee_invoices';

    protected $fillable = [
        'user_id',
        'assigned_user_id',
        'data',
        'invoice_id',
        'invoice_date',
        'customer_id',
        'status',
        'processed',
    ];

    protected $casts = [
        'data'      => 'array',
        'processed' => 'boolean'
    ];

    /**
     * User which has chargebee account to which belongs this transaction
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * User to which this transaction was manually assigned
     */
    public function assignedClient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
