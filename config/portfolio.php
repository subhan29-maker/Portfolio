<?php

/**
 * Portfolio content.
 *
 * Everything visitors read about Subhan lives here so the site can be updated
 * without touching Blade templates. Values that are personal and not yet known
 * (email, phone, location, social profiles) are read from the environment and
 * simply hidden from the interface while they remain empty.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */
    'profile' => [
        'name' => 'Subhan',
        'role' => 'Full-Stack Developer',

        // Hero: "Hello, I'm" label, the display name and the role headline.
        'hero_label' => "Hello, I'm",
        'hero_headline' => 'Full-Stack Developer',
        'hero_lead' => 'I build and maintain complete web applications: clean, responsive interfaces on the front '
            .'end, and Laravel-powered logic, validation and database features behind them.',

        'availability' => env('PORTFOLIO_AVAILABILITY', 'Available for new projects'),

        'email' => env('PORTFOLIO_EMAIL'),
        'phone' => env('PORTFOLIO_PHONE'),
        'location' => env('PORTFOLIO_LOCATION'),

        // Replace public/images/profile/profile-placeholder.svg with your own
        // photo, then point this at the new file (for example
        // "images/profile/subhan.jpg").
        //
        // This is the fallback. The dashboard (/admin/profile) can upload a
        // picture of its own, and that one is shown instead while it exists.
        'photo' => env('PORTFOLIO_PHOTO', 'images/profile/profile-placeholder.svg'),
        'photo_alt' => 'Portrait of Subhan, full-stack developer',

        // Shown on the About page. Keep these honest and editable.
        'experience' => [
            'learning' => 'Around two years of learning and building on the web',
            'practical' => 'Around six months of hands-on, practical development',
        ],

        'socials' => array_values(array_filter([
            ['label' => 'GitHub', 'icon' => 'github', 'url' => env('PORTFOLIO_GITHUB')],
            ['label' => 'LinkedIn', 'icon' => 'linkedin', 'url' => env('PORTFOLIO_LINKEDIN')],
            ['label' => 'X', 'icon' => 'x', 'url' => env('PORTFOLIO_X')],
        ], fn (array $social) => filled($social['url']))),
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    */
    'navigation' => [
        ['route' => 'home', 'label' => 'Home'],
        ['route' => 'about', 'label' => 'About'],
        ['route' => 'skills', 'label' => 'Skills'],
        ['route' => 'projects.index', 'label' => 'Projects'],
        ['route' => 'contact', 'label' => 'Contact'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Technologies (hero chips and the moving ticker)
    |--------------------------------------------------------------------------
    */
    'technologies' => ['HTML', 'CSS', 'JavaScript', 'PHP', 'Laravel', 'MySQL', 'Redis'],

    /*
    |--------------------------------------------------------------------------
    | Skills
    |--------------------------------------------------------------------------
    | Grouped honestly by area. No percentages, scores or progress bars.
    */
    'skills' => [
        [
            'key' => 'frontend',
            'title' => 'Frontend',
            'icon' => 'layout',
            'summary' => 'Semantic markup, modern CSS layouts and interactive interfaces.',
            'items' => [
                ['name' => 'HTML', 'description' => 'Semantic, accessible page structure and forms.'],
                ['name' => 'CSS', 'description' => 'Flexbox, Grid, design tokens, transitions and keyframe animation.'],
                ['name' => 'JavaScript', 'description' => 'Vanilla JS for interactive UI, DOM behaviour and fetch requests.'],
                ['name' => 'Responsive design', 'description' => 'Mobile-first layouts that hold up from small phones to wide desktops.'],
                ['name' => 'UI implementation', 'description' => 'Turning a design into a consistent, carefully spaced interface.'],
            ],
        ],
        [
            'key' => 'backend',
            'title' => 'Backend',
            'icon' => 'server',
            'summary' => 'Server-side application logic, validation and data handling.',
            'items' => [
                ['name' => 'PHP', 'description' => 'Server-side logic, form handling and clear separation of concerns.'],
                ['name' => 'Laravel', 'description' => 'Routing, controllers, Blade views, Eloquent models, validation and migrations.'],
                ['name' => 'Backend functionality', 'description' => 'Form processing, validation, authentication flows and CRUD features.'],
                ['name' => 'Database integration', 'description' => 'Connecting applications to relational data with Eloquent and the query builder.'],
            ],
        ],
        [
            'key' => 'data',
            'title' => 'Databases & supporting technology',
            'icon' => 'database',
            'summary' => 'Storing, querying and serving application data.',
            'items' => [
                ['name' => 'MySQL', 'description' => 'Relational schema design, relationships, queries and indexes.'],
                ['name' => 'Redis', 'description' => 'Caching and lightweight key-value storage for faster responses.'],
            ],
        ],
        [
            'key' => 'tools',
            'title' => 'Development tools',
            'icon' => 'terminal',
            'summary' => 'The environment I write and debug code in.',
            'items' => [
                ['name' => 'Visual Studio Code', 'description' => 'My day-to-day editor, set up for PHP, Laravel and front-end work.'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Seed projects
    |--------------------------------------------------------------------------
    | The starting content, kept here so a fresh install has something real to
    | show and so "php artisan db:seed" and the project tests have honest
    | sample data to work from. It is seed data only: the live site always
    | reads published rows from the projects table, edited through the admin
    | dashboard at /admin/projects. Running "portfolio:import-crm-data" (or
    | "ProjectsSeeder") copies anything still missing from here into the
    | database without ever overwriting what you have already edited.
    |
    | One ordered collection. Every entry uses the same shape, so the
    | screenshots for these live in public/images/projects/.
    |
    | "is_demo" => true marks the starting placeholders that ship with the
    | template. Replace or delete them once your own projects are ready: demo
    | entries are labelled in the interface so nothing is presented as
    | completed client work.
    */
    'projects' => [
        [
            'slug' => 'inventory-dashboard',
            'title' => 'Inventory Dashboard',
            'tagline' => 'Stock levels, suppliers and stock movements in one database-backed dashboard.',
            'summary' => 'A Laravel application for tracking products, suppliers and stock movements with server-rendered dashboards.',
            'overview' => 'A placeholder project entry that shows how a database-driven dashboard appears on this site. '
                .'Products, suppliers and stock movements are stored in a relational schema and summarised into a '
                .'server-rendered overview so the current state of the inventory is readable at a glance.',
            'problem' => 'Small teams often track stock in spreadsheets, where quantities drift out of date and it is hard to see what moved and when.',
            'purpose' => 'Give a single, reliable place to record stock movements and see the resulting quantities without manual recalculation.',
            'features' => [
                'Product and supplier records with validation on every input.',
                'Stock movements recorded as immutable entries rather than overwritten totals.',
                'Dashboard summaries for low-stock items and recent activity.',
                'Server-side search, filtering and pagination.',
                'Responsive tables that stay usable on smaller screens.',
            ],
            'technologies' => ['Laravel', 'PHP', 'MySQL', 'Blade', 'JavaScript', 'CSS'],
            'contribution' => 'Placeholder description of what you built: schema design, controllers, validation and the Blade interface.',
            'cover' => 'images/projects/project-one.svg',
            'gallery' => [
                ['src' => 'images/projects/project-one-detail-1.svg', 'alt' => 'Inventory Dashboard — stock overview placeholder screenshot'],
                ['src' => 'images/projects/project-one-detail-2.svg', 'alt' => 'Inventory Dashboard — product detail placeholder screenshot'],
            ],
            'demo_url' => null,
            'repo_url' => null,
            'year' => null,
            'role' => 'Design and development',
            'is_demo' => true,
        ],
        [
            'slug' => 'thinkcode-crm',
            'title' => 'ThinkCode CRM — Student, Intern & Team Management System',
            'tagline' => 'A Laravel management system for a coding institute: batches, students, interns, tasks, QR attendance and fees in one admin panel.',
            'summary' => 'A Laravel 12 CRM that runs a coding institute end to end: courses and batches, student and intern records, task workflows with submissions, QR-code attendance checked against GPS geofencing, fee and payment tracking, and a REST API for a Flutter mobile app.',
            'overview' => 'ThinkCode CRM is a role-based management system built for ThinkCode IT Solutions, a coding institute. '
                .'Super admins and admins manage courses, batches, teachers, students and interns; teachers take attendance and '
                .'review task submissions; students and interns get their own dashboards. Work that used to live in spreadsheets '
                .'and chat threads — attendance, assignments, leave requests, fees, circulars and contact leads — is handled in a '
                .'single Laravel application, and the same data is exposed through a versioned REST API for a Flutter mobile app.',
            'problem' => 'A growing institute was juggling batch schedules, attendance registers, task tracking and fee records across spreadsheets and messaging apps, so information drifted and nothing had a single source of truth.',
            'purpose' => 'Give admins, teachers, students and interns one system where attendance, tasks, leave, fees and notifications are recorded once and visible only to the right people.',
            'features' => [
                'Role-based dashboards for super admin, admin, teacher, student and intern accounts.',
                'Courses and batches with status toggling, plus teacher, student and intern records (transfers, internship extensions and leave status).',
                'Task management with bulk assignment, student/intern submissions and an admin or teacher review step.',
                'QR-code attendance validated against GPS geofencing, with per-batch reports.',
                'Leave requests with approve/reject flows, and circulars/notifications with acknowledgements.',
                'Fee structures, student fees, payments and printable receipts.',
                'Accounting with income/expense transactions, categories and reports, plus contact-lead capture and conversion.',
                'A Sanctum-protected REST API (v1) for the Flutter mobile app.',
            ],
            'highlights' => [
                'Laravel 12 on PHP 8.4, with separately authenticated dashboards for the super admin, admin, teacher, student and intern roles.',
                'Attendance uses generated QR codes, and every check-in and check-out is validated against GPS geofencing — the office coordinates in Bahawalpur with a 50 m radius.',
                'Task assignments and submissions are polymorphic, so students and interns share one workflow with a review step.',
                'A versioned REST API (v1) protected by Laravel Sanctum powers a Flutter mobile app for attendance, tasks, leaves, fees and notifications.',
                'Server-side validation and role middleware guard every route; Eloquent migrations and seeders create the full demo dataset.',
                'Blade views with Tailwind CSS and Alpine.js, SweetAlert2 confirmations, and a Vite/Tailwind build set-up.',
            ],
            'technologies' => ['Laravel', 'PHP', 'SQLite', 'Blade', 'Tailwind CSS', 'Alpine.js', 'Sanctum', 'REST API'],
            'contribution' => 'Editable placeholder — replace this with a short, honest description of your own contribution to ThinkCode CRM: which modules you built or maintained, the parts of the data model and API you designed, and what you tested. This text has not been filled in yet.',
            'cover' => 'images/projects/thinkcode-crm.png',
            'gallery' => [
                ['src' => 'images/projects/thinkcode-crm-students.png', 'alt' => 'ThinkCode CRM — student management screen'],
                ['src' => 'images/projects/thinkcode-crm-attendance.png', 'alt' => 'ThinkCode CRM — attendance management screen'],
                ['src' => 'images/projects/thinkcode-crm-tasks.png', 'alt' => 'ThinkCode CRM — task management screen'],
            ],
            'demo_url' => null,
            'repo_url' => null,
            'year' => null,
            'role' => 'Full-Stack Developer',
            'is_demo' => false,
        ],
        [
            'slug' => 'portfolio-crm',
            'title' => 'Portfolio CRM — Project Publishing & Content API',
            'tagline' => 'A secured Laravel admin application that owns the project content for this site, with a versioned public API the portfolio reads at runtime.',
            'summary' => 'A dedicated Laravel admin application for managing portfolio projects, with draft and publish states, image uploads and a read-only REST API that this website consumes.',
            'overview' => 'Portfolio CRM is the content back end for this portfolio. Instead of hand-editing a configuration '
                .'array and redeploying, projects are created, edited, published and unpublished from an admin dashboard. '
                .'Each project stores a title, slug, short and long descriptions, a technology list, a category, an optional '
                .'live demo and repository link, a featured flag, a cover image and an optional gallery.',
            'problem' => 'Project content lived in the codebase, so changing a screenshot or a description meant an edit, a commit and a deploy, and unfinished projects had no safe place to live.',
            'purpose' => 'Move project content into a small, secured application that owns publishing state, keeps drafts invisible to visitors and serves published projects over a documented API.',
            'features' => [
                'Admin sign-in with session authentication, and an admin middleware on every management route.',
                'Dashboard totals for all, published, draft and unpublished projects, plus recently added and recently updated lists.',
                'Full project CRUD with automatic slug generation and per-field server-side validation.',
                'Draft, published and unpublished states, with the first publish timestamp recorded once.',
                'Cover and gallery image uploads with type and size validation, safe replacement and deletion, and a confirmation before deleting a project.',
                'A read-only, versioned REST API that returns published projects only, with pagination and correct HTTP status codes.',
                'Response caching that is invalidated automatically whenever a project is created, updated or deleted.',
            ],
            'highlights' => [
                'Publishing state is modelled as a PHP enum, and model scopes keep published-only filtering in a single place.',
                'The API returns absolute cover and gallery URLs, so consumers never have to construct them.',
                'Cache keys are version-stamped and flushed on every write, so stale project data is never served.',
                'Unpublished and unknown slugs return a JSON 404 rather than leaking draft content.',
                'Uploaded media is validated, stored through Laravel\'s filesystem abstraction and deleted together with its project.',
            ],
            'technologies' => ['Laravel', 'PHP', 'Blade', 'Tailwind CSS', 'SQLite', 'REST API', 'PHPUnit'],
            'contribution' => 'Design and development: schema, models and enum, authentication and access control, the CRUD and publishing flows, media handling, the versioned API, the caching layer and the test suite.',
            'cover' => 'images/projects/portfolio-crm.png',
            'gallery' => [
                ['src' => 'images/projects/portfolio-crm-projects.png', 'alt' => 'Portfolio CRM — project list with status filters'],
                ['src' => 'images/projects/portfolio-crm-edit.png', 'alt' => 'Portfolio CRM — project edit form with cover and gallery uploads'],
                ['src' => 'images/projects/portfolio-crm-preview.png', 'alt' => 'Portfolio CRM — project preview page'],
            ],
            'demo_url' => null,
            'repo_url' => null,
            'year' => '2026',
            'role' => 'Design and development',
            'is_demo' => false,
        ],
        [
            'slug' => 'developer-portfolio',
            'title' => 'Developer Portfolio',
            'tagline' => 'This site: a server-rendered Laravel portfolio with a working contact form.',
            'summary' => 'A Laravel and Blade portfolio with reusable components, CSS animations and a validated contact form.',
            'overview' => 'A placeholder project entry for the site you are reading. Pages are rendered by Laravel '
                .'controllers and Blade components, styling is written as organised CSS with design tokens, and the '
                .'contact form validates input on the server and stores submissions in the database.',
            'problem' => 'A developer needs one credible place to describe their skills and show work, without a page builder or a heavy front-end framework.',
            'purpose' => 'Present skills and projects clearly, load fast, and stay easy to update as new work is finished.',
            'features' => [
                'Reusable Blade components for navigation, cards, buttons and section headings.',
                'CSS entrance animations plus a reduced-motion friendly scrolling ticker.',
                'Validated, rate-limited contact form with database storage.',
                'Responsive layouts tested from small phones to wide desktops.',
                'Accessible navigation, focus states and semantic heading structure.',
            ],
            'technologies' => ['Laravel', 'PHP', 'Blade', 'CSS', 'JavaScript', 'MySQL'],
            'contribution' => 'Placeholder description of what you built: page structure, styling system and the backend contact form.',
            'cover' => 'images/projects/project-three.svg',
            'gallery' => [
                ['src' => 'images/projects/project-three-detail-1.svg', 'alt' => 'Developer Portfolio — home page placeholder screenshot'],
            ],
            'demo_url' => null,
            'repo_url' => null,
            'year' => null,
            'role' => 'Design and development',
            'is_demo' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact form
    |--------------------------------------------------------------------------
    */
    'contact' => [
        // Where owner notifications are sent. Leave empty to keep submissions in
        // the database only. Nothing is ever reported as "emailed" unless this
        // is configured and the message is actually sent.
        'notify_email' => env('CONTACT_NOTIFICATION_EMAIL', env('PORTFOLIO_NOTIFY_EMAIL')),

        'currency' => env('PORTFOLIO_CURRENCY', '$'),

        'response_note' => 'I usually reply within one to two working days.',
    ],

];
