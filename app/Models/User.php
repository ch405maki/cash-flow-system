<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username', 'first_name', 'middle_name', 'last_name', 'email',
        'password', 'role', 'is_petty_cash', 'is_cash_advance', 'status', 
        'department_id', 'access_id', 'profile_picture_id', 'terms_accepted_at', 
        'terms_version'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $casts = [
        'terms_accepted_at' => 'datetime',
    ];

    public function needsToAcceptTerms(): bool
    {
        return is_null($this->terms_accepted_at);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function access(): BelongsTo
    {
        return $this->belongsTo(Access::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    protected $with = ['profilePicture'];

    public function profilePicture()
    {
        return $this->belongsTo(ProfilePicture::class, 'profile_picture_id');
    }

    public function requestApprovals(): HasMany
    {
        return $this->hasMany(RequestApproval::class);
    }
    
    public function requestToOrderApprovals(): HasMany
    {
        return $this->hasMany(RequestToOrderApproval::class);
    }

    public function purchaseOrderApprovals(): HasMany
    {
        return $this->hasMany(PurchaseOrderApproval::class);
    }

    public function voucherApprovals(): HasMany
    {
        return $this->hasMany(VoucherApproval::class);
    }

    public function auditedVouchers()
    {
        return $this->hasMany(Voucher::class, 'audited_by');
    }

    // Petty Cash Voucher relationship
    public function pettyCashVouchers()
    {
        return $this->hasMany(PettyCash::class);
    }

    public function pettyCashApprovals(): HasMany
    {
        return $this->hasMany(PettyCashApproval::class);
    }

    // DistributionExpense roles
    public function distributionExpensesPrepared()
    {
        return $this->hasMany(DistributionExpense::class, 'prepared_by');
    }

    public function distributionExpensesApproved()
    {
        return $this->hasMany(DistributionExpense::class, 'approved_by');
    }

    public function distributionExpensesAudited()
    {
        return $this->hasMany(DistributionExpense::class, 'audited_by');
    }

    public function distributionExpensesPaid()
    {
        return $this->hasMany(DistributionExpense::class, 'paid_by');
    }

    // Pettycash release
    public function pettyCashFund()
    {
        return $this->hasOne(PettyCashFund::class);
    }
}
