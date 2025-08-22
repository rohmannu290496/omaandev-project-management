<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'ticket_prefix',
        'start_date',
        'end_date',
        'pinned_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'pinned_date' => 'datetime',
    ];

    // Helper method to check if project is pinned
    public function getIsPinnedAttribute(): bool
    {
        return !is_null($this->pinned_date);
    }

    // Helper method to pin project
    public function pin(): void
    {
        $this->update(['pinned_date' => now()]);
    }

    // Helper method to unpin project
    public function unpin(): void
    {
        $this->update(['pinned_date' => null]);
    }

    public function ticketStatuses(): HasMany
    {
        return $this->hasMany(TicketStatus::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withTimestamps();
    }

    // Add this method for Filament RelationManager compatibility
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withTimestamps();
    }

    public function epics(): HasMany
    {
        return $this->hasMany(Epic::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ProjectNote::class);
    }

    public function getRemainingDaysAttribute()
    {
        if (!$this->end_date) {
            return null;
        }

        $today = Carbon::today();
        $endDate = Carbon::parse($this->end_date);

        if ($today->gt($endDate)) {
            return 0;
        }

        return $today->diffInDays($endDate);
    }
    
    public function externalAccess(): HasOne
    {
        return $this->hasOne(ExternalAccess::class);
    }
    
    public function generateExternalAccess()
    {
        $this->externalAccess()?->delete();
    
        return ExternalAccess::generateForProject($this->id);
    }
}
