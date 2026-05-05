<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MikrotikDevice extends Model {
    protected $fillable = ['name', 'host', 'port', 'username', 'password', 'status', 'last_seen'];
    protected $hidden = ['password'];
}
