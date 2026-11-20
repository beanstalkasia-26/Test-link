<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Announcement;
use App\Models\Author;
use App\Models\Category;
use App\Models\Centre;
use App\Models\Event;
use App\Models\Faculty;
use App\Models\Gallery;
use App\Models\Initiative;
use App\Models\JobOpening;
use App\Models\Leader;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Partner;
use App\Models\PartnershipCategory;
use App\Models\Post;
use App\Models\Program;
use App\Models\Redirect;
use App\Models\School;
use App\Models\Setting;
use App\Sections\SectionRegistry;
use App\Services\MediaUploader;
use App\Services\PartnershipPageDesign;
use App\Support\ContentCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    public const Tbc = 'Content to be confirmed.';

    private array $media = [];

    private MediaUploader $uploader;

    public function run(MediaUploader $uploader): void
    {
        $this->uploader = $uploader;
        $this->seedMedia($uploader);
        $this->seedSettings();
        $this->seedAnnouncements();
        [$schools, $centres] = $this->seedAcademics();
        $this->seedPartners();
        $this->seedFaculty($schools);
        $this->seedLeaders();
        $this->seedEvents($schools);
        $this->seedJobs();
        $this->seedPosts();
        $this->seedCampus();
        $this->seedMission();
        $this->seedSchoolLanding($schools);
        $this->seedSustainabilityMaster();
        $this->seedManufacturingLanding($schools);
        $this->seedSmartManufacturingMaster();
        $pages = $this->seedPages($schools);
        $this->seedMenus($pages, $schools, $centres);
        app(PartnershipPageDesign::class)->apply();

        ContentCache::flush();
    }

    private function seedMedia(MediaUploader $uploader): void
    {
        $images = [
            'hero-electronics-inspection' => 'Student inspecting a circuit board through magnifying safety glasses',
            'specialized-labs-vehicle' => 'Electric vehicle chassis displayed in an advanced manufacturing lab',
            'experiential-learning-robotics' => 'Student assembling a small robotic arm at a workbench',
            'industry-placements-team' => 'Mentor reviewing project work with two students at a shared table',
            'global-collaboration-student' => 'Smiling student wearing glasses at a desk',
            'inclusive-cohort' => 'Students collaborating in a classroom, including a student using a wheelchair',
            'program-student-hardware' => 'Student working on computer hardware at a desk',
            'partner-story-precision' => 'Technician using magnifying glasses for precision work',
            'faculty-portrait-placeholder' => 'Faculty portrait',
            'labs-soldering' => 'Student wearing safety goggles soldering electronic components',
            'labs-research' => 'Researcher in a lab coat using a microscope',
            'campus-building' => 'Brick campus building and landscaped courtyard',
            'campus-lawn' => 'Students relaxing on a campus lawn',
            'campus-interior' => 'Teal interior with natural light',
            'campus-seating' => 'Auditorium-style seating',
            'news-indiaskills' => 'Prize winners on the podium at the IndiaSkills 2025-26 regional competition closing ceremony',
            'news-tata-electronics' => 'Visitors being shown student-built components in a lab',
            'news-automotive' => 'Group photograph in front of an automotive lab',
            'news-robotics-student' => 'Student building a small robot',
            'turbine-engineer' => 'Engineer inspecting a turbine assembly',
            'precision-machining' => 'Technician working on a precision machine',
            'professional-tablet' => 'Professional holding a tablet in a modern office',
        ];
        foreach ($images as $file => $alt) {
            $this->media[$file] = $uploader->storePath(__DIR__.'/assets/'.$file.'.jpg', $file.'.jpg', ['alt' => $alt], sync: true)->id;
        }

        $logos = [
            'amns-india' => 'AM/NS India', 'tum-asia' => 'TUM Asia', 'schneider-electric' => 'Schneider Electric', 'cisco' => 'Cisco',
            'micron' => 'Micron', 'festo' => 'Festo', 'siemens' => 'Siemens', 'lt-heavy-engineering' => 'L&T Heavy Engineering',
        ];
        foreach ($logos as $file => $name) {
            $this->media['logo-'.$file] = $uploader->storePath(__DIR__.'/assets/logos/'.$file.'.jpg', $file.'.jpg', ['alt' => $name.' logo'], sync: true)->id;
        }
    }

    private function seedSettings(): void
    {
        $settings = [
            'General' => [
                'site_name' => 'NAMTECH',
                'site_tagline' => 'Institute of Manufacturing Innovation',
                'default_meta_description' => 'NAMTECH, India’s First MET Institute, is shaping the next generation of manufacturing leaders through engineering, technology, systems thinking and industry learning.',
                'default_og_image_id' => $this->media['hero-electronics-inspection'],
                'virtual_tour_url' => null,
            ],
            'Contact' => [
                'address_title' => 'Campus:',
                'address' => 'Research Park, IIT Gandhinagar Campus, Palaj, Gandhinagar, Gujarat - 382055, India',
                'phones' => [['number' => '+91 7923277000']],
                'email' => 'contact@namtech.ac',
                'map_url' => null,
                'call_url' => '/contact',
            ],
            'Social' => ['linkedin_url' => 'https://www.linkedin.com/company/namtechofficial/', 'x_url' => 'https://twitter.com/namtechofficial', 'youtube_url' => 'https://www.youtube.com/@NAMTECHOfficial', 'instagram_url' => 'https://www.instagram.com/namtech_official/', 'facebook_url' => 'https://www.facebook.com/profile.php?id=100092336407864'],
            'Footer & forms' => [
                'newsletter_heading' => 'Subscribe to our newsletter',
                'newsletter_consent' => 'I agree to receive email updates from NAMTECH. I can unsubscribe at any time.',
                'enquiry_consent' => 'I agree that NAMTECH may store my details and contact me about this enquiry.',
                'enquiry_recipients' => 'contact@namtech.ac',
                'copyright' => '© {year} NAMTECH. All Rights Reserved.',
            ],
        ];
        foreach ($settings as $group => $values) {
            foreach ($values as $key => $value) {
                Setting::put($key, $value, $group);
            }
        }
    }

    private function seedAnnouncements(): void
    {
        Announcement::create(['label' => 'New', 'text' => 'Deemed-to-be University - LOI Received', 'url' => null, 'is_active' => true, 'sort_order' => 0]);
    }

    private function seedAcademics(): array
    {
        $schoolList = [
            ['School of Manufacturing Technologies', 'program-student-hardware', 'The School of Manufacturing Technologies is focused on advancing the capabilities required for the next era of manufacturing. By bringing together engineering expertise, emerging technologies, industry collaboration, and experiential learning, the School prepares learners to create value across the evolving manufacturing ecosystem and contribute to India’s ambition to become a globally competitive manufacturing powerhouse.'],
            ['School of Manufacturing Design & AI', 'experiential-learning-robotics', 'The School of Manufacturing Design & AI focuses on the convergence of design, engineering, computation, and artificial intelligence to shape the next generation of products, systems, and industrial innovation. Through an industry-first and experiential approach, the school prepares learners to work at the intersection of technology and human needs, translating emerging capabilities into solutions with real-world impact.'],
            ['School of Robotics', 'labs-soldering', 'The School of Robotics prepares the next generation of professionals to design, develop, and deploy intelligent machines and autonomous systems that will transform industry and society. By combining engineering, emerging technologies, hands-on experimentation, and real-world application, the school equips learners to build the future of human–machine collaboration and intelligent automation.'],
            ['School of Sustainability', 'labs-research', 'The School of Sustainability focuses on enabling the transition towards more resilient, resource-efficient, and sustainable industries. By bringing together technology, engineering, innovation, and systems thinking, the School prepares learners to address complex environmental and industrial challenges and create solutions that balance economic growth, resource security, and long-term sustainability.'],
            ['School of Technical Education', 'precision-machining', 'The School of Technical Education enables ITI and diploma-qualified learners to build on their technical foundations and progress into higher levels of technological capability. Through practical, industry-oriented learning and curriculum designed in collaboration with ITE Education Services, Singapore, the School creates pathways for skilled technicians to evolve into confident, industry-ready technologists capable of contributing to the future of modern industry.'],
        ];
        $schools = [];
        foreach ($schoolList as $i => [$name, $image, $summary]) {
            $schools[Str::slug($name)] = $this->live(School::create([
                'name' => $name, 'slug' => Str::slug($name), 'summary' => $summary, 'image_id' => $this->media[$image], 'sort_order' => $i,
            ]));
        }
        Redirect::create(['from_path' => '/schools/school-of-technical-education', 'to_url' => '/school-of-technical-education', 'status_code' => 301]);

        $centres = [];
        $centreList = [
            ['Center of Excellence in Automotive Engineering', 'coe-in-automotive-engineering', 'CoE in Automotive Engineering', 'specialized-labs-vehicle', 'The Center of Excellence in Automotive Engineering advances the future of mobility by bringing together engineering, technology, design, and industry expertise. The Center enables learners and industry to explore emerging opportunities, address real-world mobility challenges, and develop solutions that contribute to a more intelligent, connected, and sustainable automotive ecosystem.'],
            ['Center of Advanced Computing & AI', 'centre-of-advanced-computing-ai', 'CoE in Advanced Computing & AI', 'precision-machining', 'The Center for Advanced Computing & AI focuses on harnessing computing and artificial intelligence to address complex challenges across industry and society. By connecting advanced technology with real-world application, the Center enables learners, researchers, and industry to develop intelligent solutions and unlock new possibilities for technology-led transformation.'],
            ['Center of Management Studies', 'center-of-management', 'CoE in Management Studies', 'professional-tablet', 'The Center of Management Studies helps NAMTECH graduates develop the strategic, business, and managerial capabilities required to navigate an increasingly technology-driven economy. By bringing together design thinking, strategic intelligence, and business & professional communication, the Center prepares professionals to bridge technology and enterprise, drive transformation, and drive organizational value.'],
            ['Experiential Learning Center for Sustainable Energy & Circularity', 'experiential-learning-center-for-sustainable-energy-circularity', 'ELC: Sustainable Energy & Circularity', 'turbine-engineer', 'Co-created with IIT Roorkee and supported by NTPC, The Experiential Learning Center for Sustainable Energy & Circularity (ELC:SEC) focuses on enabling the transition towards more sustainable, resource-efficient, and resilient industrial systems. The Center addresses the interconnected challenges of energy, resources, and industrial growth through convergence of technology, innovation, and systems thinking, for a low-carbon and circular future.'],
        ];
        foreach ($centreList as $i => [$name, $slug, $menuLabel, $image, $summary]) {
            $centre = $this->live(Centre::create([
                'name' => $name, 'slug' => $slug, 'summary' => $summary, 'image_id' => $this->media[$image], 'sort_order' => $i,
            ]));
            $centre->menu_label = $menuLabel;
            $centres[$slug] = $centre;
        }

        $programs = [
            ['B.Tech. Mechanical Engineering (Smart Manufacturing)', 'undergraduate', 'B.Tech', 'school-of-manufacturing-technologies', null],
            ['B.Tech. Mechanical Engineering (Automotive)', 'undergraduate', 'B.Tech', null, null],
            ['B.Tech. Robotics & AI', 'undergraduate', 'B.Tech', 'school-of-robotics', null],
            ['B.Tech. Smart & Sustainable Energy', 'undergraduate', 'B.Tech', 'school-of-sustainability', null],
            ['B.Tech. Computer Science Engineering', 'undergraduate', 'B.Tech', null, null],
            ['B.Tech. Electronics Engineering (Semiconductor Technology)', 'undergraduate', 'B.Tech', null, null],
            ['B.Tech. Electronics Engineering (Space Technology)', 'undergraduate', 'B.Tech', null, null],
            ['Master in Smart Manufacturing Technology & Management', 'postgraduate', 'Master', 'school-of-manufacturing-technologies', 'Develop advanced expertise in smart manufacturing, Industry 4.0 and 5.0, intelligent automation, digital production systems, and technology management to lead the transformation of modern manufacturing enterprises.'],
            ['Master in Heavy Industry Engineering & Management', 'postgraduate', 'Master', 'school-of-manufacturing-technologies', 'Developed in collaboration with the ArcelorMittal Design and Engineering Center (AMDEC), this program prepares engineers for heavy industries like steel manufacturing, mining and mineral processing, ports, and capital infrastructure. It combines a rigorous engineering foundation with three specialization tracks and an extended paid industry residency at AMDEC.'],
            ['Master in Semiconductor Manufacturing Technology & Management', 'postgraduate', 'Master', 'school-of-manufacturing-technologies', 'Master Industrial AI and semiconductor technologies, designing intelligent products and next-generation systems that integrate advanced computing, human-centered design, and emerging technologies to shape the future of manufacturing.'],
            ['Master in Robotics Engineering & Management', 'postgraduate', 'Master', 'school-of-robotics', 'Build hands-on expertise in aerial and legged robotics, collaborative robots, and autonomous systems to engineer connected, adaptive, and human-centric factories of the future.'],
            ['Master in Sustainability Engineering & Management', 'postgraduate', 'Master', 'school-of-sustainability', 'Develop industrial frameworks and technical strategies to build industries that balance innovation and performance with long-term environmental responsibility while working toward net-zero emissions.'],
            ['Master in Automotive Technology & Management', 'postgraduate', 'Master', 'school-of-manufacturing-technologies', 'Advance the future of mobility through the integration of advanced design, engineering and industry expertise to develop next-generation automotive solutions, intelligent vehicle systems, and emerging mobility technologies.'],
            ['Master in Data Analytics & Artificial Intelligence', 'postgraduate', 'Master', null, 'Build intelligent systems, optimize industrial processes, and translate emerging technologies into real-world industrial use-cases through advanced computing and AI.'],
            ['iPMPX Smart Manufacturing', 'executive', null, 'school-of-manufacturing-technologies', null],
            ['iPTP Automation', 'technical', 'iPTP', 'school-of-technical-education', 'Build upon your ITI or diploma foundation with advanced expertise in industrial automation, control systems, instrumentation, and connected production technologies to configure, integrate, and optimize automated manufacturing systems.'],
            ['iPTP Semiconductor Manufacturing', 'technical', 'iPTP', 'school-of-technical-education', 'Gain specialized expertise in semiconductor manufacturing, precision processes, cleanroom practices, and advanced production technologies to build the capabilities needed for India’s growing semiconductor ecosystem.'],
            ['iPTP Industrial Robotics', 'technical', 'iPTP', 'school-of-technical-education', 'Strengthen your ITI or diploma foundation with specialized expertise in industrial robotics, robotic programming, machine vision, and robot–machine integration to evolve from a skilled technician into an industry-ready technologist.'],
        ];
        $images = ['program-student-hardware', 'experiential-learning-robotics', 'labs-research', 'specialized-labs-vehicle', 'precision-machining', 'turbine-engineer'];
        foreach ($programs as $i => [$title, $level, $award, $school, $summary]) {
            $this->live(Program::create([
                'title' => $title,
                'slug' => Str::slug(str_replace('&', 'and', $title)),
                'level' => $level,
                'award' => $award,
                'summary' => $summary,
                'school_id' => $school ? $schools[$school]->id : null,
                'image_id' => $this->media[$images[$i % count($images)]],
                'is_featured' => true,
                'apply_url' => '/admissions#enquire',
                'mode' => $level === 'technical' ? null : 'On-Campus',
                'location' => $level === 'technical' ? null : 'Ahmedabad',
                'intake' => $level === 'technical' ? null : 'August, 2026',
                'application_deadline' => $level === 'technical' ? null : '2026-07-30',
                'duration' => $level === 'postgraduate' ? '2 years' : null,
                'duration_detail' => $level === 'postgraduate' ? '4 Semesters' : null,
                'sort_order' => $i,
            ]));
        }

        return [$schools, $centres];
    }

    private function seedPartners(): void
    {
        $academic = PartnershipCategory::create(['name' => 'Academic partners', 'slug' => 'academic', 'sort_order' => 0]);
        $industry = PartnershipCategory::create(['name' => 'Industry partners', 'slug' => 'industry', 'sort_order' => 1]);

        $partners = [
            ['Massachusetts Institute of Technology', 'mit', $academic], ['Carnegie Mellon University', 'carnegie-mellon-university', $academic],
            ['Indian Institute of Technology (IIT), Roorkee', 'iit-roorkee', $academic],
            ['AM/NS India', 'amns-india', $industry], ['TUM Asia', 'tum-asia', $academic], ['Siemens', 'siemens', $industry],
            ['Cisco', 'cisco', $industry], ['Micron', 'micron', $industry], ['Festo', 'festo', $industry], ['ABB', 'abb', $industry],
            ['Schneider Electric', 'schneider-electric', $industry], ['L&T Heavy Engineering', 'lt-heavy-engineering', $industry],
        ];
        $stories = [
            'mit' => ['In collaboration with MIT’s Department of Mechanical Engineering, NAMTECH brings the renowned MIT 2.008 Manufacturing course to India — combining core manufacturing principles with hands-on application, from product design and process planning to production systems and optimization.', $this->pdfImage('p043', 'Engineer building a robotics assembly'), null],
            'carnegie-mellon-university' => ['Through its collaboration with Carnegie Mellon University, NAMTECH leverages CMU’s expertise in learning science, digital education, and technology-enabled learning to develop and share advanced learning resources and methodologies with ITIs, strengthening the quality and reach of technical education. The collaboration also supports faculty training and learning science research.', $this->pdfImage('p102', 'Analyst reviewing data dashboards'), null],
            'iit-roorkee' => ['Together with IIT Roorkee, and supported by National Thermal Power Corporation (NTPC), NAMTECH has established the Experiential Learning Center for Sustainable Energy & Circularity (ELC:SEC) to advance applied research, industry-focused learning, and technology-led solutions for a more energy-efficient and resource-resilient industrial future.', $this->pdfImage('p030', 'Wind turbines on a snowy hillside'), null],
            'siemens' => ['NAMTECH’s strategic partnership with Siemens strengthens learning in smart manufacturing, automation, and technical education. The collaboration focuses on experiential learning, hands-on training, co-developed curricula, and advanced learning infrastructure to prepare learners for Industry 4.0.', $this->media['partner-story-precision'], null],
            'abb' => ['In collaboration with ABB, NAMTECH advances robotics education and supports India’s transition towards automation. The collaboration bridges the skills gap through industry-grade equipment, experiential learning, knowledge sharing via ABB’s experts, applied research, and immersive industry exposure, equipping learners with practical expertise in robotics and automation.', $this->pdfImage('p131', 'Orange industrial robot arm'), null],
            'schneider-electric' => ['Through its partnership with Schneider Electric, NAMTECH advances smart manufacturing education through industry-grade infrastructure, advanced training labs, and experiential learning.', $this->pdfImage('p120', 'Student working on an automation rig'), null],
        ];
        $profileSlugs = ['Siemens' => 'siemens', 'ABB' => 'abb', 'AM/NS India' => 'amns-india', 'Schneider Electric, Global / India' => 'schneider-electric', 'Festo' => 'festo', 'Micron' => 'micron', 'Cisco' => 'cisco', 'L&T Heavy Engineering' => 'lt-heavy-engineering'];
        $profiles = [];
        foreach ($this->corporatePartnerProfiles() as [$profileName, $paragraphs]) {
            if (isset($profileSlugs[$profileName])) {
                $profiles[$profileSlugs[$profileName]] = $paragraphs;
            }
        }
        foreach ($partners as $i => [$name, $slug, $category]) {
            [$summary, $image, $highlight] = $stories[$slug] ?? [null, null, null];
            $this->live(Partner::create([
                'name' => $name,
                'slug' => $slug,
                'partnership_category_id' => $category->id,
                'logo_id' => $this->media['logo-'.$slug] ?? null,
                'image_id' => $image,
                'summary' => $summary,
                'highlight' => $highlight,
                'story' => isset($profiles[$slug]) ? implode('', array_map(fn (string $paragraph) => '<p>'.e($paragraph).'</p>', $profiles[$slug])) : ($summary ? '<p>'.self::Tbc.'</p>' : null),
                'is_featured' => $summary !== null,
                'sort_order' => $i,
            ]));
        }
    }

    private function pdfImage(string $file, string $alt): int
    {
        return $this->media['pdf-'.$file] ??= $this->uploader->storePath(__DIR__.'/assets/pdf/'.$file.'.jpg', $file.'.jpg', ['alt' => $alt, 'credit' => 'Image from the supplied design – confirm licence'], sync: true)->id;
    }

    private function seedFaculty(array $schools): void
    {
        $sus = $schools['school-of-sustainability']->id;
        $mt = $schools['school-of-manufacturing-technologies']->id;
        $rob = $schools['school-of-robotics']->id;
        $dai = $schools['school-of-manufacturing-design-ai']->id;
        $homepage = ['Dr. Ibrahim Hafeezur Rehman' => 0, 'Dr. Andallib Tariq' => 1, 'Dr. Sanjeev Gupta' => 2, 'Dr. Ekta Singla' => 3];
        $photos = [
            'Dr. Ibrahim Hafeezur Rehman' => ['p086', 'Portrait of Dr. Ibrahim Hafeezur Rehman'],
            'Dr. Andallib Tariq' => ['p099', 'Portrait of Dr. Andallib Tariq'],
            'Dr. Ekta Singla' => ['p087', 'Portrait of Dr. Ekta Singla'],
        ];

        $featured = [
            ['Dr. Meera Iyer', 'Dean of Research, School of Manufacturing Technologies', 'residential', $mt, $this->pdfImage('p029', 'Portrait of Dr. Meera Iyer'), 'Robotics & Autonomous Systems', [
                ['period' => '1994 – 1997', 'title' => 'PhD', 'institution' => 'The Queen’s University of Belfast, United Kingdom', 'text' => null],
                ['period' => '1985 – 1987', 'title' => 'MTech', 'institution' => 'University of Delhi, Delhi', 'text' => null],
            ]],
            ['Prof. Ananya Rao', 'Director of Research | Smart Manufacturing Systems', 'residential', $mt, $this->pdfImage('p077', 'Portrait of Prof. Ananya Rao'), 'Smart Manufacturing Systems', []],
            ['Prof. Daniel Philip', 'Visiting Faculty | Advanced Manufacturing Labs', 'visiting', $mt, $this->pdfImage('p133', 'Portrait of Prof. Daniel Philip'), 'Advanced Manufacturing', []],
            ['Dr. Neeta Jain', 'Dean - Academics', 'residential', null, $this->pdfImage('p042', 'Portrait of Dr. Neeta Jain'), null, []],
            ['Dr. Sanjeev Gupta', 'Dean, Quality Assurance & Rankings; Director – School of Manufacturing Technologies', 'residential', $mt, $this->pdfImage('p058', 'Portrait of Dr. Sanjeev Gupta'), null, []],
        ];
        foreach ($featured as $i => [$name, $designation, $category, $school, $photo, $interests, $education]) {
            $this->live(Faculty::create([
                'name' => $name, 'slug' => Str::slug($name), 'designation' => $designation, 'category' => $category,
                'school_id' => $school, 'photo_id' => $photo, 'research_interests' => $interests,
                'qualifications' => $education, 'bio' => '<p>'.self::Tbc.'</p>',
                'is_featured' => isset($homepage[$name]), 'sort_order' => $homepage[$name] ?? 5 + $i,
            ]));
        }

        $list = [
            ['Rakesh Tiwari', 'Dean - Academics', null],
            ['Dr. Ibrahim Hafeezur Rehman', 'Director General & CEO; Dean – Academic Advancement, R&D & Consultancy; Director – School of Sustainability', $sus],
            ['Dr. Andallib Tariq', 'Dean, Academics; Director, School of Manufacturing, Design & AI', $dai],
            ['Dr. Velagapudi Vasu', 'Program Director, School of Manufacturing', $mt],
            ['Dr. Ekta Singla', 'Associate Director, School of Robotics', $rob],
            ['Dr. Kamalkishore C. Vora', 'Director, Centre of Excellence Automotive', null],
            ['Dr. Anupam Kumar Singh', 'Professor', $sus],
            ['Dr. Sunil Pathak', 'Associate Professor', null],
            ['Dr. Shailesh Sharma', null, null], ['Dr. P. Rajagopalan', null, null], ['Dr. Ashish Kumar Shukla', null, null],
            ['Dr. Litton Bhandari', 'Assistant Professor', null],
            ['Dr. Ankit Gaurav', 'Senior Lecturer', null],
            ['Mr. Sankata Tiwari', null, null], ['Mr. Mukesh Kumar', null, null], ['Mr. Lalit Kumar', null, null],
            ['Dr. Anubhav Mishra', null, null], ['Dr. Umangi Mehta', 'Assistant Professor', $sus], ['Mr. Rajkumar Sharma', null, null],
            ['Dr. Srishti Paliwal', null, null], ['Dr. Rishi Parvanda', null, null], ['Ms. Soumya Sangita Nayak', null, null],
            ['Dr. Hardik Patel', null, null], ['Dr. Ajit Kumar Sharma', null, null], ['Dr. Preeti Lata Mahapatra', null, null],
            ['Dr. Siddhartha Suman', null, null], ['Dr. Gavax Joshi', null, null], ['Dr. Shuchi Sharma', 'Assistant Professor', $sus], ['Dr. Abhisek Parida', 'Assistant Professor', $sus],
        ];
        foreach ($list as $i => [$name, $designation, $school]) {
            $this->live(Faculty::create([
                'name' => $name, 'slug' => Str::slug($name), 'designation' => $designation, 'category' => 'residential',
                'school_id' => $school, 'sort_order' => $homepage[$name] ?? 10 + $i,
                'is_featured' => isset($homepage[$name]),
                'photo_id' => isset($photos[$name]) ? $this->pdfImage(...$photos[$name]) : null,
            ]));
        }
    }

    private function seedLeaders(): void
    {
        $leaders = [
            'board' => [
                ['Dr. Gauri Trivedi', null, 'p108'], ['Mr. Sanjay Sharma', null, 'p086'], ['Mr. Kedar Tambe', null, 'p099'],
                ['Mr. Maulik Bhagat', null, 'p123'], ['Mr. Brad Davey', null, 'p100'], ['Mr. Hiroshi Ebina', null, 'p126'],
            ],
            'advisory' => [
                ['Dr. Ashish Nanda', 'Professor, Harvard Business School and Harvard Law School', 'p133'],
                ['Mr. Bruce Poh', 'Former CEO, ITE Education Services', 'p058'],
                ['Ms. Debjani Ghosh', 'Distinguished Fellow, NITI Aayog & Chief Architect, NITI Frontier Tech Hub', 'p077'],
                ['Dr. Krishnan Balasubramanian', 'Institute Professor, IIT Madras', 'p031'],
                ['Dr. Leena Srivastava', 'Independent Non-executive Director, Shell', 'p032'],
                ['Dr. Pinakin Chaubal', 'VP, Chief Technology Officer, ArcelorMittal', 'p097'],
            ],
            'management' => [
                ['Dr. Ibrahim Hafeezur Rehman', 'Director General & CEO; Dean of Academic Advancement, R&D & Consultancy; Director of School of Sustainability', 'p086'],
                ['Dr. Devang Joshi', 'Executive Registrar and Administration', 'p070'],
                ['Mr. Sandeep Achantani', 'Director of School of Technical Education', 'p031'],
                ['Ms. Nandini Dasgupta', 'Director of Corporate Partnerships & Placements', 'p087'],
                ['Mr. Sachin Mardikar', 'Chief Financial Officer', 'p123'],
                ['Mr. Kuldeep Koul', 'Chief Digital Officer', 'p037'],
            ],
        ];
        foreach ($leaders as $group => $people) {
            foreach ($people as $i => [$name, $designation, $photo]) {
                $this->live(Leader::create([
                    'group' => $group, 'name' => $name, 'slug' => Str::slug($name), 'designation' => $designation,
                    'organisation' => $group === 'advisory' ? 'Advisory Council, NAMTECH' : null,
                    'photo_id' => $this->pdfImage($photo, 'Portrait of '.$name),
                    'bio' => '<p>'.self::Tbc.'</p>', 'achievements' => [], 'sort_order' => $i,
                ]));
            }
        }
    }

    private function seedEvents(array $schools): void
    {
        $this->live(Event::create([
            'category' => 'conference', 'school_id' => $schools['school-of-sustainability']->id,
            'title' => 'Sustainable Manufacturing Summit 2026', 'slug' => 'sustainable-manufacturing-summit-2026',
            'subtitle' => 'Masterclass by Prof. Daniel Philip, MIT',
            'summary' => 'Experience learning with real industry equipment and state-of-the-art infrastructure designed for the future of manufacturing.',
            'overview' => '<p>'.self::Tbc.'</p>',
            'outcomes' => [['text' => self::Tbc]],
            'speaker' => ['name' => 'Prof. Daniel Philip', 'role' => 'Dean of Research, MIT', 'photo' => $this->pdfImage('p133', 'Portrait of Prof. Daniel Philip'), 'quote' => null, 'bio' => '<p>'.self::Tbc.'</p>'],
            'starts_at' => '2027-04-15 09:00:00', 'ends_at' => '2027-04-15 17:00:00',
            'venue' => 'NAMTECH Auditorium 3', 'registration_url' => '/contact',
            'image_id' => $this->pdfImage('p016', 'Audience at a conference session'),
        ]));
    }

    private function seedJobs(): void
    {
        $jobs = [
            ['Assistant Professor - Mechanical Engineering', 'academic', 'School of Manufacturing Technologies', 'On Campus (Gujarat Campus)'],
            ['Associate / Assistant Director - Marketing and Admissions', 'executive', 'NAMTECH', 'Remote'],
            ['MEPF Manager - Mechanical', 'executive', 'NAMTECH', 'On Campus (Gujarat Campus)'],
        ];
        foreach ($jobs as $i => [$title, $category, $department, $mode]) {
            $this->live(JobOpening::create([
                'title' => $title, 'slug' => Str::slug($title), 'category' => $category, 'department' => $department, 'mode' => $mode,
                'closes_on' => null,
                'summary' => null, 'description' => '<p>'.self::Tbc.'</p>', 'sort_order' => $i,
            ]));
        }
    }

    private function seedPosts(): void
    {
        $author = Author::create(['name' => 'NAMTECH Communications', 'slug' => 'namtech-communications']);
        $cat = fn (string $name) => Category::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        $visits = $cat('Notable Visits');
        $cat('Academic Partnerships');
        $corporate = $cat('Corporate Partnerships');
        $cat('Events');

        $post = function (string $type, string $title, ?Category $category, int $image, int $daysAgo, ?string $excerpt = null, ?string $body = null, bool $featured = false) use ($author): Post {
            return $this->live(Post::create([
                'type' => $type, 'title' => $title, 'slug' => Str::slug(str_replace(['&', '/', '→'], ['and', '-', 'to'], $title)),
                'category_id' => $category?->id, 'post_author_id' => $author->id, 'image_id' => $image,
                'excerpt' => $excerpt, 'body' => $body ?? '<p>'.self::Tbc.'</p>', 'is_featured' => $featured,
            ]), now()->subDays($daysAgo));
        };

        $post('news', 'AM/NS India Leadership Visits NAMTECH', $visits, $this->media['news-indiaskills'], 2, null, null, true);
        $post('news', 'Dr. Bobby Mitra, CIO & President of Tata Electronics, Engages with NAMTECH', $visits, $this->media['news-tata-electronics'], 3);
        $post('news', 'NAMTECH Strengthens Automotive Engineering Capabilities', $corporate, $this->media['news-automotive'], 4);

        $sections = ['Understanding Industry 4.0', 'India’s Industry 4.0 Journey', 'The Requirement of Industry 5.0', 'Unlocking Limitless Potential', 'Manufacturing as India’s Global Game-Changer'];
        $body = collect($sections)->map(fn (string $h) => '<h2>'.e($h).'</h2><p>'.self::Tbc.'</p>')->implode('');
        $post('blog', 'From Industry 4.0 to 5.0: India’s Stride Towards Human–Tech Synergy', $cat('Research'), $this->pdfImage('p084', 'Two researchers examining plant samples in a laboratory'), 5, null, $body, true);

        $journey = $cat('Student Journey & Innovations');
        foreach (['Industry Case Studies', 'Industrial & Social Impact', 'Faculty Stories', 'Research Breakthroughs'] as $name) {
            $cat($name);
        }
        $post('story', 'iPTP Graduate → Smart Manufacturing Engineer, Germany', $journey, $this->pdfImage('p039', 'Group of smiling students'), 6,
            'Aruna Kumari, an iPTP student from a Tier-3 town, joined NAMTECH without experience. She became a manufacturing engineer at a global automotive supplier in Germany, focusing on production optimization.', null, true);
        $post('story', 'NAMTECH Graduate Experiences Nearly 90% Increase in Salary', $journey, $this->pdfImage('p000', 'Graduate shaking hands at a job interview'), 7);
        $post('story', 'NAMTECH Students Revolutionize Prosthetics Design using 3D Printing', $cat('Research Breakthroughs'), $this->pdfImage('p062', 'Prosthetic hand against a blue background'), 8);
    }

    private function seedSchoolLanding(array $schools): void
    {
        $school = $schools['school-of-sustainability'];
        $img = fn (string $file, string $alt) => $this->pdfImage($file, $alt);
        $program = fn (string $title) => Program::query()->where('title', $title)->firstOrFail()->publicPath();
        $school->update(['summary' => 'NAMTECH’s School of Sustainability prepares the next generation to engineer a cleaner, more resilient, and resource-efficient industrial future.']);

        $this->attachSections($school, [
            ['page_hero', ['eyebrow' => 'Schools', 'title' => 'School of Sustainability', 'body' => 'NAMTECH’s School of Sustainability prepares the next generation to engineer a cleaner, more resilient, and resource-efficient industrial future.', 'image' => $img('p093', 'Sustainability model with wind turbines on a table'), 'cta_label' => 'Explore Programs', 'cta_url' => '#programs']],
            ['intro', ['eyebrow' => null, 'heading' => 'Overview', 'icon' => 'leaf', 'body' => '<p>NAMTECH’s School of Sustainability integrates engineering, technology, and sustainability to address India’s industrial challenges. Our interdisciplinary, industry-integrated approach combines academic foundations with hands-on learning through living and experiential labs and live projects.</p><p>Learners build expertise in ESG, circularity, energy transition, and decarbonization using industry-standard tools. By connecting policy, technology, and implementation, the School prepares professionals to move beyond understanding sustainability challenges to designing and deploying solutions that drive sustainable environmental and social impact across industry and infrastructure.</p>']],
            ['pillars', ['eyebrow' => null, 'heading' => 'Key Pillars', 'body' => 'The School of Sustainability focuses on five interconnected disciplines that are central to India’s transition toward a cleaner, more resilient, and resource-efficient economy.', 'items' => [
                ['title' => 'Energy', 'text' => 'Focusing on India’s energy transition, NAMTECH’s focus on energy has built expertise in renewable integration, smart management, and industrial efficiency, equipping professionals to optimize operations and ensure energy security.', 'image' => $img('p030', 'Wind turbines on a snowy hillside')],
                ['title' => 'Circularity', 'text' => 'Rethinking linear production through recycling, reuse, and life cycle assessment, NAMTECH is focused on the discipline of circularity to design closed-loop systems for the manufacturing sector for waste minimization and creation of economic value.', 'image' => $img('p136', 'Plant cuttings in laboratory glassware')],
                ['title' => 'Decarbonization', 'text' => 'Targeting high-emission sectors like steel and cement, NAMTECH’s decarbonization pillar translates climate ambition into industrial action. Learners master carbon accounting and emission-reduction strategies aligned with global climate goals.', 'image' => $img('p084', 'Two researchers examining plant samples in a laboratory')],
                ['title' => 'ESG', 'text' => 'Developing expertise in sustainability reporting, risk assessment, and data analytics, this pillar is focused on preparing professionals to integrate ESG metrics into business strategy for long-term competitiveness and value creation.', 'image' => $img('p129', 'Student analysing data on a laptop')],
                ['title' => 'Sustainable Infrastructure', 'text' => 'Combining engineering with AI and digital tools, sustainable infrastructure at NAMTECH addresses green buildings, smart cities, and resilient transportation, helping learners design systems that support growth while minimizing environmental impact.', 'image' => $img('p078', 'Engineer with a tablet on site stairs')],
            ]]],
            ['media_cards', ['eyebrow' => 'Programs', 'heading' => 'Programs Built for Impact', 'layout' => 'tall', 'card_link_label' => 'Explore program', 'items' => [
                ['image' => $this->media['turbine-engineer'], 'title' => 'B.Tech. Smart & Sustainable Energy', 'body' => 'From understanding energy to reimagining its future. Build, experiment, and innovate across renewable energy, smart grids, energy storage, and digital technologies – developing the technical depth and systems thinking to power a cleaner, smarter world.', 'link_url' => $program('B.Tech. Smart & Sustainable Energy')],
                ['image' => $this->media['labs-research'], 'title' => 'Master Program in Sustainability Engineering & Management', 'body' => 'A flagship interdisciplinary program preparing professionals to lead sustainability transformation across industries. The program combines engineering, technology, management, and sustainability to equip graduates to translate sustainability goals into practical industrial outcomes.', 'link_url' => $program('Master in Sustainability Engineering & Management')],
            ]], 'programs'],
            ['image_text', ['eyebrow' => 'Centers', 'heading' => 'Experiential Learning Centre for Sustainable Energy & Circularity', 'body' => '<p>The Experiential Learning Centre for Sustainable Energy and Circularity (ELC:SEC) is a joint initiative of NTPC, IIT Roorkee, and NAMTECH established to advance sustainable manufacturing, energy transition, circularity and workforce transformation. By combining IIT Roorkee’s research excellence with NAMTECH’s industry-integrated learning ecosystem, and NTPC’s commitment to advance sustainable energy solutions the Centre aims to create a national model that connects translational research, talent development and technology deployment.</p>', 'image' => $this->media['turbine-engineer'], 'image_position' => 'left', 'layout' => 'side', 'tone' => 'light', 'cta_label' => 'Explore the Centre', 'cta_url' => '/centres/experiential-learning-center-for-sustainable-energy-circularity']],
            ['video_feature', ['layout' => 'split', 'eyebrow' => null, 'heading' => 'From the Director’s Desk', 'body' => '“We are committed to developing the next generation of sustainability-focused leaders, equipped with the engineering, digital, and AI capabilities needed to drive India’s transition to a greener economy. At NAMTECH’s School of Sustainability, our immersive and application-based learning model brings together advanced manufacturing, industrial sustainability, ESG, sustainable energy, and circularity.”', 'name' => 'Dr. Ibrahim Hafeezur Rehman', 'role' => 'Director, School of Sustainability', 'image' => $img('p086', 'Portrait of Dr. Ibrahim Hafeezur Rehman')]],
            ['people', ['eyebrow' => 'Faculty', 'heading' => 'The Experts Driving Your Learning', 'body' => 'Distinguished faculty at the School of Sustainability bring together expertise across engineering, sustainability, science, infrastructure, and energy systems, supported by perspectives from academia, industry, and policy.', 'school' => 'school-of-sustainability', 'layout' => 'carousel', 'limit' => 16]],
            ['media_cards', ['eyebrow' => 'Labs & Infrastructure', 'heading' => 'World-Class Experiential & Living Labs', 'body' => 'Living and experiential laboratories bring sustainability concepts to life, allowing learners to observe, analyze, and work with real systems. The infrastructure spans energy, water, waste, circularity, ESG analytics, and urban infrastructure to test theory in real-world conditions.', 'layout' => 'tall', 'cta_label' => 'Explore Labs', 'cta_url' => '/academics/labs-and-infrastructure', 'items' => $this->sustainabilityLabs()]],
            ['value_list', ['eyebrow' => null, 'heading' => 'Industry & Academic Partnerships', 'body' => 'The School of Sustainability works with leading industry and academic institutions to ensure its programs remain connected to real industrial challenges and emerging global practices. Partnerships span curriculum development, industry projects, internships, technology-enabled learning and applied research.', 'items' => [
                ['icon' => 'bolt', 'title' => 'Industry: National Thermal Power Corporation (NTPC)', 'text' => 'NTPC Limited, India’s largest integrated power utility, is supporting the establishment of the Centre for Sustainable Energy and Circularity through its Corporate Social Responsibility (CSR) initiative, enabling the development of experiential learning infrastructure, capacity-building programs and outreach initiatives focused on sustainable energy and circularity.'],
                ['icon' => 'handshake', 'title' => 'Industry: KPMG India', 'text' => 'KPMG in India is part of a global network of professional services firms providing audit, tax, and advisory services, with expertise in ESG and sustainability consulting. Its collaboration with NAMTECH brings industry perspectives into sustainability learning, helping students develop practical capabilities and global exposure relevant to India’s manufacturing transformation.'],
                ['icon' => 'graduation', 'title' => 'Academic: IIT Roorkee', 'text' => 'IIT Roorkee, an Institute of National Importance and a pioneer in engineering and technical education, brings its academic and research expertise to NAMTECH through the Experiential Learning Centre for Sustainable Energy & Circularity. The partnership is focused on research and innovation, setup of joint labs, development of academic programs, and faculty development to help advance solutions for India’s sustainable energy and circular economy ambitions.'],
                ['icon' => 'globe', 'title' => 'Academic: TUM Asia', 'text' => 'TUM Asia is Technical University of Munich (TUM) Singapore-based teaching campus. Our academic collaboration with TUM Asia brings global engineering perspectives and international academic expertise into NAMTECH’s sustainability ecosystem.'],
            ]]],
            ['events_carousel', ['eyebrow' => null, 'heading' => 'Upcoming Events', 'body' => null, 'cta_label' => 'View all events', 'cta_url' => '/events', 'school' => 'school-of-sustainability']],
            ['achievements', ['eyebrow' => null, 'heading' => 'Awards and Achievements', 'layout' => 'awards', 'items' => []], 'awards', false],
            ['news_carousel', ['heading' => 'News & Insights', 'post_type' => 'news', 'limit' => 8, 'cta_label' => 'View all insights', 'cta_url' => '/media']],
        ]);
    }

    private function sustainabilityLabs(): array
    {
        $labs = [
            ['Green Energy Lab', 'Living Lab', 'A hands-on environment focused on solar energy systems and smart management, enabling learners to analyze photovoltaic performance and test practical clean-energy solutions in campus facilities.', 'p130', 'Student working at a laptop beside turbine models'],
            ['Hydrogen Innovation Lab', 'Living Lab', 'A dedicated platform for exploring hydrogen technologies and their role in the future energy transition, providing an experiential foundation for understanding emerging clean-energy systems.', 'p127', 'Scientists working with laboratory equipment'],
            ['Water Stewardship Lab', 'Living Lab', 'Built around real water-management infrastructure including a Sewage Treatment Plant (STP) and wetland systems, enabling learners to understand water treatment, resource management and sustainable water practices.', 'p033', 'Researcher examining samples under a microscope'],
            ['Waste-to-Resource Lab', 'Living Lab', 'A demonstrated approach for converting waste into useful resources through technologies, including a bioreactor, waste-plastic-to-fuel unit and carbon-sequestration systems. The lab enables learners to explore practical pathways toward resource recovery and waste reduction.', 'p136', 'Plant cuttings in laboratory glassware'],
            ['Circularity Lab', 'Living Lab', 'A hands-on environment for exploring circular production and resource efficiency concepts, supported by 3D printing and analytical equipment that enable experimentation, prototyping and material-focused learning.', 'p054', 'Modular machine model on a workbench'],
            ['Sustainable Energy Lab', 'Experiential Lab', 'An experiential lab platform spanning wind, solar, and hydrogen technologies, giving learners analytical exposure to multi-source renewable systems and the technologies enabling the broader energy transition.', 'p030', 'Wind turbines'],
            ['Water Management Lab', 'Experiential Lab', 'A dedicated environment for exploring water technologies and process automation, including FESTO-based systems that allow learners to understand automated water-management processes and industrial control.', 'p120', 'Student working on an automation rig'],
            ['ESG Analytics Lab', 'Experiential Lab', 'A computer-based learning environment focused on sustainability analytics, life cycle assessment, project management, and digital sustainability tools. Learners work with platforms including SimaPro, IBM Envizi, Revit, and Microsoft Project Professional.', 'p129', 'Student analysing data on a laptop'],
            ['Urban Infrastructure Lab', 'Experiential Lab', 'An immersive platform connecting sustainability with built environments and urban systems. The lab incorporates IoT, sensors, BIM, AR/VR, and related digital technologies to explore smarter, more efficient, and resilient sustainable infrastructure.', 'p093', 'Urban planning model'],
        ];

        return array_map(fn (array $lab) => ['image' => $this->pdfImage($lab[3], $lab[4]), 'tag' => $lab[1], 'title' => $lab[0], 'body' => $lab[2]], $labs);
    }

    private function seedManufacturingLanding(array $schools): void
    {
        $school = $schools['school-of-manufacturing-technologies'];
        $img = fn (string $file, string $alt) => $this->pdfImage($file, $alt);
        $program = fn (string $title) => Program::query()->where('title', $title)->firstOrFail()->publicPath();
        $school->update(['summary' => 'NAMTECH empowers the next-generation of engineers to build, code, and operate the intelligent, connected factories of tomorrow.']);

        $this->attachSections($school, [
            ['page_hero', ['eyebrow' => 'Schools', 'title' => 'School of Manufacturing Technologies', 'body' => 'NAMTECH empowers the next-generation of engineers to build, code, and operate the intelligent, connected factories of tomorrow.', 'image' => $img('p053', 'Students working on industrial equipment with an engineer'), 'cta_label' => 'Explore Programs', 'cta_url' => '#programs']],
            ['intro', ['eyebrow' => null, 'heading' => 'About the School of Manufacturing Technologies', 'icon' => 'factory', 'body' => '<p>The School of Manufacturing Technologies at NAMTECH develops engineers who can become leaders across the two forces shaping India’s industrial future: the rise of intelligent, connected manufacturing and the scaling of the heavy industries that anchor the economy. As steel plants, ports, and mines become as data-driven as a smart factory, India’s Industry 4.0 revolution requires engineers capable of integrating digital precision with large-scale industrial operations.</p>']],
            ['pillars', ['eyebrow' => null, 'heading' => 'Core Pillars', 'items' => [
                ['title' => 'Smart and Digital Manufacturing', 'text' => 'From digital intelligence to physical making. Master the technologies that connect machines, systems, and people on the factory floor.', 'image' => $img('p131', 'Orange industrial robot arm')],
                ['title' => 'Heavy Industry Engineering', 'text' => 'Technology creates value when it works at scale. Build the heavy-industry mastery needed to apply smart technologies to the complex industrial assets driving the country.', 'image' => $img('p028', 'Technician in a hard hat operating equipment')],
                ['title' => 'Capital Projects and Operations Management', 'text' => 'From blueprint to reality, execution is the differentiator. Build the engineering and management discipline to deliver complex industrial projects seamlessly at scale.', 'image' => $img('p078', 'Engineer with a tablet on site stairs')],
                ['title' => 'Industrial Sustainability and Decarbonization', 'text' => 'The next industrial revolution must be sustainable by design. Master the technologies and practices that make manufacturing cleaner, leaner, and future-ready.', 'image' => $img('p030', 'Wind turbines on a snowy hillside')],
            ]]],
            ['media_cards', ['eyebrow' => 'Programs', 'heading' => 'Programs Built for Impact', 'layout' => 'tall', 'card_link_label' => 'Explore program', 'items' => [
                ['image' => $this->media['program-student-hardware'], 'tag' => 'UG', 'title' => 'B.Tech. Mechanical Engineering (Smart Manufacturing)', 'body' => 'From engineering fundamentals to intelligent manufacturing. Build, experiment, and solve through hands-on learning across mechanical engineering, robotics, automation, advanced manufacturing, and digital technologies – developing the technical depth and maker mindset to lead the factories of tomorrow.', 'link_url' => $program('B.Tech. Mechanical Engineering (Smart Manufacturing)')],
                ['image' => $this->media['experiential-learning-robotics'], 'tag' => 'PG', 'title' => 'Master in Smart Manufacturing Technology and Management', 'body' => 'Prepares engineers to build and lead the intelligent, connected factory through a maker-first approach. Students work hands-on from day one across additive manufacturing, mechatronics, industrial robotics, smart automation, and digital twins, while building management depth in supply chain, operational excellence, quality, reliability, and product lifecycle management.', 'link_url' => $program('Master in Smart Manufacturing Technology & Management')],
                ['image' => $img('p028', 'Technician in a hard hat operating equipment'), 'tag' => 'PG', 'title' => 'Master in Heavy Industry Engineering and Management', 'body' => 'Developed in collaboration with the ArcelorMittal Design and Engineering Center (AMDEC), this program prepares engineers for heavy industries like steel manufacturing, mining and mineral processing, ports, and capital infrastructure. It combines a rigorous engineering foundation with three specialization tracks and an extended paid industry residency at AMDEC.', 'link_url' => $program('Master in Heavy Industry Engineering & Management')],
                ['image' => $this->media['precision-machining'], 'tag' => 'Executive', 'title' => 'iPMPX (Smart Manufacturing)', 'body' => 'Develop the strategic perspective and technological depth to harness smart manufacturing, AI, automation, and connected systems to lead industrial transformation at scale.', 'link_url' => $program('iPMPX Smart Manufacturing')],
            ]], 'programs'],
            ['video_feature', ['layout' => 'split', 'eyebrow' => null, 'heading' => 'From the Director’s Desk', 'body' => '“India aims to lift manufacturing from around 16 percent of GDP toward 30 percent by 2047. That ambition rests on two pillars: the intelligent, connected factory, where data, automation, and AI redefine how things are made, and heavy industry; the steel plants, mines, ports, and capital projects on which everything else is built. That is why this School exists. The School is built for students who learn by making, getting hands-on with the robotics, digital twins, and AI systems that define the modern factory. Our mission is to create engineers and industry leaders who merge technology with sustainability, innovation with purpose, and productivity with resilience, and who will help carry India toward a globally competitive, self-reliant, and intelligent manufacturing future.”', 'name' => 'Dr. Sanjeev Gupta', 'role' => 'Dean – Quality Assurance & Rankings; Director – School of Manufacturing Technologies', 'image' => $img('p058', 'Portrait of Dr. Sanjeev Gupta')]],
            ['people', ['eyebrow' => 'Faculty', 'heading' => 'The Experts Driving Your Learning', 'body' => 'The School’s residential & visiting faculty brings together global research credentials, deep technical grounding, and elite academic backgrounds to guide the next generation of industrial leaders.', 'school' => 'school-of-manufacturing-technologies', 'layout' => 'carousel', 'limit' => 16]],
            ['card_grid', ['eyebrow' => 'Labs & Infrastructure', 'heading' => 'WorldSkills-Aligned Industry 4.0 Laboratories', 'body' => 'Our advanced laboratories give students hands-on experience with globally benchmarked platforms across the full manufacturing lifecycle.', 'style' => 'joined', 'columns' => '3', 'tone' => 'white', 'cards' => array_map(fn (array $lab) => ['icon' => $lab[2], 'title' => $lab[0], 'body' => $lab[1]], [
                ['Precision Engineering Lab', 'Precision machining, metrology, and dimensional inspection on industry-grade equipment.', 'gear'],
                ['CAD/CAM Lab', 'Computer-aided design and manufacturing, digital modeling, and toolpath generation.', 'code'],
                ['Industrial Automation Lab', 'PLC, SCADA, sensors, and control systems for discrete and process automation.', 'factory'],
                ['Additive Manufacturing Lab', 'Metal and polymer 3D printing, rapid prototyping, and design for additive manufacturing.', 'lightbulb'],
                ['Welding 4.0 Lab', 'Advanced and automated welding processes with digital monitoring.', 'bolt'],
                ['Mechatronics Lab', 'Integrated mechanical, electronic, and control systems for smart machinery.', 'chip'],
                ['Industrial Robotics Lab', 'Six-axis industrial robots, robotic cells, and automation integration.', 'robot'],
                ['AR/VR Immersive Learning Lab', 'Augmented and virtual reality for immersive training and process simulation.', 'sparkle'],
                ['Hydraulics & Pneumatics Lab', 'Fluid power systems, actuation, and industrial motion control.', 'magnet'],
                ['Smart Supply Chain & Digital Lab', 'Data-driven logistics, digital twins, and connected factory operations.', 'chart'],
                ['Computer Center', 'High-performance computing for simulation, analytics, and digital engineering.', 'building'],
            ])]],
            ['card_grid', ['eyebrow' => 'Partnerships', 'heading' => 'Academic Partnerships and Industry Alliances', 'body' => 'Global automation leaders supporting our Smart Manufacturing labs with industrial robots, automation platforms, and didactic systems.', 'style' => 'outlined', 'columns' => '4', 'tone' => 'lavender', 'cards' => [
                ['icon' => 'factory', 'title' => 'Siemens'], ['icon' => 'factory', 'title' => 'ABB'], ['icon' => 'robot', 'title' => 'FANUC'],
                ['icon' => 'factory', 'title' => 'Rockwell Automation'], ['icon' => 'gear', 'title' => 'Festo'], ['icon' => 'bolt', 'title' => 'Schneider Electric'],
                ['icon' => 'handshake', 'title' => 'ArcelorMittal Design and Engineering Center (AMDEC) and AMNS India', 'body' => 'AMNS India is a founding partner behind NAMTECH. AMDEC has co-designed and co-delivers our Master Program in Heavy Industry Engineering and Management, offering teaching, research scoping, internships, paid industry residencies, and direct career pipelines.'],
                ['icon' => 'building', 'title' => 'L&T Heavy Engineering', 'body' => 'Provides students with real-world exposure to large-scale heavy engineering manufacturing, equipment design, and fabrication practices.'],
                ['icon' => 'graduation', 'title' => 'TUM Asia (Technical University of Munich)'], ['icon' => 'graduation', 'title' => 'Purdue University Northwest'],
                ['icon' => 'graduation', 'title' => 'Carnegie Mellon University'], ['icon' => 'graduation', 'title' => 'Dept of Mechanical Engineering, Massachusetts Institute of Technology'],
            ]]],
        ]);
    }

    private function seedSmartManufacturingMaster(): void
    {
        $program = Program::query()->where('title', 'Master in Smart Manufacturing Technology & Management')->firstOrFail();
        $img = fn (string $file, string $alt) => $this->pdfImage($file, $alt);
        $program->update([
            'mode' => 'Full-time, residential',
            'intake' => 'August 2027',
            'application_deadline' => null,
            'duration' => '24 Months',
            'duration_detail' => '4 Semesters',
        ]);
        $list = fn (array $items) => '<ul>'.implode('', array_map(fn (string $item) => '<li>'.e($item).'</li>', $items)).'</ul>';

        $this->attachSections($program, [
            ['page_hero', ['eyebrow' => 'School of Manufacturing Technologies', 'title' => 'Master Program in Smart Manufacturing Technology and Management', 'body' => 'NAMTECH equips graduates with the operational mastery, data-driven acumen and strategic depth needed to command the Industry 4.0 revolution. Co-created with globally leading universities and industry titans, the program transforms high-caliber engineers into skilled techno-managers who design, build and manage smart factories.', 'image' => $img('p118', 'Students with a collaborative robot'), 'cta_label' => 'Apply Now', 'cta_url' => '/admissions#enquire', 'secondary_label' => 'Enquire', 'secondary_url' => '#enquire', 'meta' => [
                ['icon' => 'clock', 'text' => '24 Months · 4 Semesters'],
                ['icon' => 'calendar', 'text' => 'Intake: August 2027'],
                ['icon' => 'building', 'text' => 'Full-time, residential'],
            ]]],
            ['intro', ['eyebrow' => null, 'heading' => 'Program Overview', 'icon' => 'factory', 'body' => '<p>The Master Program in Smart Manufacturing Technology and Management is a two-year residential program to build industry-ready techno-managers for Manufacturing Engineering Technology (MET) sectors. Offered in collaboration with Technical University of Munich (TUM Asia, Singapore) and incorporating MIT’s landmark 2.008 Design and Manufacturing II framework customized for NAMTECH (2.008 N), the curriculum delivers deep, hands-on expertise in additive manufacturing, mechatronics, industrial robotics, smart automation and digital twin ecosystems, complemented by executive-level management training.</p><p>Using globally benchmarked, WorldSkills-aligned platforms, students solve high-impact and live industrial challenges with guidance from world-class faculty and global industry leaders. Students graduate fully prepared to build, integrate and lead the manufacturing transformation of India.</p>']],
            ['feature_list', ['eyebrow' => null, 'heading' => 'Program Snapshot', 'tabs' => [['label' => null, 'items' => [
                ['icon' => 'clock', 'text' => 'Duration: 24 Months · 4 Semesters (16 weeks per semester + industry internship & capstone)'],
                ['icon' => 'calendar', 'text' => 'Intake: August 2027'],
                ['icon' => 'building', 'text' => 'Mode: Full-time, residential'],
                ['icon' => 'graduation', 'text' => 'Eligibility: B.E./ B.Tech. across engineering branches (Minimum score of 60% in 10th, 12th & graduation)'],
                ['icon' => 'document', 'text' => 'Application Deadline: August 2027'],
            ]]]]],
            ['card_grid', ['eyebrow' => null, 'heading' => 'The NAMTECH Advantage', 'style' => 'joined', 'columns' => '3', 'tone' => 'white', 'cards' => [
                ['icon' => 'lightbulb', 'title' => 'Future-Ready Techno-Managerial Focus', 'body' => 'NAMTECH fuses cutting-edge engineering and smart factory technology with executive leadership, data-driven technologies and sustainability, shaping graduates for next-generation careers.'],
                ['icon' => 'gear', 'title' => 'Built on MIT’s Hands-On Philosophy', 'body' => 'The curriculum integrates MIT’s gold-standard 2.008 Design and Manufacturing framework customized for NAMTECH (2.008N), establishing a true “experiential” foundation essential for Industry 4.0.'],
                ['icon' => 'globe', 'title' => 'Learn from Global Experts', 'body' => 'Learn directly from distinguished international faculty from Technical University of Munich, Asia (TUM Asia), Carnegie Mellon University and other leading universities alongside industry experts from Siemens, Schneider Electric, Cisco, Festo and L&T.'],
                ['icon' => 'trend', 'title' => 'Proven Placement Track Record', 'body' => '100% placement record for eligible students across top-tier recruiters including AM/NS India, Micron, Festo and Schneider Electric among others.'],
                ['icon' => 'rupee', 'title' => 'Scholarships and Financial Support', 'body' => 'Access merit-based scholarships of up to 100% on tuition, plus education loan support from leading banks, making our advanced programs accessible to aspiring engineers.'],
                ['icon' => 'handshake', 'title' => 'Co-Designed with Industry Giants', 'body' => 'Developed and delivered collaboratively with industry leaders and academic experts to ensure that graduates are industry-ready from day one.'],
            ]]],
            ['stat_cards', ['eyebrow' => null, 'heading' => 'Class of 2026', 'body' => null, 'tone' => 'lavender', 'items' => []], 'class-of-2026', false],
            ['value_list', ['eyebrow' => null, 'heading' => 'Program Partners', 'body' => null, 'items' => [
                ['icon' => 'graduation', 'title' => 'Academic Partner: Technical University of Munich (TUM Asia, Singapore)', 'text' => 'A globally top-ranked technical university, TUM Asia has co-designed the curriculum with NAMTECH and offers deep academic collaboration, embedding world-class German technical education standards into NAMTECH.'],
                ['icon' => 'graduation', 'title' => 'Academic Partner: Department of Mechanical Engineering, Massachusetts Institute of Technology (USA)', 'text' => 'Blending rigorous theory with intensive hands-on making, MIT’s Department of Mechanical Engineering provides the foundational 2.008 Design & Manufacturing II framework, customized for NAMTECH (2.008N) that guides NAMTECH’s “experiential learning” ethos.'],
                ['icon' => 'graduation', 'title' => 'Academic Partner: Carnegie Mellon University (USA)', 'text' => 'Carnegie Mellon University, an internationally renowned research institution, enables faculty exchange and advanced research pedagogy transfer to NAMTECH.'],
                ['icon' => 'graduation', 'title' => 'Academic Partner: Purdue University Northwest (USA)', 'text' => 'Purdue University Northwest offers an exclusive 1+1 pathway to a Master of Science in Mechanical Engineering for eligible students, unlocking global academic acceleration.'],
                ['icon' => 'factory', 'title' => 'Industry and Technology Partners: Siemens, ABB, FANUC, Rockwell Automation, Festo, Schneider Electric, Cisco and Analog Devices', 'text' => 'Global automation and technology leaders supporting Industry 4.0 laboratories and the curriculum with equipment, platforms, and training.'],
                ['icon' => 'handshake', 'title' => 'Industry Partners: AMNS India and L&T Heavy Engineering', 'text' => 'Anchor industry partners providing industrial mentorship, live manufacturing projects and recruitment.'],
            ]]],
            ['tabs', ['eyebrow' => null, 'heading' => 'Program Structure', 'body' => 'The Master Program in Smart Manufacturing Technology and Management spans four 16-week semesters, equipping learners with technical mastery, hands-on laboratory experience, live industry capstone projects and management expertise required to power the future of smart manufacturing.', 'layout' => 'standard', 'tabs' => [
                ['label' => 'Semester 1', 'title' => 'Semester 1', 'body' => $list(['Precision Engineering and Manufacturing Processes', 'Machine Systems and Industrial Mechatronics', 'Foundations of Smart Manufacturing Systems', 'Industrial Automation', 'Management: Operational Excellence through Lean Principles', 'Management: Environment, Health, Safety and Sustainability (EHSS)', 'Professional Skills: Communication and Leadership Skills'])],
                ['label' => 'Semester 2', 'title' => 'Semester 2', 'body' => $list(['Design for Manufacturing (2.008N) 1 & 2', 'Digitization, IIoT and Edge Computing', 'AI and Data Analytics for Manufacturing', 'Management: Project Management and Product Lifecycle Management', 'Management: Supply Chain Management', 'Professional Skills: Design Thinking and Innovation'])],
                ['label' => 'Semester 3', 'title' => 'Semester 3', 'body' => $list(['Industrial Robotics', 'Process Automation and Control Systems', 'Digital Twin and Immersive Technologies', 'Cyber-Physical and Manufacturing Execution Systems', 'Reliability Engineering and Asset Health Intelligence', 'Management: Financial Analysis and Management', 'Professional Skills: Entrepreneurship and New Venture Creation'])],
                ['label' => 'Semester 4', 'title' => 'Semester 4', 'body' => $list(['Industry Internship / Capstone Project'])],
            ]], 'curriculum'],
            ['rich_text', ['heading' => 'The NAMTECH Learning Model', 'width' => 'wide', 'body' => '<p>At NAMTECH, we believe learning is best driven through immersive, experiential education, which is why core technical courses at NAMTECH follow a 1-0-4 model, with one hour of theory to four hours of laboratory work, reflecting a strong practical orientation.</p><p>This inverted approach places practical application at the heart of education at NAMTECH.</p>'
                .'<table><thead><tr><th>Learning Component</th><th>Approximate Share</th></tr></thead><tbody>'
                .'<tr><td>Practical / Laboratory work</td><td>~40–55%</td></tr><tr><td>Theory / Lectures</td><td>~35–40%</td></tr>'
                .'<tr><td>Projects, Internship &amp; Capstone</td><td>~5–10%</td></tr><tr><td>Assessments / Examinations</td><td>~5%</td></tr></tbody></table>'
                .'<p><strong>Competency-Based Learning:</strong> Students focus on mastering skills and expertise rather than simply applying concepts in the classroom to ensure real-world industrial readiness.</p>'
                .'<p><strong>Project-Based Learning:</strong> Every student solves real-world problems through projects co-designed with NAMTECH’s industry partners, turning theory into impact right from the start.</p>'
                .'<p><strong>Competition-Based Learning:</strong> Challenges and competitions modeled on global industry scenarios and standards push students to collaborate, innovate and raise the bar for industry excellence.</p>']],
            ['card_grid', ['eyebrow' => null, 'heading' => 'Labs and Infrastructure', 'body' => 'NAMTECH’s specialized labs feature industry-grade laboratory infrastructure designed for students to master operational expertise and efficiency in real smart manufacturing environments.', 'style' => 'joined', 'columns' => '4', 'tone' => 'white', 'cards' => array_map(fn (array $lab) => ['icon' => $lab[2], 'title' => $lab[0], 'body' => $lab[1]], [
                ['Additive Manufacturing Lab', 'Metal and polymer 3D printing, rapid prototyping and design for additive manufacturing.', 'lightbulb'],
                ['Mechatronics Lab', 'Integrated mechanical, electronic and control systems for smart machinery.', 'chip'],
                ['Industrial Robotics Lab', 'Industrial robots, robotic cells and automation integration.', 'robot'],
                ['Smart Automation Lab', 'PLC, SCADA, sensors and control systems for discrete and process automation.', 'factory'],
                ['Precision Engineering Lab', 'Precision machining, metrology and dimensional inspection.', 'gear'],
                ['CAD / CAM Lab', 'Computer-aided design and manufacturing, digital modeling and toolpaths.', 'code'],
                ['Digital Twin and Immersive Lab', 'Digital twins, AR/VR/XR simulation and connected operations.', 'sparkle'],
                ['AI and Data Analytics Lab', 'Manufacturing data analytics, cloud computing and AI applications.', 'chart'],
            ])]],
            ['post_list', ['heading' => 'Student Innovations', 'post_type' => 'story', 'limit' => 3, 'layout' => 'carousel', 'cta_label' => 'All stories', 'cta_url' => '/impact/stories'], 'innovations', false],
            ['steps', ['eyebrow' => null, 'heading' => 'Admission Process', 'body' => null, 'layout' => 'panel', 'panel_title' => 'Steps', 'image' => $img('p096', 'Applicant meeting the admissions team'), 'items' => [
                ['title' => 'Apply online at admissions.namtech.ac & pay ₹1000/- application fee.'],
                ['title' => 'Complete your application and submit all required documents.'],
                ['title' => 'If shortlisted, attend a personal interview.'],
                ['title' => 'Receive your admission offer.'],
                ['title' => 'Confirm your seat & pay tuition fee.'],
            ]]],
            ['eligibility', ['eyebrow' => null, 'heading' => 'Eligibility Criteria', 'criteria' => [
                ['text' => 'Candidates with B.E./B.Tech. with a minimum score of 60% in 10th, 12th, and graduation or equivalent CGPA from a recognized Indian university or institute. (No active backlog)'],
                ['text' => 'Scores on CAT, XAT, GMAT, GRE, GATE are preferred.'],
                ['text' => 'Open school students are not eligible.'],
            ], 'list_heading' => 'Eligible Engineering Branches', 'items' => array_map(fn (array $b) => ['icon' => $b[1], 'text' => $b[0]], [
                ['Mechanical Engineering', 'gear'], ['Electrical Engineering', 'bolt'], ['Electronics Engineering', 'chip'], ['Computer Science Engineering', 'code'],
                ['Industrial Engineering', 'factory'], ['Material Science Engineering', 'flask'], ['Automobile Engineering', 'gear'], ['Data Science & AI', 'chart'],
                ['Aeronautical Engineering', 'drone'], ['Instrumentation & Control Engineering', 'magnet'],
            ])]],
            ['checklist_cards', ['eyebrow' => null, 'heading' => 'Required Documents', 'tone' => 'light', 'cards' => [
                ['icon' => 'graduation', 'title' => 'Academic records', 'items' => [['text' => '10th and 12th mark sheet'], ['text' => 'ITI mark sheets and Final Certificate (if applicable)'], ['text' => 'Diploma mark sheets and Degree Certificate']]],
                ['icon' => 'id', 'title' => 'Identity and other documents', 'items' => [['text' => 'ID Proof (Aadhaar Card)'], ['text' => 'Income Certificate'], ['text' => 'Employment and Experience Certificate (if applicable)']]],
            ]]],
            ['stat_cards', ['eyebrow' => null, 'heading' => 'Key Outcomes and Impact', 'body' => '100% placements for eligible students across previous batches. The highest package was ₹16 LPA and the average package was ₹8.49 LPA, with recruiters including AMNS India, Micron, Festo, Schneider Electric and others.', 'tone' => 'white', 'items' => [
                ['icon' => 'trend', 'value' => '100%', 'label' => 'Placements for eligible students across previous batches'],
                ['icon' => 'rupee', 'value' => '₹16 LPA', 'label' => 'Highest package'],
                ['icon' => 'chart', 'value' => '₹8.49 LPA', 'label' => 'Average package'],
            ]]],
            ['card_grid', ['eyebrow' => null, 'heading' => 'Build Your Future in These Roles', 'style' => 'outlined', 'columns' => '3', 'tone' => 'lavender', 'cards' => [
                ['icon' => 'factory', 'title' => 'Manufacturing Systems Engineer', 'body' => 'Design and optimize integrated production systems combining machines, automation and data.'],
                ['icon' => 'robot', 'title' => 'Automation and Robotics Engineer', 'body' => 'Program and integrate industrial robots and automated cells.'],
                ['icon' => 'sparkle', 'title' => 'Digital Twin Engineer', 'body' => 'Build virtual replicas of processes and plants to simulate, monitor and optimize operations.'],
                ['icon' => 'lightbulb', 'title' => 'Additive Manufacturing Engineer', 'body' => 'Develop and run metal and polymer 3D-printing processes for prototyping and production.'],
                ['icon' => 'check', 'title' => 'Smart Operations and Quality Lead', 'body' => 'Drive operational excellence, quality and reliability using lean and data-driven methods.'],
                ['icon' => 'chart', 'title' => 'Industrial Data and AI Analyst', 'body' => 'Turn machine and process data into insight that improves productivity and uptime.'],
            ]]],
            ['testimonials', ['eyebrow' => null, 'heading' => 'Hear from Recruiters', 'layout' => 'quotes', 'items' => []], 'recruiters', false],
            ['fees', ['eyebrow' => null, 'heading' => 'Fees and Scholarships', 'body' => null, 'fee_label' => 'Course Fee', 'fee_amount' => null,
                'rows' => [
                    ['label' => 'Tuition (Inclusive of 18% GST) – Course Fee', 'value' => '₹10,00,000/-'],
                    ['label' => 'Tuition – First Year (2027–2028)', 'value' => '₹5,00,000/-'],
                    ['label' => 'Tuition – Second Year (2028–2029)', 'value' => '₹5,00,000/-'],
                    ['label' => 'Residence (Inclusive of 5% GST) – Course Fee', 'value' => '₹4,00,000/-'],
                    ['label' => 'Residence – First Year (2027–2028)', 'value' => '₹2,00,000/-'],
                    ['label' => 'Residence – Second Year (2028–2029)', 'value' => '₹2,00,000/-'],
                ],
                'scholarships_title' => 'Scholarships',
                'scholarships' => [
                    ['title' => 'Up to 75% on tuition fees', 'text' => 'NAMTECH offers a scholarship of up to 75% on tuition fees, based on student academic performance, interview process and family income. Please note that seats are limited and the final decision rests with the admissions & scholarships committee.'],
                ],
                'link_label' => 'Know more about scholarships', 'link_url' => '/admissions#faq',
                'notes' => [
                    ['icon' => 'bank', 'title' => 'Admissions/Seat Blocking Fee', 'text' => 'A refundable fee of ₹25,000/- is payable on acceptance of the offer to confirm admission within the time stated in the LOI, subject to seat availability.'],
                    ['icon' => 'check', 'title' => 'Security Deposit & Refunds', 'text' => 'This amount doubles as your refundable security deposit. It will be fully refunded upon graduation, minus any outstanding dues. This amount will also be refunded if you withdraw from the program.'],
                ],
            ]],
            ['feature_list', ['eyebrow' => null, 'heading' => 'Education Loan Assistance', 'body' => 'Invest in your future with confidence. NAMTECH has partnered with leading banks and education finance providers to make funding your master’s program simple and accessible. Eligible students can avail loans covering up to 100% of tuition, hostel and other education-related expenses, with flexible repayment options and collateral-free schemes available through select partners.', 'tabs' => [['label' => null, 'items' => [
                ['icon' => 'rupee', 'text' => 'Loan coverage of up to 100% of the cost of education (including tuition and hostel fees through select lenders)'],
                ['icon' => 'check', 'text' => 'Collateral-free loan options available for eligible applicants'],
                ['icon' => 'calendar', 'text' => 'Repayment tenure of up to 15 years'],
                ['icon' => 'document', 'text' => 'Tax benefits on interest paid under Section 80E'],
                ['icon' => 'bank', 'text' => 'Partnerships with ICICI Bank, Axis Bank, Credila and EDUAID for streamlined financing support'],
                ['icon' => 'people', 'text' => 'Dedicated admissions guidance to help students through the loan application process'],
            ]]]]],
            $this->studentLifeSection(),
            ['testimonials', ['eyebrow' => null, 'heading' => 'Student Testimonials', 'layout' => 'portrait', 'items' => []], 'testimonials', false],
            ['faq', ['heading' => 'Frequently Asked Questions', 'body' => null, 'layout' => 'centered', 'items' => array_map(fn (array $qa) => ['question' => $qa[0], 'answer' => '<p>'.e($qa[1]).'</p>'], [
                ['What is the NAMTECH Master Program in Smart Manufacturing Technology & Management?', 'A two-year residential program preparing engineers as techno-managers in smart manufacturing, integrating MIT’s 2.008 hands-on design and manufacturing approach customized for NAMTECH (2.008N) with management depth.'],
                ['What is the eligibility?', 'B.E./ B.Tech. with minimum 60% in 10th, 12th and graduation, no active backlog. CAT, XAT, GMAT, GRE, or GATE preferred.'],
                ['How is the program delivered?', 'On campus at NAMTECH, IIT Gandhinagar, Gujarat, over four 16-week semesters.'],
                ['What are the placement outcomes?', '100% placement for eligible students across previous three batches, with a highest package of ₹16 LPA and an average of ₹8.49 LPA.'],
                ['Do I need a background in smart manufacturing to apply?', 'No, the program is designed for engineering graduates from electronics, electrical, mechanical, materials, instrumentation and allied disciplines.'],
                ['What is the medium of instruction?', 'All instruction, coursework and assessments are conducted in English.'],
                ['Is on-campus accommodation available?', 'Yes, the program is residential, with accommodation available on-campus. The details and fees are outlined in the Fees & Scholarship section.'],
                ['Are scholarships available?', 'Yes, merit-based scholarships of up to 75% on tuition, based on academic performance, interview and family income.'],
                ['Is a loan facility available?', 'Yes, through partnerships with ICICI Bank, Axis Bank, Credila and EDUAID.'],
            ])], 'faq'],
            ['enquiry_form', ['heading' => 'Enquire about this program', 'body' => 'Tell us about yourself and our admissions team will get back to you.', 'form_type' => 'program', 'show_contact_details' => true], 'enquire'],
        ]);
    }

    private function studentLifeSection(): array
    {
        return ['feature_list', ['eyebrow' => null, 'heading' => 'Student Life', 'body' => 'Students have access to a wide array of campus clubs and societies that shape their perspectives inside and outside the classroom. Some of NAMTECH’s best inventions and collaborations are the result of our clubs and societies.', 'tabs' => [['label' => null, 'items' => array_map(fn (string $club) => ['icon' => 'people', 'text' => $club], [
            'ASM International Club', 'IEEE (Institute of Electrical and Electronics Engineers) Club', 'ISTE (Indian Society of Technical Education) Club',
            'NAMTECH 3D Printing Club', 'NAMTECH Alumni Club', 'NAMTECH Entrepreneurship and Innovation Club', 'NAMTECH NSS (National Service Scheme) Club',
            'NAMTECH Sports Club', 'NAMTECH Sustainability Club', 'Robotics Club', 'SAEINDIA (Society of Automotive Engineers) Collegiate Club',
            'SPICES (Scheme for Promoting Interests, Creativity and Ethics) Cultural Club', 'Stagemaster’s Club',
        ])]]]];
    }

    private function seedSustainabilityMaster(): void
    {
        $program = Program::query()->where('slug', 'master-in-sustainability-engineering-and-management')->firstOrFail();
        $img = fn (string $file, string $alt) => $this->pdfImage($file, $alt);
        $program->update([
            'mode' => 'Full-time, On-campus',
            'intake' => 'August 2027',
            'application_deadline' => null,
            'duration' => '24 Months',
            'duration_detail' => '4 Semesters',
        ]);
        $list = fn (array $items) => '<ol>'.implode('', array_map(fn (string $item) => '<li>'.e($item).'</li>', $items)).'</ol>';

        $this->attachSections($program, [
            ['page_hero', ['eyebrow' => 'School of Sustainability', 'title' => 'Master Program in Sustainability Engineering and Management', 'body' => 'Created in collaboration with KPMG in India and Technical University of Munich (TUM), Asia, students solve real-world sustainability challenges through hands-on learning and industry collaboration, combining advanced manufacturing with ESG frameworks, circular economy and climate innovations.', 'image' => $img('p130', 'Student working at a laptop beside turbine models'), 'cta_label' => 'Apply Now', 'cta_url' => '/admissions#enquire', 'secondary_label' => 'Enquire', 'secondary_url' => '#enquire', 'meta' => [
                ['icon' => 'clock', 'text' => '24 Months · 4 Semesters'],
                ['icon' => 'calendar', 'text' => 'Intake: August 2027'],
                ['icon' => 'building', 'text' => 'Full-time, On-campus'],
            ]]],
            ['intro', ['eyebrow' => null, 'heading' => 'Program Overview', 'icon' => 'leaf', 'body' => '<p>The Master Program in Sustainability Engineering and Management is a two-year, full-time, residential master’s degree crafted to shape the next generation of green industrial leaders. Co-created with industrial and academic powerhouses like KPMG in India and TUM Asia, this program blends engineering, management and sustainability, equipping graduates with skills and technical expertise to drive a climate-resilient industrial transformation.</p><p>Delivered through interactive sessions, the program features real-world projects addressing sustainability challenges, industry-led sessions offering global perspectives and a capstone project applying interdisciplinary learning in a hands-on setting.</p><p>The program is organized into Core Courses and Electives. Core Courses (~60% of the program curricula) are mandatory foundational and advanced subjects in sustainability, while electives are specialized tracks in ESG, policy, entrepreneurship and digital transformation, which account for ~40% of the program curricula.</p>']],
            ['feature_list', ['eyebrow' => null, 'heading' => 'Program Snapshot', 'tabs' => [['label' => null, 'items' => [
                ['icon' => 'clock', 'text' => 'Duration: 24 Months · 4 Semesters (Includes 10-Week Industry Internship + 24-Week Capstone Project)'],
                ['icon' => 'calendar', 'text' => 'Intake: August 2027'],
                ['icon' => 'building', 'text' => 'Mode: Full-time, On-campus'],
                ['icon' => 'graduation', 'text' => 'Eligibility: B.Tech. /B.E. or Master in Science/Math in relevant engineering specializations (Minimum score of 60% or equivalent CGPA)'],
                ['icon' => 'document', 'text' => 'Application Deadline: August 2027'],
            ]]]]],
            ['card_grid', ['eyebrow' => null, 'heading' => 'The NAMTECH Advantage', 'style' => 'joined', 'columns' => '3', 'tone' => 'white', 'cards' => [
                ['icon' => 'handshake', 'title' => 'Co-Created with Global Leaders', 'body' => 'A first-of-its-kind master program co-developed with TUM Asia and KPMG in India, combining world-class academics with industry-led learning.'],
                ['icon' => 'lightbulb', 'title' => 'Future-Ready Interdisciplinary Curriculum', 'body' => 'Master emerging domains like AI, data analytics, digital twins, sustainable finance and circular design, converging engineering, sustainability and management.'],
                ['icon' => 'flask', 'title' => 'Experiential Learning in Advanced Labs', 'body' => 'Train in NAMTECH’s experiential sustainability Labs through live projects, simulations and industry immersion.'],
                ['icon' => 'chart', 'title' => 'KPMG in India Advantage', 'body' => 'Learn from an industry leader strengthening ESG frameworks, driving decarbonization strategies and solving systems transformation challenges across industry sectors.'],
                ['icon' => 'globe', 'title' => 'Global Immersion Opportunity', 'body' => 'Experience global academic and industrial standards at TUM Asia and TUM, connecting with international sustainability ecosystems and faculty.'],
                ['icon' => 'leaf', 'title' => 'Gateway to a Green Economy', 'body' => 'Enter a high-growth sustainability sector contributing to the growing green economy, driving India’s low-carbon transformation.'],
            ]]],
            ['stat_cards', ['eyebrow' => null, 'heading' => 'Class of 2026', 'body' => null, 'tone' => 'lavender', 'items' => [
                ['icon' => 'people', 'value' => '50%', 'label' => 'Gender Diversity', 'text' => '50% women'],
                ['icon' => 'trend', 'value' => '100%', 'label' => 'Placement Record', 'text' => '100% placement rate'],
                ['icon' => 'graduation', 'value' => '70%', 'label' => 'Batch Profile', 'text' => '70% of students have no work experience'],
            ]]],
            ['value_list', ['eyebrow' => null, 'heading' => 'Program Partners', 'body' => null, 'items' => [
                ['icon' => 'graduation', 'title' => 'Academic Partner: Technical University of Munich (TUM) Asia', 'text' => 'TUM Asia is TUM’s Singapore-based teaching campus. Having co-designed the curriculum with NAMTECH, TUM Asia is dedicated to delivering a world-class sustainability engineering education along with embedding international academic excellence and research in Asia.'],
                ['icon' => 'handshake', 'title' => 'Industry and Technology Partner: KPMG in India', 'text' => 'KPMG in India is part of a global network of firms providing audit, tax, and advisory services. KPMG in India is a recognized leader in ESG and sustainability consulting, helping organizations transition to climate-responsible and resilient business models. Their partnership with NAMTECH equips students with advanced industrial skills and provides exposure to global sustainability standards.'],
            ]]],
            ['tabs', ['eyebrow' => null, 'heading' => 'Program Curriculum', 'layout' => 'standard', 'tabs' => [
                ['label' => 'Semester 1', 'title' => 'Semester – 1', 'body' => $list(['Digital Platforms for ESG - Reporting, Measurement and Certification', 'Industrial Emission Modeling and Decarbonization Pathways', 'LCA & Circular Economy – Digital Tools', 'Sustainability Tech and AI', 'Operational Excellence through Lean Principles', 'Environment, Health, Safety and Sustainability (EHSS)', 'Professional Skills – 1: Communication and Leadership Skills'])],
                ['label' => 'Semester 2', 'title' => 'Semester – 2', 'body' => $list(['Renewable Energy & Sustainability Strategies for Conventional Energy', 'AI and Simulation for Energy Conversion Systems', 'Energy Storage and Management Solutions', '3D Simulation and Modeling, Digital Urban Transformation', 'Project Management and Product Lifecycle Management', 'Supply Chain Management', 'Professional Skills – 2: Design Thinking and Innovation'])],
                ['label' => 'Semester 3', 'title' => 'Semester – 3', 'body' => $list(['Industrial Water Management Technologies', 'Sustainable Infrastructure, Transport and Value Chains', 'Environmental and Resource Economics', 'Capstone Project (A)', 'Financial Analysis and Management', 'Strategic Leadership and Management', 'Professional Skills – 3: Entrepreneurship and New Venture Creation'])],
                ['label' => 'Semester 4', 'title' => 'Semester – 4', 'body' => $list(['Industry Internship / Capstone Project (B)'])],
            ]], 'curriculum'],
            ['rich_text', ['heading' => 'The NAMTECH Learning Model', 'width' => 'wide', 'body' => '<p>We believe learning is best driven through immersive, experiential education, which is why core technical courses at NAMTECH follow a 1-0-4 model, with one hour of theory to four hours of laboratory work, reflecting a strong practical orientation.</p>'
                .'<table><thead><tr><th>Learning Component</th><th>Approximate Share</th></tr></thead><tbody>'
                .'<tr><td>Practical / Laboratory work</td><td>~40–55%</td></tr><tr><td>Theory / Lectures</td><td>~35–40%</td></tr>'
                .'<tr><td>Projects, Internship &amp; Capstone</td><td>~5–10%</td></tr><tr><td>Assessments / Examinations</td><td>~5%</td></tr></tbody></table>'
                .'<p><strong>Competency-Based Learning:</strong> Students focus on mastering skills and expertise rather than simply applying concepts in the classroom to ensure real-world industrial readiness.</p>'
                .'<p><strong>Project-Based Learning:</strong> Every student solves real-world problems through projects that are co-designed with NAMTECH’s industry partners, turning theory into impact right from the start.</p>'
                .'<p><strong>Competition-Based Learning:</strong> Challenges and competitions modeled on global industry scenarios and standards push students to collaborate, innovate and raise the bar of industry excellence.</p>']],
            ['media_cards', ['eyebrow' => null, 'heading' => 'Labs & Infrastructure', 'layout' => 'tall', 'cta_label' => 'Explore Labs', 'cta_url' => '/academics/labs-and-infrastructure', 'items' => $this->sustainabilityLabs()]],
            ['post_list', ['heading' => 'Student Innovations', 'post_type' => 'story', 'limit' => 3, 'layout' => 'carousel', 'cta_label' => 'All stories', 'cta_url' => '/impact/stories'], 'innovations', false],
            ['steps', ['eyebrow' => null, 'heading' => 'Admission Process', 'body' => null, 'layout' => 'panel', 'panel_title' => 'Steps', 'image' => $img('p096', 'Applicant meeting the admissions team'), 'items' => [
                ['title' => 'Apply online at admissions.namtech.ac & pay ₹1,000/- application fee.'],
                ['title' => 'Complete your application and submit all required documents.'],
                ['title' => 'Get shortlisted and step into a personal interview.'],
                ['title' => 'Receive your admission offer.'],
                ['title' => 'Confirm your seat & pay tuition fee.'],
            ]]],
            ['eligibility', ['eyebrow' => null, 'heading' => 'Eligibility Criteria', 'criteria' => [
                ['text' => 'Candidates with B.E./B.Tech. or Master’s degrees in Science or Mathematics. Candidates must have a minimum of 60% marks or equivalent CGPA in 10th, 12th, and graduation from a recognized Indian university or institute. (No active backlog)'],
                ['text' => 'A score in CAT/ XAT/GMAT/GRE/GATE is preferred.'],
                ['text' => 'Open school students are not eligible.'],
            ], 'list_heading' => 'Eligible Engineering Branches', 'items' => array_map(fn (array $b) => ['icon' => $b[1], 'text' => $b[0]], [
                ['B.E./B.Tech. Civil Engineering', 'building'], ['B.E./B.Tech. Chemical Engineering', 'flask'], ['B.E./B.Tech. Energy Systems Engineering', 'bolt'],
                ['B.E./B.Tech. Environmental Engineering', 'leaf'], ['B.E./B.Tech. Electrical Engineering', 'bolt'], ['B.E./B.Tech. Electrical & Electronics Engineering', 'chip'],
                ['B.E./B.Tech. Mechanical Engineering', 'gear'], ['B.E./B.Tech. Materials Science and Engineering', 'flask'], ['B.E./B.Tech. Metallurgy Engineering', 'factory'],
                ['B.E./B.Tech. Petroleum Engineering', 'factory'], ['B.Arch Architecture', 'building'], ['B.Des Industrial Design', 'lightbulb'],
                ['M.Sc./M.Tech. Science', 'flask'], ['M.Sc./M.Tech. Mathematics', 'chart'],
            ])]],
            ['checklist_cards', ['eyebrow' => null, 'heading' => 'Required Documents', 'tone' => 'light', 'cards' => [
                ['icon' => 'graduation', 'title' => 'Academic records', 'items' => [['text' => '10th and 12th mark sheets'], ['text' => 'ITI mark sheets and final certificate'], ['text' => 'Diploma mark sheets and degree certificate']]],
                ['icon' => 'id', 'title' => 'Identity and other documents', 'items' => [['text' => 'ID proof (Aadhaar Card)'], ['text' => 'Income certificate'], ['text' => 'Employment and experience certificate (if applicable)']]],
            ]]],
            ['stat_cards', ['eyebrow' => null, 'heading' => 'Key Outcomes and Impact', 'body' => 'NAMTECH has achieved a 100% placement record for its learners over the last three years. The highest package was INR 16 LPA and the average package was INR 8.49 LPA, with recruiters including AM/NS India, Micron, Festo, Schneider Electric and others.', 'tone' => 'white', 'items' => [
                ['icon' => 'trend', 'value' => '100%', 'label' => 'Placement record over the last three years'],
                ['icon' => 'rupee', 'value' => 'INR 16 LPA', 'label' => 'Highest package'],
                ['icon' => 'chart', 'value' => 'INR 8.49 LPA', 'label' => 'Average package'],
            ]]],
            ['card_grid', ['eyebrow' => null, 'heading' => 'Build your future in these roles', 'style' => 'outlined', 'columns' => '3', 'tone' => 'lavender', 'cards' => [
                ['icon' => 'flask', 'title' => 'Environmental Scientist', 'body' => 'Research and protect natural ecosystems and resources by analyzing pollution sources, regulatory compliance and ecological impact.'],
                ['icon' => 'bolt', 'title' => 'Renewable Energy Analyst', 'body' => 'Evaluate, design and optimize clean power systems to maximize efficiency and commercial viability.'],
                ['icon' => 'people', 'title' => 'Corporate Social Responsibility Manager', 'body' => 'Spearhead corporate sustainability strategies, green initiatives and climate-ethical business practices.'],
                ['icon' => 'chart', 'title' => 'Climate Change Analyst', 'body' => 'Understand and model environmental trends, carbon metrics and risk factors to assess climate change and inform policy decisions.'],
                ['icon' => 'leaf', 'title' => 'Sustainable Agriculture Specialist', 'body' => 'Drive adoption of eco-friendly tech, push resource-efficient farming methods and increase awareness of regenerative agriculture practices.'],
                ['icon' => 'trend', 'title' => 'Social Impact Analyst', 'body' => 'Measure and scale the long-term community and environmental value of sustainable investments.'],
                ['icon' => 'gear', 'title' => 'Environmental Engineer', 'body' => 'Design and execute green infrastructure, waste management networks and pollution control systems.'],
                ['icon' => 'building', 'title' => 'Urban Planner', 'body' => 'Design smart, climate-resilient and energy-optimized cities for sustainable modern living.'],
                ['icon' => 'graduation', 'title' => 'Sustainability Educator', 'body' => 'Educate and guide the next generation of green leaders through cutting-edge environmental research.'],
            ]]],
            ['testimonials', ['eyebrow' => null, 'heading' => 'Hear from Recruiters', 'layout' => 'quotes', 'items' => []], 'recruiters', false],
            ['fees', ['eyebrow' => null, 'heading' => 'Fees and Scholarships', 'body' => null, 'fee_label' => 'Course Fee', 'fee_amount' => null,
                'rows' => [
                    ['label' => 'Tuition (Inclusive of 18% GST) – Course Fee', 'value' => '₹10,00,000/-'],
                    ['label' => 'Tuition – First Year (2027–28)', 'value' => '₹5,00,000/-'],
                    ['label' => 'Tuition – Second Year (2028–29)', 'value' => '₹5,00,000/-'],
                    ['label' => 'Residence (Inclusive of 5% GST) – Course Fee', 'value' => '₹4,00,000/-'],
                    ['label' => 'Residence – First Year (2027–28)', 'value' => '₹2,00,000/-'],
                    ['label' => 'Residence – Second Year (2028–29)', 'value' => '₹2,00,000/-'],
                ],
                'scholarships_title' => 'Scholarships',
                'scholarships' => [
                    ['title' => 'Up to 75% on tuition fees', 'text' => 'NAMTECH offers a scholarship of up to 75% on tuition fees, based on student academic performance, interview performance and family income. Please note that seats are limited and the final decision rests with the Admissions and Scholarships Committee.'],
                ],
                'link_label' => 'Know more about scholarships', 'link_url' => '/admissions#faq',
                'notes' => [
                    ['icon' => 'bank', 'title' => 'Admissions / Seat Blocking Fee', 'text' => 'The candidate is required to pay ₹25,000/- as acceptance of the offer and to confirm admission within the stipulated time mentioned in the LOI, subject to the availability of seats in the application cycle.'],
                    ['icon' => 'rupee', 'title' => 'Adjusted against Security Deposit', 'text' => 'The amount of ₹25,000/- shall be adjusted against the Security Deposit (refundable).'],
                    ['icon' => 'check', 'title' => 'Refundable Security Deposit', 'text' => 'The security deposit is refundable at the time of graduation, adjusted against any outstanding dues.'],
                ],
            ]],
            ['feature_list', ['eyebrow' => null, 'heading' => 'Education Loan Assistance', 'body' => 'Invest in your future with confidence. NAMTECH has partnered with leading banks and education finance providers to make funding your master’s program simple and accessible. Eligible students can obtain loans covering up to 100% of tuition, hostel and other education-related expenses, with flexible repayment options and collateral-free schemes available through select partners.', 'tabs' => [['label' => null, 'items' => [
                ['icon' => 'rupee', 'text' => 'Loan coverage of up to 100% of the cost of education (including tuition and hostel fees through select lenders)'],
                ['icon' => 'check', 'text' => 'Collateral-free loan options available for eligible applicants'],
                ['icon' => 'calendar', 'text' => 'Repayment tenure of up to 15 years'],
                ['icon' => 'document', 'text' => 'Tax benefits on interest paid under Section 80E'],
                ['icon' => 'bank', 'text' => 'Partnerships with ICICI Bank, Axis Bank, Credila and EDUAID for streamlined financing support'],
                ['icon' => 'people', 'text' => 'Dedicated admissions guidance to help students through the loan application process'],
            ]]]]],
            $this->studentLifeSection(),
            ['testimonials', ['eyebrow' => null, 'heading' => 'Student Testimonials', 'layout' => 'portrait', 'items' => []], 'testimonials', false],
            ['faq', ['heading' => 'Frequently Asked Questions', 'body' => null, 'layout' => 'centered', 'items' => array_map(fn (array $qa) => ['question' => $qa[0], 'answer' => '<p>'.e($qa[1]).'</p>'], [
                ['Who should enroll in the program?', 'Engineering, B.Arch., B.Des., or M.Sc. graduates, with or without work experience, as well as MBA graduates who meet the eligibility criteria and are seeking expertise in sustainable manufacturing and ESG leadership, are welcome to apply.'],
                ['Is this program suitable for working professionals?', 'Yes. Its residential structure is designed to accommodate both fresh graduates and working professionals.'],
                ['Can I transition from another engineering field?', 'Yes. The interdisciplinary curriculum helps professionals from diverse engineering backgrounds move into sustainability careers.'],
                ['What practical skills will I gain?', 'Skills include ESG strategy, circular economy, industrial decarbonization, smart manufacturing and hands-on project experience.'],
                ['How quickly can I apply these skills?', 'The industry-focused curriculum and real-world projects enable learners to apply concepts immediately in professional settings.'],
                ['Does the program include hands-on learning?', 'Yes. Students participate in industry projects, laboratories, case studies and experiential learning modules.'],
                ['How does the program incorporate emerging technologies?', 'It integrates AI, digital twins, simulation, analytics, ESG platforms and other advanced sustainability technologies.'],
                ['What labs and infrastructure are available?', 'Students have access to modern laboratories and advanced manufacturing and sustainability learning facilities at NAMTECH.'],
                ['How does the program prepare me for industry demands?', 'It prepares students through collaboration with KPMG in India and TUM Asia, live industry case studies, and mentorship aligned with current sustainability challenges.'],
                ['What career opportunities can I expect?', 'Roles include Sustainability Consultant, ESG Analyst, Renewable Energy Engineer, Circular Economy Strategist and other emerging green careers.'],
                ['Will this program make me job-ready?', 'Yes. The curriculum aligns with India’s green manufacturing and sustainability initiatives to develop industry-ready graduates.'],
                ['How is industry integration maintained?', 'Industry integration is maintained through continuous collaboration with KPMG in India and manufacturing partners, industry case studies, and expert mentorship.'],
                ['How is this different from other sustainability programs?', 'It uniquely combines advanced manufacturing, ESG and circular economy with strong industry integration and practical learning.'],
                ['What mentorship and industry exposure are provided?', 'Students learn from sustainability experts through guest lectures, mentorship and industry-led case studies.'],
                ['What financial support is available?', 'Flexible payment plans and education loan assistance are available for eligible students.'],
            ])], 'faq'],
            ['enquiry_form', ['heading' => 'Enquire about this program', 'body' => 'Tell us about yourself and our admissions team will get back to you.', 'form_type' => 'program', 'show_contact_details' => true], 'enquire'],
        ]);
    }

    private function corporatePartnerProfiles(): array
    {
        return [
            ['Siemens', [
                'NAMTECH has forged a strategic partnership with Siemens to revolutionize education in smart manufacturing, automation, and advanced technical education. This collaboration aims to integrate cutting-edge industry expertise into NAMTECH’s academic framework, introducing new electives and dedicated courses within the Master’s, Executive and iPTP programmes.',
                'The partnership emphasizes experiential learning, hands-on industry training, and the co-development of dynamic course curricula. A key highlight is the establishment of a robust industry attachment programme for both faculty and students, fostering deeper industry-academic integration.',
                'Additionally, NAMTECH and Siemens will collaborate on developing state-of-the-art infrastructure, creating continuous knowledge-sharing platforms, and designing advanced learning modules to prepare learners for the challenges of Industry 4.0 and beyond.',
                'Through this partnership, NAMTECH and Siemens are shaping a future-ready workforce equipped with the skills and expertise to drive innovation in manufacturing and automation.',
            ]],
            ['ABB', [
                'NAMTECH and ABB have partnered to create a School of Robotics, setting a benchmark in robotics and automation education in India.',
                'This visionary collaboration bridges the education gap in automation, advancing robotics skills and fostering seamless industry integration.',
                'At the NAMTECH School of Robotics, ABB Robotics will play a pivotal role in designing, supporting, and executing various programmes. The programmes will be hands-on and industry-focused, supported by world-class infrastructure and guided by global industry leaders like ABB. The goal is to cultivate a highly skilled talent pool for the industrial sector, ensuring students are ready to meet the demands of a rapidly evolving technological landscape.',
                'ABB’s robotics experts will drive knowledge-sharing initiatives, spearhead cutting-edge research, and facilitate immersive industry exposure, offering unparalleled opportunities for students to gain practical expertise.',
                'In 2023, India achieved a new milestone with the installation of 8,510 industrial robots, a 59% increase from the previous year. This growth positions India as the seventh-largest global market for annual robot installations and underscores its burgeoning potential in automation. As this trend accelerates through 2024 and 2025, job opportunities in the robotics sector are booming, particularly in roles such as Robotics Engineers, Robot Design Engineers, Application Engineers, and Smart Factory Designers.',
                'With ABB’s partnership, NAMTECH is at the forefront of shaping the future of robotics education and driving India’s automation revolution.',
            ]],
            ['AM/NS India', [
                'NAMTECH has partnered with AMNS India, a leading integrated steel manufacturer and a joint venture between ArcelorMittal and Nippon Steel. This collaboration focuses on advancing skill development, industry-aligned education, and research in manufacturing and materials technologies.',
                'Through joint initiatives in curriculum design, experiential learning, and applied research, NAMTECH and AMNS India aim to bridge the gap between academia and industry, nurturing a new generation of engineers equipped with cutting-edge technical and managerial capabilities.',
                'AMNS India’s leadership in sustainable steelmaking and advanced manufacturing complements NAMTECH’s vision of shaping future-ready talent for India’s manufacturing and engineering sectors. Together, this partnership enables real-world exposure, access to industry infrastructure, and career opportunities that empower students to contribute to India’s industrial transformation and global competitiveness.',
            ]],
            ['Schneider Electric, Global / India', [
                'Schneider Electric is a global leader in energy management, automation, and digitization, providing sustainable solutions for industries, infrastructure, data centres, buildings, and homes. The company focuses on electrification, industrial IoT, automation, smart grid technologies, and digital twin platforms to help organizations enhance reliability, safety, efficiency, and environmental performance. Operating in over 100 countries, Schneider Electric has a substantial presence in India, serving as a key global hub with multiple manufacturing sites, robust R&D capabilities, and significant investments in sustainable and digital technologies.',
                'NAMTECH’s partnership with Schneider Electric is both strategic and transformative. This collaboration allows us to develop a world-class smart campus and advanced training labs at our temporary Gandhinagar site and the upcoming Ahmedabad campus. Students gain hands-on experience with state-of-the-art lab infrastructure, industry-relevant learning, and direct apprenticeships with experts. Beyond infrastructure, we integrate Schneider’s expertise into our curriculum, particularly for the International Professional Master’s Programme in Smart Manufacturing, ensuring our graduates are prepared for Industry 4.0.',
                'This partnership offers NAMTECH students employment opportunities in India and internationally, while faculty members benefit from exposure to global best practices. The collaboration supports India’s goals to modernize manufacturing, enhance competitiveness, and bridge the gap between industry and academia. As a result, our graduates are technically skilled, innovative, and prepared to lead in the digital industrial era.',
            ]],
            ['Festo', [
                'NAMTECH has forged a strategic partnership with Festo, a global leader in automation technology. Festo will design and supply cutting-edge labs at NAMTECH interim campus at Research Park, IIT Gandhinagar, Gujarat, India.',
                'Festo’s role as a strategic partner for lab equipment and curriculum development ensures that our students have access to state-of-the-art facilities and industry-aligned learning experiences. NAMTECH work with the didactic team at Festo with an aim to offer students insights into leading Industry 4.0 technology.',
                'This partnership not only enriches our academic programmes but also empowers students with practical skills and knowledge that are highly relevant in today’s manufacturing landscape. Together, we are shaping the future of manufacturing education, preparing students to excel in their careers and contribute meaningfully to the industry.',
            ]],
            ['Micron', [
                'NAMTECH has partnered with Micron, a global leader in semiconductor solutions. This collaboration spans across academic development, infrastructure setup, student training, and recruitment initiatives.',
                'Micron is leading the semiconductor manufacturing foray in India and establishing a state-of-the-art facility in Sanand, which lies in close proximity to our campus. By working closely with industry leaders, NAMTECH ensures that its programmes are aligned with industry demands, providing students with hands-on experience, cutting-edge infrastructure, and opportunities for career advancement.',
                'This holistic approach to the partnership enhances the educational experience and prepares students for successful careers. This synergistic partnership is in line with the steadily growing ecosystem in India and South-East Asia and students stand to benefit from faculty with industry expertise and the skills exposure, as well as employment opportunities in India and abroad.',
            ]],
            ['Cisco', [
                'NAMTECH has partnered with Cisco, a global leader in networking and technology solutions. This partnership brings together expertise in manufacturing technologies and digital innovation to enhance our academic programmes and industry relevance.',
                'This strategic collaboration reflects NAMTECH’S commitment to delivering industry-relevant education and empowering students to excel in the dynamic fields of Cybersecurity and Networking in the field of manufacturing.',
                'The partnership with industry leaders like CISCO to co-design curriculum, co-invest in learning infrastructure, and participate in recruitment drives helps is highly beneficial for our young professionals. Moreover, by working closely with partners, NAMTECH ensures that its curriculum is aligned with industry needs, providing students with hands-on experience, cutting-edge infrastructure, and pathways to meaningful careers.',
            ]],
            ['ASDC', [
                'ASDC is the first sector skill council of India promoted by the automobile industry, with representations from leading industry associations from the automotive sector and the Government of India. ASDC is the apex body entrusted to address the skilling requirements of the entire automotive value chain.',
                'NAMTECH has partnered with ASDC to support curriculum design, content development and delivery for vocational upskilling programmes under NAMTECH School for Social Impact aligned with the TVET model.',
            ]],
            ['ICICI Foundation', [
                'NAMTECH has partnered with the ICICI Foundation for Inclusive Growth, dedicated to skill development and sustainable livelihoods for the less privileged. This partnership brings together expertise in technical training and social impact to enhance our outreach programmes and grassroots relevance.',
                'This strategic collaboration reflects NAMTECH’S commitment to delivering industry-relevant education and empowering underprivileged learners to excel in the dynamic fields of technical and vocational education.',
                'The partnership with leaders like the ICICI Foundation to launch the NSSI outreach program, establish digital infrastructure, and deploy "smart labs on wheels" is highly beneficial for our learners. Moreover, by working closely with partners, NAMTECH ensures that its initiatives are aligned with societal and industry needs, providing students with hands-on experience, cutting-edge infrastructure, and pathways to meaningful careers.',
            ]],
            ['Rockwell Automation', [
                'NAMTECH has partnered with Rockwell Automation, a global leader in industrial automation and digital transformation. This partnership brings together expertise in smart manufacturing and industrial technology to enhance our academic programmes and industry relevance.',
                'This strategic collaboration reflects NAMTECH’S commitment to delivering industry-relevant education and empowering students to excel in the dynamic fields of Industry 4.0 and sustainability in the field of manufacturing.',
                'The partnership with industry leaders like Rockwell to co-design curriculum electives, co-invest in dedicated training facilities equipped with proprietary hardware, and participate in internship and scholarship drives is highly beneficial for our young professionals. This ensures that the curriculum is aligned with industry needs, providing students with hands-on experience, cutting-edge infrastructure, and pathways to meaningful careers.',
            ]],
            ['Analog Devices', [
                'NAMTECH has partnered with Analog Devices, Inc. (ADI), a global semiconductor leader that bridges the physical and digital worlds. This partnership brings together expertise in semiconductor technologies and digital innovation to enhance our academic programmes and industry relevance.',
                'This strategic collaboration reflects NAMTECH’S commitment to delivering industry-relevant education and empowering students to excel in the dynamic fields of semiconductor manufacturing, engineering, and technology.',
                'The partnership with industry leaders like ADI to co-design curriculum, support the development of a state-of-the-art semiconductor laboratory, and participate in hiring and upskilling drives is highly beneficial for our young professionals.',
            ]],
            ['FANUC', [
                'NAMTECH has partnered with FANUC India Private Limited, a pioneer and global leader in Computer Numerical Control (CNC) and robotics technology. This partnership brings together expertise in precision engineering and industrial automation to enhance our academic programmes and industry relevance.',
                'This strategic collaboration reflects NAMTECH’S commitment to delivering industry-relevant education and empowering students to excel in the dynamic fields of robotics and precision engineering in the field of smart manufacturing.',
                'The partnership with industry leaders like FANUC to co-design specialized curriculum electives, establish a Centre for Excellence in Robotics, and scale ITI outreach programs is highly beneficial for our young professionals.',
            ]],
            ['L&T Heavy Engineering', [
                'NAMTECH has partnered with L&T Heavy Engineering, a prominent leader in the industrial and manufacturing sector. This partnership brings together expertise in advanced manufacturing technologies and digital innovation to enhance our academic programmes and industry relevance.',
                'This strategic collaboration reflects NAMTECH’S commitment to delivering industry-relevant education and empowering students to excel in the dynamic fields of AI/ML, automation, and advanced manufacturing operations.',
                'The partnership with industry leaders like L&T Heavy Engineering to co-design capstone projects, tackle real-world challenges like drone surveillance, and provide direct industry certification is highly beneficial for our young professionals.',
            ]],
            ['Hi-Tech Pipes', [
                'NAMTECH has partnered with HITECH PIPES LTD, a prominent manufacturing entity committed to the advancement of smart manufacturing. This partnership brings together shared aspirations and resources to build a future-ready engineering talent pipeline and enhance our academic programmes and industry relevance.',
                'This strategic collaboration reflects NAMTECH’S commitment to delivering industry-relevant education and empowering students to excel in the dynamic fields of industrial AI and advanced automation in the field of manufacturing.',
                'The partnership with industry leaders like HITECH PIPES to co-invest in campus infrastructure through a substantial financial grant for mutually identified projects is highly beneficial for our young professionals.',
            ]],
            ['Surya Roshni', [
                'NAMTECH has partnered with Surya Roshni Ltd, a key industry player committed to the evolution of smart manufacturing. This partnership brings together shared goals and financial investments to build a large-scale, future-ready engineering talent pipeline and enhance our academic programmes and industry relevance.',
                'This strategic collaboration reflects NAMTECH’S commitment to delivering industry-relevant education and empowering students to excel in the dynamic fields of industrial AI and advanced automation in the Manufacturing, Engineering, and Technology (MET) sector.',
                'The partnership with industry leaders like Surya Roshni to co-invest in developmental initiatives through targeted financial grants for campus projects is highly beneficial for our young professionals.',
            ]],
            ['KPMG in India', [
                'KPMG in India is part of a global network of professional services firms providing Audit, Tax, and Advisory services. With a presence in over 140 countries, KPMG in India is a recognised leader in ESG and sustainability consulting, helping organisations transition to responsible and resilient business models.',
                'This partnership equips students with industry-relevant skills and global sustainability exposure, preparing them for impactful careers in India’s manufacturing transformation.',
            ]],
            ['NTPC', [
                'NTPC is supporting the establishment of the Centre for Sustainable Energy and Circularity through its Corporate Social Responsibility (CSR) initiative, enabling the development of experiential learning infrastructure, capacity-building programmes and outreach initiatives focused on sustainable energy and circularity.',
            ]],
        ];
    }

    private function livingLabs(): array
    {
        $labs = [
            ['Sustainable Energy Labs', 'Work on real-world renewable systems, from generation to consumption, using IoT-driven optimisation.', 'p030', 'Wind turbines'],
            ['Hydrogen Innovation Lab', 'Explore the full hydrogen ecosystem, from production to storage to fuel cell applications.', 'p127', 'Scientists working with laboratory equipment'],
            ['Water Stewardship Lab', 'Solve water challenges through smart monitoring, filtration, and climate-resilient systems.', 'p033', 'Researcher examining samples under a microscope'],
            ['Smart Grid & Energy Systems Lab', 'Build and optimise intelligent energy networks with automation, efficiency, and grid analytics.', 'p120', 'Student working on an automation rig'],
            ['ESG Analytics Lab', 'Turn sustainability data into action using reporting tools, automation, and compliance frameworks.', 'p130', 'Student working at a laptop beside turbine models'],
            ['Urban Digitalisation Lab', 'Use AI, IoT, and data to design smarter, more efficient urban environments.', 'p093', 'Urban planning model'],
        ];

        return array_map(fn (array $lab) => ['image' => $this->pdfImage($lab[2], $lab[3]), 'title' => $lab[0], 'body' => $lab[1]], $labs);
    }

    private function attachSections(Model $owner, array $sections): void
    {
        foreach ($sections as $i => $section) {
            $owner->sections()->create([
                'type' => $section[0],
                'data' => SectionRegistry::normalize($section[0], $section[1]),
                'anchor' => $section[2] ?? null,
                'sort_order' => $i,
                'is_visible' => $section[3] ?? true,
            ]);
        }
        $owner->syncMediaUsage();
    }

    private function seedPages(array $schools): array
    {
        $pages = [];
        $img = fn (string $file, string $alt) => $this->pdfImage($file, $alt);
        $tbc = '<p>'.self::Tbc.'</p>';

        $pages['home'] = $this->homePage();

        $hero = fn (string $title, ?int $image, ?string $eyebrow = null, ?string $body = null, array $extra = []) => ['page_hero', array_merge(['eyebrow' => $eyebrow, 'title' => $title, 'body' => $body, 'image' => $image], $extra)];
        $contactCta = ['cta_band', ['heading' => 'Have Questions? Get in touch with us', 'body' => 'We partner with the best of industry, academia, and technology to co-create curriculum and align training with workforce needs. Partner with us to shape a skilled workforce for India’s future.', 'cta_label' => 'Contact us', 'cta_url' => '/contact']];
        $coCreate = ['cta_band', ['heading' => 'Co-Create With Us', 'body' => 'We partner with the best of industry, academia, and technology to co-create curriculum and align training with workforce needs. Partner with us to shape a skilled workforce for India’s future.', 'cta_label' => 'Partner with us', 'cta_url' => '#enquiry:partnership', 'tone' => 'lavender']];
        $news = ['news_carousel', ['heading' => 'News & Insights', 'post_type' => 'news', 'limit' => 8, 'cta_label' => 'View all insights', 'cta_url' => '/media']];

        $aboutNav = ['subnav', ['items' => [
            ['label' => 'About Us', 'url' => '/about'], ['label' => 'Board of Directors', 'url' => '/about/board-of-directors'],
            ['label' => 'Advisory Council', 'url' => '/about/advisory-council'], ['label' => 'Management Team', 'url' => '/about/management-team'],
            ['label' => 'Mandatory Disclosures', 'url' => '/about/mandatory-disclosures'], ['label' => 'Ranks And Accreditations', 'url' => '/about/ranks-and-accreditations'],
        ]]];
        $purpose = 'To empower 3 million youth with advancement in the MET sector and position India as a global hub for manufacturing excellence.';

        $pages['about'] = $this->page('About NAMTECH', 'about', null, [
            $hero('About NAMTECH', $img('p143', 'Team meeting around a conference table'), 'About us', 'New Age Makers’ Institute of Technology (NAMTECH), by ArcelorMittal Nippon Steel India, is India’s first Manufacturing Engineering & Technology institute.'),
            $aboutNav,
            ['image_statement', ['eyebrow' => null, 'heading' => 'Our Purpose', 'body' => $purpose, 'image' => $img('p005', 'Students talking outside a campus building'), 'position' => 'right']],
            ['image_statement', ['eyebrow' => null, 'heading' => 'Our Vision', 'body' => $purpose, 'image' => $img('p059', 'Students relaxing on a campus lawn'), 'position' => 'left']],
            ['value_list', ['eyebrow' => null, 'heading' => 'Our Core Values', 'body' => self::Tbc, 'items' => [['icon' => 'people', 'title' => self::Tbc, 'text' => null]]]],
            ['card_grid', ['eyebrow' => 'What We Do', 'heading' => 'Cultivating Talent for India’s Manufacturing Dominance', 'style' => 'joined', 'columns' => '4', 'tone' => 'white', 'cards' => [
                ['icon' => 'people', 'title' => 'Transforming India’s Technician Workforce'],
                ['icon' => 'robot', 'title' => 'Experiential Learning Through Advanced Technology'],
                ['icon' => 'factory', 'title' => 'Industry-integrated, applied learning models'],
                ['icon' => 'globe', 'title' => 'Scaling Skills Access Across India'],
            ]]],
            ['timeline', ['eyebrow' => null, 'heading' => 'Our Story', 'body' => null, 'items' => [
                ['year' => '2022', 'title' => 'June, 2022', 'text' => 'NAMTECH received the Letter of Intent from the University Grants Commission, marking a key step towards recognition as a Deemed-to-be-University.', 'image' => $img('p010', 'NAMTECH team photograph in a lab')],
                ['year' => '2023', 'title' => '2023', 'text' => self::Tbc, 'image' => $img('p134', 'Visitors being shown student-built hardware')],
                ['year' => '2024', 'title' => '2024', 'text' => self::Tbc, 'image' => $img('p116', 'Prize winners at the IndiaSkills regional competition')],
                ['year' => '2025', 'title' => '2025', 'text' => self::Tbc, 'image' => $img('p113', 'Lecture theatre')],
                ['year' => '2026', 'title' => '2026', 'text' => self::Tbc, 'image' => $img('p142', 'Students sitting together on campus steps')],
            ]]],
            ['video_feature', ['layout' => 'split', 'heading' => 'Created to aid India’s growth and development.', 'body' => 'New Age Makers’ Institute of Technology (NAMTECH), by ArcelorMittal Nippon Steel India, is India’s first Manufacturing Engineering & Technology institute. It supports ‘Make in India’ and ‘Viksit Bharat 2047’ by offering advanced engineering programs and promoting responsible technology. NAMTECH prepares young professionals with Industry 4.0 skills and training.', 'image' => $img('p134', 'Visitors being shown student-built hardware')]],
            ['intro', ['eyebrow' => 'About the Founder', 'heading' => 'About the Founder: AM/NS India', 'body' => $tbc]],
            $contactCta,
        ]);
        $groupPage = fn (string $title, string $slug, string $group, string $image, string $alt) => $this->page($title, $slug, $pages['about'], [
            $hero($title, $img($image, $alt), 'About us'),
            $aboutNav,
            ['leaders_grid', ['eyebrow' => null, 'heading' => $title, 'body' => null, 'group' => $group, 'layout' => 'grid']],
        ]);
        $groupPage('Board of Directors', 'board-of-directors', 'board', 'p014', 'Teacher with a group of students');
        $groupPage('Advisory Council', 'advisory-council', 'advisory', 'p096', 'Colleagues discussing work at a laptop');
        $groupPage('Management Team', 'management-team', 'management', 'p143', 'Team meeting around a conference table');
        $docs = ['DTBU Application submitted on UGC portal', 'Registration and list of members', 'Registration Certificate', 'Details of other members of sponsoring body', 'Detailed Project Report (DPR)', 'Non requirement of NOC from Govt. for financial support', 'List of proposed unique programs', 'Name of programs with justification for distinct category', 'Proposed Syllabi', 'Memorandum of Association (MoA) copy', 'Notarized legal Undertaking', 'Details of the proposed academic and physical infrastructure', 'Other common and recreational facilities (proposed)', 'Faculty Details (as per format)', 'Diversity, Equity, & Inclusion Policy at NAMTECH'];
        $this->page('Mandatory Disclosures', 'mandatory-disclosures', $pages['about'], [
            $hero('Mandatory Disclosures', $img('p026', 'Student reviewing documents at a desk'), 'About us'),
            $aboutNav,
            ['documents', ['eyebrow' => null, 'heading' => 'Mandatory Disclosures', 'layout' => 'grid', 'items' => array_map(fn (string $t) => ['title' => $t], $docs)]],
            ['documents', ['eyebrow' => null, 'heading' => 'Annual Reports', 'layout' => 'list', 'tone' => 'light', 'items' => [['title' => '2024-2025'], ['title' => '2023-2024'], ['title' => '2022-2023']]]],
        ]);
        $this->page('Ranks & Accreditations', 'ranks-and-accreditations', $pages['about'], [
            $hero('Ranks & Accreditations', $img('p056', 'Students raising their thumbs together'), 'About us'),
            $aboutNav,
            ['intro', ['heading' => 'Ranks & Accreditations', 'body' => $tbc, 'icon' => 'award']],
        ]);

        $learningModel = ['media_cards', ['eyebrow' => null, 'heading' => 'Our Learning Model', 'layout' => 'horizontal', 'items' => [
            ['icon' => 'graduation', 'title' => 'Competency-Based Learning', 'body' => 'We focus on mastering skills, not just completing courses. Learners progress through personalized pathways, acquiring technical, professional, and leadership competencies at their own pace. This ensures deep understanding, measurable skill development, and real-world readiness.', 'image' => $img('p044', 'Hand writing on a glass board')],
            ['icon' => 'gear', 'title' => 'Project-Based Learning', 'body' => 'Every learner solves real-world problems through hands-on projects co-designed with industry partners. This immersive approach bridges theory with practice and transforms learners into solution-oriented technologists capable of delivering impact from day one.', 'image' => $img('p139', 'Students building a robot together')],
            ['icon' => 'award', 'title' => 'Competition-Based Learning', 'body' => 'To spark innovation and critical thinking, learners engage in challenges and competitions that mirror global industry scenarios. These experiences encourage healthy competition, collaboration, and creativity-pushing learners to raise the bar continuously.', 'image' => $img('p116', 'Prize winners at the IndiaSkills regional competition')],
        ]]];
        $outcomes = ['stat_cards', ['eyebrow' => null, 'heading' => 'Outcomes', 'body' => 'Real-world labs designed to move you from theory to application, where you work on live systems, industry tools, and future-ready technologies shaping energy, infrastructure, and sustainable cities.', 'tone' => 'lavender', 'items' => [
            ['icon' => 'trend', 'value' => '100%', 'label' => 'Placement'], ['icon' => 'people', 'value' => '500+', 'label' => 'Students'], ['icon' => 'handshake', 'value' => '121+', 'label' => 'Recruiters'],
        ]]];
        $labCards = ['media_cards', ['eyebrow' => null, 'heading' => 'Labs and Facilities', 'layout' => 'tall', 'cta_label' => 'View All', 'cta_url' => '/academics/labs-and-infrastructure', 'card_link_label' => 'Know More', 'items' => [
            ['image' => $img('p054', 'Modular machine model on a workbench'), 'title' => 'Additive Manufacturing & Precision Engineering Lab', 'link_url' => '/academics/labs-and-infrastructure/additive-manufacturing-and-precision-engineering-lab'],
            ['image' => $img('p127', 'Scientists working with laboratory equipment'), 'title' => 'Hydrogen Innovation Lab', 'body' => 'Explore the full hydrogen ecosystem, from production to storage to fuel cell applications.'],
            ['image' => $img('p033', 'Researcher examining samples under a microscope'), 'title' => 'Water Stewardship Lab', 'body' => 'Solve water challenges through smart monitoring, filtration, and climate-resilient systems.'],
        ]]];
        $ready = ['cta_band', ['heading' => 'Ready to Transform Your Future?', 'body' => null, 'cta_label' => 'View all programs', 'cta_url' => '/programs']];

        $pages['academics'] = $this->page('Academics', 'academics', null, [
            $hero('Transforming Education Through Innovation & Industry Integration', $img('p085', 'Teacher presenting at a whiteboard'), 'Academics'),
            ['intro', ['heading' => 'Our Educational Philosophy', 'body' => $tbc, 'icon' => 'lightbulb']],
            $learningModel,
            ['academics_tabs', ['eyebrow' => 'Schools, Centres, and Programs', 'heading' => 'NAMTECH’s Innovation Schools for the Future of Manufacturing', 'schools_label' => 'Schools', 'centres_label' => 'Centres', 'programs_label' => 'Programs', 'default_tab' => 'schools', 'featured_only' => false]],
            $ready,
        ]);
        $this->page('Pedagogy', 'pedagogy', $pages['academics'], [
            $hero('Transforming Education Through Innovation & Industry Integration', $img('p085', 'Teacher presenting at a whiteboard'), 'Pedagogy'),
            ['intro', ['heading' => 'Our Educational Philosophy', 'body' => $tbc, 'icon' => 'lightbulb']],
            $learningModel, $outcomes, $labCards,
            ['cta_band', ['heading' => 'Ready to Transform Your Future?', 'body' => null, 'cta_label' => 'Apply Now', 'cta_url' => '/admissions']],
        ]);
        $labs = $this->page('Labs & Infrastructure', 'labs-and-infrastructure', $pages['academics'], [
            $hero('NAMTECH’s State-of-the-Art Infrastructure & Laboratories', $img('p008', 'Scientist with a clipboard in a laboratory'), 'Labs & Infrastructure', null, ['cta_label' => 'Schedule a Campus Tour', 'cta_url' => '/contact']),
            ['media_cards', ['eyebrow' => null, 'heading' => 'Labs', 'layout' => 'tall', 'card_link_label' => 'Know More', 'items' => array_merge([
                ['image' => $img('p054', 'Modular machine model on a workbench'), 'title' => 'Additive Manufacturing & Precision Engineering Lab', 'link_url' => '/academics/labs-and-infrastructure/additive-manufacturing-and-precision-engineering-lab'],
            ], $this->livingLabs())]],
            ['tabs', ['eyebrow' => null, 'heading' => 'Campus & Facilities', 'layout' => 'standard', 'tabs' => [
                ['label' => 'Campus', 'title' => 'Campus', 'body' => $tbc, 'image' => $img('p059', 'Students relaxing on a campus lawn')],
                ['label' => 'Classrooms', 'title' => 'Classrooms', 'body' => $tbc, 'image' => $img('p113', 'Lecture theatre')],
                ['label' => 'Hostel', 'title' => 'Hostel', 'body' => $tbc, 'image' => $img('p142', 'Students sitting together on campus steps')],
                ['label' => 'Library', 'title' => 'Library', 'body' => $tbc, 'image' => $img('p045', 'Curved library bookshelves')],
            ]]],
            ['cta_band', ['heading' => 'Experience NAMTECH Campus', 'body' => null, 'cta_label' => 'Take the 360° tour', 'cta_url' => '/campus/virtual-tour']],
        ]);
        $this->page('Additive Manufacturing & Precision Engineering Lab', 'additive-manufacturing-and-precision-engineering-lab', $labs, [
            $hero('Additive Manufacturing & Precision Engineering Lab', $img('p054', 'Modular machine model on a workbench'), 'Labs & Infrastructure', self::Tbc, ['layout' => 'plain']),
            ['media_cards', ['eyebrow' => null, 'heading' => 'Our State-of-the-Art Facilities', 'layout' => 'showcase', 'items' => [
                ['image' => $img('p131', 'Orange industrial robot arm'), 'title' => 'FDM Technologies'],
                ['image' => $img('p054', 'Modular machine model on a workbench'), 'title' => 'SLA (Stereolithography Apparatus)'],
                ['image' => $img('p115', 'Vehicle chassis in a clean lab'), 'title' => 'Metal 3D Printer'],
            ]]],
            ['card_grid', ['eyebrow' => null, 'heading' => 'Why Is It Important?', 'style' => 'joined', 'columns' => '3', 'tone' => 'white', 'cards' => [
                ['icon' => 'bolt', 'title' => 'Rapid prototyping', 'body' => 'Learn by engaging with faculty, peers, and live challenges that mirror real-world decision-making.'],
                ['icon' => 'gear', 'title' => 'Mass customization', 'body' => 'Learning happens by building. At NAMTECH, classrooms don’t have desks but machines used in the industry.'],
                ['icon' => 'leaf', 'title' => 'Waste reduction', 'body' => 'Our curriculum is co-designed and co-delivered with academicians from global universities.'],
                ['icon' => 'flask', 'title' => 'Education and research'],
                ['icon' => 'globe', 'title' => 'Sustainability'],
            ]]],
            ['post_list', ['heading' => 'How It’s Done: Some Student Stories', 'post_type' => 'story', 'limit' => 3, 'layout' => 'carousel', 'cta_label' => 'All stories', 'cta_url' => '/impact/stories']],
            ['media_cards', ['heading' => 'Explore More Labs', 'layout' => 'tall', 'items' => $this->livingLabs()]],
        ]);

        $this->page('Programs', 'programs', null, [$hero('Programs', $this->media['program-student-hardware'], 'Academics')]);
        $this->page('Schools & Centres', 'schools', null, [$hero('NAMTECH’s Innovation Schools for the Future of Manufacturing', $this->media['labs-research'], 'Schools, Centres, and Programs')]);
        $this->page('Faculty', 'faculty', null, [$hero('Meet our Faculty', $img('p126', 'Smiling faculty member'), 'Faculty', 'Our faculty comprises accomplished academicians, industry leaders, and global experts shaping the future of manufacturing and technology.')]);

        $pages['admissions'] = $this->page('Admissions', 'admissions', null, [
            $hero('Admissions: Undergraduate', $img('p111', 'Graduate in cap and gown'), 'Admissions', 'NAMTECH’s School of Sustainability drives Energy and Circularity to accelerate the MET sector toward clean, decarbonized futures. Blending modern science with India’s resourceful ethos, it delivers hands-on learning that creates real-world impact.', ['cta_label' => 'Apply Now', 'cta_url' => '#enquire', 'secondary_label' => 'Contact Us', 'secondary_url' => '/contact']),
            ['program_finder', ['heading' => 'Explore Admissions by UG Program', 'level' => 'undergraduate', 'tone' => 'lavender']],
            ['date_tables', ['eyebrow' => null, 'heading' => 'Important Dates & Deadlines', 'footnote' => '* Round 5 deadlines would be based on available seats.', 'columns' => [
                ['icon' => 'calendar', 'title' => 'Applications Deadlines', 'highlight' => true, 'rows' => [['label' => 'Round 1', 'value' => 'Jan 11, 2026'], ['label' => 'Round 2', 'value' => 'Feb 8, 2026'], ['label' => 'Round 3', 'value' => 'Mar 8, 2026'], ['label' => 'Round 4', 'value' => 'Apr 5, 2026']]],
                ['icon' => 'people', 'title' => 'Tentative Interview Dates', 'rows' => [['label' => 'Round 1', 'value' => '3rd week of Apr'], ['label' => 'Round 2', 'value' => '3rd week of Apr'], ['label' => 'Round 3', 'value' => '3rd week of Apr'], ['label' => 'Round 4', 'value' => '3rd week of Apr']]],
                ['icon' => 'document', 'title' => 'Offer Roll-Out', 'rows' => [['label' => 'Rounds 1 – 4', 'value' => '4th week of Apr']]],
            ]]],
            ['eligibility', ['eyebrow' => null, 'heading' => 'Eligibility Criteria', 'criteria' => [
                ['text' => 'Candidate with B.E./B.Tech., minimum 60% in 10th, 12th, graduation or equivalent CGPA from recognized Indian university. No active backlog.'],
                ['text' => 'A score in CAT/XAT/GMAT/GRE/GATE is preferred.'],
                ['text' => 'Open school students are not eligible.'],
            ], 'list_heading' => 'Eligible Engineering Branches', 'items' => array_map(fn (array $b) => ['icon' => $b[1], 'text' => $b[0]], [
                ['Mechanical Engineering', 'gear'], ['Electrical Engineering', 'bolt'], ['Electronics Engineering', 'chip'], ['Computer Science Engineering', 'code'],
                ['Industrial Engineering', 'factory'], ['Material Science Engineering', 'flask'], ['Automobile Engineering', 'gear'], ['Data Science & AI', 'chart'],
                ['Aeronautical Engineering', 'drone'], ['Instrumentation & Control Engineering', 'magnet'],
            ])]],
            ['steps', ['eyebrow' => null, 'heading' => 'Admission Process', 'body' => 'Your journey to becoming a sustainability leader starts here. A clear, fast-tracked process designed to identify thinkers, builders.', 'layout' => 'panel', 'panel_title' => 'Steps', 'image' => $img('p096', 'Applicant meeting the admissions team'), 'items' => [
                ['title' => 'Apply online at admissions.namtech.ac & pay Rs. 1000 application fee.'],
                ['title' => 'Fill out the application form and submit with all the documents.'],
                ['title' => 'Your application will be reviewed; if shortlisted, appear for an interview.'],
                ['title' => 'If selected, you will receive the admission offer letter.'],
                ['title' => 'Confirm your admission by timely payment of tuition & hostel fees as specified.'],
            ]]],
            ['checklist_cards', ['eyebrow' => null, 'heading' => 'Required Documents', 'tone' => 'light', 'cards' => [
                ['icon' => 'document', 'title' => 'Statement of purpose', 'items' => [['text' => 'Personal essay (500-1000 words)'], ['text' => 'Career goals & aspirations'], ['text' => 'Why NAMTECH']]],
                ['icon' => 'graduation', 'title' => 'Academic Transcripts', 'items' => [['text' => '10th & 12th mark sheets'], ['text' => 'Graduation degree & transcripts'], ['text' => 'Post-graduation (if applicable)']]],
                ['icon' => 'id', 'title' => 'ID Proof', 'items' => [['text' => 'Aadhar Card'], ['text' => 'PAN Card'], ['text' => 'Passport size photographs'], ['text' => 'Domicile certificate']]],
                ['icon' => 'portfolio', 'title' => 'Additional Documents', 'items' => [['text' => 'Entrance exam scorecard'], ['text' => 'Work experience certificates'], ['text' => 'Portfolio (for design/marketing)']]],
            ]]],
            ['fees', ['eyebrow' => null, 'heading' => 'Fees and Scholarships', 'body' => 'Access World-Class Learning Without Barriers', 'fee_label' => 'Program Fee', 'fee_amount' => 'Rs. 4,50,000',
                'rows' => [['label' => 'Tuition Fee', 'value' => 'Rs. 2,50,000'], ['label' => 'Accommodation, food, transport', 'value' => 'Rs. 2,00,000'], ['label' => 'Total fees', 'value' => 'Rs. 4,50,000']],
                'scholarships_title' => 'Scholarships',
                'scholarships' => [
                    ['title' => 'Tuition Fee Scholarship', 'text' => 'Up to 100% fee waiver based on academic and professional achievements'],
                    ['title' => 'Accommodation, food and transport', 'text' => '93% off for Females · 85% off for Males'],
                    ['title' => 'Fees Payable (after Scholarship)', 'text' => 'Rs. 15,000 for Females · Rs. 30,000 for Males'],
                    ['title' => 'Merit-Based Scholarships', 'text' => 'Up to 100% fee waiver for students with exceptional records.'],
                    ['title' => 'Women in Leadership', 'text' => 'Grants cover up to 93% off accommodation, food, and transport for female candidates.'],
                    ['title' => 'INI-Based Awards', 'text' => 'Financial aid for graduates from Institutes of National Importance.'],
                ],
                'link_label' => 'Learn about scholarships', 'link_url' => '#faq',
                'notes' => [
                    ['icon' => 'bank', 'title' => 'Security deposit', 'text' => 'Rs. 5,000 at admission (refundable at Program end)'],
                    ['icon' => 'calendar', 'title' => 'Payment Schedule', 'text' => 'First instalment before commencement of classes. Second instalment at the end of Semester 2'],
                    ['icon' => 'check', 'title' => 'Inclusions', 'text' => 'Tuition, accommodation, food, and transport included'],
                ],
            ]],
            ['feature_list', ['eyebrow' => null, 'heading' => 'Education Loan Assistance', 'body' => 'Loan partners: EduAID, Axis Bank, Credila and ICICI Bank.', 'tabs' => [['label' => null, 'items' => [
                ['icon' => 'bank', 'text' => 'Multi-bank education loan aggregator and facilitation support'],
                ['icon' => 'rupee', 'text' => 'Up to 100% coverage of tuition, hostel, and total education cost'],
                ['icon' => 'calendar', 'text' => 'Flexible repayment tenure up to 15 yrs, with Interest rates starting at 9.8% and processing fee between 0.5 - 2%'],
                ['icon' => 'building', 'text' => 'Direct disbursement to the university (as per admission letter)'],
                ['icon' => 'document', 'text' => 'Tax benefits on interest paid under Section 80(E)'],
                ['icon' => 'check', 'text' => 'Collateral-free loan options available'],
            ]]]]],
            ['contact_cards', ['eyebrow' => null, 'heading' => 'Meet the NAMTECH Team in Your City', 'items' => [
                ['region' => 'Bihar, Jharkhand, Odisha, West Bengal and Karnataka', 'name' => 'Rakesh Tiwari', 'email' => 'rakesh.tiwari@namtech.ac'],
            ]]],
            ['faq', ['heading' => 'Frequently Asked Questions', 'body' => 'Find answers to common questions about the Program, admissions, and student life.', 'layout' => 'centered', 'items' => array_map(fn (string $q) => ['question' => $q, 'answer' => $tbc], [
                'What are the eligibility criteria for admission?', 'Is work experience required for this program?', 'What is the placement record?',
                'Are there opportunities for international exposure?', 'What kind of projects will I work on?', 'How are scholarships awarded?',
                'What support is available for accommodation?', 'Can I pursue specializations within the program?',
            ])], 'faq'],
            ['enquiry_form', ['heading' => 'Program enquiry', 'body' => 'Tell us which program you are interested in and our admissions team will get back to you.', 'form_type' => 'program', 'show_contact_details' => true], 'enquire'],
        ]);

        $industryPartners = ['media_cards', ['eyebrow' => null, 'heading' => 'Collaboration for a Cause: Meet our Industry Partners', 'body' => 'Our curriculum is co-designed with leading organizations to align with evolving workforce needs and integrate Industry 4.0 standards, advanced tools, and emerging practices. These collaborations provide students with hands-on experience, expert mentorship, and strong placement opportunities.', 'layout' => 'logo', 'items' => [
            ['image' => $this->media['partner-story-precision'], 'logo' => $this->media['logo-siemens'], 'title' => 'Siemens', 'body' => 'Our collaboration with Siemens aims to revolutionize education in smart manufacturing, automation, and advanced technical education. It brings cutting-edge industry expertise and hands-on industry training into our academic framework.'],
            ['image' => $this->media['precision-machining'], 'logo' => $this->media['logo-cisco'], 'title' => 'Cisco', 'body' => 'Cisco is a global leader in networking and technology solutions. Our collaboration enhances NAMTECH’s academic Programs by bringing in the expertise and industry-relevant education in manufacturing technologies and digital innovation.'],
        ]]];

        $pages['partnerships'] = $this->page('Partnerships', 'partnerships', null, [
            $hero('Built with Industry. Backed by Global Expertise.', $this->media['global-collaboration-student'], 'Partnerships', 'Through strategic partnerships with global industry leaders and premier academic institutions, NAMTECH delivers education shaped by real-world challenges and advanced technologies.'),
            ['logo_strip', ['heading' => 'Our partners', 'category' => null]],
            $industryPartners,
            ['partners_showcase', ['eyebrow' => 'Partner stories', 'heading' => 'Learning with the World’s Leading Universities and Industries.', 'body' => 'Our partnerships bring global expertise into the classroom. It integrates cutting-edge research, advanced technologies, and real industry practices into the learning experience.', 'show_stories' => true]],
            $coCreate,
        ]);
        $corporateCta = ['cta_label' => 'Partner with us', 'cta_url' => '#enquiry:partnership'];
        $corporateContacts = ['contact_band', ['heading' => 'Co-create with us', 'body' => 'NAMTECH partners with leading industries and technology organizations to build future-ready training programs. Together, we design best-in-class learning programs and deliver 100% experiential education, building a globally competitive engineering workforce.', ...$corporateCta, 'contacts_label' => 'For More Information', 'contacts' => [
            ['name' => 'Ms. Nandini Dasgupta', 'role' => 'Director - Corporate Partnerships', 'email' => 'nandini.dasgupta@namtech.ac'],
            ['name' => 'Mr. Sridhar Kanukollu', 'role' => 'Associate Director – Corporate Partnerships', 'email' => 'sridhar.kanukollu@namtech.ac'],
        ]]];
        $industryProfiles = ['faq', ['heading' => 'Meet our Industry Partners', 'body' => null, 'layout' => 'split', 'items' => array_map(fn (array $partner) => [
            'question' => $partner[0],
            'answer' => implode('', array_map(fn (string $paragraph) => '<p>'.e($paragraph).'</p>', $partner[1])),
        ], $this->corporatePartnerProfiles())], 'industry-partners'];
        $leaderQuotes = ['testimonials', ['eyebrow' => null, 'heading' => 'Hear from our Leaders', 'body' => 'Hear from industrial pioneers, visionaries and institutional heads who have experienced the approach, progress and success of the NAMTECH model.', 'layout' => 'quotes', 'items' => [
            ['quote' => 'The progress NAMTECH has achieved is nothing short of remarkable. We are happy to partner with NAMTECH – a beacon of technical education in India.', 'name' => 'Mr. Bruce Poh', 'role' => 'CEO, ITEES Singapore'],
            ['quote' => 'NAMTECH has a visionary approach… focus on skilling at high scales for manufacturing… Posterity will acknowledge this impactful initiative and its institutional model that would bring up a future-ready high-tech workforce for India and the world.', 'name' => 'Dr. Koppillil Radhakrishnan', 'role' => 'Former Chairman – ISRO also led Mangalyaan (India’s Mars mission)'],
            ['quote' => 'I commend the founders and faculty of NAMTECH for creating a unique institution that is not just a center of academic excellence but also a beacon of hope for the future of engineering in India.', 'name' => 'Dr. Kamlesh Nilkanth Vyas', 'role' => 'Former Chairman – Indian Atomic Energy Commission, Distinguished Homi Bhabha Chair Professor at the Department of Atomic Energy'],
        ]]];

        $corporate = $this->page('Corporate Partnerships', 'corporate', $pages['partnerships'], [
            $hero('A Collaborative Platform for Industry Leaders Powering Manufacturing Innovation', $img('p096', 'Colleagues discussing work at a laptop'), 'Corporate Partnerships', 'India’s leadership in Manufacturing Engineering Technology (MET) is being driven by next generation professionals who are industry-ready and globally competitive. As a first-of-its-kind industry-academia institution, NAMTECH is bringing together industry pioneers and policymakers to build talent through co-created curricula, world-class infrastructure and industry-first learning. This mission is made possible by partners with a shared belief that the future of engineering education is built through collaboration.', $corporateCta),
            ['stat_cards', ['eyebrow' => null, 'heading' => 'Here’s Why Leading Corporates Build with NAMTECH', 'body' => 'India stands at a defining moment in its manufacturing journey. NAMTECH offers industry partners a structured ecosystem to capitalize on this shift across three industrial opportunity areas.', 'tone' => 'lavender', 'items' => [
                ['icon' => 'trend', 'value' => '25%', 'label' => 'Manufacturing Contribution to India’s GDP (GDP Acceleration)', 'text' => 'The national drive to scale manufacturing from 17% to 25% of GDP requires unprecedented demand for advanced manufacturing talent, innovation and industry-academia collaboration.'],
                ['icon' => 'people', 'value' => '65%', 'label' => 'Of India’s Population is Below 35 Years of Age (Demographic Edge)', 'text' => 'Harnessing the world’s largest demographic needs future-ready engineering education designed to empower modern manufacturing workforces with cutting-edge technical and digital skills.'],
                ['icon' => 'leaf', 'value' => '$10 Trillion', 'label' => 'Opportunity in India’s Green Transition (Sustainable Development)', 'text' => 'The shift toward sustainable growth and net-zero manufacturing produces new pathways for industrial innovation and clean energy leadership and presents a new model of development.'],
            ]]],
            ['tabs_media', ['eyebrow' => 'Partnership Opportunities', 'heading' => 'Explore Partnership Opportunities at NAMTECH', 'body' => 'Partner with NAMTECH to create impact at scale. From establishing world-class laboratories, centers of excellence and academic infrastructure to empowering students through scholarships, NAMTECH offers diverse avenues for organizations to co-create a thriving manufacturing talent development ecosystem.', 'tabs' => [
                ['label' => 'Strategic & Founding Partnership', 'image' => $img('p143', 'Team meeting around a conference table'), 'title' => 'Strategic & Founding Partnership', 'text' => 'Strategic and Founding Partnerships are NAMTECH’s highest-tier of partnerships, built exclusively for organizations committed to shaping the future of engineering education in India.', 'points' => [['title' => 'Infrastructure Development'], ['title' => 'Outreach'], ['title' => 'Endowments']], 'links' => [['label' => 'Explore more', 'url' => '/partnerships/corporate/strategic-and-founding-partnerships']]],
                ['label' => 'Schools, Centers, CoEs & Lab Partnerships', 'image' => $img('p120', 'Student working on an automation rig'), 'title' => 'Schools, Centers, CoEs & Lab Partnerships', 'text' => 'School & center partners play a defining role in shaping NAMTECH’s engineering education model. From program design and delivery to its continuous evolution, working with our partners ensures that every school, lab and center is cutting-edge and aligned with the changing needs of industry.', 'points' => [['title' => 'School-level Partnerships'], ['title' => 'Center and CoE Partnerships'], ['title' => 'Lab Partnerships']], 'links' => [['label' => 'Explore more', 'url' => '/partnerships/corporate/schools-centers-coes-and-lab-partnerships']]],
                ['label' => 'Sponsored Research', 'image' => $img('p127', 'Scientists working with laboratory equipment'), 'title' => 'Sponsored Research', 'text' => 'Our sponsored research model allows industry leaders to pilot and scale targeted research programs, access global faculty expertise and co-develop intellectual property in a collaborative and structurally sound framework.', 'points' => [['title' => 'Directed Research Projects'], ['title' => 'Faculty Exchange & Collaborative Research'], ['title' => 'Research Chairs & Long-Term Programs']], 'links' => [['label' => 'Explore more', 'url' => '/partnerships/corporate/sponsored-research']]],
            ]]],
            $industryProfiles,
            ['logo_strip', ['heading' => 'Industry partners', 'category' => 'industry']],
            $leaderQuotes,
            $corporateContacts,
        ]);

        $this->page('Strategic & Founding Partnerships', 'strategic-and-founding-partnerships', $corporate, [
            $hero('Strategic & Founding Partnerships', $img('p143', 'Team meeting around a conference table'), 'Corporate Partnerships', 'For organizations shaping and leading the future of engineering education. Strategic and Founding Partnerships are NAMTECH’s highest-tier of engagement, offering visionary organizations the opportunity to co-define the future of engineering education in India.', $corporateCta),
            ['intro', ['eyebrow' => null, 'heading' => 'Here’s How We Work Together', 'icon' => 'handshake', 'body' => '<p>Strategic and Founding Partnerships are designed for organizations that seek deep, long-term institutional alignment with NAMTECH’s mission to build the future of engineering education in India.</p><p>Strategic and Founding Partners are co-architects of India’s next generation of manufacturing talent. Together, we embark upon a shared mission, delivering value across talent development, innovation and social impact.</p>']],
            ['card_grid', ['eyebrow' => null, 'heading' => 'Partnership Areas', 'style' => 'joined', 'columns' => '3', 'tone' => 'white', 'cards' => [
                ['icon' => 'building', 'title' => 'Infrastructure Development', 'body' => 'Shape how the next generation engineers learn by co-creating dedicated schools, innovation centres and labs that simulate real-world industrial environments. Together, we translate technical expertise, application of emerging technologies and industry challenges into learning experiences that build more capable, deployment-ready professionals.'],
                ['icon' => 'people', 'title' => 'Outreach & Skilling Ecosystems', 'body' => 'Engage in talent development across the manufacturing pyramid, with a focused pathway to India’s technician and operator workforce through ITI outreach & upgradation. Ensure that skilled talent meets opportunity through curriculum interventions, immersive workshops and industry challenges along with hands-on training and career programs.'],
                ['icon' => 'award', 'title' => 'Endowments', 'body' => 'Build a legacy in engineering education. Endowments allow partners to establish named scholarships, faculty chairs, research grants and innovation prizes that strengthen NAMTECH’s academic ecosystem, advancing excellence and innovation for generations.'],
            ]]],
            $corporateContacts,
        ]);

        $this->page('Schools, Centers, CoEs & Lab Partnerships', 'schools-centers-coes-and-lab-partnerships', $corporate, [
            $hero('Schools, Centers, CoEs & Lab Partnerships', $img('p120', 'Student working on an automation rig'), 'Corporate Partnerships', 'Build academic ecosystems that converge learning, research and industry practice to advance engineering education. School, Center, CoE and Lab partnerships offer industry leaders a definitive stake in how engineering education is designed, practiced, delivered and scaled at NAMTECH.', $corporateCta),
            ['intro', ['eyebrow' => null, 'heading' => 'Here’s How We Work Together', 'icon' => 'handshake', 'body' => '<p>School, Center, CoE and Lab partnerships offer industry leaders a definitive stake in how engineering education is designed, practiced, delivered and scaled at NAMTECH. By bringing industry expertise into curriculum, pedagogy, research and experiential learning, partners help shape an academic model built for the future of engineering.</p><p>Join us in redefining engineering education. By enabling technology access, mobilizing resources, building academic infrastructure, or integrating your industry expertise, partners can co-design curricula, drive applied research and power advanced training programs at NAMTECH.</p>']],
            ['tabs', ['eyebrow' => null, 'heading' => 'Partnership Models', 'layout' => 'standard', 'tabs' => [
                ['label' => 'School-level Partnerships', 'title' => 'School-level Partnerships', 'body' => '<p>Co-create a school dedicated to your sector’s evolving needs by shaping its academic vision, program portfolio, research methodology and industry engagement. School-level partnerships offer the deepest level of institutional co-ownership and a long-term platform to influence how the next generation of sector-specific talent is developed.</p>', 'image' => $this->media['program-student-hardware']],
                ['label' => 'Center and CoE Partnerships', 'title' => 'Center and CoE Partnerships', 'body' => '<p>Shape research agendas, deepen academic expertise and directly benefit from the intellectual output of talented graduates from NAMTECH’s centers and centers of excellence (CoEs). Centers operate across broad domains like AI or sustainability, while CoEs bring concentrated academic and research depth to specific technologies or areas of strategic importance.</p>', 'image' => $this->media['specialized-labs-vehicle']],
                ['label' => 'Lab Partnerships', 'title' => 'Lab Partnerships', 'body' => '<p>Integrate advanced technology shaping modern industry into engineering education through dedicated experiential learning environments. Built around your technologies, tools and applications, lab partnerships enable meaningful hands-on exposure to industrial practice for learners while creating opportunities for technology validation, field trials and collaborative innovation.</p>', 'image' => $img('p131', 'Orange industrial robot arm')],
            ]]],
            ['media_cards', ['eyebrow' => 'Schools', 'heading' => 'Explore MET Innovation Schools', 'body' => 'NAMTECH’s MET Innovation Schools are interdisciplinary, industry-led academic units that prepare graduates to shape India’s industrial transformation. Each school offers structured partnership opportunities for industry leaders looking to shape the next generation of industrial engineers and professionals.', 'layout' => 'tall', 'card_link_label' => 'Explore school', 'items' => [
                ['image' => $this->media['program-student-hardware'], 'title' => 'School of Manufacturing Technologies (SoMT)', 'body' => 'The School of Manufacturing Technologies (SoMT) equips engineers with the expertise to operate, optimize and revolutionize modern production environments. Students master CNC machining, lean manufacturing, industrial automation, IoT and digital manufacturing. The school has been built with support from Siemens, Schneider Electric, L&T, Rockwell Automation, Festo and Endress+Hauser for laboratories, projects and hands-on learning experiences in addition to a global academic partnership with TUM Asia.', 'link_url' => '/schools/school-of-manufacturing-technologies'],
                ['image' => $this->media['experiential-learning-robotics'], 'title' => 'School of Manufacturing Design & AI (SoMDAI)', 'body' => 'The School of Manufacturing Design & AI (SoMDAI) converges at product innovation, digital engineering and intelligent manufacturing. Students master generative design, AI-driven simulation, sustainable design and cybersecurity, equipping them to lead the next wave of industrial transformation. Built in collaboration with leading semiconductor companies along with top-tier AI and cybersecurity companies, the school integrates industry-grade laboratories with advanced curricula. Graduates drive innovation across manufacturing, semiconductors, product development and Industry 4.0 environments.', 'link_url' => '/schools/school-of-manufacturing-design-ai'],
                ['image' => $this->media['labs-soldering'], 'title' => 'School of Robotics (SoR)', 'body' => 'The School of Robotics (SoR) equips engineers to design, program and deploy intelligent automation systems across manufacturing, warehousing, logistics and industrial infrastructure. The curriculum builds expertise in industrial robotics, cobots, mechatronics and robotic process automation through experiential learning in industry-grade labs. Built with support from leading technology partners like Addverb for warehouse automation, ABB for robotics infrastructure and FANUC through WorldSkills and industrial automation initiatives. Students also train on Mitsubishi robotic systems already deployed across NAMTECH’s facilities.', 'link_url' => '/schools/school-of-robotics'],
                ['image' => $this->media['labs-research'], 'title' => 'School of Sustainability (SoS)', 'body' => 'The School of Sustainability (SoS) trains engineers to accelerate India’s transition towards clean energy, circular economy models and sustainable industrial growth. Through experiential learning in renewable energy, energy transition and sustainable supply chains, graduates master skills required to lead India’s sustainability transformation. As India’s first school focused on industrial and manufacturing sustainability, it embeds sustainability principles across campus development and academia. In partnership with KPMG India, TUM Asia, Schneider Electric and Siemens, the school has created a global learning ecosystem implementing India’s Net Zero and sustainable development ambitions.', 'link_url' => '/schools/school-of-sustainability'],
            ]]],
            ['media_cards', ['eyebrow' => 'Centers', 'heading' => 'MET Innovation Centers', 'layout' => 'tall', 'tone' => 'light', 'card_link_label' => 'Explore center', 'items' => [
                ['image' => $this->media['specialized-labs-vehicle'], 'title' => 'Center of Excellence in Automotive Engineering', 'body' => 'NAMTECH’s Center of Excellence (CoE) in Automotive Engineering is a dynamic focal point for advanced research, skill-building and technological breakthroughs across internal combustion engines, EVs and hydrogen and fuel cell systems. By prioritizing key sectors like smart manufacturing, alternate fuels, ADAS, sustainable materials and vehicle electrification, the CoE successfully links academic learning with real-world industry demands to fuel the future of mobility. To build tomorrow’s technology leaders and entrepreneurs, the center features a specialized 2-year Master Program in Automotive Technology and Management.', 'link_url' => '/centres/coe-in-automotive-engineering'],
                ['image' => $this->media['precision-machining'], 'title' => 'Center of Advanced Computing & AI (CoACAI)', 'body' => 'NAMTECH’s Center of Advanced Computing & Artificial Intelligence (CoACAI) is a premier hub for innovation, research and talent development in data science, computing and intelligent systems. Specializing in high-impact domains like Generative AI, Machine Learning, Computer Vision, Cloud Computing and AI-driven robotics, the center bridges the gap between academia and industry. Through immersive, hands-on training, it equips learners to build and scale practical solutions for sectors such as healthcare, manufacturing, fintech, automotive, sustainability and smart cities.', 'link_url' => '/centres/centre-of-advanced-computing-ai'],
                ['image' => $this->media['turbine-engineer'], 'title' => 'Center of Sustainable Energy & Circularity (CSEC)', 'body' => 'NAMTECH, NTPC and IIT Roorkee jointly established the Center for Sustainable Energy and Circularity (CSEC) to pioneer advancements in energy transition, sustainable manufacturing, workforce transformation and circular economies. Blending IIT Roorkee’s academic research with NTPC’s clean energy leadership and NAMTECH’s practical learning framework, the center serves as a national benchmark linking applied research, skill building and technology implementation.', 'link_url' => '/centres/experiential-learning-center-for-sustainable-energy-circularity'],
            ]]],
            ['card_grid', ['eyebrow' => null, 'heading' => 'Labs Partnership', 'style' => 'joined', 'columns' => '3', 'tone' => 'white', 'cards' => [
                ['icon' => 'gear', 'title' => 'Equipment'], ['icon' => 'flask', 'title' => 'Full Lab'], ['icon' => 'graduation', 'title' => 'Training'],
            ]], 'labs-partnership', false],
            ['card_grid', ['eyebrow' => null, 'heading' => 'Co-Building the Ecosystem', 'body' => 'NAMTECH’s Center of Excellence (CoE) and Lab partnerships are designed as collaborative ecosystems for industry leaders, researchers and government bodies to converge, solve complex industrial challenges and build shared capability.', 'style' => 'joined', 'columns' => '3', 'tone' => 'white', 'cards' => [
                ['icon' => 'book', 'title' => 'Joint Curriculum Development', 'body' => 'Co-author course modules, design project briefs and embed your organizational experience and technical expertise directly into learning modules. NAMTECH graduates enter the workforce aligned with your operational environment and industrial know-how.'],
                ['icon' => 'people', 'title' => 'Faculty & Researcher Engagement', 'body' => 'Your technical experts become part of NAMTECH’s academic community. Serving as visiting faculty, research co-supervisors and capstone mentors, this ensures your experts are at the frontier of research innovation while our faculty focuses on industrial application.'],
                ['icon' => 'lightbulb', 'title' => 'Applied Research & IP Development', 'body' => 'Co-invest in research programs addressing your organization’s most critical engineering challenges. Leverage NAMTECH’s cutting-edge research infrastructure in a commercially aligned IP framework co-designed to provide business value for your enterprise.'],
            ]]],
            $industryProfiles,
            $leaderQuotes,
        ]);

        $this->page('Sponsored Research', 'sponsored-research', $corporate, [
            $hero('Sponsored Research', $img('p127', 'Scientists working with laboratory equipment'), 'Corporate Partnerships', 'Driving industry innovation through research-led collaboration. Support targeted research programmes, engage with leading faculty and researchers and advance new knowledge and intellectual property development through a flexible, collaborative research partnership.', $corporateCta),
            ['card_grid', ['eyebrow' => null, 'heading' => 'How We Work Together', 'body' => 'NAMTECH’s Sponsored Research partnerships give industry sponsors meaningful influence over research direction and a clear pathway to commercialize outcomes. Whether you have a specific industrial problem to solve or a technology frontier to explore, we design research engagements that deliver.', 'style' => 'joined', 'columns' => '3', 'tone' => 'white', 'cards' => [
                ['icon' => 'flask', 'title' => 'Directed Research Projects', 'body' => 'Sponsor a focused, time-bound research project led by NAMTECH faculty and graduate researchers. You define the problem statement and desired outcomes; we bring the research capability, facilities and academic methodology. Regular reporting, milestone check-ins and IP-sharing agreements ensure that the project delivers commercially viable insights within agreed timelines.'],
                ['icon' => 'people', 'title' => 'Faculty Exchange & Collaborative Research', 'body' => 'Embed your technical experts in NAMTECH’s laboratories and research community and vice versa. Faculty exchange programs allow your team to co-author research, co-supervise graduate students and jointly present findings at international conferences.'],
                ['icon' => 'award', 'title' => 'Research Chairs & Long-Term Programs', 'body' => 'Establish a named Research Chair in your chosen technology or industry domain. This could be a professorial or associate professorial position focused on academic research challenges. Endowed chairs attract top-tier academic talent, build research capability over multiple years and create a permanent institutional association.'],
            ]]],
            $corporateContacts,
        ]);
        $this->page('Academic Partnerships', 'academic', $pages['partnerships'], [
            $hero('A Collaborative Academic Platform to Advance Engineering Education In India', $img('p044', 'Hand writing on a glass board'), 'Academic Partnerships', 'Academic partnerships at NAMTECH are pioneering a shift in engineering and technical education. Through robust partnerships with world-class academic institutions in India and beyond, NAMTECH integrates experiential learning with cutting-edge labs, applied research and advanced industrial systems. Partnering with NAMTECH ensures that top educational establishments have a definitive stake in equipping learners with the expertise to shape the future of manufacturing.', ['cta_label' => 'Partner with us', 'cta_url' => '#enquiry:partnership']),
            ['feature_list', ['eyebrow' => null, 'heading' => 'Our Academic Partners', 'tabs' => [['label' => null, 'items' => [
                ['icon' => 'graduation', 'text' => 'Massachusetts Institute of Technology Department of Mechanical Engineering, United States'],
                ['icon' => 'graduation', 'text' => 'Carnegie Mellon University, United States'],
                ['icon' => 'graduation', 'text' => 'Technical University of Munich (TUM ASIA), Singapore'],
                ['icon' => 'graduation', 'text' => 'Purdue University Northwest, United States'],
                ['icon' => 'graduation', 'text' => 'ITEES, Singapore'],
            ]]]]],
            ['media_cards', ['eyebrow' => null, 'heading' => 'Global Academic Collaborations In Action', 'layout' => 'logo', 'items' => [
                ['image' => $img('p043', 'Engineer building a robotics assembly'), 'title' => 'Massachusetts Institute of Technology Department of Mechanical Engineering, United States', 'body' => 'An 18-month pilot initiative designed to adapt, scale and implement MIT MechE’s renowned curriculum. By integrating advanced AR/VR architectures and phygital labs, we’re setting a new standard for engineering execution.'],
                ['image' => $img('p102', 'Analyst reviewing data dashboards'), 'title' => 'Carnegie Mellon University, United States', 'body' => 'Our collaboration with Carnegie Mellon includes their Simon Initiative and Open Learning Initiative (OLI). NAMTECH and OLI are co-designing STEM courses, comprehensive faculty upskilling via the Learning Engineering Fellows program and upskilling ITI students. With the impact already visible, this valuable collaboration with Carnegie Mellon University will also lead to the establishment of a Learning Science Research Center on the NAMTECH campus.'],
                ['image' => $this->media['global-collaboration-student'], 'title' => 'Technical University of Munich (TUM ASIA), Singapore', 'body' => 'A continuous curriculum enhancement initiative in collaboration with TUM Asia is designed to anchor NAMTECH’s iPMP programs within changing Industry 4.0 standards, integrating innovative teaching methodologies and global academic practices. TUM Asia regularly conducts specialized training programs for NAMTECH faculty and students, while NAMTECH’s elite faculty members conduct specialized training programs in Singapore. Students completing iPMP programs receive joint certification from NAMTECH and TUM Asia.'],
                ['image' => $img('p085', 'Teacher presenting at a whiteboard'), 'title' => 'Purdue University Northwest (PNW), United States', 'body' => 'The objective of this collaboration is to deliver customized, experiential and smart manufacturing-focused engineering programs that have helped close the gap between foundational studies and advanced research. Highly experienced PNW visiting faculty members assist NAMTECH faculty in delivering specialized modules in its programs. Additionally, NAMTECH and PNW’s 1+1 Pathway Program provides iPMP-SMG students with an exclusive opportunity to seamlessly transition into PNW’s Master of Science in Mechanical Engineering program in Indiana.'],
                ['image' => $img('p116', 'Prize winners at the IndiaSkills regional competition'), 'title' => 'ITEES, Singapore', 'body' => 'A co-designed curriculum that integrates Indian technologists with Industry 4.0 standards and moulds them through Singapore’s elite postgraduate discipline. This partnership has enabled NAMTECH to build a strong trainer ecosystem. Over the last two years, NAMTECH has trained more than 100 trainers and impacted over 3,600 learners through its programs.'],
            ]]],
            ['card_grid', ['eyebrow' => null, 'heading' => 'Here’s Why Leading Academic Institutions Partner With NAMTECH', 'style' => 'joined', 'columns' => '3', 'tone' => 'white', 'cards' => [
                ['icon' => 'handshake', 'title' => 'Curriculum Co-Designed with Industry', 'body' => 'Our curriculum is designed and delivered in direct collaboration with industry leaders, ensuring students learn the skills, technologies and practices that are relevant to the evolving demands of the workplace.'],
                ['icon' => 'factory', 'title' => 'Immersive and Experiential Learning', 'body' => 'NAMTECH trains within specialized labs, or ‘micro-factories’ that replicate live, high-tech production floors. By developing skills within these micro-factories, NAMTECH graduates are “Day-Zero Ready”, when they enter the workforce.'],
                ['icon' => 'globe', 'title' => 'The MET Ecosystem Hub', 'body' => 'NAMTECH is the founding member, convenor, and the catalyst of the Manufacturing Engineering Technology (MET) platform that seeks to engage government, industry, and academia in a shared execution and governance framework to enhance India’s manufacturing competitiveness.'],
            ]]],
            ['card_grid', ['eyebrow' => null, 'heading' => 'Co-Create The Future With NAMTECH', 'body' => 'NAMTECH works with leading Indian and global academic institutions, researchers, and think tanks across six core areas for academic collaboration.', 'style' => 'outlined', 'columns' => '3', 'tone' => 'lavender', 'cards' => [
                ['icon' => 'flask', 'title' => 'Joint Research, Innovation & Technology Development', 'body' => 'Collaborate on applied research, industry-sponsored projects and deep-tech innovations in smart manufacturing, sustainability, automotive systems, robotics, semiconductor manufacturing and AI. NAMTECH delivers direct access to robust industry networks, advanced experiential labs and interdisciplinary research opportunities.'],
                ['icon' => 'book', 'title' => 'Advanced Skilling, Curriculum Development & Academic Innovation', 'body' => 'Co-develop industry-aligned curricula, micro-credentials, certification programs and experiential learning modules. NAMTECH houses extensive expertise in outcome-based education, industry integration and competency mapping.'],
                ['icon' => 'globe', 'title' => 'Joint Academic Programs & Global Pathways', 'body' => 'Establish integrated academic programs, credit-transfer and semester abroad opportunities along with jointly delivered courses. NAMTECH provides flexible program structures, industry-integrated learning and access to a growing network of reputed international academic institutions.'],
                ['icon' => 'graduation', 'title' => 'Faculty Development, Executive Education & Professional Upskilling', 'body' => 'Design and deliver jointly certified Faculty Development Programs (FDPs), Executive Development Programs (EDPs), leadership programs and professional upskilling programs. NAMTECH possesses expertise in advanced manufacturing & sustainability education as well as workforce transformation initiatives.'],
                ['icon' => 'people', 'title' => 'Faculty, Student & Industry Practitioner Exchanges', 'body' => 'Facilitate short-term and long-term exchange and engagement opportunities for faculty, researchers, students and industry experts. NAMTECH provides robust industrial infrastructure, strong industry connectivity and experiential learning environments.'],
                ['icon' => 'lightbulb', 'title' => 'Ecosystem Innovation, Entrepreneurship & Venture Platforms', 'body' => 'Collaborate to strengthen innovation and entrepreneurship through startup incubation, innovation challenges, hackathons, consortiums, competitions and platforms addressing national priorities such as green mobility, advanced manufacturing and sustainable development. NAMTECH offers access to industry partnerships and convening power to scale meaningfully.'],
            ]]],
            ['testimonials', ['eyebrow' => null, 'heading' => 'Endorsements Of Global Leaders On NAMTECH', 'body' => 'Hear from visionaries and institutional heads who have experienced the approach, progress and success of the NAMTECH model.', 'layout' => 'quotes', 'items' => [
                ['quote' => 'The progress NAMTECH has achieved is nothing short of remarkable. We are happy to partner with NAMTECH – a beacon of technical education in India.', 'name' => 'Mr. Bruce Poh', 'role' => 'CEO, ITEES Singapore'],
                ['quote' => 'NAMTECH has a visionary approach… focus on skilling at high scales for manufacturing… Posterity will acknowledge this impactful initiative and its institutional model that would bring up a future-ready high-tech workforce for India and the world.', 'name' => 'Dr. Koppillil Radhakrishnan', 'role' => 'Former Chairman, ISRO. Also led Mangalyaan (India’s Mars mission)'],
                ['quote' => 'I commend the founders and faculty of NAMTECH for creating a unique institution that is not just a centre of academic excellence but also a beacon of hope for the future of engineering in India.', 'name' => 'Dr. Kamlesh Nilkanth Vyas', 'role' => 'Former Chairman, Indian Atomic Energy Commission. Distinguished Homi Bhabha Chair Professor at the Department of Atomic Energy'],
            ]]],
            $news,
            ['cta_band', ['heading' => 'Explore Collaboration Opportunities', 'body' => null, 'cta_label' => 'Partner with us', 'cta_url' => '#enquiry:partnership', 'tone' => 'lavender']],
        ]);

        $researchNav = ['subnav', ['items' => [
            ['label' => 'Research @NAMTECH', 'url' => '/research-and-consultancy/research'],
            ['label' => 'Consultancy Services', 'url' => '/research-and-consultancy/consultancy'],
            ['label' => 'Publications', 'url' => '/research-and-consultancy/publications'],
        ]]];
        $researchCards = ['card_grid', ['eyebrow' => null, 'heading' => 'Research & Publications @ NAMTECH', 'style' => 'outlined', 'columns' => '2', 'tone' => 'white', 'cards' => [
            ['icon' => 'factory', 'title' => 'Learning by building', 'body' => 'Learning happens by building. At NAMTECH, classrooms don’t have desks but machines used in the industry.'],
            ['icon' => 'globe', 'title' => 'Global co-delivery', 'body' => 'Our curriculum is co-designed and co-delivered with academicians from global universities.'],
        ]]];
        $publicationsCarousel = ['publications_carousel', ['heading' => 'Latest publications', 'cta_label' => 'View All Research and Publications', 'cta_url' => '/research-and-consultancy/publications']];
        $pages['research'] = $this->page('Research and Consultancy', 'research-and-consultancy', null, [
            $hero('Research and Consultancy', $img('p064', 'Two researchers reviewing documents'), 'Research & Consultancy', null, ['cta_label' => 'Explore Partnerships', 'cta_url' => '/partnerships/corporate']),
            $researchNav, $researchCards, $publicationsCarousel, $contactCta,
        ]);
        $this->page('Research @NAMTECH', 'research', $pages['research'], [
            $hero('Research & Publications @ NAMTECH', $img('p064', 'Two researchers reviewing documents'), 'Research & Consultancy', null, ['cta_label' => 'Explore Partnerships', 'cta_url' => '/partnerships/corporate']),
            $researchNav, $researchCards, $publicationsCarousel, $contactCta,
        ]);
        $this->page('Consultancy', 'consultancy', $pages['research'], [
            $hero('Research and Consultancy', $this->media['professional-tablet'], 'Consultancy Services', null, ['cta_label' => 'Explore Partnerships', 'cta_url' => '/partnerships/corporate']),
            $researchNav,
            ['card_grid', ['eyebrow' => null, 'heading' => 'Our Approach', 'style' => 'joined', 'columns' => '4', 'tone' => 'white', 'cards' => [
                ['icon' => 'factory', 'title' => 'Tailored to the sector and businesses'], ['icon' => 'chart', 'title' => 'Built on deep critical evaluation'],
                ['icon' => 'handshake', 'title' => 'Co-created for practical implementations'], ['icon' => 'trend', 'title' => 'Long term approach'],
            ]]],
            ['tabs', ['eyebrow' => null, 'heading' => 'Industrial Sectors we serve', 'layout' => 'centered', 'tone' => 'light', 'tabs' => array_map(fn (string $t) => ['label' => $t, 'title' => $t, 'body' => $tbc], ['Energy & Resources', 'Industrials & Mobility', 'Financial Services', 'MET Sector', 'Health & Life Sciences', 'Infrastructure & Built Environment'])]],
            ['intro', ['heading' => 'Our Consulting Services', 'body' => $tbc]],
            ['contact_cards', ['heading' => 'Leadership Team', 'items' => [['name' => 'Rakesh Tiwari', 'email' => 'rakesh.tiwari@namtech.ac']]]],
            $contactCta,
        ]);
        $this->page('Research and Publications', 'publications', $pages['research'], [
            $hero('Research and Publications', $img('p064', 'Two researchers reviewing documents'), 'Research & Consultancy'),
        ]);

        $steContacts = ['contact_band', ['heading' => 'Connect with our Team', 'body' => null, 'cta_label' => 'Contact us', 'cta_url' => '/contact', 'contacts_label' => 'For More Information', 'contacts' => [
            ['name' => 'Dr. Ravi Shankar', 'role' => 'Associate Director', 'email' => 'dvravi.shankar@namtech.ac'],
            ['name' => 'Vibha Desai', 'role' => 'Associate Director', 'email' => 'vibha.desai@namtech.ac'],
        ]]];
        $pages['ste'] = $this->page('School of Technical Education', 'school-of-technical-education', null, [
            $hero('School of Technical Education: Building Industry 4.0–Ready Technologists', $img('p028', 'Technician in a hard hat operating equipment'), 'School of Technical Education', null, ['cta_label' => 'Explore Programs', 'cta_url' => '#programs']),
            ['intro', ['heading' => 'Overview', 'body' => $tbc, 'icon' => 'cap']],
            ['card_grid', ['eyebrow' => 'Talking Points', 'heading' => 'Transforming India’s technical workforce', 'style' => 'joined', 'columns' => '4', 'tone' => 'white', 'cards' => [
                ['icon' => 'people', 'title' => 'Transforming India’s Technician Workforce'], ['icon' => 'robot', 'title' => 'Experiential Learning Through Advanced Technology'],
                ['icon' => 'factory', 'title' => 'Industry-integrated, applied learning models'], ['icon' => 'globe', 'title' => 'Scaling Skills Access Across India'],
            ]]],
            ['programs_list', ['eyebrow' => null, 'heading' => 'Explore Programs @ School of Technical Education', 'level' => 'technical', 'school' => null], 'programs'],
            ['video_feature', ['layout' => 'overlay', 'heading' => 'Here’s What the Dean Says', 'body' => self::Tbc, 'name' => 'Mr. Sandeep Achantani', 'role' => 'Director, School of Technical Education', 'image' => $img('p031', 'Portrait of the dean in a library')]],
            ['people', ['eyebrow' => 'Faculty', 'heading' => 'Featured Faculty', 'layout' => 'carousel', 'limit' => 8]],
            ['media_cards', ['eyebrow' => null, 'heading' => 'Labs & Infrastructure', 'layout' => 'tall', 'cta_label' => 'View All', 'cta_url' => '/academics/labs-and-infrastructure', 'items' => [
                ['image' => $img('p120', 'Student working on an automation rig'), 'title' => 'Advanced Manufacturing Labs'],
                ['image' => $img('p102', 'Analyst reviewing data dashboards'), 'title' => 'Digital Learning Centers'],
                ['image' => $img('p033', 'Researcher examining samples under a microscope'), 'title' => 'Water Stewardship Lab'],
            ]]],
            ['partners_showcase', ['eyebrow' => 'Industry & Academic', 'heading' => 'Partnerships at NAMTECH', 'show_stories' => true]],
            ['events_carousel', ['heading' => 'Upcoming Events', 'body' => 'Join us at our upcoming events as we collaborate, learn, and drive meaningful change for a sustainable future.', 'cta_label' => 'View All Events', 'cta_url' => '/events']],
            $news, $steContacts,
        ]);
        $gaps = [
            ['A growing skills gap', 'Vocational education focuses on routine skills, but Industry 4.0 requires more. Digital systems and collaboration are now essential. Without updated training, many students risk being left behind.', 'p075', 'Student concentrating on precision work'],
            ['Infrastructure hasn’t kept up', 'Most ITIs aren’t designed for advanced manufacturing. They lack smart labs and real tools, limiting hands-on learning and exposure to modern production.', 'p065', 'Empty classroom with stools'],
            ['From operators to future leaders', 'Many students are trained to operate machines. Tomorrow’s industry needs technicians who can adapt and lead on the shop floor.', 'p028', 'Technician in a hard hat operating equipment'],
        ];
        $support = [
            ['building', 'Infrastructure & labs Sponsorship', 'Support the development of advanced labs, smart classrooms, EV training facilities, and Lab on Wheels to provide students with industry-relevant, hands-on learning experiences.'],
            ['code', 'Phygital Training Program', 'Sponsor student cohorts through NAMTECH’s Phygital Learning Program, enabling access to digital learning platforms, simulations, and practical training.'],
            ['people', 'Serve India Fellowship', 'Fund fellowships for professionals working with government ITIs to mentor faculty, strengthen institutions, and improve student learning outcomes.'],
            ['graduation', 'Skill Development & Employability Programs', 'Support specialized training, industry immersion, placement readiness initiatives, and soft skills programs that prepare students for successful careers.'],
            ['handshake', 'Adopt-an-ITI / Hub & Spoke Model', 'Partner with one or more ITIs to strengthen infrastructure, faculty capability, and student outcomes through NAMTECH’s Hub & Spoke model.'],
            ['leaf', 'Corporate CSR Projects & Impact Funding', 'Collaborate on customized CSR initiatives with measurable outcomes, transparent reporting, and long-term community impact.'],
        ];
        $modelImages = [['p120', 'Student working on an automation rig'], ['p102', 'Analyst reviewing data dashboards'], ['p085', 'Teacher presenting at a whiteboard'], ['p075', 'Student concentrating on precision work'], ['p010', 'NAMTECH team photograph in a lab'], ['p039', 'Group of smiling students']];
        $this->page('Outreach', 'outreach', $pages['ste'], [
            $hero('The Blueprint for India’s ITI Force', $img('p078', 'Engineer with a tablet on site stairs'), 'Outreach'),
            ['image_text', ['eyebrow' => null, 'heading' => 'Our Vision', 'body' => '<p>'.$purpose.'</p>', 'image' => $img('p124', 'Student soldering electronics at a desk'), 'image_position' => 'left', 'layout' => 'staggered']],
            ['image_text', ['eyebrow' => null, 'heading' => 'Our Mission', 'body' => '<p>'.$purpose.'</p>', 'image' => $img('p012', 'Student soldering components in a workshop'), 'image_position' => 'right', 'layout' => 'staggered']],
            ['accordion_media', ['eyebrow' => null, 'heading' => 'Current Gaps in Tech-Vocational Education', 'items' => array_map(fn (array $g) => ['title' => $g[0], 'text' => $g[1], 'image' => $img($g[2], $g[3])], $gaps)]],
            ['card_tabs', ['eyebrow' => null, 'heading' => 'NAMTECH’s Hubs and Spokes Model', 'body' => null, 'cta_label' => 'Read Case Study', 'cta_url' => '/impact/stories', 'tone' => 'lavender', 'tabs' => [
                ['label' => 'Overview', 'cards' => [['icon' => 'building', 'title' => 'Infrastructure of the hubs'], ['icon' => 'globe', 'title' => 'Spokes’ ITI Network'], ['icon' => 'code', 'title' => 'Learning Delivery Model'], ['icon' => 'trend', 'title' => 'Student Impact & Career Pathways']]],
                ['label' => 'Infrastructure', 'cards' => [['icon' => 'building', 'title' => 'Infrastructure of the hubs', 'text' => self::Tbc]]],
                ['label' => 'Locations', 'cards' => [['icon' => 'pin', 'title' => 'Locations', 'text' => self::Tbc]]],
                ['label' => 'Impact Stories', 'cards' => [['icon' => 'trend', 'title' => 'Student Impact & Career Pathways', 'text' => self::Tbc]]],
            ]]],
            ['numbered_list', ['eyebrow' => null, 'heading' => 'NAMTECH’s Scaling Strategies', 'body' => 'NAMTECH is scaling its Hub-and-Spoke Model by partnering with Central & State Departments, Government ITIs and institutions for large-scale program implementation and with Industry to upskill their existing workforce.', 'list_label' => 'Our expansion focuses on:', 'items' => [
                ['text' => 'Collaborating with Central & State Government for effective program implementation across technical institutions'],
                ['text' => 'Partnering with Industry for customized upskilling, apprenticeships, and placements'],
                ['text' => 'Replicating proven infrastructure and phygital learning solutions'],
                ['text' => 'Strengthening faculty development and industry-aligned curriculum'],
            ], 'footer' => 'Through this collaborative approach, NAMTECH aims to empower nearly 3 million learners across India with future-ready technical skills and sustainable employment opportunities.']],
            ['card_grid', ['eyebrow' => 'Partnerships Opportunities', 'heading' => 'Support & Partnership Opportunities', 'body' => 'NAMTECH School of Technical Education invites industry leaders, corporates, and CSR foundations to partner with us in transforming technical education and empowering underprivileged youth across India. We offer multiple meaningful collaboration models that deliver measurable social impact while providing strategic value to your organization.', 'style' => 'outlined', 'columns' => '3', 'tone' => 'white', 'cards' => array_map(fn (array $c) => ['icon' => $c[0], 'title' => $c[1], 'body' => $c[2]], $support)]],
            ['tabs_media', ['eyebrow' => null, 'heading' => 'Partnership Models', 'body' => 'Through the innovative Hub-and-Spoke model, the program proudly supports an impressive 1,800 ITI learners each year. This initiative not only connects them to essential industry-ready skills but also provides invaluable mentorship and diverse career pathways.', 'tabs' => array_map(fn (array $c, array $im) => ['label' => str_replace([' Sponsorship', ' & Employability Programs', 'Adopt-an-ITI / ', 'Corporate CSR Projects & Impact Funding'], ['', '', '', 'CSR & Impact'], $c[1]), 'image' => $img($im[0], $im[1]), 'title' => $c[1], 'text' => $c[2]], $support, $modelImages)]],
            ['feature_list', ['heading' => 'Why Partner with NAMTECH?', 'tabs' => [['label' => null, 'items' => [
                ['icon' => 'people', 'text' => 'Industry-ready Talent Pipeline'], ['icon' => 'handshake', 'text' => 'Employee Engagement'],
                ['icon' => 'trend', 'text' => 'High-impact Social Outcomes'], ['icon' => 'sparkle', 'text' => 'Brand Visibility & Recognition'],
                ['icon' => 'document', 'text' => 'CSR & ESG Reporting'],
            ]]]]],
            $steContacts,
        ]);
        $this->page('iPTP', 'iptp', $pages['ste'], [$hero('International Professional Technologist Program (iPTP)', $img('p028', 'Technician in a hard hat operating equipment'), 'School of Technical Education'), ['intro', ['heading' => 'About iPTP', 'body' => $tbc]]]);

        $pages['impact'] = $this->page('Impact & Initiatives', 'impact', null, [
            $hero('Power of a Collaborative Institution', $img('p039', 'Group of smiling students'), 'Impact'),
            ['media_cards', ['eyebrow' => null, 'heading' => 'NAMTECH’s Institutional Impact', 'layout' => 'horizontal', 'card_link_label' => 'View All Impact Stories', 'items' => [
                ['icon' => 'people', 'title' => 'Engineering Talent Transformation', 'image' => $img('p139', 'Students building a robot together'), 'link_url' => '/impact/stories'],
                ['icon' => 'lightbulb', 'title' => 'Innovation for the Next Generation', 'image' => $img('p118', 'Students with a collaborative robot'), 'link_url' => '/impact/stories'],
                ['icon' => 'leaf', 'title' => 'Sustainable Manufacturing (from industry perspective)', 'image' => $img('p136', 'Plant cuttings in laboratory glassware'), 'link_url' => '/impact/stories'],
            ]]],
            ['post_list', ['heading' => 'Student Impact', 'post_type' => 'story', 'limit' => 6, 'layout' => 'carousel', 'cta_label' => 'View All Impact Stories', 'cta_url' => '/impact/stories']],
            ['logo_strip', ['heading' => 'Partnerships at NAMTECH', 'category' => null]],
            ['card_tabs', ['eyebrow' => 'Social Impact', 'heading' => 'Partner for Progress', 'tone' => 'lavender', 'cta_label' => 'Partner with us', 'cta_url' => '#enquiry:partnership', 'tabs' => [
                ['label' => 'Hubs & Spokes', 'cards' => [['icon' => 'building', 'title' => 'Infrastructure of the hubs'], ['icon' => 'globe', 'title' => 'Spokes’ ITI Network'], ['icon' => 'code', 'title' => 'Learning Delivery Model']]],
            ]]],
            ['mission', ['heading' => 'Built to Build India - The 3 Million Mission', 'body' => null, 'limit' => 6]],
            ['cta_band', ['heading' => 'Explore More Impact Stories', 'body' => null, 'cta_label' => 'View all', 'cta_url' => '/impact/stories']],
        ]);
        $this->page('Impact Stories', 'stories', $pages['impact'], [$hero('Impact Stories', $img('p039', 'Group of smiling students'), 'Impact')]);

        $this->page('Placements', 'placements', null, [
            $hero('Placements', $img('p000', 'Graduate shaking hands at a job interview'), 'Career Development', null, ['cta_label' => 'Download Placement Brochure', 'cta_url' => '/contact']),
            ['stat_cards', ['heading' => null, 'tone' => 'white', 'items' => [
                ['icon' => 'people', 'value' => '584+', 'label' => 'Overall students placed in year 2024-25'],
                ['icon' => 'trend', 'value' => '30%', 'label' => 'Average year-on-year CTC increase'],
                ['icon' => 'check', 'value' => '98%', 'label' => 'Placement rate within 6 months of graduation'],
                ['icon' => 'rupee', 'value' => '₹24 LPA', 'label' => 'The highest CTC offered in the year 2024-25'],
            ]]],
            ['post_list', ['heading' => 'Impact Stories', 'post_type' => 'story', 'limit' => 6, 'layout' => 'carousel', 'cta_label' => 'View All Impact Stories', 'cta_url' => '/impact/stories']],
            ['stat_cards', ['heading' => 'Why Employers Choose NAMTECH Talent', 'tone' => 'lavender', 'items' => [
                ['icon' => 'bolt', 'value' => '50%', 'label' => 'Faster productivity.'], ['icon' => 'check', 'value' => '30%', 'label' => 'Higher task accuracy.'], ['icon' => 'code', 'value' => '2x', 'label' => 'Faster transition into digital roles.'],
            ]]],
            ['placement_trends', ['heading' => 'Placement Trends Across Programs', 'show_logos' => true, 'items' => [
                ['level' => 'Masters Programs', 'program' => 'Master Program in Smart Manufacturing Technology and Management', 'school' => 'School of Manufacturing Technologies', 'text' => null, 'stats' => [
                    ['value' => '98%', 'label' => 'Placement rate'], ['value' => '₹24 LPA', 'label' => 'Highest salary'], ['value' => '584+', 'label' => 'Total number of recruiters'],
                ]],
            ]]],
            ['logo_strip', ['heading' => 'Hear From Recruiters Around the World', 'category' => 'industry']],
            ['contact_band', ['heading' => 'Connect with our Career Development Team', 'body' => null, 'cta_label' => 'Contact us', 'cta_url' => '/contact', 'contacts_label' => 'For More Information', 'contacts' => [
                ['name' => 'Dr. Ravi Shankar', 'role' => 'Head Placements', 'email' => 'dvravi.shankar@namtech.ac'],
                ['name' => 'Vibha Desai', 'role' => 'Associate Director', 'email' => 'vibha.desai@namtech.ac'],
            ]]],
        ]);

        $this->page('Life @ NAMTECH', 'life-at-namtech', null, [
            $hero('A day in the life of a NAMTECH Student', $img('p142', 'Students sitting together on campus steps'), 'Life @ NAMTECH'),
            ['tabs', ['eyebrow' => null, 'heading' => 'What You’ll Find On NAMTECH Campus', 'layout' => 'standard', 'tabs' => [
                ['label' => 'Campus', 'title' => 'Campus', 'body' => $tbc, 'image' => $img('p059', 'Students relaxing on a campus lawn')],
                ['label' => 'Housing', 'title' => 'Housing', 'body' => $tbc, 'image' => $img('p088', 'Students on a campus walkway')],
                ['label' => 'Health and Services', 'title' => 'Health and Services', 'body' => $tbc, 'image' => $img('p117', 'Two women talking at a table')],
                ['label' => 'Transport and Accessibility', 'title' => 'Transport and Accessibility', 'body' => $tbc, 'image' => $this->media['inclusive-cohort']],
                ['label' => 'Library', 'title' => 'Library', 'body' => $tbc, 'image' => $img('p045', 'Curved library bookshelves')],
            ]]],
            ['image_mosaic', ['heading' => 'Life @ NAMTECH', 'body' => null, 'images' => [['image' => $img('p142', 'Students sitting together on campus steps')], ['image' => $img('p088', 'Students on a campus walkway')], ['image' => $img('p067', 'Students applauding in a classroom')], ['image' => $img('p144', 'Students working together at a laptop')]]]],
            ['faq', ['heading' => 'Frequently Asked Questions', 'layout' => 'centered', 'items' => array_map(fn (string $q) => ['question' => $q, 'answer' => $tbc], [
                'What clubs, events, and extracurricular activities can students participate in?', 'What makes the campus experience at NAMTECH unique?',
                'How does NAMTECH support student wellbeing and personal growth?', 'What support systems are available for students?', 'How does NAMTECH foster a vibrant campus community?',
            ])]],
            ['cta_band', ['heading' => 'Experience NAMTECH Campus', 'body' => null, 'cta_label' => 'Take a Virtual 360° Tour', 'cta_url' => '/campus/virtual-tour']],
        ]);
        $pages['campus'] = $this->page('Campus', 'campus', null, [
            $hero('A World-Class Industry-Integrated Campus', $this->media['campus-building'], 'Campus'),
            ['image_text', ['eyebrow' => null, 'heading' => 'Advanced Manufacturing Labs & Facilities', 'body' => '<p>Experience learning with real industry equipment and state-of-the-art infrastructure designed for the future of manufacturing.</p>', 'image' => $this->media['labs-research'], 'image_position' => 'left', 'layout' => 'panel']],
            ['gallery', ['heading' => 'Campus gallery', 'gallery' => 'campus', 'show_tour' => true]],
        ]);
        $this->page('Virtual Tour', 'virtual-tour', $pages['campus'], [
            $hero('Virtual Tour', $img('p125', '360-degree panorama of the campus courtyard'), 'Campus'),
            ['gallery', ['heading' => 'Explore the campus', 'gallery' => 'campus', 'show_tour' => true]],
        ]);

        $this->page('NAMTECH in the Media', 'media', null, [$hero('NAMTECH in the Media', $img('p060', 'Two colleagues walking and talking outdoors'), 'Media')]);
        $this->page('News', 'news', null, [$hero('News & Insights', $this->media['news-automotive'], 'Media')]);
        $this->page('Voices of NAMTECH', 'blogs', null, [$hero('Voices of NAMTECH', $img('p107', 'Group of colleagues gathered around a laptop'), 'Blogs')]);
        $this->page('Events', 'events', null, [$hero('Events', $img('p088', 'Students on a campus walkway'), 'Events', 'Conferences, lectures, masterclasses and outreach programmes at NAMTECH.')]);
        $this->page('Careers', 'careers', null, [
            $hero('Join Us to Shape the Future of MET Education', $img('p034', 'Smiling colleagues around a table'), 'Careers', null, ['cta_label' => 'View Open Positions', 'cta_url' => '#open-positions']),
            ['card_grid', ['eyebrow' => null, 'heading' => 'Why Build Your Career @ NAMTECH', 'style' => 'joined', 'columns' => '3', 'tone' => 'white', 'cards' => [
                ['icon' => 'factory', 'title' => 'Learning by building', 'body' => 'Learning happens by building. At NAMTECH, classrooms don’t have desks but machines used in the industry.'],
                ['icon' => 'globe', 'title' => 'Global collaboration', 'body' => 'Our curriculum is co-designed and co-delivered with academicians from global universities.'],
                ['icon' => 'people', 'title' => self::Tbc],
            ]]],
            ['job_listings', ['heading' => 'See Open Positions']],
            ['image_list', ['heading' => 'Life @ NAMTECH', 'image' => $img('p145', 'Colleagues talking in a meeting'), 'items' => [['title' => self::Tbc, 'text' => null]]]],
        ]);

        $pages['contact'] = $this->page('Contact', 'contact', null, [
            $hero('Contact NAMTECH', $this->media['campus-building'], 'Contact'),
            ['enquiry_form', ['heading' => 'Send us an enquiry', 'body' => 'Fill in the form and the right team will respond by email.', 'form_type' => 'contact', 'show_contact_details' => true]],
        ]);

        $pages['privacy'] = $this->page('Privacy Policy', 'privacy-policy', null, [['rich_text', ['heading' => null, 'body' => '<p>'.self::Tbc.' The approved privacy policy must be added before launch.</p>']]], template: 'legal', publish: false);
        $pages['terms'] = $this->page('Terms of Use', 'terms-of-use', null, [['rich_text', ['heading' => null, 'body' => $tbc]]], template: 'legal', publish: false);
        $pages['sitemap'] = $this->page('Sitemap', 'sitemap', null, [['rich_text', ['heading' => 'Sitemap', 'body' => '<p>Browse the main sections of the website using the links below.</p><ul><li><a href="/about">About NAMTECH</a></li><li><a href="/academics">Academics</a></li><li><a href="/programs">Programs</a></li><li><a href="/schools">Schools &amp; Centres</a></li><li><a href="/admissions">Admissions</a></li><li><a href="/faculty">Faculty</a></li><li><a href="/partnerships">Partnerships</a></li><li><a href="/research-and-consultancy">Research &amp; Consultancy</a></li><li><a href="/school-of-technical-education">School of Technical Education</a></li><li><a href="/impact">Impact</a></li><li><a href="/placements">Placements</a></li><li><a href="/life-at-namtech">Life @ NAMTECH</a></li><li><a href="/campus">Campus</a></li><li><a href="/media">Media</a></li><li><a href="/events">Events</a></li><li><a href="/blogs">Blogs</a></li><li><a href="/careers">Careers</a></li><li><a href="/contact">Contact</a></li></ul>']]], template: 'legal');

        return $pages;
    }

    private function seedCampus(): void
    {
        $gallery = $this->live(Gallery::create([
            'title' => 'Campus life and facilities',
            'slug' => 'campus',
            'cover_id' => $this->media['campus-building'],
        ]));
        foreach (['campus-building', 'campus-lawn', 'campus-interior', 'campus-seating', 'labs-research', 'labs-soldering'] as $i => $key) {
            $gallery->items()->create(['media_id' => $this->media[$key], 'type' => 'image', 'sort_order' => $i]);
        }
        $gallery->syncMediaUsage();
    }

    private function seedMission(): void
    {
        $initiatives = [
            ['The Core Infrastructure', 'NAMTECH’s synchronized Hub-and-Spoke model is engineered to bring world-class technical education directly to the field. The model has been piloted in Mehsana, Gujarat, through 5 Hubs & 10 Spoke ITIs.', 'building'],
            ['The Mobile Campus', 'One of our most innovative solutions, NAMTECH’s mobile campus delivers advanced training and labs directly to students and technicians in the field. Equipped with computing systems, simulation software and training modules, it enables specialized, cutting-edge upskilling at various hub & spoke ITIs.', 'pin'],
            ['The Impact', 'NAMTECH’s Sharing Technology initiative has proven its merit in the form of upgraded labs, empowered trainers, and stronger success outcomes for learners. In just two years, we have empowered X++ learners, each of whom has evolved from a technician to a technologist.', 'trend'],
            ['The Scale-Up', 'Having demonstrated the success of its model, NAMTECH is now tasked with empowerment of ITIs in Surat, Gujarat, and Visakhapatnam, Andhra Pradesh, with anchor partner AM/NS India, under the PM-SETU scheme.', 'globe'],
        ];
        foreach ($initiatives as $i => [$title, $body, $icon]) {
            Initiative::create(['title' => $title, 'body' => $body, 'icon' => $icon, 'is_active' => true, 'sort_order' => $i]);
        }
    }

    private function homePage(): Page
    {
        return $this->page('Home', 'home', null, [
            ['home_hero', [
                'badges' => [['text' => 'India’s First Manufacturing Engineering Technology (MET) Institute']],
                'slides' => [[
                    'title' => 'Powering India’s Next Manufacturing Revolution',
                    'body' => 'NAMTECH is preparing the workforce that will drive India’s next phase of manufacturing growth and global competitiveness.',
                    'image' => $this->media['hero-electronics-inspection'],
                    'cta_label' => null, 'cta_url' => null,
                ]],
            ]],
            ['feature_blocks', [
                'eyebrow' => 'The NAMTECH Learning Model',
                'heading' => 'A New Blueprint for Manufacturing Education',
                'items' => [
                    ['title' => 'Specialized Labs', 'body' => 'Our labs are our classrooms. Students work with specialized, industry-grade equipment, mirroring the environments in which they will work and lead. Studying at NAMTECH is an experience rooted in practice. Students develop their capabilities by applying theoretical concepts and not just knowing them.', 'image' => $this->media['specialized-labs-vehicle'], 'link_url' => '/academics/labs-and-infrastructure'],
                    ['title' => 'Industry-Driven Placements', 'body' => 'Our placements are aligned with the future of industry, not just the jobs of today. With NAMTECH’s strong industry partnerships, students gain access to elusive career opportunities in advanced manufacturing and technology at leading organizations.', 'image' => $this->media['industry-placements-team'], 'link_url' => '/placements'],
                    ['title' => 'Integrated Industry Partnerships', 'body' => 'Industry partnerships are the cornerstone of our education model, which goes far beyond textbook scenarios. At NAMTECH, students work on active industrial projects and gain expertise in real manufacturing processes and challenges.', 'image' => $this->pdfImage('p053', 'Students working on industrial equipment with an engineer'), 'link_url' => '/partnerships/corporate'],
                    ['title' => 'Experiential Learning', 'body' => 'Learning at NAMTECH is deeply experiential. Through a problem-led approach, students apply concepts in real-world contexts to solve complex problems in modern manufacturing and engineering. Students learn by doing, building, and making.', 'image' => $this->media['experiential-learning-robotics'], 'link_url' => '/academics/pedagogy'],
                    ['title' => 'Global Academic Collaboration', 'body' => 'At NAMTECH, students are taught by the minds shaping modern manufacturing education. Co-created with global academic institutions and leading industry practitioners, the curriculum is built around real industry scenarios, ensuring graduates are prepared to lead from day one.', 'image' => $this->media['global-collaboration-student'], 'link_url' => '/partnerships/academic'],
                    ['title' => 'Inclusivity', 'body' => 'At NAMTECH, world-class manufacturing education is designed to reach talent across every level. Through specialized learning pathways and dedicated programs for undergraduate & postgraduate engineering students, ITI & Diploma holders, faculty, and executives, we cultivate talent that can be deployed across levels of the organization.', 'image' => $this->media['inclusive-cohort'], 'link_url' => '/school-of-technical-education'],
                ],
            ]],
            ['academics_tabs', [
                'eyebrow' => 'Schools, Centres & Programs',
                'heading' => 'MET Innovation Schools: Engineering Education for Industry 5.0',
                'schools_label' => 'MET Innovation Schools', 'centres_label' => 'MET Innovation Centers', 'programs_label' => 'MET Programs',
                'schools_heading' => 'Manufacturing Engineering Technology (MET) Innovation Schools: Engineering Education for Industry 5.0',
                'schools_body' => 'MET Innovation Schools at NAMTECH prepare graduates to become next-generation industrial and technology leaders. Through an industry-integrated approach, experiential learning, highly practical and specialized education, and applied innovation, each school builds deep expertise in critical domains shaping the future of manufacturing.',
                'centres_heading' => 'MET Innovation Centers - Shaping Innovation for Industry 5.0',
                'centres_body' => 'MET Innovation Centers at NAMTECH are built around a simple principle: innovation is best learned by doing. Students work alongside faculty and industry to explore emerging technologies, tackle real-world challenges, and develop practical solutions through applied research, experimentation, and domain-specific programs co-created and co-delivered with industry.',
                'programs_heading' => 'MET Programs',
                'programs_body' => "At NAMTECH, we understand that running the industries of the future will require deep technical and engineering expertise alongside strong leadership capabilities.\n\nThe programs are built in collaboration with industry experts and globally leading institutions, as well as are deeply experiential. This immersive approach helps NAMTECH graduates develop the hands-on expertise and decisive strategic thinking needed to lead tomorrow’s industries and contribute to India’s industrial transformation.",
                'default_tab' => 'schools', 'featured_only' => false,
            ]],
            ['partners_showcase', [
                'eyebrow' => 'Partnerships',
                'heading' => 'Learning with Leading Global Universities & Industries',
                'body' => 'Through strategic partnerships with global industry leaders and premier academic institutions, NAMTECH delivers education shaped by real-world insights and advanced technologies. We integrate advanced technologies, experiential learning on industry-grade equipment, and actual industry practices to offer an unparalleled learning experience.',
                'cta2_label' => 'Corporate Partnership', 'cta2_url' => '/partnerships/corporate',
                'cta3_label' => 'Academic Partnership', 'cta3_url' => '/partnerships/academic',
                'show_stories' => true, 'stories_layout' => 'text',
            ]],
            ['faculty_showcase', [
                'eyebrow' => 'Faculty',
                'heading' => "Learn from\nGlobal Experts",
                'body' => 'NAMTECH’s world-class faculty includes leading academicians, industry leaders, and technology experts who bring decades of rich experience and real-world insights into the classroom to shape technically rigorous, industry-ready professionals.',
                'cta_label' => 'Explore Faculty', 'cta_url' => '/faculty',
            ]],
            ['labs_showcase', [
                'eyebrow' => 'Labs & Infrastructure',
                'heading' => "Next-Gen Labs\n& Facilities",
                'body' => 'With state-of-the-art infrastructure and experiential labs, students gain hands-on experience on real-industry equipment designed for the future of manufacturing.',
                'cta_label' => 'Explore More', 'cta_url' => '/academics/labs-and-infrastructure',
                'slides' => [
                    ['image' => $this->media['labs-soldering'], 'caption_start' => 'Learning with real machines', 'caption_end' => 'Into the real world', 'video_url' => null],
                    ['image' => $this->media['labs-research'], 'caption_start' => 'Learning with real machines', 'caption_end' => 'Into the real world', 'video_url' => null],
                    ['image' => $this->media['precision-machining'], 'caption_start' => 'Learning with real machines', 'caption_end' => 'Into the real world', 'video_url' => null],
                ],
            ]],
            ['campus_showcase', [
                'eyebrow' => 'Campus',
                'heading' => 'A World-Class Industry-Integrated Campus',
                'body' => 'Built to replicate the smart manufacturing ecosystems shaping today’s industrial landscape, the NAMTECH campus houses advanced labs, industry-grade equipment, and AI-enabled phygital learning experiences that simulate real industrial environments. The NAMTECH campus embodies MET philosophy — enabling convergence of technology, academic experience, and industry to make learning deeply applied and relevant.',
                'gallery' => 'campus',
                'cta_label' => 'Explore Campus', 'cta_url' => '/campus',
                'tour_label' => 'Virtual tour',
            ]],
            ['mission', [
                'heading' => 'The 3 Million Mission',
                'body' => 'Through its Sharing Technology initiative, NAMTECH is on a mission to empower 3 million youth in India with future-ready Industry 4.0 and Industry 5.0 skills through smart labs, industry mentorship, mobile campuses, and advanced technology training.',
                'limit' => 4, 'statistics_group' => null, 'cta_label' => 'Join Our Mission', 'cta_url' => '/school-of-technical-education/outreach',
            ]],
            ['news_carousel', [
                'heading' => 'News & Insights', 'post_type' => 'news', 'limit' => 8,
                'cta_label' => 'View all insights', 'cta_url' => '/media',
            ]],
        ], template: 'home', meta: 'NAMTECH prepares the next generation of innovators who are shaping the future of manufacturing in India.');
    }

    private function seedMenus(array $pages, array $schools, array $centres): void
    {
        $menu = fn (string $location, string $name, array $items) => $this->menu($location, $name, $items);
        $schoolPath = fn (School $s) => $s->slug === 'school-of-technical-education' ? '/school-of-technical-education' : $s->publicPath();

        $menu('utility', 'Header – utility', [
            ['About us', '/about', [
                ['About Us', '/about'], ['Board of Directors', '/about/board-of-directors'], ['Advisory Council', '/about/advisory-council'],
                ['Management Team', '/about/management-team'], ['Mandatory Disclosures', '/about/mandatory-disclosures'], ['Ranks & Accreditations', '/about/ranks-and-accreditations'],
            ]],
            ['Partnerships', '/partnerships', [['Corporate Partnerships', '/partnerships/corporate'], ['Academic Partnerships', '/partnerships/academic']]],
            ['Research & Consultancy', '/research-and-consultancy', [['Research & Publications @ NAMTECH', '/research-and-consultancy/research'], ['Consultancy', '/research-and-consultancy/consultancy'], ['Publications', '/research-and-consultancy/publications']]],
            ['School of technical education', '/school-of-technical-education', [
                ['STE', '/school-of-technical-education'],
                ['iPTP', '/school-of-technical-education/iptp'],
                ['Outreach', '/school-of-technical-education/outreach'],
            ]],
            ['Impact', '/impact', [['Impact & Initiatives', '/impact'], ['Impact Stories', '/impact/stories']]],
            ['Media', '/media', [['News, Press & Announcements', '/media'], ['Events', '/events'], ['Blogs', '/blogs']]],
        ]);

        $programLinks = fn (string $level) => Program::query()->where('level', $level)->orderBy('sort_order')->get()
            ->map(fn (Program $p) => [$p->title, $p->publicPath()])->all();

        $menu('primary', 'Header – main', [
            ['Academics', '/academics', [
                ['Schools and Centers', '/schools', collect($schools)->map(fn (School $s) => [$s->name, $schoolPath($s)])
                    ->concat(collect($centres)->map(fn (Centre $c) => [$c->menu_label ?? $c->name, $c->publicPath()]))->values()->all()],
                ['Pedagogy', '/academics/pedagogy'],
                ['Admission', '/admissions'],
            ]],
            ['Programs', '/programs', [
                ['UG Programs', '/programs?level=undergraduate', $programLinks('undergraduate')],
                ['PG Programs', '/programs?level=postgraduate', $programLinks('postgraduate')],
                ['Executive Programs', '/programs?level=executive', $programLinks('executive')],
            ]],
            ['Admissions', '/admissions'],
            ['Faculty', '/faculty'],
            ['Placements', '/placements'],
            ['Life @ NAMTECH', '/life-at-namtech'],
            ['Blogs', '/blogs'],
        ]);

        $menu('footer_programs', 'Footer – programs', Program::query()->orderBy('sort_order')->get()->map(fn (Program $p) => [$p->title, $p->publicPath()])->all());
        $menu('footer_schools', 'Footer – schools and centres', collect($schools)->map(fn (School $s) => [$s->name, $schoolPath($s)])
            ->concat(collect($centres)->map(fn (Centre $c) => [$c->name, $c->publicPath()]))->values()->all());
        $menu('footer_partnerships', 'Footer – partnerships', [['Academic Partnerships', '/partnerships/academic'], ['Corporate Partnerships', '/partnerships/corporate']]);
        $menu('footer_quick_links', 'Footer – quick links', [
            ['Media Center', '/media'], ['Student Portal', null], ['Staff Portal', null], ['Career Development Services', '/placements'], ['Careers', '/careers'], ['Events', '/events'], ['Sitemap', '/sitemap'],
        ]);
        $menu('legal', 'Footer – legal', [['Privacy Policy', $pages['privacy']], ['Terms of Use', $pages['terms']]]);
    }

    private function menu(string $location, string $name, array $items): void
    {
        $menu = Menu::create(['name' => $name, 'location' => $location]);
        $this->menuItems($menu, $items, null);
    }

    private function menuItems(Menu $menu, array $items, ?int $parentId): void
    {
        foreach ($items as $i => $item) {
            $model = $this->menuItem($menu, $item, $i, $parentId);
            $this->menuItems($menu, $item[2] ?? [], $model->id);
        }
    }

    private function menuItem(Menu $menu, array $item, int $order, ?int $parentId): Model
    {
        [$label, $target] = $item;

        return $menu->items()->create([
            'label' => $label,
            'parent_id' => $parentId,
            'page_id' => $target instanceof Page ? $target->id : null,
            'url' => is_string($target) ? $target : null,
            'is_visible' => $target !== null,
            'sort_order' => $order,
        ]);
    }

    private function page(string $title, string $slug, ?Page $parent, array $sections, string $template = 'default', ?string $meta = null, bool $publish = true): Page
    {
        $page = Page::create([
            'title' => $title,
            'slug' => $slug,
            'parent_id' => $parent?->id,
            'template' => $template,
            'meta_description' => $meta,
        ]);

        foreach ($sections as $i => $section) {
            $page->sections()->create([
                'type' => $section[0],
                'data' => SectionRegistry::normalize($section[0], $section[1]),
                'anchor' => $section[2] ?? null,
                'sort_order' => $i,
                'is_visible' => $section[3] ?? true,
            ]);
        }
        $page->syncMediaUsage();

        return $publish ? $this->live($page) : $page;
    }

    private function live(Model $model, $publishedAt = null): Model
    {
        $model->forceFill(['status' => ContentStatus::Published, 'published_at' => $publishedAt ?? now()->subMinute()])->save();
        $model->revisions()->create(['kind' => 'snapshot', 'note' => 'Initial content', 'data' => $model->fresh()->toSnapshot()]);

        return $model;
    }
}
