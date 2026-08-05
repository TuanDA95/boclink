<?php

namespace App\Models;

use CodeIgniter\Model;

class LinkModel extends Model
{
    protected $table = 'links';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'code',
        'target_url',
        'flow',
        'enable_free',
        'registrator'
    ];
}
