<?php

namespace Yannelli\EntryVault\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Yannelli\EntryVault\Enums\ContentType;

class EntryContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_id',
        'contentable_type',
        'contentable_id',
        'type',
        'body',
        'metadata',
        'order',
    ];

    protected $casts = [
        'metadata' => 'array',
        'order' => 'integer',
    ];

    public function getTable(): string
    {
        return config('entry-vault.tables.contents', 'entry_contents');
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(config('entry-vault.models.entry'), 'entry_id');
    }

    public function contentable(): MorphTo
    {
        return $this->morphTo('contentable');
    }

    public function isMarkdown(): bool
    {
        return $this->type === ContentType::MARKDOWN->value;
    }

    public function isHtml(): bool
    {
        return $this->type === ContentType::HTML->value;
    }

    public function isJson(): bool
    {
        return $this->type === ContentType::JSON->value;
    }

    public function isText(): bool
    {
        return $this->type === ContentType::TEXT->value;
    }

    public function getDecodedBody(): mixed
    {
        if ($this->isJson() && is_string($this->body)) {
            return json_decode($this->body, true);
        }

        return $this->body;
    }

    public function setEncodedBody(mixed $value): void
    {
        if ($this->isJson() && ! is_string($value)) {
            $this->body = json_encode($value);
        } else {
            $this->body = $value;
        }
    }
}
