import { useEffect, useRef, useState } from 'react';
import Icon, { ArrowDot } from '../../../components/Icon';
import { Media } from '../../../components/ResponsiveImage';
import SmartLink from '../../../components/SmartLink';
import { Pagination } from '../../../components/site/Common';
import Layout from '../../../components/site/Layout';
import SectionRenderer from '../../../components/site/sections/SectionRenderer';

function StoryFilters({ categories, active }) {
    const links = useRef(null);
    const [canScroll, setCanScroll] = useState({ previous: false, next: false });

    const updateControls = () => {
        const rail = links.current;
        if (!rail) return;
        setCanScroll({
            previous: rail.scrollLeft > 1,
            next: rail.scrollLeft + rail.clientWidth < rail.scrollWidth - 1,
        });
    };

    useEffect(() => {
        const rail = links.current;
        const mobile = window.matchMedia('(max-width: 767.98px)');
        const alignActive = () => {
            const selected = rail.querySelector('[aria-current="page"]');
            if (selected) {
                const left = selected.offsetLeft;
                const right = left + selected.offsetWidth;
                if (mobile.matches || left < rail.scrollLeft || right > rail.scrollLeft + rail.clientWidth) {
                    rail.scrollLeft = (left + right - rail.clientWidth) / 2;
                }
            }
            updateControls();
        };

        alignActive();
        window.addEventListener('resize', alignActive);
        return () => window.removeEventListener('resize', alignActive);
    }, [active, categories]);

    const scroll = (direction) => {
        const rail = links.current;
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        rail?.scrollBy({ left: direction * rail.clientWidth * 0.65, behavior: reducedMotion ? 'auto' : 'smooth' });
    };

    const items = [...Object.entries(categories), ['', 'All']];
    return (
        <div className="nt-impact-story-filters">
            <nav ref={links} className="nt-impact-story-filters__links" aria-label="Story type" onScroll={updateControls}>
                {items.map(([slug, label]) => (
                    <SmartLink
                        key={slug}
                        href={slug ? `/impact/stories?category=${encodeURIComponent(slug)}` : '/impact/stories?view=all'}
                        aria-current={active === slug ? 'page' : undefined}
                        preserveScroll
                    >
                        {label}
                    </SmartLink>
                ))}
            </nav>
            <div className="nt-impact-story-filters__controls">
                <button type="button" aria-label="Show earlier story types" disabled={!canScroll.previous} onClick={() => scroll(-1)}>
                    <Icon name="chevronLeft" strokeWidth={1.6} />
                </button>
                <button type="button" aria-label="Show more story types" disabled={!canScroll.next} onClick={() => scroll(1)}>
                    <Icon name="chevronRight" strokeWidth={1.6} />
                </button>
            </div>
        </div>
    );
}

function StoryDate({ post }) {
    return post.datetime ? <time dateTime={post.datetime}>{post.date}</time> : post.date;
}

function StoryAction({ post, label }) {
    if (!post.url) return null;
    return (
        <SmartLink href={post.url} className="nt-link-arrow stretched-link" aria-label={`${label} ${post.title}`}>
            {label} <ArrowDot />
        </SmartLink>
    );
}

function FeaturedStory({ post }) {
    return (
        <article className={`nt-impact-story-feature${post.image ? ' nt-impact-story-feature--with-image' : ''}`}>
            {post.image ? (
                <Media image={post.image} sizes="(min-width: 1440px) 1280px, 100vw" priority className="nt-impact-story-feature__media" />
            ) : null}
            <div className="nt-impact-story-feature__panel on-dark">
                {post.date || post.reading_minutes ? (
                    <p className={`nt-impact-story-feature__meta${post.date ? '' : ' nt-impact-story-feature__meta--mobile'}`}>
                        {post.date ? (
                            <span>
                                <Icon name="calendar" strokeWidth={1.5} />
                                <StoryDate post={post} />
                            </span>
                        ) : null}
                        {post.reading_minutes ? (
                            <span className="nt-impact-story-feature__read-time">
                                <Icon name="clock" strokeWidth={1.5} />
                                {post.reading_minutes} min read
                            </span>
                        ) : null}
                    </p>
                ) : null}
                <h2>{post.title}</h2>
                {post.excerpt ? <p className="nt-impact-story-feature__excerpt">{post.excerpt}</p> : null}
                <StoryAction post={post} label="Read" />
            </div>
        </article>
    );
}

function StoryCard({ post }) {
    return (
        <article className="nt-impact-story-card">
            {post.image ? (
                <Media image={post.image} sizes="(min-width: 1440px) 400px, (min-width: 992px) 30vw, (min-width: 768px) 44vw, 90vw" alt="" className="nt-impact-story-card__media" />
            ) : null}
            {post.category || post.date ? (
                <div className="nt-impact-story-card__meta">
                    {post.category ? <span className="nt-impact-story-card__tag">{post.category}</span> : null}
                    {post.date ? <span className="nt-impact-story-card__date"><StoryDate post={post} /></span> : null}
                </div>
            ) : null}
            <h2>{post.title}</h2>
            {post.excerpt ? <p>{post.excerpt}</p> : null}
            <StoryAction post={post} label="View" />
        </article>
    );
}

const sampleStory = (index, category, image) => ({
    id: `sample-${index}`,
    title: index === 0 ? 'SAMPLE – Featured story' : `SAMPLE – Story title ${index}`,
    excerpt: 'SAMPLE – replace with real content.',
    category,
    date: 'SAMPLE – date',
    image,
});

export default function Stories({ title, sections = [], categories, featured, posts, filters, seo, sample_image: sampleImage }) {
    const [firstSection, ...otherSections] = sections;
    const intro = firstSection?.type === 'page_hero' ? firstSection.data : null;
    const heading = intro?.title || title;
    const description = intro?.body || seo?.description;
    const category = categories[filters.category] ?? null;
    const image = intro?.image ?? sampleImage;
    const showSamples = posts.current_page === 1 && !featured && posts.total === 0;
    const lead = showSamples ? sampleStory(0, category, image) : featured;
    const items = showSamples ? Array.from({ length: 6 }, (_, index) => sampleStory(index + 1, category, image)) : posts.data;

    return (
        <div className="nt-impact-stories">
            <header className="nt-impact-stories__wrap nt-impact-stories__intro">
                <h1>{heading}</h1>
                {description ? <p>{description}</p> : null}
            </header>
            <SectionRenderer sections={intro ? otherSections : sections} page={{ title: heading }} />
            <section className="nt-impact-stories__wrap nt-impact-stories__listing" aria-label="Browse impact stories">
                <StoryFilters categories={categories} active={filters.category ?? ''} />
                {lead ? <FeaturedStory post={lead} /> : null}
                {items.length ? (
                    <div className="nt-impact-stories__grid">
                        {items.map((post) => <StoryCard key={post.id} post={post} />)}
                    </div>
                ) : null}
                <Pagination meta={posts} />
            </section>
        </div>
    );
}

Stories.layout = (page) => <Layout>{page}</Layout>;
