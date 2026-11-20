import { PostCard } from '../../../components/site/Cards';
import { blockSwitch, Breadcrumbs, RichText } from '../../../components/site/Common';
import Layout from '../../../components/site/Layout';
import SectionRenderer from '../../../components/site/sections/SectionRenderer';
import { Media } from '../../../components/ResponsiveImage';
import SmartLink from '../../../components/SmartLink';
import Carousel from '../../../components/Carousel';
import ImpactStoryShow from './ImpactStoryShow';

export default function PostShow({ post, sections, latest, related, breadcrumbs, listing, hidden, caseStudy, sampleStoryBody, sampleStoryImages }) {
    if (post.kind === 'story') {
        return (
            <ImpactStoryShow
                post={post}
                sections={sections}
                related={related}
                hidden={hidden}
                caseStudy={caseStudy}
                sampleBody={sampleStoryBody}
                sampleImages={sampleStoryImages}
            />
        );
    }

    const show = blockSwitch(hidden);
    const toc = show('contents') ? (post.toc ?? []) : [];
    const latestPosts = show('latest') ? latest : [];
    const sidebar = toc.length > 1 || latestPosts?.length;

    return (
        <>
            <section className="nt-page-hero on-dark">
                <div className="nt-page-hero__grid">
                    <div className="nt-page-hero__content">
                        <p className="nt-pill align-self-start nt-hero-in">{post.category || post.type}</p>
                        <h1 className="nt-hero-in" style={{ '--d': 1 }}>{post.title}</h1>
                        {post.excerpt ? <p className="nt-page-hero__body nt-hero-in" style={{ '--d': 2 }}>{post.excerpt}</p> : null}
                        <p className="small mt-3 mb-0 nt-hero-in" style={{ '--d': 3 }}>
                            {post.author_name ? `${post.author_name} · ` : ''}
                            {post.date ? <time dateTime={post.datetime}>{post.date}</time> : null}
                            {post.date ? ' · ' : ''}
                            {post.reading_minutes} min read
                        </p>
                    </div>
                    {post.image && show('hero_image') ? (
                        <div className="nt-page-hero__media">
                            <Media image={post.image} sizes="(min-width: 992px) 50vw, 100vw" priority />
                        </div>
                    ) : null}
                </div>
            </section>
            <Breadcrumbs items={breadcrumbs} />
            <article className="nt-section">
                <div className="container">
                    <div className={sidebar ? 'row g-5' : ''}>
                        {sidebar ? (
                            <aside className="col-lg-3 order-lg-1" aria-label="Article navigation">
                                <div className="nt-toc">
                                    {toc.length > 1 ? (
                                        <nav aria-label="Sections">
                                            <p className="nt-toc__title">Sections</p>
                                            <ol>
                                                {toc.map((t) => (
                                                    <li key={t.id}><a href={`#${t.id}`}>{t.text}</a></li>
                                                ))}
                                            </ol>
                                        </nav>
                                    ) : null}
                                    {latestPosts?.length ? (
                                        <div className="nt-latest d-none d-lg-block">
                                            <p className="nt-toc__title">Latest Blogs</p>
                                            <ul className="list-unstyled m-0">
                                                {latestPosts.map((item) => (
                                                    <li key={item.id}>
                                                        <SmartLink href={item.url}>
                                                            <Media image={item.image} sizes="72px" alt="" />
                                                            <span>{item.title}</span>
                                                        </SmartLink>
                                                    </li>
                                                ))}
                                            </ul>
                                        </div>
                                    ) : null}
                                </div>
                            </aside>
                        ) : null}
                        <div className={sidebar ? 'col-lg-9 order-lg-2' : 'mx-auto'} style={sidebar ? undefined : { maxWidth: '48rem' }}>
                            <RichText html={post.body} className="nt-article" />
                            {post.tags?.length && show('tags') ? (
                                <ul className="list-unstyled d-flex flex-wrap gap-2 mt-5" aria-label="Tags">
                                    {post.tags.map((tag) => (
                                        <li key={tag} className="nt-tag">{tag}</li>
                                    ))}
                                </ul>
                            ) : null}
                        </div>
                    </div>
                </div>
            </article>
            <SectionRenderer sections={sections} page={{ title: post.title }} />
            {related.length && show('related') ? (
                <section className="nt-section nt-divider">
                    <div className="container">
                        <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                            <h2 className="nt-h2 m-0">{post.kind === 'blog' ? 'Related Blogs' : 'Featured News'}</h2>
                            {listing ? <SmartLink href={listing.url} className="nt-btn nt-btn--outline">View all</SmartLink> : null}
                        </div>
                        <Carousel label="Related" slideWidth="min(21rem, 82%)" gap="1.5rem">
                            {related.map((item) => (
                                <PostCard key={item.id} post={item} />
                            ))}
                        </Carousel>
                    </div>
                </section>
            ) : null}
        </>
    );
}

PostShow.layout = (page) => <Layout>{page}</Layout>;
