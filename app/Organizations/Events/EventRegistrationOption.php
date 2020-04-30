<?php

namespace App\Organizations\Events;

use App\Organizations\OrganizationGroup;
use Illuminate\Database\Eloquent\Model;

class EventRegistrationOption extends Model
{
    protected $guarded = [];
    protected $with = ['groupRequirements'];

    protected $casts = [
        'count_to_slots' => 'boolean',
        'enabled' => 'boolean',
    ];

    protected $dates = [
        'opens_at',
        'closes_at',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function groupRequirements()
    {
        return $this->belongsToMany(OrganizationGroup::class, EventRegistrationOptionRequiredGroup::class)
            ->orderBy('event_registration_option_required_groups.organization_group_id');
    }
}
