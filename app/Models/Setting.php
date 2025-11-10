<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public $incrementing = false;
    protected $primaryKey = 'key';
    protected $keyType     = 'string';
    protected $fillable   = ['key','value'];

    public static function get($key, $default = null)
    {
        $s = static::find($key);
        return $s ? $s->value : $default;
    }

    public static function set($key, $value)
    {
        return static::updateOrCreate(['key'=>$key], ['value'=>$value]);
    }
}
