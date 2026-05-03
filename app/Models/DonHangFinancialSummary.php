<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonHangFinancialSummary extends Model
{
    protected $table = 'don_hang_financial_summary';

    protected $primaryKey = 'id';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];
}
