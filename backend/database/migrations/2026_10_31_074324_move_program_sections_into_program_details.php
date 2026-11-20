<?php

use App\Models\PageSection;
use App\Models\Program;
use App\Support\Fields;
use App\Support\ProgramDetails;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Program::query()->whereNull('details')->whereHas('sections')->with('sections')->each(function (Program $program): void {
            $details = [];
            $count = ['feature_list' => 0, 'card_grid' => 0, 'stat_cards' => 0];

            foreach ($program->sections as $section) {
                $data = $section->data ?? [];
                $type = $section->type;
                $nth = isset($count[$type]) ? $count[$type]++ : 0;
                $items = fn (string $key) => array_values(array_filter($data[$key] ?? [], 'is_array'));
                $base = ['heading' => $data['heading'] ?? null, 'intro' => $data['body'] ?? null];

                match (true) {
                    $type === 'intro' => $program->body ??= $data['body'] ?? null,
                    $type === 'feature_list' && $nth === 0 => $details['snapshot'] = $this->snapshot($items('tabs')[0]['items'] ?? []),
                    $type === 'feature_list' && $nth === 1 => $details['loans'] = $base + ['providers' => array_map(fn ($tab) => ['name' => $tab['label'] ?? null, 'items' => $tab['items'] ?? []], $items('tabs'))],
                    $type === 'feature_list' && $nth === 2 => [
                        $details['life_video'] = $base,
                        $details['clubs'] = ['items' => array_map(fn ($item) => ['text' => $item['text'] ?? null], $items('tabs')[0]['items'] ?? [])],
                    ],
                    $type === 'card_grid' && $nth === 0 => $details['why_choose'] = ['heading' => null, 'items' => array_map(fn ($c) => ['icon' => $c['icon'] ?? null, 'title' => $c['title'] ?? null, 'text' => $c['body'] ?? null], $items('cards'))],
                    $type === 'card_grid' && $nth === 1 => $details['roles'] = $base + ['items' => array_map(fn ($c) => ['icon' => $c['icon'] ?? null, 'title' => $c['title'] ?? null, 'text' => $c['body'] ?? null], $items('cards'))],
                    $type === 'stat_cards' && $nth === 0 => $details['class_profile'] = ['heading' => $data['heading'] ?? null, 'stats' => array_map(fn ($i) => ['icon' => $i['icon'] ?? null, 'value' => $i['value'] ?? null, 'text' => $i['label'] ?? $i['text'] ?? null], $items('items'))],
                    $type === 'stat_cards' && $nth === 1 => $details['outcomes'] = $base + ['stats' => array_map(fn ($i) => ['icon' => $i['icon'] ?? null, 'value' => $i['value'] ?? null, 'label' => $i['label'] ?? null, 'text' => $i['text'] ?? null], $items('items'))],
                    $type === 'value_list' => $details['partners'] = ['heading' => $data['heading'] ?? null, 'items' => array_map(fn ($i) => ['title' => $i['title'] ?? null, 'text' => $i['text'] ?? null], $items('items'))],
                    $type === 'tabs' => $details['structure'] = $base + ['semesters' => array_map(fn ($tab) => ['title' => $tab['title'] ?? $tab['label'] ?? null, 'courses' => $this->lines($tab['body'] ?? '')], $items('tabs'))],
                    $type === 'rich_text' => $details['learning_model'] = ['notes' => $data['body'] ?? null],
                    $type === 'media_cards' => $details['labs'] = $base + ['items' => array_map(fn ($i) => ['image' => $i['image'] ?? null, 'title' => $i['title'] ?? null, 'text' => $i['body'] ?? null], $items('items'))],
                    $type === 'steps' => $details['admission_steps'] = $base + ['steps' => array_map(fn ($i) => ['text' => $i['title'] ?? null], $items('items'))],
                    $type === 'eligibility' => $details['eligibility'] = $base + ['branches' => $items('items')],
                    $type === 'checklist_cards' => $details['documents'] = ['heading' => $data['heading'] ?? null, 'items' => array_map(fn ($c) => ['icon' => $c['icon'] ?? null, 'title' => $c['title'] ?? null, 'text' => implode(', ', array_column($c['items'] ?? [], 'text'))], $items('cards'))],
                    $type === 'fees' => $details['fees'] = $base + [
                        'fee_label' => $data['fee_label'] ?? null, 'total' => $data['fee_amount'] ?? null, 'rows' => $items('rows'),
                        'scholarships' => $items('scholarships'), 'scholarships_url' => $data['link_url'] ?? null,
                        'scholarships_label' => $data['link_label'] ?? null, 'notes' => $items('notes'),
                    ],
                    $type === 'faq' => $details['faqs'] = $base + ['items' => $items('items')],
                    default => null,
                };
            }

            $program->details = Fields::normalize(ProgramDetails::fields(), $details);
            $program->saveQuietly();
            PageSection::query()->whereKey($program->sections->modelKeys())->update(['is_visible' => false]);
        });
    }

    public function down(): void {}

    private function snapshot(array $items): array
    {
        $eligibility = collect($items)->first(fn ($item) => str_starts_with((string) ($item['text'] ?? ''), 'Eligibility:'))['text'] ?? '';
        $eligibility = trim(Str::after($eligibility, 'Eligibility:'));

        return [
            'eligibility' => Str::limit(trim(Str::before($eligibility, '(')), 200, '') ?: null,
            'eligibility_note' => str_contains($eligibility, '(') ? Str::limit(trim(Str::between($eligibility, '(', ')')), 200, '') : null,
        ];
    }

    private function lines(string $html): string
    {
        preg_match_all('/<li[^>]*>(.*?)<\/li>/s', $html, $matches);
        $lines = $matches[1] ?: preg_split('/<\/p>|<br\s*\/?>/', $html);

        return implode("\n", array_filter(array_map(fn ($line) => trim(html_entity_decode(strip_tags($line))), $lines)));
    }
};
