<?php

namespace App\Organizations\Events;

use App\Organizations\Organization;
use App\Organizations\OrganizationGroup;
use App\Users\User;
use Illuminate\Database\Eloquent\Model;
use App\Activity\SavesActivityAsLogEntries;

class Event extends Model
{
    use SavesActivityAsLogEntries;

    public $fieldToModelTypes = [
        'organization_id' => [Organization::class, 'id'],
    ];

    public function getFieldNameTranslationPrefix()
    {
        return 'events.fields.';
    }

    protected $guarded = [];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    public function registrationOptions()
    {
        return $this->hasMany(EventRegistrationOption::class)
            ->orderByDesc('priority');
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class)
            ->with('user');
    }

    /**
     * @param User $user
     * @return EventRegistrationOption|null
     */
    public function getRegistrationOption(User $user): ?EventRegistrationOption
    {
        $this->loadMissing('organization.groups', 'registrationOptions', 'registrationOptions.groupRequirements');
        $now = now();
        foreach ($this->registrationOptions as $option) {
            if ($option->enabled
                && $option->opens_at->isBefore($now)
                && $option->closes_at->isAfter($now)
                && ($option->groupRequirements->isEmpty()
                    || $option->groupRequirements->filter(fn (OrganizationGroup $group) => $group->hasMember($user))->isNotEmpty())) {
                return $option;
            }
        }

        return null;
    }

    public function getTakenSlots()
    {
        return $this->relationLoaded('registrations')
            ? $this->registrations->filter(fn (EventRegistration $registration) => $registration->count_to_slots && ($registration->confirmed || $registration->waitlist_confirmation_required_by !== null))->count()
            : $this->registrations()->where('count_to_slots', true)->where('confirmed', true)->orWhere('waitlist_confirmation_required_by', '!=', null)->count();
    }
}
