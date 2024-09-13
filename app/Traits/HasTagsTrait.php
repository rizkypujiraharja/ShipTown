<?php

namespace App\Traits;

use ArrayAccess;
use Illuminate\Support\Collection;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Tags\HasTags;
use Spatie\Tags\Tag;

/**
 * App\Models\Product.
 *
 * @method static withAnyTags($tags, $type = null)
 * @method static withAnyTagsOfAnyType($tags)
 */
trait HasTagsTrait
{
    use HasTags {
        attachTags as originalAttachTags;
        detachTags as originalDetachTags;
        scopeWithAllTags as traitHasTagsScopeWithAllTags;
    }

    protected function onTagAttached($tag)
    {
        // override this function on model
    }

    protected function onTagDetached($tag)
    {
        // override this function on model
    }

    public function hasTags(?array $tags = null): bool
    {
        return static::withAllTags($tags)->whereId($this->getKey())->exists();
    }

    public function scopeHasTags(Builder $query, $tags): Builder
    {
        $tags = collect(func_get_args());
        $tags->shift();

        $tags = $tags->map(function ($tag) {
            return Str::slug($tag);
        });

        $query->whereHas('tags', function (Builder $query) use ($tags) {
            $tags->each(function ($tag) use ($query) {
                $query->where(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(slug, '$.\"en\"'))"), $tag);
            });
        });

        return $query;
    }

    /**
     *
     * @throws Exception
     */
    public function attachTags(array $tags, ?string $type = null): self
    {
        collect($tags)
            ->filter()
            ->each(function ($tag) use ($type) {
                retry(3, function () use ($tag, $type) {
                    if ($this->hasTags([$tag])) {
                        return;
                    }

                    $this->originalAttachTags([$tag], $type);

                    // if LogsActivityTrait is used, we log it
                    if (in_array(LogsActivityTrait::class, class_uses_recursive($this))) {
                        $this->log('attached "'.$tag.'" tag');
                    }

                    $this->onTagAttached($tag);
                }, 1);
            });

        return $this;
    }

    public function detachTags(array|Collection $tags, ?string $type = null): self
    {
        collect($tags)
            ->filter()
            ->each(function ($tag) use ($type) {
                if ($this->doesNotHaveTags([$tag])) {
                    return;
                }
                $this->originalDetachTags([$tag], $type);
                $this->onTagDetached($tag);
                $this->log('detached "'.$tag.'" tag');
            });

        return $this;
    }

    /**
     * @param  string|Tag  $tag
     */
    public function detachTagSilently($tag, ?string $type = null): self
    {
        activity()->withoutLogs(function () use ($tag, $type) {
            $this->detachTag($tag, $type);
        });

        return $this;
    }

    /**
     * @param  array|ArrayAccess|Tag  $tags
     */
    public function scopeWithoutAllTags(Builder $query, $tags, ?string $type = null): Builder
    {
        $tags = static::convertToTags($tags, $type);

        collect($tags)->each(function ($tag) use ($query) {
            $query->whereDoesntHave('tags', function (Builder $query) use ($tag) {
                $query->where('tags.id', $tag ? $tag->id : 0);
            });
        });

        return $query;
    }

    public function doesNotHaveTags(?array $tags = null): bool
    {
        return ! $this->hasTags($tags);
    }

    public function syncTagByType(string $tagType, string $tagName): void
    {
        $tag = $this->tags()->where(['type' => $tagType])->first();

        if ($tag === null) {
            $this->attachTag($tagName, $tagType);

            return;
        }

        if ($tag->name === $tagName) {
            return;
        }

        $this->detachTag($tag);
        $this->attachTag($tagName, $tagType);
    }
}
