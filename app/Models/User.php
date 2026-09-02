<?php

namespace App\Models;

use App\Nexus\Modules\Auth\Notifications\ResetPasswordNotification;
use App\Nexus\Modules\Auth\Notifications\VerifyEmailNotification;
use App\Nexus\Modules\User\Enums\Gender;
use App\Nexus\Modules\User\Models\UserAddress;
use App\Nexus\Modules\User\Traits\UserModelTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Relation;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;

/**
 * Minimal baseline shipped by nodex/nexus (published together with the
 * User/Auth/Permission/Role default modules — see
 * Nodex\Nexus\commands\PublishDefaultModules). Trimmed from the production
 * model this was based on: relations into modules that aren't part of this
 * bundle (Cart, Wishlist) and the ActivityLog trait were removed, since
 * publishing just these 4 modules must not leave the app with an
 * unloadable class. Extend this file directly — it's a normal file in your
 * app once published, not a package-owned template that gets overwritten.
 */
#[Module(
    name: 'user',
    label: 'Users',
    icon: 'user',
    group: 'User',
    showInMenu: true,
    isTree: false,
    menuResolver: 'App\Nexus\Modules\User\Services\UserMenuResolver',
    livewire: true,
)]
#[Requests(store: \App\Nexus\Modules\User\Requests\AdminStoreRequest::class, update: \App\Nexus\Modules\User\Requests\AdminUpdateRequest::class)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableAction(name: 'restore', label: 'Restore', icon: 'restore', isConfirm: true)]
#[TableAction(name: 'deletePermanent', label: 'Delete permanent', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[Section(name: 'information', column: 'right', type: 'columns_2', icon: 'cart')]
#[Section(name: 'relations', column: 'left', type: 'base')]
#[Section(name: 'change_password', column: 'left', type: 'base', icon: 'lock')]
#[Section(name: 'roles_and_permissions', column: 'left', type: 'base', icon: 'shield')]
#[Section(name: 'addresses_section', column: 'addresses_column', type: 'base', icon: 'map_point')]
class User extends Authenticatable implements MustVerifyEmail
{
    #[Column(label: 'name', sortable: true)]
    #[Field(type: 'string', section: 'information', label: 'name', required: false)]
    protected $name;

    #[Column(label: 'last_name', sortable: true)]
    #[Field(type: 'string', section: 'information', label: 'last_name', required: false)]
    protected $last_name;

    #[Field(type: 'string', section: 'information', label: 'middle_name', required: false)]
    protected $middle_name;

    // Method, not property: RoutesNotifications::routeNotificationForMail() and
    // CanResetPassword::getEmailForPasswordReset() read $this->email directly
    // (bypassing Eloquent's magic accessor) inside traits mixed straight into
    // this class, so a declared property here would silently break password
    // reset and email verification, the same way $password broke login.
    #[Column(label: 'email', sortable: true)]
    #[Field(type: 'email', section: 'information', label: 'email', required: false)]
    public function email(): void
    {
    }

    #[Column(label: 'phone')]
    #[Field(type: 'phone', section: 'information', label: 'phone', required: false)]
    protected $phone;

    #[Field(type: 'gender', section: 'information', label: 'gender', required: false)]
    protected $gender;

    #[Field(type: 'birthday', section: 'information', label: 'birthday', required: false)]
    protected $birthday;

    // Methods, not properties: Spatie's HasRoles/HasPermissions trait internals
    // (assignRole(), hasPermissionTo(), etc.) read $this->roles/$this->permissions
    // directly, bypassing Eloquent's magic accessor. A declared property with
    // either name would shadow that and silently break every permission check.
    // UserModelTrait aliases the trait's real implementations to
    // baseRoles()/basePermissions() so these just delegate to them.
    #[Field(type: 'relation', section: 'roles_and_permissions', label: 'Permission', required: false)]
    #[Relation(type: 'bELONGSTOMANY', show: 'display_name')]
    public function permissions(): BelongsToMany
    {
        return $this->basePermissions();
    }

    #[Field(type: 'relation', section: 'roles_and_permissions', label: 'Roles', required: false)]
    #[Relation(type: 'bELONGSTOMANY', show: 'display_name')]
    public function roles(): BelongsToMany
    {
        return $this->baseRoles();
    }

    // private (not protected): a protected $password here shadows Illuminate's
    // getAuthPassword(), which reads $this->password from the parent Authenticatable
    // class and silently gets null instead of Eloquent's magic accessor, breaking login.
    #[Field(type: 'password', section: 'change_password', label: 'new_password', required: false)]
    private $password;

    // private (not protected): same reasoning as $password — Authenticatable's
    // getAuthIdentifier() reads $this->{$this->getAuthIdentifierName()} (i.e.
    // $this->id) from the parent class. With this protected, Auth::login()
    // persisted a null user id into the session on every request, so the very
    // next request always looked logged-out.
    #[Column(label: 'ID', sortable: true)]
    private $id;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, UserModelTrait, HasAttributeSchemaProperties;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'middle_name',
        'email',
        'phone',
        'gender',
        'birthday',
        'avatar',
        'email_verified_at',
        'password',
        'google_id',
        'facebook_id',
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
            'gender' => Gender::class,
            'birthday' => 'date',
        ];
    }

    #[Field(type: 'addresses_table', section: 'addresses_section', label: 'addresses', required: false)]
    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }
}
