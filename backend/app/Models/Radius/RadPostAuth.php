<?php
namespace App\Models\Radius;
use Illuminate\Database\Eloquent\Model;
class RadPostAuth extends Model
{
    protected $connection = 'radius';
    protected $table = 'radpostauth';
    public $timestamps = false;
    protected $fillable = [];
}
