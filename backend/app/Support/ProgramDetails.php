<?php

namespace App\Support;

use App\Models\Media;
use App\Sections\SampleContent;
use App\Sections\SectionRegistry;
use Illuminate\Support\Collection;

class ProgramDetails
{
    public const Tabs = [
        'overview' => 'Program Overview',
        'structure' => 'Program Structure',
        'admissions' => 'Admissions',
        'outcomes' => 'Key Outcomes & Impact',
        'fees' => 'Fees & Scholarship',
        'life' => 'Student Life',
        'faqs' => 'FAQs',
    ];

    public const Blocks = [
        'hero_image' => ['overview', 'Hero image'],
        'brochure' => ['overview', 'Download brochure button'],
        'overview' => ['overview', 'Overview text'],
        'snapshot' => ['overview', 'Program snapshot'],
        'why_choose' => ['overview', 'Why choose this program'],
        'class_profile' => ['overview', 'Class profile and statistics'],
        'partners' => ['overview', 'Program partners'],
        'faculty' => ['overview', 'Faculty'],
        'structure' => ['structure', 'Semesters and courses'],
        'learning_model' => ['structure', 'Learning model'],
        'labs' => ['structure', 'Labs & industry immersion'],
        'admission_steps' => ['admissions', 'Admission steps'],
        'eligibility' => ['admissions', 'Eligibility criteria'],
        'documents' => ['admissions', 'Required documents'],
        'outcomes' => ['outcomes', 'Outcome statistics and hiring companies'],
        'roles' => ['outcomes', 'Career roles'],
        'alumni' => ['outcomes', 'Where our alumni work'],
        'recruiter_quotes' => ['outcomes', 'Hear from recruiters'],
        'innovations' => ['outcomes', 'Student innovation and research'],
        'awards' => ['outcomes', 'Awards & recognitions'],
        'fees' => ['fees', 'Program fee and scholarships'],
        'loans' => ['fees', 'Education loan assistance'],
        'life_video' => ['life', 'A day in the life (video)'],
        'clubs' => ['life', 'Student clubs'],
        'life_alumni' => ['life', 'Hear from our alumni'],
        'testimonials' => ['life', 'Student testimonials'],
        'faqs' => ['faqs', 'Frequently asked questions'],
        'enquire' => ['faqs', 'Enquiry form'],
    ];

    public static function blockLabels(): array
    {
        return array_map(fn (array $block): string => self::Tabs[$block[0]].': '.$block[1], self::Blocks);
    }

    public static function fields(): array
    {
        $icon = ['name' => 'icon', 'type' => 'select', 'label' => 'Icon', 'options' => SectionRegistry::icons()];
        $heading = fn (string $default) => ['name' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 120, 'help' => "Leave blank for \"{$default}\"."];
        $intro = ['name' => 'intro', 'type' => 'textarea', 'label' => 'Introduction', 'max' => 600];
        $countUp = 'Counts up from zero when it scrolls into view, e.g. 96%, ₹12L, 10+. Keep it short (up to 6 characters stays on one line).';
        $people = ['name' => 'items', 'type' => 'repeater', 'label' => 'People', 'maxItems' => 16, 'fields' => [
            ['name' => 'photo', 'type' => 'image', 'label' => 'Photo'],
            ['name' => 'name', 'type' => 'text', 'label' => 'Name', 'max' => 80, 'required' => true],
            ['name' => 'class', 'type' => 'text', 'label' => 'Class of', 'max' => 40, 'help' => 'e.g. Class of 2024'],
            ['name' => 'role', 'type' => 'text', 'label' => 'Role and company', 'max' => 120],
            ['name' => 'link_url', 'type' => 'url', 'label' => 'Link'],
        ]];
        $cards = fn (string $label) => ['name' => 'items', 'type' => 'repeater', 'label' => $label, 'maxItems' => 16, 'fields' => [
            ['name' => 'image', 'type' => 'image', 'label' => 'Image'],
            ['name' => 'date', 'type' => 'text', 'label' => 'Date', 'max' => 40],
            ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 140, 'required' => true],
            ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 600],
            ['name' => 'link_url', 'type' => 'url', 'label' => 'Link'],
        ]];
        $module = fn (string $key, string $tab, array $fields, string $help = '') => [
            'name' => $key, 'type' => 'group', 'label' => self::Blocks[$key][1], 'group' => 'Page: '.$tab,
            'store' => 'details', 'hideItems' => true, 'fields' => $fields, 'help' => $help ?: null,
        ];

        return [
            $module('brochure', 'Overview', [
                ['name' => 'file', 'type' => 'file', 'label' => 'Program brochure (PDF)', 'help' => 'Visitors fill in a short form, then download it.'],
            ]),
            $module('snapshot', 'Overview', [
                ['name' => 'start_date', 'type' => 'text', 'label' => 'Start date', 'max' => 60, 'help' => 'Leave blank to use the intake from Key facts.'],
                ['name' => 'deadline', 'type' => 'text', 'label' => 'Application deadline', 'max' => 60, 'help' => 'For a month without a day, e.g. August 2027. Leave blank to use the date from Key facts.'],
                ['name' => 'eligibility', 'type' => 'text', 'label' => 'Eligibility', 'max' => 200, 'help' => 'e.g. B.E./B.Tech'],
                ['name' => 'eligibility_note', 'type' => 'text', 'label' => 'Eligibility note', 'max' => 200, 'help' => 'e.g. ≥60% in 10th, 12th & Graduation'],
            ], 'Duration, deadline and mode come from the Key facts tab.'),
            $module('why_choose', 'Overview', [
                $heading('Why Choose this Program?'),
                ['name' => 'items', 'type' => 'repeater', 'label' => 'Reasons', 'maxItems' => 9, 'fields' => [
                    $icon,
                    ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 80, 'required' => true],
                    ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 400],
                ]],
            ]),
            $module('class_profile', 'Overview', [
                $heading('Class of 2026'),
                $intro,
                ['name' => 'image', 'type' => 'image', 'label' => 'Image'],
                ['name' => 'mix_label', 'type' => 'text', 'label' => 'Mix label', 'max' => 60, 'help' => 'Leave blank for "Student Profile Mix".'],
                ['name' => 'mix', 'type' => 'repeater', 'label' => 'Student profile mix', 'maxItems' => 8, 'fields' => [
                    ['name' => 'label', 'type' => 'text', 'label' => 'Label', 'max' => 60, 'required' => true],
                    ['name' => 'percent', 'type' => 'number', 'label' => 'Percent', 'min' => 0, 'max' => 100, 'required' => true],
                ]],
                ['name' => 'stats', 'type' => 'repeater', 'label' => 'Statistics', 'maxItems' => 4, 'fields' => [
                    $icon,
                    ['name' => 'value', 'type' => 'text', 'label' => 'Value', 'max' => 20, 'required' => true, 'help' => $countUp],
                    ['name' => 'text', 'type' => 'text', 'label' => 'Text', 'max' => 120],
                ]],
            ]),
            $module('partners', 'Overview', [
                $heading('Program Partners'),
                ['name' => 'items', 'type' => 'repeater', 'label' => 'Partners', 'maxItems' => 20, 'fields' => [
                    ['name' => 'image', 'type' => 'image', 'label' => 'Photo', 'help' => 'Square crop. Without one, the card shows the logo (or initials) on a lavender tile.'],
                    ['name' => 'tag', 'type' => 'text', 'label' => 'Label', 'max' => 40, 'help' => 'Small line above the name, e.g. Academic Partner or Industry Partner.'],
                    ['name' => 'date', 'type' => 'text', 'label' => 'Date', 'max' => 40, 'help' => 'Optional; shown instead of the label.'],
                    ['name' => 'logo', 'type' => 'image', 'label' => 'Partner logo'],
                    ['name' => 'title', 'type' => 'text', 'label' => 'Partner name', 'max' => 140, 'required' => true, 'help' => 'Shown on up to 2 lines.'],
                    ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 800, 'help' => 'Shown on up to 3 lines.'],
                    ['name' => 'link_url', 'type' => 'url', 'label' => 'Link', 'help' => 'Adds a "View" link to the card.'],
                ]],
            ], 'Leave empty to show NAMTECH’s featured partners from Partnerships → Partners. Missing photos and logos are filled in from Partners when the name matches.'),
            $module('structure', 'Structure', [
                $heading('Program Structure'),
                $intro,
                ['name' => 'semesters', 'type' => 'repeater', 'label' => 'Semesters', 'maxItems' => 12, 'fields' => [
                    ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 80, 'required' => true],
                    ['name' => 'columns', 'type' => 'text', 'label' => 'Table columns', 'max' => 120, 'help' => 'Optional, e.g. Course, L-T-P, Credits. Shows the courses as a table.'],
                    ['name' => 'courses', 'type' => 'textarea', 'label' => 'Courses (one per line)', 'max' => 3000, 'help' => 'With table columns, separate the values with |, e.g. Semiconductor Testing | 1-0-4 | 3.'],
                ]],
            ]),
            $module('learning_model', 'Structure', [
                $heading('NAMTECH’s Learning Model'),
                $intro,
                ['name' => 'cards', 'type' => 'repeater', 'label' => 'Learning approaches', 'maxItems' => 6, 'fields' => [
                    $icon,
                    ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 80, 'required' => true],
                    ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 400],
                ]],
                ['name' => 'mix', 'type' => 'repeater', 'label' => 'Learning mix (chart)', 'maxItems' => 6, 'fields' => [
                    ['name' => 'label', 'type' => 'text', 'label' => 'Label', 'max' => 60, 'required' => true],
                    ['name' => 'percent', 'type' => 'number', 'label' => 'Percent', 'min' => 0, 'max' => 100, 'required' => true],
                    ['name' => 'display', 'type' => 'text', 'label' => 'Shown as', 'max' => 20, 'help' => 'Optional, for a range such as ~40–55%. The percent above sets the chart size.'],
                ]],
                ['name' => 'notes', 'type' => 'richtext', 'label' => 'Further notes'],
            ]),
            $module('labs', 'Structure', [
                $heading('Labs & Industry Immersion'),
                $intro,
                ['name' => 'items', 'type' => 'repeater', 'label' => 'Labs', 'maxItems' => 12, 'fields' => [
                    ['name' => 'image', 'type' => 'image', 'label' => 'Image', 'required' => true],
                    ['name' => 'title', 'type' => 'text', 'label' => 'Lab name', 'max' => 120, 'required' => true],
                    ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 600],
                ]],
            ]),
            $module('admission_steps', 'Admissions', [
                $heading('Admission Process'),
                $intro,
                ['name' => 'steps', 'type' => 'repeater', 'label' => 'Steps', 'maxItems' => 8, 'fields' => [
                    ['name' => 'text', 'type' => 'textarea', 'label' => 'Step', 'max' => 200, 'required' => true],
                ]],
            ]),
            $module('eligibility', 'Admissions', [
                $heading('Eligibility Criteria'),
                $intro,
                ['name' => 'criteria', 'type' => 'repeater', 'label' => 'Criteria', 'maxItems' => 10, 'fields' => [
                    ['name' => 'text', 'type' => 'textarea', 'label' => 'Criterion', 'max' => 400, 'required' => true],
                ]],
                ['name' => 'branches_heading', 'type' => 'text', 'label' => 'Branches heading', 'max' => 120, 'help' => 'Leave blank for "Eligible Engineering Branches".'],
                ['name' => 'branches', 'type' => 'repeater', 'label' => 'Eligible branches', 'maxItems' => 30, 'fields' => [
                    $icon,
                    ['name' => 'text', 'type' => 'text', 'label' => 'Branch', 'max' => 120, 'required' => true],
                ]],
            ]),
            $module('documents', 'Admissions', [
                $heading('Required documents'),
                $intro,
                ['name' => 'items', 'type' => 'repeater', 'label' => 'Documents', 'maxItems' => 8, 'fields' => [
                    $icon,
                    ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 80, 'required' => true],
                    ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 400],
                ]],
            ]),
            $module('outcomes', 'Outcomes', [
                $heading('Key Outcomes & Impact'),
                $intro,
                ['name' => 'stats', 'type' => 'repeater', 'label' => 'Statistics', 'maxItems' => 4, 'fields' => [
                    $icon,
                    ['name' => 'value', 'type' => 'text', 'label' => 'Value', 'max' => 20, 'required' => true, 'help' => $countUp],
                    ['name' => 'label', 'type' => 'text', 'label' => 'Label', 'max' => 60],
                    ['name' => 'text', 'type' => 'text', 'label' => 'Text', 'max' => 120],
                ]],
                ['name' => 'brochure', 'type' => 'file', 'label' => 'Placement brochure (PDF)'],
                ['name' => 'logos_heading', 'type' => 'text', 'label' => 'Companies heading', 'max' => 120, 'help' => 'Leave blank for "Top companies hiring our graduates across industries".'],
                ['name' => 'logos', 'type' => 'repeater', 'label' => 'Hiring companies', 'maxItems' => 18, 'fields' => [
                    ['name' => 'image', 'type' => 'image', 'label' => 'Logo', 'help' => 'Without a logo, the company name is shown.'],
                    ['name' => 'name', 'type' => 'text', 'label' => 'Company name', 'max' => 80, 'required' => true],
                ]],
            ]),
            $module('roles', 'Outcomes', [
                $heading('Build Your Future in These Roles'),
                $intro,
                ['name' => 'items', 'type' => 'repeater', 'label' => 'Roles', 'maxItems' => 12, 'fields' => [
                    $icon,
                    ['name' => 'title', 'type' => 'text', 'label' => 'Role', 'max' => 120, 'required' => true],
                    ['name' => 'text', 'type' => 'textarea', 'label' => 'Description', 'max' => 400],
                ]],
            ]),
            $module('alumni', 'Outcomes', [$heading('Where Our Alumni Work'), $people]),
            $module('recruiter_quotes', 'Outcomes', [
                $heading('Hear From Recruiters Around the World'),
                ['name' => 'items', 'type' => 'repeater', 'label' => 'Quotes', 'maxItems' => 12, 'fields' => [
                    ['name' => 'logo', 'type' => 'image', 'label' => 'Company logo'],
                    ['name' => 'name', 'type' => 'text', 'label' => 'Company or person', 'max' => 120, 'required' => true],
                    ['name' => 'quote', 'type' => 'textarea', 'label' => 'Quote', 'max' => 600, 'required' => true],
                ]],
            ]),
            $module('innovations', 'Outcomes', [
                $heading('Student innovation and fruitful research'),
                $intro,
                ['name' => 'cta_url', 'type' => 'url', 'label' => '"View all" link'],
                $cards('Stories'),
            ]),
            $module('awards', 'Outcomes', [$heading('Awards & Recognitions'), $intro, $cards('Awards')]),
            $module('fees', 'Fees', [
                $heading('Fees and Scholarship'),
                $intro,
                ['name' => 'fee_label', 'type' => 'text', 'label' => 'Fee label', 'max' => 60, 'help' => 'Leave blank for "Program Fee".'],
                ['name' => 'total', 'type' => 'text', 'label' => 'Program fee', 'max' => 40, 'help' => 'e.g. Rs. 4,50,000'],
                ['name' => 'rows', 'type' => 'repeater', 'label' => 'Fee breakdown', 'maxItems' => 12, 'fields' => [
                    ['name' => 'label', 'type' => 'text', 'label' => 'Item', 'max' => 120, 'required' => true],
                    ['name' => 'value', 'type' => 'text', 'label' => 'Amount', 'max' => 40, 'required' => true],
                ]],
                ['name' => 'scholarships', 'type' => 'repeater', 'label' => 'Scholarships', 'maxItems' => 8, 'fields' => [
                    ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 80, 'required' => true],
                    ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 300],
                ]],
                ['name' => 'scholarships_url', 'type' => 'url', 'label' => 'Scholarships link'],
                ['name' => 'scholarships_label', 'type' => 'text', 'label' => 'Scholarships link label', 'max' => 60, 'help' => 'Leave blank for "Learn about scholarships".'],
                ['name' => 'notes', 'type' => 'repeater', 'label' => 'Payment notes', 'maxItems' => 6, 'fields' => [
                    $icon,
                    ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 80],
                    ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 600],
                ]],
            ]),
            $module('loans', 'Fees', [
                $heading('Education Loan Assistance'),
                $intro,
                ['name' => 'providers', 'type' => 'repeater', 'label' => 'Loan partners (tabs)', 'maxItems' => 8, 'fields' => [
                    ['name' => 'name', 'type' => 'text', 'label' => 'Partner name', 'max' => 60, 'help' => 'Shown as a tab when there is more than one partner.'],
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Benefits', 'maxItems' => 10, 'fields' => [
                        $icon,
                        ['name' => 'text', 'type' => 'text', 'label' => 'Benefit', 'max' => 200, 'required' => true],
                    ]],
                ]],
            ]),
            $module('life_video', 'Student life', [
                $heading('Student Life'),
                $intro,
                ['name' => 'image', 'type' => 'image', 'label' => 'Video cover image'],
                ['name' => 'video_url', 'type' => 'url', 'label' => 'Video link (YouTube or Vimeo)'],
                ['name' => 'title', 'type' => 'text', 'label' => 'Video title', 'max' => 120],
                ['name' => 'text', 'type' => 'textarea', 'label' => 'Video text', 'max' => 400],
            ]),
            $module('clubs', 'Student life', [
                ['name' => 'image', 'type' => 'image', 'label' => 'Image'],
                ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 80, 'help' => 'Leave blank for "Student Clubs".'],
                ['name' => 'items', 'type' => 'repeater', 'label' => 'Clubs', 'maxItems' => 20, 'fields' => [
                    ['name' => 'text', 'type' => 'text', 'label' => 'Club', 'max' => 120, 'required' => true],
                ]],
                ['name' => 'link_url', 'type' => 'url', 'label' => '"View all clubs" link'],
            ]),
            $module('life_alumni', 'Student life', [$heading('Hear From Our Alumni'), $people]),
            $module('testimonials', 'Student life', [
                $heading('Student Testimonials'),
                ['name' => 'items', 'type' => 'repeater', 'label' => 'Testimonials', 'maxItems' => 12, 'fields' => [
                    ['name' => 'quote', 'type' => 'textarea', 'label' => 'Quote', 'max' => 800, 'required' => true],
                    ['name' => 'name', 'type' => 'text', 'label' => 'Name', 'max' => 80, 'required' => true],
                    ['name' => 'role', 'type' => 'text', 'label' => 'Background and company', 'max' => 120],
                ]],
            ]),
            $module('faqs', 'FAQs', [
                $heading('Frequently Asked Questions'),
                $intro,
                ['name' => 'items', 'type' => 'repeater', 'label' => 'Questions', 'maxItems' => 30, 'fields' => [
                    ['name' => 'question', 'type' => 'text', 'label' => 'Question', 'max' => 200, 'required' => true],
                    ['name' => 'answer', 'type' => 'richtext', 'label' => 'Answer', 'required' => true],
                ]],
            ]),
        ];
    }

    public static function present(?array $details): array
    {
        $fields = self::fields();
        $details = Fields::withoutHiddenItems($fields, $details ?? []);
        $media = Media::query()->whereIn('id', Fields::mediaIds($fields, $details))->get()->keyBy('id');

        return self::withSamples($fields, self::withMedia($fields, $details, $media));
    }

    private const SampleLists = [
        'why_choose' => ['items'], 'class_profile' => ['mix', 'stats'], 'structure' => ['semesters'], 'learning_model' => ['cards', 'mix'],
        'labs' => ['items'], 'admission_steps' => ['steps'], 'eligibility' => ['criteria', 'branches'], 'documents' => ['items'],
        'outcomes' => ['stats', 'logos'], 'roles' => ['items'], 'alumni' => ['items'], 'innovations' => ['items'],
        'awards' => ['items'], 'fees' => ['rows', 'scholarships', 'notes'], 'loans' => ['providers'], 'clubs' => ['items'],
        'life_alumni' => ['items'], 'testimonials' => ['items'], 'faqs' => ['items'],
    ];

    private const SampleRecruiterSlugs = [
        'cisco', 'schneider-electric', 'amns-india', 'tum-asia',
        'festo', 'siemens', 'lt-heavy-engineering', 'micron',
    ];

    private static function withSamples(array $fields, array $details): array
    {
        $samples = new SampleContent;
        foreach ($fields as $module) {
            $key = $module['name'];
            $data = is_array($details[$key] ?? null) ? $details[$key] : [];
            foreach (self::SampleLists[$key] ?? [] as $list) {
                $field = collect($module['fields'])->firstWhere('name', $list);
                if ($field && empty($data[$list])) {
                    $data[$list] = $samples->many(fn (int $i) => self::sampleItem($samples, $field['fields'], $i), $key === 'awards' ? 4 : 3);
                }
            }
            if ($key === 'recruiter_quotes' && collect($data['items'] ?? [])->every(fn (array $item) => empty($item['logo']) && ($item['name'] ?? null) === 'SAMPLE – Company name'
                && ($item['quote'] ?? null) === 'SAMPLE – replace with a real recruiter quote.'
            )) {
                $data['items'] = $samples->many(fn (int $i) => [
                    'name' => 'Sample recruiter '.$i,
                    'quote' => 'Sample quote. Use this space for a recruiter’s feedback on graduates and how they apply their skills in the workplace.',
                    'logo' => null,
                    'sample_company_slug' => self::SampleRecruiterSlugs[$i - 1],
                ], count(self::SampleRecruiterSlugs));
            }
            if (in_array($key, ['innovations', 'awards'], true)) {
                $items = array_values($data['items'] ?? []);
                if ($key === 'awards' && count($items) === 3 && collect($items)->every(fn (array $item) => ($item['title'] ?? null) === 'SAMPLE – Award name' && ($item['text'] ?? null) === SampleContent::Text && empty($item['link_url'])
                )) {
                    $items[] = [...$items[0], 'title' => 'SAMPLE – Project showcase recognition'];
                }
                $sampleText = $key === 'awards'
                    ? 'Sample award. A student team receives recognition for an engineering project that demonstrates thoughtful design, careful testing, and practical application.'
                    : 'Sample story. A student team explores a practical engineering challenge through research, prototype development, and testing. The project shows how classroom ideas can guide hands-on problem solving.';
                $data['items'] = array_map(function (array $item) use ($sampleText): array {
                    if (! str_starts_with($item['title'] ?? '', 'SAMPLE – ')) {
                        return $item;
                    }
                    if (empty($item['date']) || str_starts_with($item['date'], 'SAMPLE – Date')) {
                        $item['date'] = 'January 10, 2026';
                    }
                    if (($item['text'] ?? null) === SampleContent::Text) {
                        $item['text'] = $sampleText;
                    }

                    return $item;
                }, $items);
            }
            if ($key === 'class_profile' && empty($data['image'])) {
                $data['image'] = $samples->image();
            }
            if ($key === 'life_video' && empty($data['image']) && empty($data['video_url']) && empty($data['title'])) {
                $data = [...$data, 'image' => $samples->image(), 'title' => 'A Day in the Life at NAMTECH', 'text' => SampleContent::Text];
            }
            if ($key === 'snapshot' && empty($data['eligibility'])) {
                $data['eligibility'] = 'SAMPLE – eligibility';
            }
            $details[$key] = $data;
        }

        return $details;
    }

    private static function sampleItem(SampleContent $samples, array $fields, int $i): array
    {
        $item = $samples->item($fields, $i);
        foreach ($fields as $field) {
            if ($field['type'] === 'repeater') {
                $item[$field['name']] = $samples->many(fn (int $j) => $samples->item($field['fields'], $j), 2);
            }
        }

        return $item;
    }

    private static function withMedia(array $fields, array $data, Collection $media): array
    {
        foreach ($fields as $field) {
            $name = $field['name'];
            $value = $data[$name] ?? null;
            $data[$name] = match ($field['type']) {
                'image' => is_numeric($value) ? $media->get((int) $value)?->toImage() : null,
                'file' => is_numeric($value) && ($document = $media->get((int) $value))?->isDocument()
                    ? ['url' => $document->documentUrl(), 'title' => $document->alt ?: $document->original_name, 'size' => $document->size]
                    : null,
                'group' => self::withMedia($field['fields'], is_array($value) ? $value : [], $media),
                'repeater' => array_map(fn ($item) => self::withMedia($field['fields'], is_array($item) ? $item : [], $media), is_array($value) ? $value : []),
                default => $value,
            };
        }

        return $data;
    }
}
