<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ocurrence extends Model
{
    use HasFactory;

     /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ocurrences';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'vendorid',
        'productid',
        'latitude',
        'longitude',
        'value',
        'dateinsert',
        'resolvida',
        'interna'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'productid', 'productid');
    }


}