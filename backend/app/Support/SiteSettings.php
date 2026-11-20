<?php

namespace App\Support;

use App\Models\Media;
use App\Models\Setting;

class SiteSettings
{
    public static function fields(): array
    {
        return [
            ['name' => 'site_name', 'type' => 'text', 'label' => 'Site name', 'required' => true, 'max' => 80, 'group' => 'General'],
            ['name' => 'site_tagline', 'type' => 'text', 'label' => 'Tagline', 'max' => 120, 'group' => 'General'],
            ['name' => 'default_meta_description', 'type' => 'textarea', 'label' => 'Default meta description', 'max' => 170, 'group' => 'General'],
            ['name' => 'default_og_image_id', 'type' => 'image', 'label' => 'Default social sharing image', 'group' => 'General'],
            ['name' => 'virtual_tour_url', 'type' => 'url', 'label' => '360° virtual tour link (header icon)', 'group' => 'General'],

            ['name' => 'address_title', 'type' => 'text', 'label' => 'Address heading', 'max' => 80, 'group' => 'Contact'],
            ['name' => 'address', 'type' => 'textarea', 'label' => 'Postal address', 'max' => 400, 'group' => 'Contact'],
            ['name' => 'phones', 'type' => 'repeater', 'label' => 'Phone numbers', 'maxItems' => 4, 'group' => 'Contact', 'fields' => [
                ['name' => 'number', 'type' => 'text', 'label' => 'Number', 'max' => 30, 'required' => true],
            ]],
            ['name' => 'email', 'type' => 'email', 'label' => 'Public email', 'group' => 'Contact'],
            ['name' => 'map_url', 'type' => 'url', 'label' => 'Map link', 'group' => 'Contact'],
            ['name' => 'call_url', 'type' => 'url', 'label' => 'Floating "contact us" button link', 'group' => 'Contact', 'help' => 'e.g. tel:+917923277000 or /contact'],

            ['name' => 'linkedin_url', 'type' => 'url', 'label' => 'LinkedIn', 'group' => 'Social'],
            ['name' => 'x_url', 'type' => 'url', 'label' => 'X (Twitter)', 'group' => 'Social'],
            ['name' => 'youtube_url', 'type' => 'url', 'label' => 'YouTube', 'group' => 'Social'],
            ['name' => 'instagram_url', 'type' => 'url', 'label' => 'Instagram', 'group' => 'Social'],
            ['name' => 'facebook_url', 'type' => 'url', 'label' => 'Facebook', 'group' => 'Social'],

            ['name' => 'newsletter_heading', 'type' => 'text', 'label' => 'Newsletter heading', 'max' => 80, 'group' => 'Footer & forms'],
            ['name' => 'newsletter_consent', 'type' => 'textarea', 'label' => 'Newsletter consent statement', 'max' => 400, 'group' => 'Footer & forms'],
            ['name' => 'enquiry_consent', 'type' => 'textarea', 'label' => 'Enquiry consent statement', 'max' => 400, 'group' => 'Footer & forms'],
            ['name' => 'enquiry_recipients', 'type' => 'text', 'label' => 'Enquiry notification recipients', 'max' => 500, 'group' => 'Footer & forms', 'help' => 'Comma-separated email addresses.'],
            ['name' => 'copyright', 'type' => 'text', 'label' => 'Copyright line', 'max' => 120, 'group' => 'Footer & forms', 'help' => 'Use {year} for the current year.'],
            ['name' => 'footer_programs_title', 'type' => 'text', 'label' => 'Footer heading: programs column', 'max' => 60, 'group' => 'Footer & forms'],
            ['name' => 'footer_schools_title', 'type' => 'text', 'label' => 'Footer heading: schools and centres column', 'max' => 60, 'group' => 'Footer & forms'],
            ['name' => 'footer_partnerships_title', 'type' => 'text', 'label' => 'Footer heading: partnerships column', 'max' => 60, 'group' => 'Footer & forms'],
            ['name' => 'footer_quick_links_title', 'type' => 'text', 'label' => 'Footer heading: quick links column', 'max' => 60, 'group' => 'Footer & forms'],
            ['name' => 'footer_contact_title', 'type' => 'text', 'label' => 'Footer heading: contact column', 'max' => 60, 'group' => 'Footer & forms'],

            ['name' => 'schools_listing_heading', 'type' => 'text', 'label' => 'Schools & Centres page: schools heading', 'max' => 80, 'group' => 'Listings'],
            ['name' => 'centres_listing_heading', 'type' => 'text', 'label' => 'Schools & Centres page: centres heading', 'max' => 80, 'group' => 'Listings'],

            ...array_map(fn (array $toggle): array => $toggle + ['type' => 'boolean', 'default' => true, 'group' => 'Show / hide'], self::Toggles),
        ];
    }

    private const Toggles = [
        ['name' => 'show_announcement_bar', 'label' => 'Announcement bar (strip above the header)'],
        ['name' => 'show_utility_bar', 'label' => 'Top link bar (desktop header)'],
        ['name' => 'show_search', 'label' => 'Search button'],
        ['name' => 'show_virtual_tour', 'label' => '360° virtual tour icon'],
        ['name' => 'show_breadcrumbs', 'label' => 'Breadcrumbs'],
        ['name' => 'show_floating_contact', 'label' => 'Floating "contact us" button'],
        ['name' => 'show_footer_social', 'label' => 'Footer social links'],
        ['name' => 'show_footer_newsletter', 'label' => 'Footer newsletter signup'],
        ['name' => 'show_footer_links', 'label' => 'Footer link columns'],
        ['name' => 'show_footer_contact', 'label' => 'Footer contact details'],
    ];

    public static function values(): array
    {
        $values = Setting::allValues();

        return collect(self::fields())->mapWithKeys(fn (array $field) => [$field['name'] => $values[$field['name']] ?? $field['default'] ?? null])->all();
    }

    public static function public(): array
    {
        $values = self::values();
        $only = ['site_name', 'site_tagline', 'virtual_tour_url', 'address_title', 'address', 'phones', 'email', 'map_url', 'call_url',
            'linkedin_url', 'x_url', 'youtube_url', 'instagram_url', 'facebook_url', 'newsletter_heading', 'newsletter_consent', 'enquiry_consent', 'copyright',
            'footer_programs_title', 'footer_schools_title', 'footer_partnerships_title', 'footer_quick_links_title', 'footer_contact_title',
            'schools_listing_heading', 'centres_listing_heading', ...array_column(self::Toggles, 'name')];

        $settings = array_intersect_key($values, array_flip($only));
        foreach (self::Toggles as $toggle) {
            $settings[$toggle['name']] = (bool) $settings[$toggle['name']];
        }
        $settings['copyright'] = str_replace('{year}', (string) now()->year, (string) ($settings['copyright'] ?? '© {year} NAMTECH. All Rights Reserved'));

        return $settings;
    }

    public static function defaultImage(): ?Media
    {
        $id = Setting::get('default_og_image_id');

        return $id ? Media::query()->find($id) : null;
    }
}
