import Carousel from '../../../components/Carousel';
import Icon, { ArrowDot } from '../../../components/Icon';
import { Media } from '../../../components/ResponsiveImage';
import SmartLink from '../../../components/SmartLink';
import { blockSwitch, RichText } from '../../../components/site/Common';
import SectionRenderer from '../../../components/site/sections/SectionRenderer';
import './ImpactStoryShow.scss';

const sampleIntroduction = 'Lorem ipsum dolor sit amet consectetur. Cursus faucibus non velit mauris. Eu at dui metus aenean gravida feugiat. Phasellus ac risus mattis rhoncus elit duis aliquet. Dui at purus pulvinar.';
const sampleParagraph = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec quis risus sit amet mi dignissim feugiat. Nam lobortis, mi eu finibus ullamcorper, ante leo scelerisque erat, in consectetur risus diam et lectus. Etiam mollis mi non ante sollicitudin pellentesque. Nulla tempor massa sodales felis porttitor mattis.</p>';
const sampleAdditionalParagraph = '<p>Suspendisse condimentum turpis in massa sodales aliquet. Nullam sit amet nibh viverra lacus sollicitudin pulvinar. Quisque at convallis nibh. Maecenas feugiat enim eu tellus pretium, sit amet pretium risus condimentum. Vivamus tincidunt magna egestas arcu rhoncus rhoncus. Proin lorem libero, egestas nec tortor a, pharetra convallis lectus.</p>';

function sampleSections(caseStudy, images, fallback) {
    const text = (id, heading, body) => ({ id, type: 'rich_text', data: { heading, body } });
    const photos = (...names) => names.map((name) => ({ image: images[name] || fallback })).filter((item) => item.image);
    const mosaic = { id: 'sample-mosaic', type: 'image_mosaic', data: { images: photos('p033', 'p050', 'p136', 'p127') } };
    const subheading = sampleParagraph + sampleAdditionalParagraph;

    if (!caseStudy) {
        return [
            text('sample-introduction', 'Introduction', sampleParagraph),
            text('sample-opening', 'Subheading', subheading),
            mosaic,
            text('sample-middle', 'Subheading', subheading),
            { id: 'sample-photos', type: 'image_carousel', data: { images: photos('p114', 'p114', 'p114', 'p114') } },
            text('sample-conclusion', 'Conclusion', subheading),
        ];
    }

    return [
        {
            id: 'sample-challenge',
            anchor: 'case-study-challenge',
            type: 'image_mosaic',
            data: {
                heading: 'The Challenge',
                body: sampleIntroduction,
                images: photos('p028', 'p139'),
                quote: 'Sample quotation. Lorem ipsum dolor sit amet consectetur. Cursus faucibus non velit mauris.',
            },
        },
        {
            id: 'sample-approach',
            anchor: 'case-study-approach',
            type: 'image_text',
            data: { heading: 'Our Approach', body: sampleParagraph, image: images.p120 || fallback, layout: 'panel' },
        },
        { ...text('sample-solutions', 'Our Solutions', sampleParagraph), anchor: 'case-study-solutions' },
        { id: 'sample-solution-photo', type: 'image_mosaic', data: { images: photos('p114') } },
        { ...text('sample-subheading', 'Subheading', subheading), anchor: 'case-study-subheading' },
        mosaic,
    ];
}

function StoryHero({ post, image }) {
    return (
        <section className={`nt-page-hero nt-impact-detail__hero on-dark${image ? '' : ' nt-impact-detail__hero--plain'}`}>
            <div className="nt-page-hero__grid">
                <div className="nt-page-hero__content">
                    <h1 className="nt-hero-in" style={{ '--d': 1 }}>{post.title}</h1>
                    {post.excerpt ? <p className="nt-page-hero__body nt-hero-in" style={{ '--d': 2 }}>{post.excerpt}</p> : null}
                    <ul className="nt-story-meta list-unstyled nt-hero-in" style={{ '--d': 3 }}>
                        <li><Icon name="clock" strokeWidth={1.4} />{post.reading_minutes} min read</li>
                        {post.date ? <li><Icon name="calendar" strokeWidth={1.4} /><time dateTime={post.datetime}>{post.date}</time></li> : null}
                        <li className="nt-impact-detail__author"><Icon name="user" strokeWidth={1.4} />{post.author_name || 'NAMTECH'}</li>
                    </ul>
                </div>
                {image ? (
                    <div className="nt-page-hero__media">
                        <Media image={image} sizes="(min-width: 768px) 50vw, 100vw" priority />
                    </div>
                ) : null}
            </div>
        </section>
    );
}

function Contents({ items, collapsed = false }) {
    if (items.length < 2) return null;
    const list = (
        <ul className="list-unstyled m-0">
            {items.map((item) => <li key={item.id}><a href={`#${item.id}`}>{item.text}</a></li>)}
        </ul>
    );

    return collapsed ? (
        <details className="nt-impact-detail__contents">
            <summary>Contents</summary>
            {list}
        </details>
    ) : (
        <nav className="nt-impact-detail__contents" aria-label="Contents">{list}</nav>
    );
}

function StoryBody({ post, caseStudy, show }) {
    const contents = show('contents') ? (post.toc ?? []) : [];
    const tags = show('tags') ? (post.tags ?? []) : [];
    if (!post.body && !tags.length) return null;

    return (
        <section className="nt-section">
            <div className={`nt-impact-detail__width${caseStudy && contents.length > 1 ? ' nt-impact-detail__article-grid' : ' nt-impact-detail__prose-width'}`}>
                <Contents items={contents} collapsed={!caseStudy} />
                <div>
                    <RichText html={post.body} className="nt-article" />
                    {tags.length ? (
                        <ul className="list-unstyled d-flex flex-wrap gap-2 mt-4 mb-0" aria-label="Tags">
                            {tags.map((tag) => <li key={tag} className="nt-tag">{tag}</li>)}
                        </ul>
                    ) : null}
                </div>
            </div>
        </section>
    );
}

function CaseStudySolutions({ sections, page, showContents }) {
    if (!sections.length) return null;
    const solution = sections[0];
    const headings = sections
        .filter((section) => section.anchor && section.data.heading)
        .map((section) => ({ id: section.anchor, text: section.data.heading }));
    const contents = showContents ? [...headings, ...(solution.data.toc_items ?? [])] : [];
    const flow = [{ ...solution, data: { ...solution.data, toc: false } }, ...sections.slice(1)];

    return (
        <div className={`nt-impact-detail__width nt-impact-detail__solutions${contents.length > 1 ? ' nt-impact-detail__article-grid' : ''}`}>
            <Contents items={contents} />
            <div className="nt-impact-detail__solution-flow"><SectionRenderer sections={flow} page={page} /></div>
        </div>
    );
}

function RelatedStories({ related, caseStudy, sampleImage }) {
    const heading = caseStudy ? 'Related Case Studies' : 'Related Stories';
    const sampleTitles = caseStudy
        ? ['Case Study', 'Case Study', 'Case Study']
        : ['Career Outcome Pattern', 'Advancing MET Research', 'Industry-Validated Talent'];
    const stories = [
        ...related,
        ...sampleTitles.map((title, index) => ({
            id: `sample-related-${index}`,
            title: `SAMPLE – ${title}`,
            date: 'Sample date',
            excerpt: 'Lorem ipsum dolor sit amet consectetur. Ipsum aliquet phasellus rhoncus fames velit.',
            image: sampleImage,
        })),
    ].slice(0, 3);

    return (
        <section className={`nt-section nt-impact-detail__related${caseStudy ? '' : ' nt-tone-lavender'}`}>
            <div className="nt-impact-detail__width">
                <h2 className="nt-h2">{heading}</h2>
                <Carousel label={heading} slideWidth="min(82vw, 26rem)" gap="0.75rem" trackClassName="nt-impact-detail__related-track" showArrows={false} showDots={false}>
                    {stories.map((story) => (
                        <article className="nt-impact-detail__card" key={story.id}>
                            <Media image={story.image} sizes="(min-width: 768px) 30vw, 82vw" alt="" />
                            {story.date ? <time dateTime={story.datetime}>{story.date}</time> : null}
                            <h3>{story.title}</h3>
                            {story.excerpt ? <p>{story.excerpt}</p> : null}
                            {story.url ? (
                                <SmartLink href={story.url} className="nt-link-arrow stretched-link">
                                    View<span className="visually-hidden"> {story.title}</span> <ArrowDot />
                                </SmartLink>
                            ) : null}
                        </article>
                    ))}
                </Carousel>
            </div>
        </section>
    );
}

export default function ImpactStoryShow({ post, sections = [], related = [], hidden, caseStudy, sampleBody, sampleImages = {} }) {
    const show = blockSwitch(hidden);
    const hero = sections.find((section) => section.type === 'page_hero');
    const image = hero?.data.image || post.image;
    const articleSections = sampleBody
        ? sampleSections(caseStudy, sampleImages, post.image || image)
        : sections.filter((section) => section !== hero);
    const introduction = articleSections.find((section) => section.type === 'rich_text' && section.anchor === 'introduction');
    const bodyAlreadyInIntroduction = introduction && introduction.data.body === post.body;
    const solutionsIndex = caseStudy ? articleSections.findIndex((section) => section.anchor === 'case-study-solutions') : -1;
    const opening = solutionsIndex < 0 ? [] : articleSections.slice(0, solutionsIndex);
    const remaining = solutionsIndex < 0 ? articleSections : articleSections.slice(solutionsIndex);
    const page = { title: post.title };

    return (
        <div className={`nt-impact-detail${caseStudy ? ' nt-impact-detail--case' : ' nt-impact-detail--short'}`}>
            <StoryHero post={post} image={show('hero_image') ? image : null} />
            <article className="nt-impact-detail__sections" aria-label={post.title}>
                {sampleBody ? <p className="nt-impact-detail__sample nt-impact-detail__width">Sample article content</p> : null}
                <SectionRenderer sections={opening} page={page} />
                <StoryBody post={bodyAlreadyInIntroduction ? { ...post, body: null, toc: [] } : post} caseStudy={caseStudy} show={show} />
                {solutionsIndex < 0
                    ? <SectionRenderer sections={remaining} page={page} />
                    : <CaseStudySolutions sections={remaining} page={page} showContents={show('contents')} />}
            </article>
            {show('related') ? <RelatedStories related={related} caseStudy={caseStudy} sampleImage={sampleImages.p139 || post.image || image} /> : null}
        </div>
    );
}
