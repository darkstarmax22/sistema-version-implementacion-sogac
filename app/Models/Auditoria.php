<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditorias';
    protected $fillable = ['persona_id', 'accion', 'modulo', 'ip', 'user_agent'];

    public function user() { return $this->belongsTo(User::class, 'persona_id'); }
}
