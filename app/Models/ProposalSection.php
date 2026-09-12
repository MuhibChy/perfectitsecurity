<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposalSection extends Model
{
    protected $fillable = ['proposal_id', 'heading', 'body', 'sort_order'];

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }
}
