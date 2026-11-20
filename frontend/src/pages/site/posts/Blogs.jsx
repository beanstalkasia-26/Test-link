import { useState } from 'react';
import Icon, { ArrowDot } from '../../../components/Icon';
import { Media } from '../../../components/ResponsiveImage';
import SmartLink from '../../../components/SmartLink';
import { Pagination } from '../../../components/site/Common';
import { visit } from '../../../components/site/Filters';
import Layout from '../../../components/site/Layout';
import ListingShell from '../../../components/site/ListingShell';

const tagOf = (post) => post.category || post.type || 'Blog';

function BlogCard({ post, headingLevel = 3 }) {
    const Heading = `h${headingLevel}`;
    return (
        <article className="nt-blog-card">
            <Media className="nt-blog-card__media" image={post.image} sizes="(min-width: 1200px) 400px, (min-width: 768px) 33vw, 90vw" alt="" />
            <div className="nt-blog-card__meta">
                <span className="nt-blog-card__tag">{tagOf(post)}</span>
                {post.date ? <time dateTime={post.datetime}>{post.date}</time> : null}
            </div>
            <Heading className="nt-blog-card__title">
                <SmartLink href={post.url} className="stretched-link">
                    {post.title}
                </SmartLink>
            </Heading>
            {post.excerpt ? <p className="nt-blog-card__excerpt">{post.excerpt}</p> : null}
            <span className="nt-link-arrow mt-auto" aria-hidden="true">
                View <ArrowDot />
            </span>
        </article>
    );
}

function FeaturedBlog({ post }) {
    return (
        <article className="nt-blog-feature">
            <Media className="nt-blog-feature__media" image={post.image} sizes="(min-width: 1200px) 1280px, 100vw" alt="" />
            <div className="nt-blog-feature__panel on-dark">
                <span className="nt-blog-feature__tag">{tagOf(post)}</span>
                <p className="nt-blog-feature__meta">
                    {post.date ? (
                        <span>
                            <Icon name="calendar" strokeWidth={1.6} />
                            <time dateTime={post.datetime}>{post.date}</time>
                        </span>
                    ) : null}
                    {post.reading_minutes ? (
                        <span>
                            <Icon name="clock" strokeWidth={1.6} />
                            {post.reading_minutes} min read
                        </span>
                    ) : null}
                </p>
                <h3>
                    <SmartLink href={post.url} className="stretched-link">
                        {post.title}
                    </SmartLink>
                </h3>
                {post.excerpt ? <p className="nt-blog-feature__excerpt">{post.excerpt}</p> : null}
                <span className="nt-link-arrow" aria-hidden="true">
                    Read <ArrowDot />
                </span>
            </div>
        </article>
    );
}

export default function Blogs({ title, sections, breadcrumbs, featured, posts, topics, filters }) {
    const [q, setQ] = useState(filters.q ?? '');
    const apply = (params) => visit('/blogs', { ...filters, q: q || undefined, ...params, page: undefined });
    const [lead, ...highlights] = featured ?? [];

    return (
        <ListingShell title={title} sections={sections} breadcrumbs={breadcrumbs} bare>
            {lead ? (
                <section className="nt-section nt-blogs-featured" aria-labelledby="featured-blogs">
                    <div className="container">
                        <h2 className="nt-h2" id="featured-blogs">Featured Highlights</h2>
                        <FeaturedBlog post={lead} />
                        {highlights.length ? (
                            <div className="nt-blog-grid nt-stagger">
                                {highlights.slice(0, 3).map((post) => <BlogCard key={post.id} post={post} />)}
                            </div>
                        ) : null}
                    </div>
                </section>
            ) : null}

            <section className="nt-section nt-tone-lavender nt-blogs-list" aria-label="All blogs">
                <div className="container">
                    <form className="nt-blog-filters" role="search" aria-label="Find blogs" onSubmit={(e) => {
                        e.preventDefault();
                        apply({});
                    }}>
                        <div className="nt-blog-filters__search">
                            <label className="visually-hidden" htmlFor="blog-q">Search blogs</label>
                            <input id="blog-q" type="search" placeholder="Search blog..." value={q} onChange={(e) => setQ(e.target.value)} />
                            <button type="submit" aria-label="Search">
                                <Icon name="search" strokeWidth={1.6} />
                            </button>
                        </div>
                        <div className="nt-blog-filters__select">
                            <label className="visually-hidden" htmlFor="blog-topic">Topic</label>
                            <select id="blog-topic" value={filters.topic ?? ''} onChange={(e) => apply({ topic: e.target.value || undefined })}>
                                <option value="">Topic</option>
                                {Object.entries(topics).map(([value, label]) => <option key={value} value={value}>{label}</option>)}
                            </select>
                        </div>
                        <div className="nt-blog-filters__select">
                            <label className="visually-hidden" htmlFor="blog-sort">Sort by</label>
                            <select id="blog-sort" value={filters.sort === 'oldest' ? 'oldest' : ''} onChange={(e) => apply({ sort: e.target.value || undefined })}>
                                <option value="">Sort by</option>
                                <option value="oldest">Oldest first</option>
                            </select>
                        </div>
                    </form>
                    <p className="visually-hidden" role="status">{posts.total} blogs found</p>
                    {posts.data.length ? (
                        <div className="nt-blog-grid nt-stagger">
                            {posts.data.map((post) => <BlogCard key={post.id} post={post} headingLevel={2} />)}
                        </div>
                    ) : (
                        <p className="nt-empty">No blogs match your search.</p>
                    )}
                    <Pagination meta={posts} />
                </div>
            </section>
        </ListingShell>
    );
}

Blogs.layout = (page) => <Layout>{page}</Layout>;
