import { PostCard } from '../../../components/site/Cards';
import { Pagination } from '../../../components/site/Common';
import { CheckboxGroup, FilterTabs, SidebarToggle, visit } from '../../../components/site/Filters';
import Layout from '../../../components/site/Layout';
import ListingShell from '../../../components/site/ListingShell';
import { Media as Img } from '../../../components/ResponsiveImage';
import SmartLink from '../../../components/SmartLink';
import { ArrowDot } from '../../../components/Icon';

function RowCard({ post }) {
    return (
        <article className={`nt-row-card ${post.external ? 'nt-row-card--press' : ''}`}>
            <Img image={post.image} sizes="(min-width: 992px) 18rem, 40vw" alt="" className="nt-row-card__media" />
            <div className="nt-row-card__body">
                <p className="nt-row-card__meta">
                    {post.category ? <span className="nt-tag">{post.category}</span> : null}
                    {post.date ? <time dateTime={post.datetime}>{post.date}</time> : null}
                </p>
                <h2><SmartLink href={post.url} newTab={post.external} className="stretched-link">{post.title}</SmartLink></h2>
                {post.excerpt ? <p className="nt-row-card__excerpt">{post.excerpt}</p> : null}
            </div>
        </article>
    );
}

export default function MediaIndex({ title, sections, breadcrumbs, tabs, tab, featured, posts, categories, years, filters }) {
    const apply = (params) => visit('/media', { tab, ...filters, ...params, page: undefined });
    const hasFilters = (tab === 'news' && Object.keys(categories).length) || years.length;

    return (
        <ListingShell title={title} sections={sections} breadcrumbs={breadcrumbs}>
            <FilterTabs label="Media type" items={Object.entries(tabs)} active={tab} href={(key) => `/media?tab=${key}`} />
            <div className="row g-5 mt-1">
                {hasFilters ? (
                    <aside className="col-lg-3" aria-label="Filters">
                        <SidebarToggle>
                            {tab === 'news' ? <CheckboxGroup legend="Category" name="cat" options={Object.entries(categories)} selected={filters.categories} onChange={(v) => apply({ categories: v })} /> : null}
                            <CheckboxGroup legend="Year" name="year" options={years.map((y) => [y, y])} selected={filters.years.map(String)} onChange={(v) => apply({ years: v })} />
                        </SidebarToggle>
                    </aside>
                ) : null}
                <div className={hasFilters ? 'col-lg-9' : 'col-12'}>
                    {featured ? (
                        <article className="nt-featured-post">
                            <Img image={featured.image} sizes="(min-width: 992px) 50vw, 100vw" alt="" className="nt-featured-post__media" />
                            <div className="nt-featured-post__body">
                                <p className="nt-row-card__meta">
                                    <span className="nt-tag">Featured</span>
                                    {featured.date ? <time dateTime={featured.datetime}>{featured.date}</time> : null}
                                </p>
                                <h2><SmartLink href={featured.url} className="stretched-link">{featured.title}</SmartLink></h2>
                                {featured.excerpt ? <p>{featured.excerpt}</p> : null}
                                <span className="nt-link-arrow" aria-hidden="true">Read <ArrowDot /></span>
                            </div>
                        </article>
                    ) : null}
                    <p className="visually-hidden" role="status">{posts.total} items</p>
                    {posts.data.length ? (
                        tab === 'news' ? (
                            <div className="nt-card-grid nt-card-grid--3">
                                {posts.data.map((post) => <PostCard key={post.id} post={post} headingLevel={2} />)}
                            </div>
                        ) : (
                            <div className="nt-row-cards">
                                {posts.data.map((post) => <RowCard key={post.id} post={post} />)}
                            </div>
                        )
                    ) : !featured ? (
                        <p className="nt-empty">Nothing has been published here yet.</p>
                    ) : null}
                    <Pagination meta={posts} />
                </div>
            </div>
        </ListingShell>
    );
}

MediaIndex.layout = (page) => <Layout>{page}</Layout>;
