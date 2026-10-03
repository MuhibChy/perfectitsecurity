<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CiRelationship extends Model
{
    use HasFactory;

    protected $fillable = ['parent_ci_id', 'child_ci_id', 'relationship_type'];

    public function parent() { return $this->belongsTo(ConfigurationItem::class, 'parent_ci_id'); }
    public function child() { return $this->belongsTo(ConfigurationItem::class, 'child_ci_id'); }
}
