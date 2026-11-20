<?php

namespace App\Sections;

use App\Support\Fields;

class SectionRegistry
{
    private static ?array $cache = null;

    public static function definitions(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $cta = fn (string $prefix, string $label) => [
            ['name' => $prefix.'_label', 'type' => 'text', 'label' => $label.' label', 'max' => 40],
            ['name' => $prefix.'_url', 'type' => 'url', 'label' => $label.' link'],
        ];

        $heading = [
            ['name' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow label', 'max' => 60, 'help' => 'Small pill label above the heading.'],
            ['name' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 160],
        ];

        $base = [
            'home_hero' => [
                'label' => 'Homepage hero',
                'group' => 'Homepage',
                'description' => 'Purple split hero with rotating slides and topic badges.',
                'fields' => [
                    ['name' => 'badges', 'type' => 'repeater', 'label' => 'Topic badges', 'maxItems' => 4, 'fields' => [
                        ['name' => 'text', 'type' => 'text', 'label' => 'Badge', 'max' => 30, 'required' => true],
                    ]],
                    ['name' => 'slides', 'type' => 'repeater', 'label' => 'Slides', 'minItems' => 1, 'maxItems' => 5, 'fields' => [
                        ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 90, 'required' => true],
                        ['name' => 'body', 'type' => 'textarea', 'label' => 'Text', 'max' => 400],
                        ['name' => 'image', 'type' => 'image', 'label' => 'Image', 'required' => true],
                        ...$cta('cta', 'Button'),
                    ]],
                ],
            ],
            'feature_blocks' => [
                'label' => 'Alternating feature blocks',
                'group' => 'Homepage',
                'description' => 'Image and text blocks in pairs (e.g. "A New Blueprint for Manufacturing Education"): overlapping layout with equal-size images on desktop, cards that stack while scrolling on phones.',
                'fields' => [
                    ...$heading,
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Blocks', 'minItems' => 1, 'maxItems' => 8, 'fields' => [
                        ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 80, 'required' => true],
                        ['name' => 'body', 'type' => 'textarea', 'label' => 'Text', 'max' => 600, 'required' => true],
                        ['name' => 'image', 'type' => 'image', 'label' => 'Image', 'required' => true],
                        ['name' => 'link_url', 'type' => 'url', 'label' => 'Read more link'],
                    ]],
                ],
            ],
            'academics_tabs' => [
                'label' => 'Schools, centres and programs tabs',
                'group' => 'Homepage',
                'description' => 'Tabbed carousels populated from published schools, centres and programs.',
                'fields' => [
                    ...$heading,
                    ['name' => 'schools_label', 'type' => 'text', 'label' => 'Schools tab label', 'max' => 40],
                    ['name' => 'centres_label', 'type' => 'text', 'label' => 'Centres tab label', 'max' => 40],
                    ['name' => 'programs_label', 'type' => 'text', 'label' => 'Programs tab label', 'max' => 40],
                    ['name' => 'schools_heading', 'type' => 'text', 'label' => 'Schools tab: heading', 'max' => 160, 'help' => 'Replaces the section heading while this tab is open.'],
                    ['name' => 'schools_body', 'type' => 'textarea', 'label' => 'Schools tab: introduction', 'max' => 1000],
                    ['name' => 'centres_heading', 'type' => 'text', 'label' => 'Centres tab: heading', 'max' => 160],
                    ['name' => 'centres_body', 'type' => 'textarea', 'label' => 'Centres tab: introduction', 'max' => 1000],
                    ['name' => 'programs_heading', 'type' => 'text', 'label' => 'Programs tab: heading', 'max' => 160],
                    ['name' => 'programs_body', 'type' => 'textarea', 'label' => 'Programs tab: introduction', 'max' => 1000, 'help' => 'Programs are grouped as UG Programs, PG Programs, Executive Education and iPTP.'],
                    ['name' => 'default_tab', 'type' => 'select', 'label' => 'Tab shown first', 'options' => ['schools' => 'Schools', 'centres' => 'Centres', 'programs' => 'Programs']],
                    ['name' => 'featured_only', 'type' => 'boolean', 'label' => 'Only featured programs'],
                ],
            ],
            'partners_showcase' => [
                'label' => 'Partnerships showcase',
                'group' => 'Homepage',
                'description' => 'Heading, partner logo strip and partner story carousel.',
                'fields' => [
                    ...$heading,
                    ['name' => 'body', 'type' => 'textarea', 'label' => 'Introduction', 'max' => 600],
                    ...$cta('cta', 'Button'),
                    ...$cta('cta2', 'Second button'),
                    ...$cta('cta3', 'Third button'),
                    ['name' => 'category', 'type' => 'reference', 'source' => 'partnership_category_slugs', 'label' => 'Limit to category'],
                    ['name' => 'show_stories', 'type' => 'boolean', 'label' => 'Show partner stories carousel'],
                    ['name' => 'stories_layout', 'type' => 'select', 'label' => 'Partner story cards', 'options' => ['photo' => 'Photo and text', 'text' => 'Text only (narrow cards)']],
                    ['name' => 'sample_image', 'type' => 'image', 'label' => 'Sample story image when no partner stories are available'],
                    ['name' => 'story_images', 'type' => 'repeater', 'label' => 'Featured partners and cover images', 'maxItems' => 8, 'fields' => [
                        ['name' => 'slug', 'type' => 'slug', 'label' => 'Partner slug', 'required' => true],
                        ['name' => 'image', 'type' => 'image', 'label' => 'Story cover', 'required' => true],
                    ]],
                    ['name' => 'hide_logos', 'type' => 'boolean', 'label' => 'Hide the partner logo row'],
                    ...$cta('stories_cta', 'Stories button'),
                ],
            ],
            'faculty_showcase' => [
                'label' => 'Faculty showcase',
                'group' => 'Homepage',
                'description' => 'Introduction with a carousel of featured faculty profiles.',
                'fields' => [
                    ...$heading,
                    ['name' => 'body', 'type' => 'textarea', 'label' => 'Introduction', 'max' => 600],
                    ...$cta('cta', 'Button'),
                    ['name' => 'school', 'type' => 'reference', 'source' => 'school_slugs', 'label' => 'Limit to school'],
                ],
            ],
            'labs_showcase' => [
                'label' => 'Labs media carousel',
                'group' => 'Homepage',
                'description' => 'Purple band with large image slides ("Learn by Building").',
                'fields' => [
                    ...$heading,
                    ['name' => 'body', 'type' => 'textarea', 'label' => 'Introduction', 'max' => 400],
                    ...$cta('cta', 'Button'),
                    ['name' => 'slides', 'type' => 'repeater', 'label' => 'Slides', 'minItems' => 1, 'maxItems' => 8, 'fields' => [
                        ['name' => 'image', 'type' => 'image', 'label' => 'Image', 'required' => true],
                        ['name' => 'caption_start', 'type' => 'text', 'label' => 'Caption (left)', 'max' => 60],
                        ['name' => 'caption_end', 'type' => 'text', 'label' => 'Caption (right)', 'max' => 60],
                        ['name' => 'video_url', 'type' => 'url', 'label' => 'Video link (optional, loads on click)'],
                    ]],
                ],
            ],
            'campus_showcase' => [
                'label' => 'Campus showcase',
                'group' => 'Homepage',
                'description' => 'Campus introduction, feature image, gallery thumbnails and virtual tour link.',
                'fields' => [
                    ...$heading,
                    ['name' => 'body', 'type' => 'textarea', 'label' => 'Introduction', 'max' => 1000],
                    ['name' => 'feature_title', 'type' => 'text', 'label' => 'Feature title', 'max' => 100],
                    ['name' => 'feature_body', 'type' => 'textarea', 'label' => 'Feature text', 'max' => 400],
                    ['name' => 'gallery', 'type' => 'reference', 'source' => 'gallery_slugs', 'label' => 'Gallery', 'required' => true],
                    ...$cta('cta', 'Button'),
                    ['name' => 'tour_label', 'type' => 'text', 'label' => 'Virtual tour button label', 'max' => 40],
                ],
            ],
            'mission' => [
                'label' => 'Mission band',
                'group' => 'Homepage',
                'description' => '"Built to Build India" band with initiative cards and approved statistics.',
                'fields' => [
                    ['name' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 120],
                    ['name' => 'body', 'type' => 'textarea', 'label' => 'Text', 'max' => 1200],
                    ['name' => 'statistics_group', 'type' => 'reference', 'source' => 'statistic_groups', 'label' => 'Approved statistics to show'],
                    ['name' => 'limit', 'type' => 'number', 'label' => 'Maximum initiative cards', 'min' => 1, 'max' => 12],
                    ...$cta('cta', 'Button'),
                ],
            ],
            'news_carousel' => [
                'label' => 'News and insights carousel',
                'group' => 'Homepage',
                'description' => 'Latest published posts in a carousel.',
                'fields' => [
                    ['name' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 120],
                    ['name' => 'post_type', 'type' => 'reference', 'source' => 'post_types', 'label' => 'Post type (blank = all)'],
                    ['name' => 'limit', 'type' => 'number', 'label' => 'Number of posts', 'min' => 1, 'max' => 12],
                    ['name' => 'sample_image', 'type' => 'image', 'label' => 'Sample insight image when no posts are available'],
                    ...$cta('cta', 'Button'),
                ],
            ],

            'page_hero' => [
                'label' => 'Page hero',
                'group' => 'Page structure',
                'description' => 'Purple split hero with title, text, image, buttons and optional statistics.',
                'fields' => [
                    ['name' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow label', 'max' => 60],
                    ['name' => 'title', 'type' => 'text', 'label' => 'Title (defaults to page title)', 'max' => 120],
                    ['name' => 'body', 'type' => 'textarea', 'label' => 'Text', 'max' => 700],
                    ['name' => 'image', 'type' => 'image', 'label' => 'Image'],
                    ...$cta('cta', 'Primary button'),
                    ...$cta('secondary', 'Secondary button'),
                    ['name' => 'button_size', 'type' => 'select', 'label' => 'Button size', 'options' => ['standard' => 'Standard', 'large' => 'Large (as on program pages)']],
                    ['name' => 'statistics_group', 'type' => 'reference', 'source' => 'statistic_groups', 'label' => 'Approved statistics to show'],
                ],
            ],
            'intro' => [
                'label' => 'Heading and introduction',
                'group' => 'Text',
                'description' => 'Heading on the left, text on the right.',
                'fields' => [
                    ...$heading,
                    ['name' => 'body', 'type' => 'richtext', 'label' => 'Text'],
                    ...$cta('cta', 'Button'),
                ],
            ],
            'rich_text' => [
                'label' => 'Rich text',
                'group' => 'Text',
                'description' => 'Formatted article-style text with headings, lists, links and approved embeds.',
                'fields' => [
                    ['name' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 160],
                    ['name' => 'body', 'type' => 'richtext', 'label' => 'Text', 'required' => true],
                ],
            ],
            'image_text' => [
                'label' => 'Image and text',
                'group' => 'Media',
                'description' => 'Image beside text, left or right.',
                'fields' => [
                    ...$heading,
                    ['name' => 'body', 'type' => 'richtext', 'label' => 'Text'],
                    ['name' => 'image', 'type' => 'image', 'label' => 'Image', 'required' => true],
                    ['name' => 'image_position', 'type' => 'select', 'label' => 'Image position', 'options' => ['left' => 'Left', 'right' => 'Right']],
                    ...$cta('cta', 'Button'),
                ],
            ],
            'card_grid' => [
                'label' => 'Icon card grid',
                'group' => 'Cards',
                'description' => 'Grid of icon cards (values, approach, eligibility, etc.).',
                'fields' => [
                    ...$heading,
                    ['name' => 'body', 'type' => 'textarea', 'label' => 'Introduction', 'max' => 600],
                    ['name' => 'columns', 'type' => 'select', 'label' => 'Columns on desktop', 'options' => ['2' => '2', '3' => '3', '4' => '4']],
                    ['name' => 'tone', 'type' => 'select', 'label' => 'Background', 'options' => ['white' => 'White', 'lavender' => 'Lavender', 'gradient' => 'Lavender gradient', 'purple' => 'Purple']],
                    ['name' => 'cards', 'type' => 'repeater', 'label' => 'Cards', 'minItems' => 1, 'maxItems' => 12, 'fields' => [
                        ['name' => 'icon', 'type' => 'select', 'label' => 'Icon', 'options' => self::icons()],
                        ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 80, 'required' => true],
                        ['name' => 'body', 'type' => 'textarea', 'label' => 'Text', 'max' => 400],
                        ['name' => 'link_url', 'type' => 'url', 'label' => 'Link'],
                    ]],
                ],
            ],
            'stats_band' => [
                'label' => 'Statistics band',
                'group' => 'Cards',
                'description' => 'Row of approved statistics.',
                'fields' => [
                    ...$heading,
                    ['name' => 'statistics_group', 'type' => 'reference', 'source' => 'statistic_groups', 'label' => 'Statistics group', 'required' => true],
                ],
            ],
            'media_cards' => [
                'label' => 'Image card carousel',
                'group' => 'Cards',
                'description' => 'Carousel of image cards (labs, stories, initiatives).',
                'fields' => [
                    ...$heading,
                    ['name' => 'body', 'type' => 'textarea', 'label' => 'Introduction', 'max' => 600],
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Cards', 'minItems' => 1, 'maxItems' => 16, 'fields' => [
                        ['name' => 'image', 'type' => 'image', 'label' => 'Image', 'required' => true],
                        ['name' => 'logo', 'type' => 'image', 'label' => 'Partner logo (optional)'],
                        ['name' => 'icon', 'type' => 'select', 'label' => 'Icon (optional)', 'options' => self::icons()],
                        ['name' => 'tag', 'type' => 'text', 'label' => 'Tag', 'max' => 60, 'help' => 'Not shown on the "Large centred slides" or "Cards with partner logo" styles.'],
                        ['name' => 'date', 'type' => 'text', 'label' => 'Date', 'max' => 40, 'help' => 'Only shown on the "Image on top, lavender body" style.'],
                        ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 140, 'required' => true],
                        ['name' => 'subtitle', 'type' => 'text', 'label' => 'Subtitle', 'max' => 160, 'help' => 'Only shown on the "Icon, text and image", "Image with lavender story panel" and "Image on top, lavender body" styles.'],
                        ['name' => 'body', 'type' => 'textarea', 'label' => 'Text', 'max' => 800],
                        ['name' => 'meta', 'type' => 'text', 'label' => 'Highlight line', 'max' => 120, 'help' => 'Only shown on the "Image with lavender story panel" and "Cards with partner logo" styles.'],
                        ['name' => 'sample_metrics', 'type' => 'boolean', 'label' => 'Label these figures as samples'],
                        ['name' => 'metrics', 'type' => 'repeater', 'label' => 'Story figures', 'maxItems' => 3, 'fields' => [
                            ['name' => 'value', 'type' => 'text', 'label' => 'Value', 'max' => 20, 'required' => true],
                            ['name' => 'label', 'type' => 'text', 'label' => 'Description', 'max' => 60, 'required' => true],
                        ]],
                        ['name' => 'link_url', 'type' => 'url', 'label' => 'Link'],
                    ]],
                ],
            ],
            'tabs' => [
                'label' => 'Tabbed content',
                'group' => 'Text',
                'description' => 'Accessible tabs, each with text and an optional image.',
                'fields' => [
                    ...$heading,
                    ['name' => 'body', 'type' => 'textarea', 'label' => 'Introduction', 'max' => 600],
                    ['name' => 'tabs', 'type' => 'repeater', 'label' => 'Tabs', 'minItems' => 2, 'maxItems' => 6, 'fields' => [
                        ['name' => 'label', 'type' => 'text', 'label' => 'Tab label', 'max' => 40, 'required' => true],
                        ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 120],
                        ['name' => 'body', 'type' => 'richtext', 'label' => 'Text'],
                        ['name' => 'image', 'type' => 'image', 'label' => 'Image'],
                        ['name' => 'panel_anchor', 'type' => 'slug', 'label' => 'Section anchor for tab content'],
                    ]],
                ],
            ],
            'faq' => [
                'label' => 'FAQ accordion',
                'group' => 'Text',
                'description' => 'Questions and answers.',
                'fields' => [
                    ['name' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 120],
                    ['name' => 'body', 'type' => 'textarea', 'label' => 'Introduction', 'max' => 400],
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Questions', 'minItems' => 1, 'maxItems' => 30, 'fields' => [
                        ['name' => 'question', 'type' => 'text', 'label' => 'Question', 'max' => 200, 'required' => true],
                        ['name' => 'answer', 'type' => 'richtext', 'label' => 'Answer', 'required' => true],
                    ]],
                ],
            ],
            'steps' => [
                'label' => 'Process steps / timeline',
                'group' => 'Text',
                'description' => 'Numbered steps (admission process, milestones).',
                'fields' => [
                    ...$heading,
                    ['name' => 'body', 'type' => 'textarea', 'label' => 'Introduction', 'max' => 400],
                    ['name' => 'image', 'type' => 'image', 'label' => 'Image'],
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Steps', 'minItems' => 1, 'maxItems' => 12, 'fields' => [
                        ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 120, 'required' => true],
                        ['name' => 'body', 'type' => 'textarea', 'label' => 'Text', 'max' => 400],
                    ]],
                ],
            ],
            'people' => [
                'label' => 'Faculty / people grid',
                'group' => 'Listings',
                'description' => 'Published faculty profiles, optionally filtered by school.',
                'fields' => [
                    ...$heading,
                    ['name' => 'body', 'type' => 'textarea', 'label' => 'Introduction', 'max' => 400],
                    ['name' => 'members', 'type' => 'repeater', 'label' => 'Specific people, in this order', 'maxItems' => 48, 'help' => 'Leave empty to list everyone in the school chosen below.', 'fields' => [
                        ['name' => 'faculty', 'type' => 'reference', 'source' => 'faculty_slugs', 'label' => 'Faculty profile', 'required' => true],
                    ]],
                    ['name' => 'school', 'type' => 'reference', 'source' => 'school_slugs', 'label' => 'Limit to school'],
                    ['name' => 'limit', 'type' => 'number', 'label' => 'Maximum profiles', 'min' => 1, 'max' => 48],
                    ...$cta('cta', 'Button'),
                ],
            ],
            'programs_list' => [
                'label' => 'Programs list',
                'group' => 'Listings',
                'description' => 'Published programs, optionally filtered by level or school.',
                'fields' => [
                    ...$heading,
                    ['name' => 'level', 'type' => 'reference', 'source' => 'program_levels', 'label' => 'Level'],
                    ['name' => 'school', 'type' => 'reference', 'source' => 'school_slugs', 'label' => 'School'],
                ],
            ],
            'post_list' => [
                'label' => 'Latest posts',
                'group' => 'Listings',
                'description' => 'Grid of the latest published news, blogs or press releases.',
                'fields' => [
                    ['name' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 120],
                    ['name' => 'post_type', 'type' => 'reference', 'source' => 'post_types', 'label' => 'Post type (blank = all)'],
                    ['name' => 'category', 'type' => 'reference', 'source' => 'category_slugs', 'label' => 'Category'],
                    ['name' => 'limit', 'type' => 'number', 'label' => 'Number of posts', 'min' => 1, 'max' => 12],
                    ...$cta('cta', 'Button'),
                ],
            ],
            'logo_strip' => [
                'label' => 'Partner logo strip',
                'group' => 'Listings',
                'description' => 'Logos of published partners.',
                'fields' => [
                    ['name' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 120],
                    ['name' => 'category', 'type' => 'reference', 'source' => 'partnership_category_slugs', 'label' => 'Limit to category'],
                ],
            ],
            'testimonials' => [
                'label' => 'Testimonials',
                'group' => 'Cards',
                'description' => 'Quotes with name, role and photo.',
                'fields' => [
                    ...$heading,
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Quotes', 'minItems' => 1, 'maxItems' => 10, 'fields' => [
                        ['name' => 'quote', 'type' => 'textarea', 'label' => 'Quote', 'max' => 1000, 'required' => true],
                        ['name' => 'name', 'type' => 'text', 'label' => 'Name', 'max' => 80, 'required' => true],
                        ['name' => 'role', 'type' => 'text', 'label' => 'Role / organisation', 'max' => 200],
                        ['name' => 'image', 'type' => 'image', 'label' => 'Photo'],
                    ]],
                ],
            ],
            'gallery' => [
                'label' => 'Image gallery',
                'group' => 'Media',
                'description' => 'Gallery grid with lightbox and optional virtual tour.',
                'fields' => [
                    ['name' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 120],
                    ['name' => 'gallery', 'type' => 'reference', 'source' => 'gallery_slugs', 'label' => 'Gallery', 'required' => true],
                    ['name' => 'show_tour', 'type' => 'boolean', 'label' => 'Show embedded virtual tour (loads on click)'],
                ],
            ],
            'video' => [
                'label' => 'Video',
                'group' => 'Media',
                'description' => 'YouTube or Vimeo video that only loads after the visitor presses play.',
                'fields' => [
                    ['name' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 120],
                    ['name' => 'video_url', 'type' => 'url', 'label' => 'YouTube or Vimeo link', 'required' => true],
                    ['name' => 'poster', 'type' => 'image', 'label' => 'Poster image', 'required' => true],
                    ['name' => 'caption', 'type' => 'text', 'label' => 'Caption', 'max' => 200],
                ],
            ],
            'documents' => [
                'label' => 'Document links',
                'group' => 'Text',
                'description' => 'List of downloadable documents or reports (links).',
                'fields' => [
                    ...$heading,
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Documents', 'minItems' => 1, 'maxItems' => 40, 'fields' => [
                        ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 160, 'required' => true],
                        ['name' => 'file', 'type' => 'file', 'label' => 'PDF from the media library'],
                        ['name' => 'url', 'type' => 'url', 'label' => 'Or a link'],
                        ['name' => 'meta', 'type' => 'text', 'label' => 'Format / size', 'max' => 40],
                    ]],
                ],
            ],
            'cta_band' => [
                'label' => 'Call-to-action band',
                'group' => 'Page structure',
                'description' => 'Lavender band such as "Have questions? Get in touch".',
                'fields' => [
                    ['name' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 120, 'required' => true],
                    ['name' => 'body', 'type' => 'textarea', 'label' => 'Text', 'max' => 400],
                    ...$cta('cta', 'Button'),
                ],
            ],
            'enquiry_form' => [
                'label' => 'Enquiry form',
                'group' => 'Forms',
                'description' => 'Contact or program enquiry form with contact details.',
                'fields' => [
                    ['name' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 120],
                    ['name' => 'body', 'type' => 'textarea', 'label' => 'Introduction', 'max' => 600],
                    ['name' => 'form_type', 'type' => 'select', 'label' => 'Form', 'options' => ['contact' => 'General contact', 'program' => 'Program enquiry']],
                    ['name' => 'show_contact_details', 'type' => 'boolean', 'label' => 'Show contact details beside the form'],
                ],
            ],
        ];

        foreach (self::layoutOptions() as $type => $fields) {
            $base[$type]['fields'] = array_merge($base[$type]['fields'], $fields);
        }

        return self::$cache = $base + self::innerPageDefinitions($cta, $heading);
    }

    private static function layoutOptions(): array
    {
        $tone = ['name' => 'tone', 'type' => 'select', 'label' => 'Background', 'options' => ['white' => 'White', 'light' => 'Light grey', 'lavender' => 'Lavender', 'gradient' => 'Lavender gradient', 'purple' => 'Purple']];

        return [
            'partners_showcase' => [$tone],
            'page_hero' => [
                ['name' => 'layout', 'type' => 'select', 'label' => 'Hero style', 'options' => ['split' => 'Purple with image', 'light' => 'Light lavender with image (dark text)', 'plain' => 'Plain title and text (no image)']],
                ['name' => 'meta', 'type' => 'repeater', 'label' => 'Detail items (icon + text)', 'maxItems' => 4, 'fields' => [
                    ['name' => 'icon', 'type' => 'select', 'label' => 'Icon', 'options' => self::icons()],
                    ['name' => 'text', 'type' => 'text', 'label' => 'Text', 'max' => 80, 'required' => true],
                ]],
            ],
            'intro' => [
                ['name' => 'icon', 'type' => 'select', 'label' => 'Icon above heading', 'options' => self::icons()],
                $tone,
                ['name' => 'align', 'type' => 'select', 'label' => 'Layout', 'options' => ['split' => 'Heading left, text right', 'center' => 'Centred heading with text below', 'statement' => 'Centred large statement text']],
            ],
            'rich_text' => [
                ['name' => 'toc', 'type' => 'boolean', 'label' => 'Show "Sections" navigation built from H2 headings'],
                ['name' => 'width', 'type' => 'select', 'label' => 'Width', 'options' => ['narrow' => 'Narrow (article)', 'wide' => 'Wide']],
            ],
            'image_text' => [
                ['name' => 'layout', 'type' => 'select', 'label' => 'Style', 'options' => ['side' => 'Side by side', 'panel' => 'Image with purple text panel', 'staggered' => 'Staggered (offset)', 'research' => 'Research photo and lavender text panel']],
                $tone,
            ],
            'card_grid' => [
                ['name' => 'style', 'type' => 'select', 'label' => 'Card style', 'options' => ['joined' => 'Joined outline grid', 'joined_light' => 'Joined grid with light lines and tall cards', 'outlined' => 'Separate outlined cards', 'filled' => 'Filled grey cards', 'lines' => 'Icon and title with a line above (no box)', 'numbered' => 'Numbered columns with a line above (01, 02…); button below']],
                ['name' => 'list_label', 'type' => 'text', 'label' => 'Label above the cards', 'max' => 120],
                ['name' => 'footer', 'type' => 'textarea', 'label' => 'Note below the cards (e.g. a disclaimer)', 'max' => 400],
                ['name' => 'cta_label', 'type' => 'text', 'label' => 'Button label', 'max' => 40],
                ['name' => 'cta_url', 'type' => 'url', 'label' => 'Button link'],
            ],
            'media_cards' => [
                ['name' => 'layout', 'type' => 'select', 'label' => 'Card style', 'options' => [
                    'cards' => 'Image cards', 'showcase' => 'Large centred slides', 'horizontal' => 'Icon, text and image (wide)',
                    'story' => 'Image with lavender story panel', 'logo' => 'Cards with partner logo', 'tall' => 'Image on top, lavender body',
                    'feature' => 'Large lavender card beside a square image, centred heading',
                    'tall_wide' => 'Wide cards: image on top, lavender body (e.g. Ranks & Accreditations)',
                    'labs' => 'Photo cards with title and text, no box (e.g. Living Labs on a purple band)',
                    'centers' => 'Wide photo cards with partner logo, title, text and View link (e.g. Our Centers)',
                    'partnership_tiles' => 'Outlined icon cards for partnership models',
                ]],
                ['name' => 'head_align', 'type' => 'select', 'label' => 'Heading', 'options' => ['split' => 'Heading left, introduction right', 'center' => 'Centred heading']],
                $tone,
                ['name' => 'cta_label', 'type' => 'text', 'label' => 'Button label', 'max' => 40],
                ['name' => 'cta_url', 'type' => 'url', 'label' => 'Button link'],
                ['name' => 'footer_label', 'type' => 'text', 'label' => 'Link under cards label', 'max' => 60],
                ['name' => 'footer_url', 'type' => 'url', 'label' => 'Link under cards'],
                ['name' => 'card_link_label', 'type' => 'text', 'label' => 'Card link text', 'max' => 30, 'help' => 'e.g. Read, Know More, View'],
            ],
            'tabs' => [
                ['name' => 'layout', 'type' => 'select', 'label' => 'Style', 'options' => ['standard' => 'Standard', 'centered' => 'Centred narrow text', 'section_panels' => 'Sections inside each tab']],
                $tone,
            ],
            'faq' => [
                ['name' => 'layout', 'type' => 'select', 'label' => 'Style', 'options' => ['split' => 'Heading beside questions', 'centered' => 'Centred in a lavender panel']],
            ],
            'steps' => [
                ['name' => 'layout', 'type' => 'select', 'label' => 'Style', 'options' => ['list' => 'Numbered list', 'panel' => 'Image beside a "Steps" panel']],
                ['name' => 'panel_title', 'type' => 'text', 'label' => 'Panel title', 'max' => 40],
            ],
            'people' => [
                ['name' => 'layout', 'type' => 'select', 'label' => 'Style', 'options' => ['grid' => 'Grid', 'carousel' => 'Carousel with Residential/Visiting tabs', 'list' => 'Photo list (mobile style)']],
                $tone,
            ],
            'testimonials' => [
                ['name' => 'layout', 'type' => 'select', 'label' => 'Style', 'options' => ['quotes' => 'Quote cards', 'portrait' => 'Portrait with purple quote box', 'spotlight' => 'Name list with large portrait']],
                ['name' => 'body', 'type' => 'textarea', 'label' => 'Introduction', 'max' => 600],
                $tone,
                ['name' => 'image', 'type' => 'image', 'label' => 'Background photo (name list style)', 'help' => 'Shown behind the quote when the selected person has no photo.'],
                ['name' => 'photo_caption', 'type' => 'text', 'label' => 'Background photo caption', 'max' => 80],
                ['name' => 'cta_label', 'type' => 'text', 'label' => 'Button label (name list style)', 'max' => 40],
                ['name' => 'cta_url', 'type' => 'url', 'label' => 'Button link (name list style)'],
            ],
            'documents' => [
                ['name' => 'layout', 'type' => 'select', 'label' => 'Style', 'options' => ['grid' => 'Card grid', 'list' => 'List beside heading']],
                ['name' => 'body', 'type' => 'textarea', 'label' => 'Introduction', 'max' => 600],
                $tone,
            ],
            'post_list' => [
                ['name' => 'layout', 'type' => 'select', 'label' => 'Style', 'options' => ['grid' => 'Card grid', 'carousel' => 'Carousel']],
            ],
            'stats_band' => [
                ['name' => 'layout', 'type' => 'select', 'label' => 'Style', 'options' => ['band' => 'Lavender band', 'dividers' => 'Large figures with dividers']],
            ],
            'cta_band' => [$tone],
            'logo_strip' => [
                $tone,
                ['name' => 'layout', 'type' => 'select', 'label' => 'Style', 'options' => ['centred' => 'Centred logos with heading', 'band' => 'Full-width row (under a hero, no heading)']],
                ['name' => 'sample_when_empty', 'type' => 'boolean', 'label' => 'Show sample partner when there are no logos'],
                ['name' => 'custom_logos', 'type' => 'repeater', 'label' => 'Choose logos (optional)', 'help' => 'When set, these logos are shown in this order instead of the partner list.', 'maxItems' => 12, 'fields' => [
                    ['name' => 'logo', 'type' => 'image', 'label' => 'Logo', 'required' => true],
                    ['name' => 'name', 'type' => 'text', 'label' => 'Organisation name', 'max' => 120, 'required' => true],
                ]],
            ],
        ];
    }

    private static function innerPageDefinitions(callable $cta, array $heading): array
    {
        $tone = ['name' => 'tone', 'type' => 'select', 'label' => 'Background', 'options' => ['white' => 'White', 'light' => 'Light grey', 'lavender' => 'Lavender', 'gradient' => 'Lavender gradient', 'purple' => 'Purple']];
        $body = ['name' => 'body', 'type' => 'textarea', 'label' => 'Introduction', 'max' => 800];
        $icon = ['name' => 'icon', 'type' => 'select', 'label' => 'Icon', 'options' => self::icons()];

        return [
            'subnav' => [
                'label' => 'Sub-navigation tabs',
                'group' => 'Page structure',
                'description' => 'Row of tab links under the hero (e.g. About Us · Board of Directors · …). The current page is highlighted.',
                'fields' => [
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Links', 'minItems' => 1, 'maxItems' => 10, 'fields' => [
                        ['name' => 'label', 'type' => 'text', 'label' => 'Label', 'max' => 60, 'required' => true],
                        ['name' => 'url', 'type' => 'url', 'label' => 'Link', 'required' => true],
                    ]],
                ],
            ],
            'image_statement' => [
                'label' => 'Full-width image with statement',
                'group' => 'Media',
                'description' => 'Large photo with a text overlay (e.g. Our Purpose, Our Vision).',
                'fields' => [
                    ...$heading,
                    ['name' => 'body', 'type' => 'textarea', 'label' => 'Text', 'max' => 600],
                    ['name' => 'image', 'type' => 'image', 'label' => 'Image', 'required' => true],
                    ['name' => 'position', 'type' => 'select', 'label' => 'Text position', 'options' => ['right' => 'Right', 'left' => 'Left']],
                ],
            ],
            'value_list' => [
                'label' => 'Values list',
                'group' => 'Text',
                'description' => 'Heading and text on the left, icon list with dividers on the right (e.g. Our Core Values).',
                'fields' => [
                    ...$heading, $body,
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Items', 'minItems' => 1, 'maxItems' => 10, 'fields' => [
                        $icon,
                        ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 160, 'required' => true],
                        ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 800],
                    ]],
                ],
            ],
            'stat_cards' => [
                'label' => 'Statistic cards',
                'group' => 'Cards',
                'description' => 'Large figures in cards with icons (e.g. Outcomes, Why Employers Choose NAMTECH Talent). Only use verified figures.',
                'fields' => [
                    ['name' => 'layout', 'type' => 'select', 'label' => 'Style', 'options' => ['cards' => 'White outlined cards', 'lavender' => 'Lavender cards, three then two (e.g. Scholarship Criteria)']],
                    ...$heading, $body, $tone,
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Figures', 'minItems' => 1, 'maxItems' => 6, 'fields' => [
                        $icon,
                        ['name' => 'value', 'type' => 'text', 'label' => 'Figure', 'max' => 20, 'required' => true],
                        ['name' => 'label', 'type' => 'text', 'label' => 'Label', 'max' => 80, 'required' => true],
                        ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 300],
                    ]],
                ],
            ],
            'timeline' => [
                'label' => 'Timeline',
                'group' => 'Text',
                'description' => 'Story told year by year with a year scale (e.g. Our Story).',
                'fields' => [
                    ...$heading, $body,
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Milestones', 'minItems' => 1, 'maxItems' => 20, 'fields' => [
                        ['name' => 'year', 'type' => 'text', 'label' => 'Year', 'max' => 10, 'required' => true],
                        ['name' => 'title', 'type' => 'text', 'label' => 'Title (e.g. June, 2022)', 'max' => 80],
                        ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 800],
                        ['name' => 'image', 'type' => 'image', 'label' => 'Image'],
                    ]],
                ],
            ],
            'video_feature' => [
                'label' => 'Video feature / quote',
                'group' => 'Media',
                'description' => 'Photo or video beside a purple text panel, or a full-width photo with a quote (e.g. Here\'s What the Dean Says).',
                'fields' => [
                    ['name' => 'layout', 'type' => 'select', 'label' => 'Style', 'options' => ['split' => 'Image beside purple panel', 'overlay' => 'Full-width photo with quote']],
                    ...$heading,
                    ['name' => 'body', 'type' => 'textarea', 'label' => 'Text or quote', 'max' => 1000],
                    ['name' => 'name', 'type' => 'text', 'label' => 'Quoted person', 'max' => 120],
                    ['name' => 'role', 'type' => 'text', 'label' => 'Their role', 'max' => 160],
                    ['name' => 'image', 'type' => 'image', 'label' => 'Image', 'required' => true],
                    ['name' => 'flip_image', 'type' => 'boolean', 'label' => 'Mirror the photo (flip left to right)'],
                    ['name' => 'video_url', 'type' => 'url', 'label' => 'YouTube or Vimeo link (optional)'],
                ],
            ],
            'leaders_grid' => [
                'label' => 'Leadership profiles',
                'group' => 'Listings',
                'description' => 'Published leadership profiles from one group (Board, Advisory Council, Management Team…).',
                'fields' => [
                    ...$heading, $body,
                    ['name' => 'group', 'type' => 'reference', 'source' => 'leader_groups', 'label' => 'Group', 'required' => true],
                    ['name' => 'layout', 'type' => 'select', 'label' => 'Style', 'options' => ['grid' => 'Photo grid with "Know More"', 'contacts' => 'Contact cards carousel']],
                ],
            ],
            'achievements' => [
                'label' => 'Ranks / awards list',
                'group' => 'Text',
                'description' => 'Rows of rankings or awards with year/figure, title, text and icon.',
                'fields' => [
                    ...$heading, $body, $tone,
                    ['name' => 'layout', 'type' => 'select', 'label' => 'Style', 'options' => ['awards' => 'Awards (year)', 'ranks' => 'Rankings (logo and rank)']],
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Rows', 'minItems' => 1, 'maxItems' => 20, 'fields' => [
                        ['name' => 'logo', 'type' => 'image', 'label' => 'Logo (rankings)'],
                        ['name' => 'value', 'type' => 'text', 'label' => 'Year or rank', 'max' => 20, 'required' => true],
                        ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 120, 'required' => true],
                        ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 300],
                        ['name' => 'icon', 'type' => 'select', 'label' => 'Icon', 'options' => ['award' => 'Trophy', 'sparkle' => 'Sparkle']],
                    ]],
                ],
            ],
            'pillars' => [
                'label' => 'Core pillars (scrolling)',
                'group' => 'Cards',
                'description' => 'Sticky list of pillar names beside stacked cards that highlight as you scroll.',
                'fields' => [
                    ...$heading,
                    ['name' => 'body', 'type' => 'textarea', 'label' => 'Introduction', 'max' => 600],
                    ['name' => 'layout', 'type' => 'select', 'label' => 'Style', 'options' => ['nav' => 'Heading in the side column, outlined cards', 'split' => 'Heading across the top, lavender cards (e.g. Ways to Collaborate)', 'pill' => 'Pill and names beside lavender cards (e.g. School Core Pillars)']],
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Pillars', 'minItems' => 1, 'maxItems' => 10, 'fields' => [
                        ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 120, 'required' => true],
                        ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 700],
                        ['name' => 'image', 'type' => 'image', 'label' => 'Image'],
                    ]],
                ],
            ],
            'programs_tabs' => [
                'label' => 'Programs by level (tabs)',
                'group' => 'Listings',
                'description' => 'Centred heading with Undergraduate / Master\'s / Executive tabs of published programs.',
                'fields' => [
                    ...$heading,
                    ['name' => 'school', 'type' => 'reference', 'source' => 'school_slugs', 'label' => 'Limit to school'],
                    ['name' => 'tone', 'type' => 'select', 'label' => 'Background', 'options' => ['light' => 'Light grey', 'white' => 'White']],
                    ['name' => 'tab_by', 'type' => 'select', 'label' => 'Tabs', 'options' => ['level' => 'One tab per level', 'program' => 'One tab per program']],
                    ['name' => 'card_buttons', 'type' => 'boolean', 'label' => 'Show Apply now / Learn more buttons on the cards'],
                ],
            ],
            'events_carousel' => [
                'label' => 'Upcoming events',
                'group' => 'Listings',
                'description' => 'Carousel of upcoming published events.',
                'fields' => [
                    ...$heading, $body, ...$cta('cta', 'Button'), $tone,
                    ['name' => 'layout', 'type' => 'select', 'label' => 'Card style', 'options' => ['cards' => 'Small cards', 'wide' => 'Wide card: photo beside a lavender panel with buttons']],
                    ['name' => 'category', 'type' => 'reference', 'source' => 'event_categories', 'label' => 'Event type'],
                    ['name' => 'school', 'type' => 'reference', 'source' => 'school_slugs', 'label' => 'School'],
                ],
            ],
            'publications_carousel' => [
                'label' => 'Research publications carousel',
                'group' => 'Listings',
                'description' => 'Latest published research papers.',
                'fields' => [
                    ...$heading, $body, ...$cta('cta', 'Button'),
                    ['name' => 'school', 'type' => 'reference', 'source' => 'school_slugs', 'label' => 'School'],
                ],
            ],
            'program_levels' => [
                'label' => 'Programmes by level',
                'group' => 'Listings',
                'description' => 'Image blocks per level (Bachelors, Masters…), each listing its published programs.',
                'fields' => [
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Levels', 'minItems' => 1, 'maxItems' => 6, 'fields' => [
                        ['name' => 'level', 'type' => 'reference', 'source' => 'program_levels', 'label' => 'Level', 'required' => true],
                        ['name' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 120, 'required' => true],
                        ['name' => 'body', 'type' => 'textarea', 'label' => 'Text', 'max' => 600],
                        ['name' => 'image', 'type' => 'image', 'label' => 'Image'],
                    ]],
                ],
            ],
            'program_finder' => [
                'label' => 'Program finder',
                'group' => 'Listings',
                'description' => 'Choose a school and program to see its key facts with Apply / Learn more buttons.',
                'fields' => [
                    ['name' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 120],
                    ['name' => 'body', 'type' => 'textarea', 'label' => 'Introduction', 'max' => 1000, 'help' => 'Optional text under the heading. Separate paragraphs with an empty line.'],
                    ['name' => 'level', 'type' => 'reference', 'source' => 'program_levels', 'label' => 'Level'],
                    $tone,
                ],
            ],
            'date_tables' => [
                'label' => 'Dates table',
                'group' => 'Text',
                'description' => 'Columns of rounds and dates (e.g. Important Dates & Deadlines).',
                'fields' => [
                    ...$heading, $body,
                    ['name' => 'columns', 'type' => 'repeater', 'label' => 'Columns', 'minItems' => 1, 'maxItems' => 4, 'fields' => [
                        $icon,
                        ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 60, 'required' => true],
                        ['name' => 'highlight', 'type' => 'boolean', 'label' => 'Highlight this column'],
                        ['name' => 'rows', 'type' => 'repeater', 'label' => 'Rows', 'maxItems' => 10, 'fields' => [
                            ['name' => 'label', 'type' => 'text', 'label' => 'Label', 'max' => 40, 'required' => true],
                            ['name' => 'value', 'type' => 'text', 'label' => 'Value', 'max' => 40, 'required' => true],
                        ]],
                    ]],
                    ['name' => 'footnote', 'type' => 'text', 'label' => 'Footnote', 'max' => 200],
                ],
            ],
            'eligibility' => [
                'label' => 'Eligibility criteria',
                'group' => 'Text',
                'description' => 'Numbered criteria and an icon list of eligible branches.',
                'fields' => [
                    ...$heading, $body,
                    ['name' => 'criteria', 'type' => 'repeater', 'label' => 'Criteria', 'maxItems' => 6, 'fields' => [
                        ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 400, 'required' => true],
                    ]],
                    ['name' => 'list_heading', 'type' => 'text', 'label' => 'List heading', 'max' => 80],
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'List items', 'maxItems' => 24, 'fields' => [
                        $icon,
                        ['name' => 'text', 'type' => 'text', 'label' => 'Text', 'max' => 120, 'required' => true],
                    ]],
                ],
            ],
            'checklist_cards' => [
                'label' => 'Checklist columns',
                'group' => 'Cards',
                'description' => 'Outlined columns with an icon, title and checklist (e.g. Required Documents).',
                'fields' => [
                    ...$heading, $body, $tone,
                    ['name' => 'cards', 'type' => 'repeater', 'label' => 'Columns', 'minItems' => 1, 'maxItems' => 6, 'fields' => [
                        $icon,
                        ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 80, 'required' => true],
                        ['name' => 'items', 'type' => 'repeater', 'label' => 'Checklist', 'maxItems' => 10, 'fields' => [
                            ['name' => 'text', 'type' => 'text', 'label' => 'Item', 'max' => 160, 'required' => true],
                        ]],
                    ]],
                ],
            ],
            'fees' => [
                'label' => 'Fees and scholarships',
                'group' => 'Text',
                'description' => 'Fee table, purple scholarships panel and extra fee notes.',
                'fields' => [
                    ...$heading, $body,
                    ['name' => 'fee_label', 'type' => 'text', 'label' => 'Fee label', 'max' => 60],
                    ['name' => 'fee_amount', 'type' => 'text', 'label' => 'Fee amount', 'max' => 40],
                    ['name' => 'rows', 'type' => 'repeater', 'label' => 'Fee breakdown', 'maxItems' => 10, 'fields' => [
                        ['name' => 'label', 'type' => 'text', 'label' => 'Label', 'max' => 80, 'required' => true],
                        ['name' => 'value', 'type' => 'text', 'label' => 'Amount', 'max' => 40, 'required' => true],
                    ]],
                    ['name' => 'scholarships_title', 'type' => 'text', 'label' => 'Scholarships heading', 'max' => 60],
                    ['name' => 'scholarships', 'type' => 'repeater', 'label' => 'Scholarships', 'maxItems' => 10, 'fields' => [
                        ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 80, 'required' => true],
                        ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 300],
                    ]],
                    ...$cta('link', 'Scholarships link'),
                    ['name' => 'notes', 'type' => 'repeater', 'label' => 'Notes cards', 'maxItems' => 4, 'fields' => [
                        $icon,
                        ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 80, 'required' => true],
                        ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 300],
                    ]],
                ],
            ],
            'feature_list' => [
                'label' => 'Icon feature list',
                'group' => 'Cards',
                'description' => 'Two-column icon list in a lavender panel, optionally in tabs (e.g. Education Loan Assistance, Why Partner with NAMTECH?).',
                'fields' => [
                    ...$heading, $body,
                    ['name' => 'layout', 'type' => 'select', 'label' => 'Style', 'options' => ['panel' => 'Icon list in a lavender panel', 'band' => 'Names in a full-width grey row (like a logo strip, first tab only)']],
                    ['name' => 'tabs', 'type' => 'repeater', 'label' => 'Tabs (one tab = no tab bar)', 'minItems' => 1, 'maxItems' => 8, 'fields' => [
                        ['name' => 'label', 'type' => 'text', 'label' => 'Tab label', 'max' => 40],
                        ['name' => 'items', 'type' => 'repeater', 'label' => 'Items', 'maxItems' => 16, 'fields' => [
                            $icon,
                            ['name' => 'text', 'type' => 'text', 'label' => 'Text', 'max' => 200, 'required' => true],
                        ]],
                    ]],
                ],
            ],
            'contact_cards' => [
                'label' => 'Regional contact cards',
                'group' => 'Cards',
                'description' => 'Carousel of contact cards (e.g. Meet the NAMTECH Team in Your City).',
                'fields' => [
                    ...$heading, $body,
                    ['name' => 'layout', 'type' => 'select', 'label' => 'Style', 'options' => ['region' => 'Regional contacts (location pin)', 'people' => 'People (wide cards with a person icon, e.g. Leadership Team)']],
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Contacts', 'minItems' => 1, 'maxItems' => 20, 'fields' => [
                        ['name' => 'region', 'type' => 'text', 'label' => 'Region', 'max' => 160],
                        ['name' => 'name', 'type' => 'text', 'label' => 'Name', 'max' => 120, 'required' => true],
                        ['name' => 'phone', 'type' => 'text', 'label' => 'Phone', 'max' => 40],
                        ['name' => 'email', 'type' => 'email', 'label' => 'Email'],
                    ]],
                ],
            ],
            'contact_band' => [
                'label' => 'Contact the team band',
                'group' => 'Page structure',
                'description' => 'Lavender band with named contacts (e.g. Connect with our Career Development Team).',
                'fields' => [
                    ['name' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 120, 'required' => true],
                    ['name' => 'body', 'type' => 'textarea', 'label' => 'Text', 'max' => 600],
                    ...$cta('cta', 'Button'),
                    ['name' => 'contacts_label', 'type' => 'text', 'label' => 'Contacts label', 'max' => 60],
                    ['name' => 'contacts', 'type' => 'repeater', 'label' => 'Contacts', 'maxItems' => 4, 'fields' => [
                        ['name' => 'name', 'type' => 'text', 'label' => 'Name', 'max' => 120, 'required' => true],
                        ['name' => 'role', 'type' => 'text', 'label' => 'Role', 'max' => 120],
                        ['name' => 'email', 'type' => 'email', 'label' => 'Email'],
                    ]],
                ],
            ],
            'accordion_media' => [
                'label' => 'Expanding rows with image',
                'group' => 'Text',
                'description' => 'Centred heading and rows that expand to show an image and text (e.g. Current Gaps in Tech-Vocational Education).',
                'fields' => [
                    ...$heading,
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Rows', 'minItems' => 1, 'maxItems' => 8, 'fields' => [
                        ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 120, 'required' => true],
                        ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 500],
                        ['name' => 'image', 'type' => 'image', 'label' => 'Image'],
                    ]],
                ],
            ],
            'numbered_list' => [
                'label' => 'Numbered list',
                'group' => 'Text',
                'description' => 'Heading, introduction and numbered rows (e.g. Scaling Strategies).',
                'fields' => [
                    ...$heading, $body,
                    ['name' => 'list_label', 'type' => 'text', 'label' => 'List label', 'max' => 80],
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Rows', 'minItems' => 1, 'maxItems' => 12, 'fields' => [
                        ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 300, 'required' => true],
                    ]],
                    ['name' => 'footer', 'type' => 'textarea', 'label' => 'Closing text', 'max' => 500],
                ],
            ],
            'story_tabs' => [
                'label' => 'Tabs of story cards',
                'group' => 'Cards',
                'description' => 'Heading and introduction, then tabs, each with a carousel of photo cards beside a lavender panel (e.g. Training & Development).',
                'fields' => [
                    ...$heading, $body, $tone,
                    ['name' => 'card_link_label', 'type' => 'text', 'label' => 'Card link text', 'max' => 30, 'help' => 'e.g. Read story'],
                    ['name' => 'tabs', 'type' => 'repeater', 'label' => 'Tabs', 'minItems' => 1, 'maxItems' => 6, 'fields' => [
                        ['name' => 'label', 'type' => 'text', 'label' => 'Tab label', 'max' => 40, 'required' => true],
                        ['name' => 'cards', 'type' => 'repeater', 'label' => 'Cards', 'minItems' => 1, 'maxItems' => 12, 'fields' => [
                            ['name' => 'image', 'type' => 'image', 'label' => 'Photo', 'required' => true],
                            ['name' => 'date', 'type' => 'text', 'label' => 'Date (optional)', 'max' => 40],
                            ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 120, 'required' => true],
                            ['name' => 'subtitle', 'type' => 'text', 'label' => 'Highlighted item', 'max' => 140, 'help' => 'With a highlighted item, the text sits between two lines under it.'],
                            ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 400],
                            ['name' => 'link_url', 'type' => 'url', 'label' => 'Link'],
                        ]],
                    ]],
                ],
            ],
            'card_tabs' => [
                'label' => 'Tabs of icon cards',
                'group' => 'Cards',
                'description' => 'Tabs, each containing a row of outlined icon cards (e.g. Hubs and Spokes Model).',
                'fields' => [
                    ...$heading, $body, ...$cta('cta', 'Button'), $tone,
                    ['name' => 'tabs', 'type' => 'repeater', 'label' => 'Tabs', 'minItems' => 1, 'maxItems' => 6, 'fields' => [
                        ['name' => 'label', 'type' => 'text', 'label' => 'Tab label', 'max' => 40, 'required' => true],
                        ['name' => 'cards', 'type' => 'repeater', 'label' => 'Cards', 'maxItems' => 8, 'fields' => [
                            $icon,
                            ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 80, 'required' => true],
                            ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 500],
                        ]],
                    ]],
                ],
            ],
            'tabs_media' => [
                'label' => 'Tabs with image panel',
                'group' => 'Media',
                'description' => 'Tabs, each showing an image beside a lavender panel with a title, points and links (e.g. Partnership Models).',
                'fields' => [
                    ...$heading, $body, $tone,
                    ['name' => 'layout', 'type' => 'select', 'label' => 'Style', 'options' => ['panel' => 'Image beside a lavender panel', 'card' => 'Image beside a white card, tabs across the full width (last link is a button)']],
                    ['name' => 'tabs_align', 'type' => 'select', 'label' => 'Tabs (white card style)', 'options' => ['fill' => 'Spread across the width, tall panel', 'start' => 'Left-aligned, compact panel (e.g. Industrial Sectors)']],
                    ['name' => 'tabs', 'type' => 'repeater', 'label' => 'Tabs', 'minItems' => 1, 'maxItems' => 8, 'fields' => [
                        ['name' => 'label', 'type' => 'text', 'label' => 'Tab label', 'max' => 60, 'required' => true],
                        ['name' => 'image', 'type' => 'image', 'label' => 'Image'],
                        ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 120],
                        ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 600],
                        ['name' => 'points', 'type' => 'repeater', 'label' => 'Points', 'maxItems' => 8, 'fields' => [
                            ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 120],
                            ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 400],
                        ]],
                        ['name' => 'links', 'type' => 'repeater', 'label' => 'Links', 'maxItems' => 8, 'fields' => [
                            ['name' => 'label', 'type' => 'text', 'label' => 'Label', 'max' => 80, 'required' => true],
                            ['name' => 'url', 'type' => 'url', 'label' => 'Link', 'required' => true],
                        ]],
                    ]],
                ],
            ],
            'image_list' => [
                'label' => 'Image with list panel',
                'group' => 'Media',
                'description' => 'Large image beside a lavender panel with a heading and a list of points (e.g. Life @ NAMTECH).',
                'fields' => [
                    ['name' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 120],
                    ['name' => 'body', 'type' => 'textarea', 'label' => 'Introduction', 'max' => 400],
                    ['name' => 'image', 'type' => 'image', 'label' => 'Image', 'required' => true],
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Points', 'minItems' => 1, 'maxItems' => 8, 'fields' => [
                        ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 120],
                        ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 400],
                    ]],
                ],
            ],
            'image_mosaic' => [
                'label' => 'Image mosaic',
                'group' => 'Media',
                'description' => 'Two to four photos in a mosaic, optionally with a heading, text and a quote card (e.g. The Challenge, Student Impact).',
                'fields' => [
                    ...$heading, $body,
                    ['name' => 'images', 'type' => 'repeater', 'label' => 'Images', 'minItems' => 1, 'maxItems' => 4, 'fields' => [
                        ['name' => 'image', 'type' => 'image', 'label' => 'Image', 'required' => true],
                    ]],
                    ['name' => 'layout', 'type' => 'select', 'label' => 'Style', 'options' => ['side' => 'Text beside the photos', 'strip' => 'Heading above; a narrow and a wide photo, quote over the wide one (e.g. Student Impact)']],
                    ['name' => 'quote', 'type' => 'textarea', 'label' => 'Quote', 'max' => 400],
                    ['name' => 'quote_name', 'type' => 'text', 'label' => 'Quoted person', 'max' => 120],
                    ['name' => 'quote_role', 'type' => 'text', 'label' => 'Their role', 'max' => 120],
                ],
            ],
            'image_carousel' => [
                'label' => 'Image strip carousel',
                'group' => 'Media',
                'description' => 'Horizontal carousel of photos.',
                'fields' => [
                    ['name' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 120],
                    ['name' => 'images', 'type' => 'repeater', 'label' => 'Images', 'minItems' => 1, 'maxItems' => 20, 'fields' => [
                        ['name' => 'image', 'type' => 'image', 'label' => 'Image', 'required' => true],
                        ['name' => 'caption', 'type' => 'text', 'label' => 'Caption', 'max' => 160],
                    ]],
                ],
            ],
            'job_listings' => [
                'label' => 'Open positions',
                'group' => 'Forms',
                'description' => 'Search box, category tabs, published job openings and the Apply Now form.',
                'fields' => [
                    ['name' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 120],
                ],
            ],
            'placement_trends' => [
                'label' => 'Placement trends by program',
                'group' => 'Cards',
                'description' => 'Choose a level and program to see its placement figures, report and recruiters.',
                'fields' => [
                    ['name' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 120],
                    ['name' => 'items', 'type' => 'repeater', 'label' => 'Programs', 'minItems' => 1, 'maxItems' => 30, 'fields' => [
                        ['name' => 'level', 'type' => 'text', 'label' => 'Level label (first dropdown)', 'max' => 60, 'required' => true],
                        ['name' => 'program', 'type' => 'text', 'label' => 'Program name', 'max' => 160, 'required' => true],
                        ['name' => 'school', 'type' => 'text', 'label' => 'School label', 'max' => 120],
                        ['name' => 'text', 'type' => 'textarea', 'label' => 'Text', 'max' => 500],
                        ['name' => 'report', 'type' => 'file', 'label' => 'Placement report (PDF)'],
                        ['name' => 'stats', 'type' => 'repeater', 'label' => 'Figures', 'maxItems' => 4, 'fields' => [
                            ['name' => 'value', 'type' => 'text', 'label' => 'Figure', 'max' => 20, 'required' => true],
                            ['name' => 'label', 'type' => 'text', 'label' => 'Label', 'max' => 80, 'required' => true],
                        ]],
                    ]],
                    ['name' => 'show_logos', 'type' => 'boolean', 'label' => 'Show recruiter logos'],
                ],
            ],
        ];
    }

    public static function icons(): array
    {
        return [
            'graduation' => 'Graduation cap', 'book' => 'Book', 'people' => 'People', 'factory' => 'Factory',
            'chip' => 'Chip', 'robot' => 'Robot arm', 'leaf' => 'Leaf', 'globe' => 'Globe', 'handshake' => 'Handshake',
            'chart' => 'Chart', 'lightbulb' => 'Light bulb', 'gear' => 'Gear', 'flask' => 'Flask', 'award' => 'Award',
            'document' => 'Document', 'building' => 'Building', 'calendar' => 'Calendar', 'clock' => 'Clock', 'pin' => 'Location',
            'check' => 'Check', 'bank' => 'Bank', 'rupee' => 'Rupee', 'id' => 'ID card', 'portfolio' => 'Portfolio', 'trend' => 'Trend',
            'sparkle' => 'Sparkle', 'cap' => 'Mortar board', 'bolt' => 'Energy', 'drone' => 'Drone', 'magnet' => 'Magnet', 'code' => 'Code',
        ];
    }

    public static function exists(string $type): bool
    {
        return array_key_exists($type, self::definitions());
    }

    public static function fields(string $type): array
    {
        return self::definitions()[$type]['fields'] ?? [];
    }

    public static function rules(string $type, string $prefix): array
    {
        return [$prefix => ['array']] + Fields::rules(self::fields($type), $prefix);
    }

    public static function normalize(string $type, array $data): array
    {
        return Fields::normalize(self::fields($type), $data);
    }

    public static function mediaIds(string $type, array $data): array
    {
        return Fields::mediaIds(self::fields($type), $data);
    }

    public static function forAdmin(): array
    {
        return collect(self::definitions())->map(fn (array $definition, string $type): array => [
            'type' => $type,
            'label' => $definition['label'],
            'group' => $definition['group'],
            'description' => $definition['description'],
            'fields' => Fields::forAdmin($definition['fields']),
        ])->values()->all();
    }
}
