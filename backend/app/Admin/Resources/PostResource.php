<?php

namespace App\Admin\Resources;

use App\Models\Post;
use App\Support\PageBlocks;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PostResource extends Resource
{
    public function model(): string
    {
        return Post::class;
    }

    public function slug(): string
    {
        return 'posts';
    }

    public function label(): string
    {
        return 'News & blogs';
    }

    public function singular(): string
    {
        return 'Post';
    }

    public function navigationGroup(): string
    {
        return 'Editorial';
    }

    public function fields(): array
    {
        return [
            ['name' => 'title', 'type' => 'text', 'label' => 'Headline', 'required' => true, 'max' => 255],
            ['name' => 'slug', 'type' => 'slug', 'label' => 'URL slug', 'from' => 'title'],
            ['name' => 'type', 'type' => 'select', 'label' => 'Type', 'required' => true, 'options' => collect(Post::Types)->map(fn ($t) => $t['label'])->all()],
            ['name' => 'category_id', 'type' => 'belongsTo', 'source' => 'category_ids', 'label' => 'Category'],
            ['name' => 'tags', 'type' => 'belongsToMany', 'source' => 'tag_ids', 'label' => 'Tags'],
            ['name' => 'post_author_id', 'type' => 'belongsTo', 'source' => 'author_ids', 'label' => 'Author'],
            ['name' => 'image_id', 'type' => 'image', 'label' => 'Featured image'],
            ['name' => 'excerpt', 'type' => 'textarea', 'label' => 'Excerpt', 'max' => 400],
            ['name' => 'body', 'type' => 'richtext', 'label' => 'Article body', 'help' => 'Headings (H2) appear in the "Sections" navigation on blogs and stories. Use the Image button for photo groups.'],
            ['name' => 'external_url', 'type' => 'url', 'label' => 'Opens this link instead of an article', 'help' => 'For press coverage: a link to the publication or to a file. Leave empty to show the article body.'],
            ['name' => 'document_id', 'type' => 'file', 'label' => 'Opens this PDF instead of an article', 'help' => 'Used in place of the link above.'],
            ['name' => 'is_featured', 'type' => 'boolean', 'label' => 'Featured (shown large at the top of listings)'],
            ['name' => 'sections', 'type' => 'sections', 'label' => 'Additional layout sections', 'group' => 'Sections', 'help' => 'Optional. Used for case studies and long-form stories (challenge, approach, galleries…). Shown after the article body.'],
            PageBlocks::field(Post::class),
        ];
    }

    public function searchable(): array
    {
        return ['title', 'excerpt'];
    }

    public function with(): array
    {
        return ['workingCopy', 'category:id,name'];
    }

    public function defaultSort(): array
    {
        return ['updated_at', 'desc'];
    }

    public function defaults(): array
    {
        return ['type' => 'news', 'tags' => []];
    }

    public function columns(): array
    {
        return [['key' => 'title', 'label' => 'Headline'], ['key' => 'type', 'label' => 'Type'], ['key' => 'category', 'label' => 'Category'], ['key' => 'published', 'label' => 'Published']];
    }

    public function row(Model $record): array
    {
        return parent::row($record) + [
            'type' => Post::Types[$record->type]['label'] ?? $record->type,
            'category' => $record->category?->name,
            'published' => $record->published_at?->format('M j, Y'),
        ];
    }

    protected function slugUniqueRule(Request $request, ?Model $record): mixed
    {
        return Rule::unique('posts', 'slug')->where(fn (Builder|\Illuminate\Database\Query\Builder $q) => $q->where('type', $request->input('type')))->ignore($record?->getKey());
    }
}
