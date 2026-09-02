<?php

namespace App\Nexus\Modules\User\Traits;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;


/**
 * Trait UserModelTrait
 * @property array $extraFillable
 * @var $this \Illuminate\Database\Eloquent\Model
 */
trait UserModelTrait
{
    // Aliased (not used directly as roles()/permissions()) so User can redeclare
    // those as real relation methods carrying #[Field]/#[Relation] attributes —
    // see the comment on User::roles()/permissions() for why that redeclaration
    // is required rather than optional.
    use HasRoles {
        roles as protected baseRoles;
        permissions as protected basePermissions;
    }

    protected array $additionalFillable = [
        'user_type',
        'store_id',
        'avatar',
        'status',
        'display_name',
        'last_name',
        'patronymic',
        'price_list',
        'address',
        'phone',
//        'language',
        'google2fa_secret'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
//        \Log::info('UserModelTrait constructor called, updating fillable');
        $this->fillable = array_merge(parent::getFillable(), $this->additionalFillable);
    }

    public function getFillable()
    {
        // Об'єднуємо поля з трейту з полями моделі
        return array_merge(parent::getFillable(), $this->additionalFillable);
    }

    /**
     * @return string[]
     */
    public function getHidden(): array
    {
        // Об'єднуємо поля з трейту з полями моделі
        return array_merge(parent::getHidden(), $this->additionalHidden);
    }

    protected $additionalHidden = [
        'remember_token',
        'google2fa_secret'
    ];

    /**
     * @return string[]
     */
    public function getGuarded(): array
    {
        // Об'єднуємо поля з трейту з полями моделі
        return array_merge(parent::getGuarded(), $this->additionalGuarded);
    }

    protected $additionalGuarded = [
          '_token',
          '_method',
    ];

//    public function avatar()
//    {
//        if (!isset($this->image)) {
//            return '/larkon/images/no-image.jpg';
//        }
//        return $this->image;
//    }

    protected function avatar(): Attribute
    {
//        return Attribute::make(
//            get: fn () => $this->image ?? '/larkon/images/no-image.jpg',
//        );
        return Attribute::make(
            get: function (){
                return $this->attributes['avatar'] ?? '/nexus/images/no-image.jpg';
            }
        );
    }

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::needsRehash($password) ? Hash::make($password) : $password;
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }

}
