<?php

namespace App;

use App\Enums\TodoType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Todo extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'type', 'done_time', 'parent_id', 'creator_id', 'overline', 'order'];

    /**
     * Relation: parent.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Relation: parent.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Relation: contacts
     */
    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'todo_contact')->withTimestamps();
    }

    /**
     * Relation: user
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    protected function updatedAtApi(): Attribute
    {
        return new Attribute(
            get: fn ($value) => match ($this->type) {
                TodoType::GROUP => ($this->children()->exists() && $this->children()->orderBy('updated_at', 'desc')->value('updated_at') > $this->updated_at) ? $this->children()->orderBy('updated_at', 'desc')->value('updated_at') : $this->updated_at,
                default => $this->updated_at,
            },
        );
    }

    protected function updatedBy(): Attribute
    {
        return new Attribute(
            get: fn ($value) => match ($this->type) {
                TodoType::GROUP => $this->children()->exists() ? $this->children()->orderBy('updated_at', 'desc')->first()?->creator?->name : $this->creator->name,
                default => $this->creator->name,
            },
        );
    }

    public function scopeNotDone($query)
    {
        return $query->whereNull('done_time');
    }

    /**
     * Relation: parent.
     */
    public function childrenNotDone(): HasMany
    {
        return $this->children()->notDone()->orderBy('order');
    }
}
