<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'job_title',
        'work_enterprise_id',
        'email',
        'password',
        'storage',
        'address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'id',
        'created_at',
        'updated_at',
        'email_verified_at',
        'work_enterprise_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'address' => 'array',
    ];

    protected $with = ['work_enterprise'];

    public function work_enterprise() :HasOne
    {
      return $this->hasOne(Enterprise::class, 'id', 'work_enterprise_id');
    }

    /**
     * awalnya diperlukan untuk Dmc@fillTable
     */
    public function setProtected(array $props){
      foreach($props as $prop => $v){
        $this->$prop = $v;
      }
    }

    /**
     * @return string
     */
    public function name($firstToLast = true)
    {
      if($firstToLast){
        $this->name = 
        ($this->first_name ? $this->first_name . " " : '') . 
        ($this->middle_name ? $this->middle_name . " " : '').
        ($this->last_name);
      } else {
        $this->name = 
        ($this->last_name . ", ");
        ($this->first_name ? $this->first_name . " " : '').
        ($this->middle_name);
      }
      return $this->name ?? $this->first_name;
    }


}
