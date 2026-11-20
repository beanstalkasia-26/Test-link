import usePage from '../../../hooks/usePage';
import { useId, useState } from 'react';
import Carousel from '../../Carousel';
import Icon, { ArrowDot } from '../../Icon';
import { Media } from '../../ResponsiveImage';
import SmartLink from '../../SmartLink';
import Tabs from '../../Tabs';
import { FacultyGrid, IconCard, PersonCard, PostCard, ProgramCard, SimpleCard } from '../Cards';
import { Cta, RichText, SectionHeading } from '../Common';
import EnquiryForm from '../EnquiryForm';
import { SplitHead, toneClass } from './InnerSections';
import { GalleryGrid, TourEmbed, VideoEmbed } from './MediaEmbeds';

function HeroMeta({ items }) {
    if (!items?.length) return null;
    return (
        <ul className="nt-meta-list nt-meta-list--row nt-page-hero__meta">
            {items.map((m, i) => (
                <li key={i}>
                    <Icon name={m.icon || 'check'} strokeWidth={1.6} />
                    {m.text}
                </li>
            ))}
        </ul>
    );
}

function HeroStats({ statistics }) {
    if (!statistics?.length) return null;
    return (
        <dl className="nt-page-hero__stats">
            {statistics.map((stat) => (
                <div key={stat.label}>
                    <dt className="visually-hidden">{stat.label}</dt>
                    <dd className="m-0">
                        <strong data-countup={stat.value}>{stat.value}</strong>
                        <span>{stat.label}</span>
                    </dd>
                </div>
            ))}
        </dl>
    );
}

export function PageHero({ data, page, first }) {
    const title = data.title || page?.title;
    const Heading = first ? 'h1' : 'h2';
    const buttons =
        (data.cta_label && data.cta_url) || (data.secondary_label && data.secondary_url) ? (
            <div className="d-flex flex-wrap gap-3 mt-3 nt-hero-in" style={{ '--d': 3 }}>
                <Cta label={data.cta_label} url={data.cta_url} variant={data.layout === 'plain' || data.layout === 'light' ? '' : 'light'} />
                {data.secondary_label && data.secondary_url ? (
                    <SmartLink href={data.secondary_url} className={`nt-btn ${data.layout === 'plain' ? 'nt-btn--outline' : 'nt-btn--ghost'}`}>
                        {data.secondary_label}
                    </SmartLink>
                ) : null}
            </div>
        ) : null;

    if (data.layout === 'plain') {
        return (
            <section className="nt-plain-hero">
                <div className="container">
                    {data.eyebrow ? <p className="nt-pill nt-hero-in">{data.eyebrow}</p> : null}
                    <Heading className="nt-hero-in" style={{ '--d': 1 }}>{title}</Heading>
                    {data.body ? <p className="nt-plain-hero__body nt-hero-in" style={{ '--d': 2 }}>{data.body}</p> : null}
                    <HeroMeta items={data.meta} />
                    {buttons}
                </div>
                {data.image ? (
                    <div className="container mt-4">
                        <Media image={data.image} sizes="100vw" priority={first} className="nt-plain-hero__media" />
                    </div>
                ) : null}
            </section>
        );
    }

    return (
        <section className={`nt-page-hero ${data.layout === 'light' ? 'nt-page-hero--light' : 'on-dark'} ${data.button_size === 'large' ? 'nt-page-hero--large-buttons' : ''}`}>
            <div className="nt-page-hero__grid">
                <div className="nt-page-hero__content">
                    {data.eyebrow ? <p className="nt-pill align-self-start nt-hero-in">{data.eyebrow}</p> : null}
                    <Heading className="nt-hero-in" style={{ '--d': 1 }}>{title}</Heading>
                    {data.body ? <p className="nt-page-hero__body nt-hero-in" style={{ '--d': 2 }}>{data.body}</p> : null}
                    <HeroMeta items={data.meta} />
                    {buttons}
                    <HeroStats statistics={data.statistics} />
                </div>
                {data.image ? (
                    <div className="nt-page-hero__media">
                        <Media image={data.image} sizes="(min-width: 992px) 50vw, 100vw" priority={first} />
                    </div>
                ) : null}
            </div>
        </section>
    );
}

export function Intro({ data }) {
    if (data.align === 'statement') {
        return (
            <section className={`nt-section ${toneClass(data.tone)}`}>
                <div className="container">
                    <RichText html={data.body} className="nt-intro__statement" />
                </div>
            </section>
        );
    }
    if (data.align === 'center') {
        return (
            <section className={`nt-section ${toneClass(data.tone)}`}>
                <div className="container text-center nt-intro--center">
                    {data.icon ? <Icon name={data.icon} strokeWidth={1} className="nt-intro__icon" /> : null}
                    <SectionHeading eyebrow={data.eyebrow} heading={data.heading} className="mb-0" />
                    {data.body ? <RichText html={data.body} className="nt-intro__body mx-auto mt-4" /> : null}
                    <Cta label={data.cta_label} url={data.cta_url} variant={data.tone === 'purple' ? 'light' : ''} className="mt-4" />
                </div>
            </section>
        );
    }
    if (!data.heading && !data.eyebrow && !data.icon) {
        return (
            <section className={`nt-section nt-intro--solo ${toneClass(data.tone)}`}>
                <div className="container">
                    <div className="nt-intro__solo">
                        <RichText html={data.body} />
                        <Cta label={data.cta_label} url={data.cta_url} variant={data.tone === 'purple' ? 'light' : ''} className="mt-4" />
                    </div>
                </div>
            </section>
        );
    }
    return (
        <section className={`nt-section ${toneClass(data.tone)}`}>
            <div className="container">
                <div className="row g-4 g-lg-5">
                    <div className="col-lg-5">
                        {data.icon ? <Icon name={data.icon} strokeWidth={1} className="nt-intro__icon" /> : null}
                        <SectionHeading eyebrow={data.eyebrow} heading={data.heading} className="mb-0" />
                    </div>
                    <div className="col-lg-7">
                        <RichText html={data.body} />
                        <Cta label={data.cta_label} url={data.cta_url} variant={data.tone === 'purple' ? 'light' : ''} className="mt-4" />
                    </div>
                </div>
            </div>
        </section>
    );
}

export function RichTextSection({ data }) {
    const toc = data.toc_items ?? [];
    if (data.toc && toc.length) {
        return (
            <section className="nt-section">
                <div className="container">
                    <div className="row g-5">
                        <aside className="col-lg-3" aria-label="Sections">
                            <nav className="nt-toc">
                                <p className="nt-toc__title">Sections</p>
                                <ol>
                                    {toc.map((t) => (
                                        <li key={t.id}>
                                            <a href={`#${t.id}`}>{t.text}</a>
                                        </li>
                                    ))}
                                </ol>
                            </nav>
                        </aside>
                        <div className="col-lg-9">
                            {data.heading ? <h2 className="nt-h2">{data.heading}</h2> : null}
                            <RichText html={data.body} />
                        </div>
                    </div>
                </div>
            </section>
        );
    }
    return (
        <section className="nt-section">
            <div className="container" style={{ maxWidth: data.width === 'wide' ? undefined : '52rem' }}>
                {data.heading ? <h2 className="nt-h2">{data.heading}</h2> : null}
                <RichText html={data.body} />
            </div>
        </section>
    );
}

export function ImageText({ data }) {
    const left = data.image_position !== 'right';
    const layout = data.layout || 'side';

    if (layout === 'research') {
        return (
            <section className="nt-section nt-partnership-research">
                <div className="container">
                    <SectionHeading eyebrow={data.eyebrow} heading={data.heading} />
                    <div className="nt-partnership-research__panel">
                        <Media image={data.image} sizes="(min-width: 768px) 50vw, 100vw" />
                        <RichText html={data.body} />
                    </div>
                    <Cta label={data.cta_label} url={data.cta_url} className="mt-4" />
                </div>
            </section>
        );
    }

    if (layout === 'panel') {
        return (
            <section className={`nt-image-panel ${left ? '' : 'nt-image-panel--reverse'}`}>
                <Media image={data.image} sizes="(min-width: 992px) 55vw, 100vw" className="nt-image-panel__media" />
                <div className="nt-image-panel__text on-dark">
                    <SectionHeading eyebrow={data.eyebrow} heading={data.heading} />
                    <RichText html={data.body} />
                    <Cta label={data.cta_label} url={data.cta_url} variant="light" className="mt-4" />
                </div>
            </section>
        );
    }

    return (
        <section className={`nt-section ${toneClass(data.tone)} ${layout === 'staggered' ? 'nt-staggered' : ''}`}>
            <div className="container">
                <div className={`row g-4 g-lg-5 align-items-center ${layout === 'staggered' ? 'nt-staggered__row' : ''}`}>
                    <div className={`col-lg-6 ${left ? '' : 'order-lg-2'}`}>
                        <Media image={data.image} sizes="(min-width: 992px) 50vw, 100vw" className="nt-zoom" style={{ aspectRatio: '4 / 3' }} />
                    </div>
                    <div className="col-lg-6">
                        <div className={layout === 'staggered' ? 'nt-staggered__text' : ''}>
                            <SectionHeading eyebrow={data.eyebrow} heading={data.heading} />
                            <RichText html={data.body} />
                            <Cta label={data.cta_label} url={data.cta_url} className="mt-4" />
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}

export function CardGrid({ data, anchor }) {
    const tone = data.tone || 'white';
    const columns = Number(data.columns || 3);
    const style = data.style || (tone === 'purple' ? 'filled' : 'outlined');
    const cards = data.cards ?? [];
    const research = anchor === 'academic-research-themes';
    const joinedCards = cards.map((card, i) => (
        <div className="nt-joined-card" key={i}>
            <Icon name={card.icon || 'graduation'} strokeWidth={1.1} />
            <h3>{card.link_url ? <SmartLink href={card.link_url} className="stretched-link">{card.title}</SmartLink> : card.title}</h3>
            {card.body ? <p>{card.body}</p> : null}
        </div>
    ));

    if (style === 'lines' || style === 'numbered') {
        const numbered = style === 'numbered';
        return (
            <section className={`nt-section ${toneClass(tone)}`}>
                <div className="container">
                    <SplitHead data={numbered ? { ...data, tone, cta_label: null, cta_url: null } : { ...data, tone }} />
                    {data.list_label ? <h3 className="nt-card-lines__label">{data.list_label}</h3> : null}
                    <ul className={`nt-card-lines nt-card-lines--${style} list-unstyled nt-stagger`} style={{ '--cols': columns }}>
                        {cards.map((card, i) => (
                            <li key={i}>
                                {numbered ? <span className="nt-card-lines__num">{String(i + 1).padStart(2, '0')}</span> : <Icon name={card.icon || 'graduation'} strokeWidth={1.1} />}
                                <div>
                                    {card.title ? <h4>{card.link_url ? <SmartLink href={card.link_url}>{card.title}</SmartLink> : card.title}</h4> : null}
                                    {card.body ? <p>{card.body}</p> : null}
                                </div>
                            </li>
                        ))}
                    </ul>
                    {data.footer ? <p className="nt-card-lines__footer">{data.footer}</p> : null}
                    {numbered ? (
                        <div className="text-center">
                            <Cta label={data.cta_label} url={data.cta_url} className="mt-5" />
                        </div>
                    ) : null}
                </div>
            </section>
        );
    }

    return (
        <section className={`nt-section ${toneClass(tone)}`}>
            <div className="container">
                <SplitHead data={research ? { ...data, tone, cta_label: null, cta_url: null } : { ...data, tone }} />
                {style === 'joined' || style === 'joined_light' ? (
                    <div className={`nt-joined-cards nt-stagger ${style === 'joined_light' ? 'nt-joined-cards--light' : ''}${research ? ' d-none d-md-grid' : ''}`} style={{ '--cols': columns }}>
                        {joinedCards}
                    </div>
                ) : (
                    <div className={`row g-4 row-cols-1 row-cols-md-2 row-cols-lg-${columns} nt-stagger`}>
                        {cards.map((card, i) => (
                            <div className="col" key={i}>
                                <IconCard icon={card.icon} title={card.title} body={card.body} url={card.link_url} outlined={style === 'outlined'} />
                            </div>
                        ))}
                    </div>
                )}
                {research ? (
                    <>
                        <div className="d-md-none">
                            <Carousel label="Sample research themes" slideWidth="82%" gap="0">{joinedCards}</Carousel>
                        </div>
                        <div className="text-center mt-4"><Cta label={data.cta_label} url={data.cta_url} /></div>
                    </>
                ) : null}
            </div>
        </section>
    );
}

export function StatsBand({ data }) {
    if (!data.statistics?.length) return null;
    if (data.layout === 'dividers') {
        return (
            <section className="nt-section">
                <div className="container">
                    <SectionHeading eyebrow={data.eyebrow} heading={data.heading} />
                    <dl className="nt-stat-dividers nt-stagger">
                        {data.statistics.map((stat) => (
                            <div key={stat.label}>
                                <dt>{stat.label}</dt>
                                <dd data-countup={stat.value}>{stat.value}</dd>
                            </div>
                        ))}
                    </dl>
                </div>
            </section>
        );
    }
    return (
        <section className="nt-section nt-tone-lavender">
            <div className="container">
                <SectionHeading eyebrow={data.eyebrow} heading={data.heading} />
                <dl className="row g-4 m-0 nt-stagger">
                    {data.statistics.map((stat) => (
                        <div key={stat.label} className="col-6 col-lg-3 d-flex flex-column-reverse">
                            <dt className="fw-normal">{stat.label}</dt>
                            <dd className="m-0 nt-stat-figure" data-countup={stat.value}>
                                {stat.value}
                            </dd>
                        </div>
                    ))}
                </dl>
            </div>
        </section>
    );
}

function MediaCard({ item, layout, linkLabel }) {
    const label = linkLabel || 'Read';
    const link = item.link_url ? (
        <SmartLink href={item.link_url} className="nt-link-arrow">
            {label}
            <span className="visually-hidden">: {item.title}</span> <ArrowDot />
        </SmartLink>
    ) : null;

    switch (layout) {
        case 'showcase':
            return (
                <figure className="nt-showcase-card">
                    <Media image={item.image} sizes="(min-width: 992px) 60vw, 90vw" />
                    <figcaption>
                        <h3>{item.title}</h3>
                        {item.body ? <p>{item.body}</p> : null}
                        {link}
                    </figcaption>
                </figure>
            );
        case 'horizontal':
            return (
                <article className="nt-hcard">
                    <div className="nt-hcard__text">
                        {item.icon ? <Icon name={item.icon} strokeWidth={1.1} /> : null}
                        {item.tag ? <span className="nt-tag align-self-start">{item.tag}</span> : null}
                        <h3>{item.title}</h3>
                        {item.subtitle ? <p className="nt-hcard__sub">{item.subtitle}</p> : null}
                        {item.body ? <p>{item.body}</p> : null}
                        {link}
                    </div>
                    <Media image={item.image} sizes="(min-width: 992px) 30vw, 90vw" className="nt-hcard__media" />
                </article>
            );
        case 'feature':
            return (
                <article className="nt-feature-card">
                    <div className="nt-feature-card__text">
                        {item.icon ? <Icon name={item.icon} strokeWidth={1} /> : null}
                        <h3>{item.title}</h3>
                        {item.subtitle ? <p className="nt-feature-card__sub">{item.subtitle}</p> : null}
                        {item.body ? <p>{item.body}</p> : null}
                        {link}
                    </div>
                    <Media image={item.image} sizes="(min-width: 992px) 600px, 90vw" className="nt-feature-card__media" />
                </article>
            );
        case 'story':
            return (
                <article className="nt-story-panel">
                    <Media image={item.image} sizes="(min-width: 992px) 45vw, 90vw" className="nt-story-panel__media" />
                    <div className="nt-story-panel__body">
                        {item.tag ? <span className="nt-tag align-self-start">{item.tag}</span> : null}
                        <h3>{item.title}</h3>
                        {item.subtitle ? <p className="nt-hcard__sub">{item.subtitle}</p> : null}
                        {item.body ? <p className="nt-pre-line">{item.body}</p> : null}
                        {item.meta ? (
                            <p className="nt-story-card__highlight">
                                <Icon name="trend" />
                                {item.meta}
                            </p>
                        ) : null}
                        {item.metrics?.length ? (
                            <div>
                                {item.sample_metrics ? <p className="nt-partnership-figures__label">Sample figures</p> : null}
                                <dl className="nt-partnership-figures">
                                    {item.metrics.map((metric) => (
                                        <div key={metric.label}>
                                            <dt>{metric.label}</dt>
                                            <dd>{metric.value}</dd>
                                        </div>
                                    ))}
                                </dl>
                            </div>
                        ) : null}
                        {link}
                    </div>
                </article>
            );
        case 'partnership_tiles':
            return (
                <article className="nt-partnership-tile">
                    <Icon name={item.icon || 'building'} strokeWidth={1.1} />
                    <h3>{item.title}</h3>
                    <p>{item.body}</p>
                    {link}
                </article>
            );
        case 'logo':
            return (
                <article className="nt-logo-card">
                    <Media image={item.image} sizes="(min-width: 992px) 30vw, 85vw" className="nt-logo-card__media" />
                    <div className="nt-logo-card__body">
                        {item.logo ? <img src={item.logo.src} alt={item.logo.alt} width={item.logo.width} height={item.logo.height} loading="lazy" className="nt-logo-card__logo" /> : null}
                        <h3>{item.title}</h3>
                        {item.body ? <p className="nt-pre-line">{item.body}</p> : null}
                        {item.meta ? (
                            <p className="nt-story-card__highlight">
                                <Icon name="trend" />
                                {item.meta}
                            </p>
                        ) : null}
                        {link}
                    </div>
                </article>
            );
        case 'centers':
            return (
                <article className="nt-center-card">
                    <Media image={item.image} sizes="(min-width: 992px) 1000px, 88vw" className="nt-center-card__media" alt="" />
                    {item.logo ? <img src={item.logo.src} alt={item.logo.alt} width={item.logo.width} height={item.logo.height} loading="lazy" className="nt-center-card__logo" /> : null}
                    <h3>{item.title}</h3>
                    {item.body ? <p>{item.body}</p> : null}
                    {link}
                </article>
            );
        case 'labs':
            return (
                <article className="nt-lab-card">
                    <Media image={item.image} sizes="(min-width: 992px) 680px, 85vw" className="nt-lab-card__media" alt="" />
                    <h3>{item.link_url ? <SmartLink href={item.link_url} className="stretched-link">{item.title}</SmartLink> : item.title}</h3>
                    {item.body ? <p>{item.body}</p> : null}
                </article>
            );
        case 'tall':
        case 'tall_wide':
            return (
                <article className={`nt-tall-card ${layout === 'tall_wide' ? 'nt-tall-card--wide' : ''}`}>
                    <Media image={item.image} sizes="(min-width: 992px) 25vw, 80vw" className="nt-tall-card__media" />
                    <div className="nt-tall-card__body">
                        {item.date || item.tag ? <p className="nt-tall-card__meta">{[item.tag, item.date].filter(Boolean).join(' · ')}</p> : null}
                        <h3>{item.link_url ? <SmartLink href={item.link_url} className="stretched-link">{item.title}</SmartLink> : item.title}</h3>
                        {item.subtitle ? <p className="nt-hcard__sub">{item.subtitle}</p> : null}
                        {item.body ? <p>{item.body}</p> : null}
                        {item.link_url && linkLabel ? (
                            <span className="nt-link-arrow mt-auto" aria-hidden="true">
                                {linkLabel} <ArrowDot />
                            </span>
                        ) : null}
                    </div>
                </article>
            );
        default:
            return <SimpleCard item={{ ...item, url: item.link_url, summary: item.body }} linkLabel={linkLabel} />;
    }
}

export function MediaCards({ data, page }) {
    const layout = data.layout || 'cards';
    const items = data.items ?? [];
    const widths = { cards: 'min(22rem, 82%)', showcase: 'min(56rem, 88%)', horizontal: 'min(60rem, 92%)', story: 'min(71.7rem, 92%)', logo: 'min(24rem, 85%)', tall: 'min(18rem, 75%)', tall_wide: 'min(36.5rem, 85%)', labs: 'min(32.1rem, 85%)', centers: 'min(62.5rem, 88%)', feature: 'min(75rem, 92%)', partnership_tiles: 'min(21rem, 78%)' };
    const stackOnMobile = layout === 'feature' && page?.path?.split('/')[0] === 'partnerships';
    const cards = items.map((item, i) => <MediaCard key={i} item={item} layout={layout} linkLabel={data.card_link_label} />);

    return (
        <section className={`nt-section ${toneClass(data.tone)} nt-media-cards--${layout}${layout === 'centers' ? ' nt-divider' : ''}`}>
            <div className="container">
                {layout === 'feature' || data.head_align === 'center' ? (
                    <div className="nt-media-cards__head text-center mx-auto">
                        {data.eyebrow ? <p className="nt-pill d-table mx-auto">{data.eyebrow}</p> : null}
                        {data.heading ? <h2 className="nt-h2">{data.heading}</h2> : null}
                        {data.body ? <p className="nt-lead mx-auto">{data.body}</p> : null}
                    </div>
                ) : (
                    <SplitHead data={data} />
                )}
                <div className={stackOnMobile ? 'd-none d-md-block' : undefined}>
                    <Carousel label={data.heading || 'Highlights'} slideWidth={widths[layout] ?? widths.cards} gap={{ tall: '1.25rem', centers: '2.25rem', partnership_tiles: '0' }[layout] ?? '2rem'} align={layout === 'showcase' ? 'center' : 'start'}>
                        {cards}
                    </Carousel>
                </div>
                {stackOnMobile ? <div className="nt-partnership-feature-stack d-md-none">{cards}</div> : null}
                {data.footer_label && data.footer_url ? (
                    <p className="text-center mt-4 mb-0">
                        <SmartLink href={data.footer_url} className="nt-link-arrow">
                            {data.footer_label} <ArrowDot />
                        </SmartLink>
                    </p>
                ) : null}
            </div>
        </section>
    );
}

export function TabsSection({ data, panels }) {
    if (!data.tabs?.length) return null;
    const centered = data.layout === 'centered';
    const sectionPanels = data.layout === 'section_panels';
    return (
        <section className={`nt-section ${toneClass(data.tone)}${sectionPanels ? ' nt-partnership-models' : ''}`}>
            <div className="container">
                <div className={centered ? 'text-center mx-auto' : ''} style={centered ? { maxWidth: '48rem' } : undefined}>
                    {data.eyebrow ? <p className={`nt-pill ${centered ? 'd-table mx-auto' : ''}`}>{data.eyebrow}</p> : null}
                    {data.heading ? <h2 className="nt-h2">{data.heading}</h2> : null}
                    {data.body ? <p className={`nt-lead mt-3 mb-0 ${centered ? 'mx-auto' : ''}`} style={{ maxWidth: '48rem' }}>{data.body}</p> : null}
                </div>
                <Tabs
                    label={data.heading || 'Tabs'}
                    className={`mt-4 ${centered ? 'nt-tabs-center' : ''}`}
                    tabs={data.tabs.map((tab, i) => ({
                        key: i,
                        label: tab.label,
                        anchor: sectionPanels ? tab.panel_anchor : undefined,
                        content: sectionPanels ? (
                            <div className="nt-partnership-models__panel">
                                <RichText html={tab.body} />
                                {panels && Object.hasOwn(panels, tab.panel_anchor) ? panels[tab.panel_anchor] : null}
                            </div>
                        ) : centered ? (
                            <div className="text-center mx-auto nt-fade-in" style={{ maxWidth: '48rem' }}>
                                {tab.image ? <Media image={tab.image} sizes="(min-width: 992px) 48rem, 100vw" className="mb-4" style={{ aspectRatio: '16 / 9' }} /> : null}
                                {tab.title ? <h3 className="nt-h3 mb-3">{tab.title}</h3> : null}
                                <RichText html={tab.body} />
                            </div>
                        ) : (
                            <div className="row g-4 g-lg-5 align-items-center nt-fade-in">
                                <div className={tab.image ? 'col-lg-6' : 'col-12'}>
                                    {tab.title ? <h3 className="nt-h3 mb-3">{tab.title}</h3> : null}
                                    <RichText html={tab.body} />
                                </div>
                                {tab.image ? (
                                    <div className="col-lg-6">
                                        <Media image={tab.image} sizes="(min-width: 992px) 50vw, 100vw" style={{ aspectRatio: '4 / 3' }} />
                                    </div>
                                ) : null}
                            </div>
                        ),
                    }))}
                />
            </div>
        </section>
    );
}

export function FaqItems({ items, openFirst = false }) {
    const [openIndex, setOpenIndex] = useState(openFirst ? 0 : -1);
    return (
        <div className="nt-faq">
            {(items ?? []).map((item, i) => (
                <details key={i} open={openIndex === i}>
                    <summary
                        onClick={(e) => {
                            e.preventDefault();
                            setOpenIndex(openIndex === i ? -1 : i);
                        }}
                    >
                        {item.question}
                    </summary>
                    <RichText html={item.answer} />
                </details>
            ))}
        </div>
    );
}

export function Faq({ data }) {
    if (data.layout === 'centered') {
        return (
            <section className="nt-section">
                <div className="container">
                    <div className="nt-faq-panel">
                        <h2 className="nt-h2 text-center">{data.heading || 'Frequently Asked Questions'}</h2>
                        {data.body ? <p className="text-center mb-4">{data.body}</p> : null}
                        <FaqItems items={data.items} />
                    </div>
                </div>
            </section>
        );
    }
    return (
        <section className="nt-section">
            <div className="container">
                <div className="row g-4 g-lg-5">
                    <div className="col-lg-4">
                        <h2 className="nt-h2">{data.heading || 'Frequently Asked Questions'}</h2>
                        {data.body ? <p>{data.body}</p> : null}
                    </div>
                    <div className="col-lg-8">
                        <FaqItems items={data.items} />
                    </div>
                </div>
            </div>
        </section>
    );
}

export function Steps({ data }) {
    const items = data.items ?? [];
    if (data.layout === 'panel') {
        return (
            <section className="nt-section">
                <div className="container">
                    <SplitHead data={data} />
                    <div className="nt-steps-panel">
                        {data.image ? <Media image={data.image} sizes="(min-width: 992px) 45vw, 100vw" className="nt-steps-panel__media" /> : null}
                        <div className="nt-steps-panel__list">
                            {data.panel_title ? <p className="nt-steps-panel__title">{data.panel_title}</p> : null}
                            <ol>
                                {items.map((item, i) => (
                                    <li key={i}>
                                        <span>{String(i + 1).padStart(2, '0')}</span>
                                        <div>
                                            <h3>{item.title}</h3>
                                            {item.body ? <p>{item.body}</p> : null}
                                        </div>
                                    </li>
                                ))}
                            </ol>
                        </div>
                    </div>
                </div>
            </section>
        );
    }
    return (
        <section className="nt-section">
            <div className="container">
                <div className="row g-4 g-lg-5">
                    <div className="col-lg-5">
                        <SectionHeading eyebrow={data.eyebrow} heading={data.heading} />
                        {data.body ? <p>{data.body}</p> : null}
                        {data.image ? <Media image={data.image} sizes="(min-width: 992px) 40vw, 100vw" className="mt-4" style={{ aspectRatio: '4 / 3' }} /> : null}
                    </div>
                    <div className="col-lg-7">
                        <ol className="nt-steps">
                            {items.map((item, i) => (
                                <li key={i}>
                                    <h3>{item.title}</h3>
                                    {item.body ? <p className="mb-0">{item.body}</p> : null}
                                </li>
                            ))}
                        </ol>
                    </div>
                </div>
            </div>
        </section>
    );
}

export function People({ data }) {
    const people = data.people ?? [];
    if (!people.length) return null;
    const layout = data.layout || 'grid';

    let body;
    if (layout === 'carousel') {
        const groups = [
            ['residential', 'Residential Faculty'],
            ['visiting', 'Visiting Faculty'],
        ]
            .map(([key, label]) => {
                const list = people.filter((p) => (p.category || 'residential') === key);
                const samples = [1, 2, 3, 4].map((i) => ({ id: `sample-${key}-${i}`, name: `SAMPLE – ${label.replace(' Faculty', '')} faculty name`, designation: 'SAMPLE – Designation', url: null, photo: people[0]?.photo ?? null }));
                return { key, label, list: list.length ? list : samples };
            });
        const carousel = (list, label) => (
            <Carousel label={label} slideWidth="min(22.2rem, 80%)" gap="2.25rem">
                {list.map((person) => (
                    <PersonCard key={person.id} person={person} />
                ))}
            </Carousel>
        );
        body = (
            <Tabs
                label="Faculty type"
                className="nt-people-tabs"
                tabs={groups.map((g) => ({ key: g.key, label: g.label, content: carousel(g.list, g.label) }))}
            />
        );
    } else if (layout === 'list') {
        body = (
            <ul className="nt-people-list list-unstyled nt-stagger">
                {people.map((p) => (
                    <li key={p.id}>
                        <Media image={p.photo} sizes="96px" alt="" />
                        <div>
                            <h3><SmartLink href={p.url} className="stretched-link">{p.name}</SmartLink></h3>
                            {p.designation ? <p>{p.designation}</p> : null}
                        </div>
                        <Icon name="arrowRight" strokeWidth={2} />
                    </li>
                ))}
            </ul>
        );
    } else {
        body = <FacultyGrid people={people} />;
    }

    return (
        <section className={`nt-section ${toneClass(data.tone)}`}>
            <div className="container">
                <SplitHead data={data} />
                {body}
            </div>
        </section>
    );
}

export function ProgramsList({ data }) {
    if (!data.programs?.length) return null;
    return (
        <section className="nt-section">
            <div className="container">
                <SectionHeading eyebrow={data.eyebrow} heading={data.heading} />
                <Carousel label={data.heading || 'Programs'} slideWidth="min(58.75rem, 88%)">
                    {data.programs.map((program) => (
                        <ProgramCard key={program.id} item={program} />
                    ))}
                </Carousel>
            </div>
        </section>
    );
}

export function PostList({ data }) {
    if (!data.posts?.length) return null;
    return (
        <section className="nt-section">
            <div className="container">
                <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                    {data.heading ? <h2 className="nt-h2 m-0">{data.heading}</h2> : null}
                    <Cta label={data.cta_label} url={data.cta_url} variant="outline" />
                </div>
                {data.layout === 'carousel' ? (
                    <Carousel label={data.heading || 'Articles'} slideWidth="min(21rem, 82%)" gap="1.5rem">
                        {data.posts.map((post) => (
                            <PostCard key={post.id} post={post} />
                        ))}
                    </Carousel>
                ) : (
                    <div className="nt-card-grid nt-stagger">
                        {data.posts.map((post) => (
                            <PostCard key={post.id} post={post} />
                        ))}
                    </div>
                )}
            </div>
        </section>
    );
}

export function LogoStrip({ data }) {
    if (!data.logos?.length) return null;
    if (data.layout === 'band') {
        return (
            <section className="nt-logo-band" aria-label={data.heading || 'Partners'}>
                <ul className="nt-logos nt-logo-band__list list-unstyled m-0 nt-stagger">
                    {data.logos.map((logo) => (
                        <li key={logo.id}>
                            {logo.logo ? <img src={logo.logo.src} width={logo.logo.width} height={logo.logo.height} alt={logo.name} loading="lazy" /> : <span>{logo.name}</span>}
                        </li>
                    ))}
                </ul>
            </section>
        );
    }
    return (
        <section className={`nt-section ${toneClass(data.tone)}`}>
            <div className="container">
                {data.heading ? <h2 className="nt-h3 text-center mb-4">{data.heading}</h2> : null}
                <ul className="nt-logos list-unstyled m-0 justify-content-center flex-wrap nt-stagger" aria-label={data.heading || 'Partners'}>
                    {data.logos.map((logo) => (
                        <li key={logo.id}>
                            <img src={logo.logo?.src} width={logo.logo?.width} height={logo.logo?.height} alt={logo.name} loading="lazy" />
                        </li>
                    ))}
                </ul>
            </div>
        </section>
    );
}

function Spotlight({ data, items, responsive }) {
    const [active, setActive] = useState(0);
    const id = useId();
    const item = items[active];
    const photo = item.image || data.image;
    return (
        <>
        <section className={`nt-spotlight${responsive ? ' d-none d-lg-grid' : ''}`}>
            <div className="nt-spotlight__side">
                <div className="nt-spotlight__inner">
                    {data.eyebrow ? <p className="nt-pill">{data.eyebrow}</p> : null}
                    {data.heading ? <h2 className="nt-h2">{data.heading}</h2> : null}
                    {data.body ? <p className="nt-spotlight__intro">{data.body}</p> : null}
                    <ul className="nt-spotlight__names" role="tablist" aria-label={data.heading || 'Testimonials'}>
                        {items.map((it, i) => (
                            <li key={i} role="presentation">
                                <button
                                    type="button"
                                    role="tab"
                                    id={`${id}-t${i}`}
                                    aria-controls={`${id}-p`}
                                    aria-selected={i === active}
                                    tabIndex={i === active ? 0 : -1}
                                    onClick={() => setActive(i)}
                                >
                                    <strong>{it.name}</strong>
                                    {it.role ? <span>{it.role}</span> : null}
                                </button>
                            </li>
                        ))}
                    </ul>
                    {data.cta_label && data.cta_url ? <Cta label={data.cta_label} url={data.cta_url} /> : null}
                </div>
            </div>
            <div className="nt-spotlight__stage" role="tabpanel" id={`${id}-p`} aria-labelledby={`${id}-t${active}`}>
                {photo ? <Media image={photo} sizes="(min-width: 992px) 52vw, 100vw" alt="" className="nt-spotlight__media" /> : null}
                {photo && !item.image && data.photo_caption ? <span className="nt-spotlight__photo-caption">{data.photo_caption}</span> : null}
                <SpotlightQuote item={item} key={active} />
            </div>
        </section>
        {responsive ? (
            <section className="nt-section nt-tone-lavender nt-partnership-leadership d-lg-none">
                <div className="container">
                    <SectionHeading eyebrow={data.eyebrow} heading={data.heading} />
                    {data.body ? <p>{data.body}</p> : null}
                    <Carousel label={data.heading || 'Leadership perspectives'} slideWidth="86%" gap="0.75rem">
                        {items.map((entry, index) => (
                            <div className="nt-spotlight-mobile" key={index}>
                                {entry.image || data.image ? (
                                    <div className="nt-spotlight-mobile__photo">
                                        <Media image={entry.image || data.image} sizes="86vw" alt="" />
                                        {!entry.image && data.photo_caption ? <span className="nt-spotlight__photo-caption">{data.photo_caption}</span> : null}
                                    </div>
                                ) : null}
                                <SpotlightQuote item={entry} />
                            </div>
                        ))}
                    </Carousel>
                    <div className="text-center"><Cta label={data.cta_label} url={data.cta_url} className="mt-4" /></div>
                </div>
            </section>
        ) : null}
        </>
    );
}

function SpotlightQuote({ item }) {
    return (
        <figure className="nt-spotlight__card on-dark nt-fade-in">
            <figcaption>
                <strong>{item.name}</strong>
                {item.role ? <span>{item.role}</span> : null}
            </figcaption>
            <blockquote>“{item.quote}”</blockquote>
        </figure>
    );
}

export function Testimonials({ data, page }) {
    const items = data.items ?? [];
    const layout = data.layout || 'quotes';
    let body;
    if (layout === 'spotlight' && items.length) {
        return <Spotlight data={data} items={items} responsive={page?.path?.split('/')[0] === 'partnerships'} />;
    } else if (layout === 'portrait') {
        body = (
            <Carousel label={data.heading || 'Testimonials'} slideWidth="min(60rem, 92%)">
                {items.map((item, i) => (
                    <figure className="nt-portrait-quote m-0" key={i}>
                        {item.image ? <Media image={item.image} sizes="(min-width: 992px) 30vw, 90vw" alt="" className="nt-portrait-quote__media" /> : null}
                        <div className="nt-portrait-quote__box on-dark">
                            <Icon name="quote" />
                            <blockquote>{item.quote}</blockquote>
                            <figcaption>
                                <strong>{item.name}</strong>
                                {item.role ? <span>{item.role}</span> : null}
                            </figcaption>
                        </div>
                    </figure>
                ))}
            </Carousel>
        );
    } else {
        body = (
            <Carousel label={data.heading || 'Testimonials'} slideWidth="min(36rem, 88%)">
                {items.map((item, i) => (
                    <figure className="nt-quote m-0" key={i}>
                        <blockquote>“{item.quote}”</blockquote>
                        <figcaption className="d-flex align-items-center gap-3">
                            {item.image ? <Media image={item.image} sizes="64px" alt="" style={{ width: '3.5rem', height: '3.5rem', borderRadius: '50%' }} /> : null}
                            <span>
                                <strong className="d-block">{item.name}</strong>
                                {item.role ? <span className="small">{item.role}</span> : null}
                            </span>
                        </figcaption>
                    </figure>
                ))}
            </Carousel>
        );
    }
    return (
        <section className={`nt-section ${toneClass(data.tone)}`}>
            <div className="container">
                <SplitHead data={data} />
                {body}
            </div>
        </section>
    );
}

export function GallerySection({ data }) {
    const gallery = data.gallery;
    if (!gallery) return null;
    return (
        <section className="nt-section">
            <div className="container">
                {data.heading ? <h2 className="nt-h2">{data.heading}</h2> : null}
                {gallery.description ? <p className="nt-lead mb-4">{gallery.description}</p> : null}
                {data.show_tour ? (
                    <div className="mb-5">
                        <TourEmbed gallery={gallery} />
                    </div>
                ) : null}
                <GalleryGrid items={gallery.items} />
            </div>
        </section>
    );
}

export function VideoSection({ data }) {
    return (
        <section className="nt-section">
            <div className="container">
                {data.heading ? <h2 className="nt-h2">{data.heading}</h2> : null}
                <figure className="m-0">
                    <VideoEmbed url={data.video_url} poster={data.poster} title={data.heading || data.caption} />
                    {data.caption ? <figcaption className="mt-2 small">{data.caption}</figcaption> : null}
                </figure>
            </div>
        </section>
    );
}

const formatSize = (bytes) => (bytes ? (bytes > 1048576 ? `${(bytes / 1048576).toFixed(1)} MB` : `${Math.max(1, Math.round(bytes / 1024))} KB`) : null);

export function Documents({ data }) {
    const items = (data.items ?? []).map((item) => ({
        title: item.title,
        url: item.file?.url || item.url,
        meta: item.meta || (item.file ? ['PDF', formatSize(item.file.size)].filter(Boolean).join(' · ') : null),
        isFile: Boolean(item.file?.url),
    }));

    const docLink = (item) =>
        item.url ? (
            <a href={item.url} className="nt-doc-link" {...(item.isFile ? { target: '_blank', rel: 'noopener' } : {})}>
                View PDF<span className="visually-hidden">: {item.title}</span>
            </a>
        ) : (
            <span className="nt-doc-link nt-doc-link--soon">Coming soon</span>
        );

    if (data.layout === 'list') {
        return (
            <section className={`nt-section ${toneClass(data.tone)}`}>
                <div className="container">
                    <div className="row g-5 align-items-start">
                        <div className="col-lg-5">
                            <SectionHeading eyebrow={data.eyebrow} heading={data.heading} />
                            {data.body ? <p className="nt-doc-intro">{data.body}</p> : null}
                        </div>
                        <div className="col-lg-6 offset-lg-1">
                            <ul className="nt-doc-rows">
                                {items.map((item, i) => (
                                    <li key={i}>
                                        <span className="nt-doc-rows__title">
                                            {item.title}
                                            {item.meta ? <small className="d-block text-muted">{item.meta}</small> : null}
                                        </span>
                                        {docLink(item)}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
        );
    }

    return (
        <section className={`nt-section ${toneClass(data.tone)}`}>
            <div className="container">
                {data.heading || data.body || data.eyebrow ? <SplitHead data={data} /> : null}
                <ul className="nt-doc-grid nt-stagger">
                    {items.map((item, i) => (
                        <li key={i}>
                            <span className="nt-doc-grid__title">{item.title}</span>
                            {item.meta ? <span className="nt-doc-grid__meta">{item.meta}</span> : null}
                            {docLink(item)}
                        </li>
                    ))}
                </ul>
            </div>
        </section>
    );
}

export function CtaBand({ data }) {
    return (
        <section className={`nt-cta-band ${data.tone && data.tone !== 'purple' ? `nt-cta-band--${data.tone}` : ''}`}>
            <div className="container">
                <div>
                    <h2>{data.heading}</h2>
                    {data.body ? <p className="m-0">{data.body}</p> : null}
                </div>
                <Cta label={data.cta_label} url={data.cta_url} />
            </div>
        </section>
    );
}

export function EnquiryFormSection({ data, anchor }) {
    const { site } = usePage().props;
    const settings = site?.settings ?? {};
    return (
        <section className="nt-section" aria-labelledby={`${anchor || 'enquiry'}-heading`}>
            <div className="container">
                <div className="row g-5">
                    <div className={data.show_contact_details ? 'col-lg-7' : 'col-lg-8 mx-auto'}>
                        <h2 className="nt-h2" id={`${anchor || 'enquiry'}-heading`}>
                            {data.heading || 'Send us an enquiry'}
                        </h2>
                        {data.body ? <p className="mb-4">{data.body}</p> : null}
                        <EnquiryForm type={data.form_type || 'contact'} programs={data.programs ?? []} />
                    </div>
                    {data.show_contact_details ? (
                        <aside className="col-lg-4 offset-lg-1" aria-label="Contact details">
                            <div className="nt-quote">
                                {settings.address ? (
                                    <>
                                        <h3 className="h6 text-uppercase">{settings.address_title || 'Address'}</h3>
                                        <address>{settings.address}</address>
                                    </>
                                ) : null}
                                {settings.phones?.length ? (
                                    <>
                                        <h3 className="h6 text-uppercase mt-4">Phone</h3>
                                        {settings.phones.map((p) => (
                                            <a key={p.number} className="d-block" href={`tel:${p.number.replace(/[^+\d]/g, '')}`}>
                                                {p.number}
                                            </a>
                                        ))}
                                    </>
                                ) : null}
                                {settings.email ? (
                                    <>
                                        <h3 className="h6 text-uppercase mt-4">Email</h3>
                                        <a href={`mailto:${settings.email}`}>{settings.email}</a>
                                    </>
                                ) : null}
                            </div>
                        </aside>
                    ) : null}
                </div>
            </div>
        </section>
    );
}
