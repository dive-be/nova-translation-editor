<?php

namespace Dive\NovaTranslationEditor;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class LanguageLine extends Model
{
    public $guarded = ['id'];

    protected $casts = ['published_at' => 'date'];

    public function getIdentifier() {
        return $this->group.'-'.$this->key;
    }

    public function toArray()
    {
        return array_merge(parent::toArray(), ['customized' => $this->exists, 'published' => $this->published_at instanceof Carbon]);
    }
}
