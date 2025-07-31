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
        'password', 'role', 'status', 'department_id', 'access_id', 'profile_picture_id', 'terms_accepted_at', 'terms_version'
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
            'terms_accepted_at' => 'datetime'
        ];
    }

    public function hasAcceptedCurrentTerms(): bool
    {
        return $this->terms_accepted_at && 
            $this->terms_version === config('app.terms_version');
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
}
