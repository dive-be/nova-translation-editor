<?php

namespace Dive\NovaTranslationEditor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * @property bool   $customized
 * @property bool   $published
 * @property string $unique_id
 */
class LanguageLine extends Model
{
    public $guarded = ['id'];

    protected $appends = [
        'customized',
        'published',
    ];

    protected $casts = ['published_at' => 'date'];

    public function getCustomizedAttribute(): bool
    {
        return $this->exists;
    }

    public function getPublishedAttribute(): bool
    {
        return is_string(Arr::get($this->attributes, 'published_at'));
    }

    public function getUniqueIdAttribute(): string
    {
        return $this->group.'-'.$this->key;
    }
}
