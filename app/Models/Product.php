<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

     /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'products';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'productid',
        'description',
        'type',
        'example',
        'validateexpression'
    ];

    public function ocurrences()
    {
        return $this->hasMany(Ocurrence::class, 'productid', 'productid');
    }


}