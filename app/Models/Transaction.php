<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'Transactions';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'userID',
        'TypeTransaction',
        'Description',
        'When',
        'TransactionId',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'userID' => 'integer',
            'TypeTransaction' => 'integer',
            'When' => 'datetime',
            'TransactionId' => 'string',
        ];
    }

    public function personNavigation()
    {
        return $this->belongsTo(Person::class, 'userID', 'Id');
    }
}
