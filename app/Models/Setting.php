<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

     /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'settings';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'ultimaconsulta'
    ];
}