import usePage from '../../../hooks/usePage';
import { useEffect, useId, useRef, useState } from 'react';
import Carousel from '../../Carousel';
import Tabs from '../../Tabs';
import useStackCover from '../../../hooks/useStackCover';
import Icon, { ArrowDot } from '../../Icon';
import { Media } from '../../ResponsiveImage';
import SmartLink from '../../SmartLink';
import { toEmbedUrl } from '../../../lib/embed';
import { Cta, SectionHeading } from '../Common';

export const toneClass = (tone, fallback = 'white') => {
    const t = tone || fallback;
    return `nt-tone-${t} ${t === 'purple' ? 'on-dark' : ''}`;
};

export function SplitHead({ data, id, className = '', children }) {
    if (!data.heading && !data.eyebrow && !data.body) return null;
    return (
        <div className={`nt-split-head ${className}`}>
            <div>
                <SectionHeading eyebrow={data.eyebrow} heading={data.heading} id={id} className="mb-0" />
            </div>
            {data.body || (data.cta_label && data.cta_url) || children ? (
                <div className="nt-split-head__aside">
                    {data.body
                        ? data.body.split(/\n\s*\n/).map((para, i) => (
                              <p key={i} className="nt-lead">
                                  {para}
                              </p>
                          ))
                        : null}
                    <Cta label={data.cta_label} url={data.cta_url} variant={data.tone === 'purple' ? 'light' : ''} className="mt-2" />
                    {children}
                </div>
            ) : null}
        </div>
    );
}

export function Subnav({ data }) {
    const { url } = usePage();
    const current = (url || '/').split('?')[0];
    const items = data.items ?? [];
    const activeIndex = items.reduce((best, item, i) => (current === item.url || current.startsWith(item.url + '/') ? (best === -1 || item.url.length > items[best].url.length ? i : best) : best), -1);
    const listRef = useRef(null);

    useEffect(() => {
        listRef.current?.querySelector('[aria-current]')?.scrollIntoView?.({ block: 'nearest', inline: 'center' });
    }, []);

    return (
        <nav className="nt-subnav" aria-label="In this section">
            <div className="container">
                <ul ref={listRef}>
                    {items.map((item, i) => (
                        <li key={item.url}>
                            <SmartLink href={item.url} aria-current={i === activeIndex ? 'page' : undefined}>
                                {item.label}
                            </SmartLink>
                        </li>
                    ))}
                </ul>
            </div>
        </nav>
    );
}

export function ImageStatement({ data }) {
    return (
        <section className={`nt-statement nt-statement--${data.position || 'right'}`}>
            <Media image={data.image} sizes="100vw" className="nt-statement__media" />
            <div className="container">
                <div className="nt-statement__box on-dark">
                    {data.eyebrow ? <p className="nt-pill">{data.eyebrow}</p> : null}
                    {data.heading ? <h2>{data.heading}</h2> : null}
                    {data.body ? <p className="mb-0">{data.body}</p> : null}
                </div>
            </div>
        </section>
    );
}

export function ValueList({ data }) {
    return (
        <section className="nt-section">
            <div className="container">
                <div className="row g-5">
                    <div className="col-lg-5">
                        <SectionHeading eyebrow={data.eyebrow} heading={data.heading} />
                        {data.body ? <p className="nt-lead">{data.body}</p> : null}
                    </div>
                    <div className="col-lg-7">
                        <ul className="nt-value-list">
                            {(data.items ?? []).map((item, i) => (
                                <li key={i}>
                                    <Icon name={item.icon || 'people'} strokeWidth={1.1} />
                                    <div>
                                        <h3>{item.title}</h3>
                                        {item.text ? <p>{item.text}</p> : null}
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    );
}

export function StatCards({ data }) {
    return (
        <section className={`nt-section ${toneClass(data.tone)}`}>
            <div className="container">
                {data.heading || data.eyebrow ? <SectionHeading eyebrow={data.eyebrow} heading={data.heading} /> : null}
                {data.body ? <p className="nt-lead mb-5" style={{ maxWidth: '48rem' }}>{data.body}</p> : null}
                <div className={`nt-stat-cards nt-stagger${data.layout === 'lavender' ? ' nt-stat-cards--lavender' : ''}`}>
                    {(data.items ?? []).map((item, i) => (
                        <div className="nt-stat-card" key={i}>
                            {item.icon ? <Icon name={item.icon} strokeWidth={1.1} /> : null}
                            <strong data-countup={item.value}>{item.value}</strong>
                            <span className="nt-stat-card__label">{item.label}</span>
                            {item.text ? <p>{item.text}</p> : null}
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}

export function Timeline({ data }) {
    const items = data.items ?? [];
    const [index, setIndex] = useState(0);
    const item = items[index];
    if (!item) return null;

    return (
        <section className="nt-section nt-tone-gradient nt-timeline">
            <div className="container">
                <div className="nt-split-head">
                    <div>
                        <SectionHeading eyebrow={data.eyebrow} heading={data.heading} className="mb-0" />
                    </div>
                    {data.body ? (
                        <div className="nt-split-head__aside">
                            <p className="nt-lead">{data.body}</p>
                        </div>
                    ) : null}
                </div>
                <div className="nt-timeline__panel" aria-live="polite">
                    <div className="nt-timeline__text" key={index}>
                        <h3>{item.title || item.year}</h3>
                        {item.text ? <p>{item.text}</p> : null}
                        <div className="nt-carousel__arrows mt-4">
                            <button type="button" className="nt-carousel__arrow" disabled={index === 0} onClick={() => setIndex(index - 1)} aria-label="Previous year">
                                <Icon name="arrowLeft" strokeWidth={2} />
                            </button>
                            <button type="button" className="nt-carousel__arrow" disabled={index === items.length - 1} onClick={() => setIndex(index + 1)} aria-label="Next year">
                                <Icon name="arrowRight" strokeWidth={2} />
                            </button>
                        </div>
                    </div>
                    {item.image ? <Media image={item.image} sizes="(min-width: 992px) 40vw, 100vw" className="nt-timeline__media" key={`m${index}`} /> : null}
                </div>
                <ol className="nt-timeline__axis" style={{ '--progress': items.length > 1 ? index / (items.length - 1) : 1 }}>
                    {items.map((it, i) => (
                        <li key={i}>
                            <button type="button" aria-current={i === index ? 'step' : undefined} onClick={() => setIndex(i)}>
                                {it.year}
                            </button>
                        </li>
                    ))}
                </ol>
            </div>
        </section>
    );
}

function PlayButton({ onClick, label }) {
    return (
        <button type="button" className="nt-play" onClick={onClick} aria-label={label}>
            <Icon name="play" />
        </button>
    );
}

export function VideoFeature({ data }) {
    const [playing, setPlaying] = useState(false);
    const embed = data.video_url ? toEmbedUrl(data.video_url) : null;
    const flip = data.flip_image ? ' nt-media--flip' : '';
    const media = playing && embed ? (
        <iframe src={embed} title={data.heading || 'Video'} allow="autoplay; encrypted-media; picture-in-picture" allowFullScreen className="nt-video-frame" />
    ) : (
        <>
            <Media image={data.image} sizes="(min-width: 992px) 60vw, 100vw" className={`nt-feature__media${flip}`} />
            {embed ? <PlayButton onClick={() => setPlaying(true)} label={`Play video${data.heading ? `: ${data.heading}` : ''}`} /> : null}
        </>
    );

    if (data.layout === 'overlay') {
        if (playing && embed) {
            return (
                <section className="nt-quote-feature nt-quote-feature--playing">
                    <iframe src={embed} title={data.heading || 'Video'} allow="autoplay; encrypted-media; picture-in-picture" allowFullScreen className="nt-video-frame" />
                </section>
            );
        }
        return (
            <section className="nt-quote-feature on-dark">
                <Media image={data.image} sizes="100vw" className={`nt-feature__media${flip}`} />
                <div className="container nt-quote-feature__inner">
                    <div className="nt-quote-feature__head">
                        {embed ? (
                            <button type="button" className="nt-quote-feature__play" onClick={() => setPlaying(true)} aria-label={`Play video${data.heading ? `: ${data.heading}` : ''}`}>
                                <Icon name="play" />
                            </button>
                        ) : null}
                        {data.eyebrow ? <p className="nt-pill">{data.eyebrow}</p> : null}
                        {data.heading ? <h2>{data.heading}</h2> : null}
                    </div>
                    <blockquote>
                        {data.body ? <p>{data.body}</p> : null}
                        {data.name ? (
                            <footer>
                                <strong>{data.name}</strong>
                                {data.role ? <span>{data.role}</span> : null}
                            </footer>
                        ) : null}
                    </blockquote>
                </div>
            </section>
        );
    }

    return (
        <section className="nt-feature">
            <div className="nt-feature__visual">{media}</div>
            <div className="nt-feature__panel on-dark">
                {data.eyebrow ? <p className="nt-pill">{data.eyebrow}</p> : null}
                {data.heading ? <h2>{data.heading}</h2> : null}
                {data.body ? <p>{data.body}</p> : null}
                {data.name ? (
                    <p className="mt-3 mb-0">
                        <strong>{data.name}</strong>
                        {data.role ? <span className="d-block small">{data.role}</span> : null}
                    </p>
                ) : null}
            </div>
        </section>
    );
}

export function LeadersGrid({ data }) {
    const people = data.people ?? [];
    if (!people.length) return null;
    if (data.layout === 'contacts') {
        return (
            <section className="nt-section">
                <div className="container">
                    {data.eyebrow ? <p className="nt-pill">{data.eyebrow}</p> : null}
                    {data.heading ? <h2 className="nt-h3 mb-4">{data.heading}</h2> : null}
                    {data.body ? <p className="mb-4">{data.body}</p> : null}
                    <Carousel label={data.heading || 'Team'} slideWidth="min(14.5rem, 70%)" gap="1rem">
                        {people.map((p) => (
                            <article className="nt-contact-card" key={p.id}>
                                <span className="nt-contact-card__icon"><Icon name="people" strokeWidth={1.6} /></span>
                                <h3><SmartLink href={p.url} className="stretched-link">{p.name}</SmartLink></h3>
                                {p.designation ? <p>{p.designation}</p> : null}
                                {p.email ? <p className="nt-contact-card__mail">{p.email}</p> : null}
                            </article>
                        ))}
                    </Carousel>
                </div>
            </section>
        );
    }
    return (
        <section className="nt-section">
            <div className="container">
                <SplitHead data={data} />
                <div className="nt-leaders nt-stagger">
                    {people.map((p) => (
                        <article className="nt-leader-card" key={p.id}>
                            <Media image={p.photo} sizes="(min-width: 992px) 30vw, (min-width: 576px) 45vw, 90vw" alt="" className="nt-leader-card__media" />
                            <h3>{p.name}</h3>
                            {p.summary || p.designation ? <p>{p.summary || p.designation}</p> : null}
                            <SmartLink href={p.url} className="nt-link-arrow">
                                Know More<span className="visually-hidden"> about {p.name}</span> <ArrowDot />
                            </SmartLink>
                        </article>
                    ))}
                </div>
            </div>
        </section>
    );
}

export function Achievements({ data }) {
    const ranks = data.layout === 'ranks';
    const rows = useRef([]);
    useStackCover(rows, ranks, undefined, (data.items ?? []).length);
    return (
        <section className={`nt-section ${toneClass(data.tone)}`}>
            <div className="container">
                <div className="row g-5">
                    <div className={ranks ? 'col-lg-5' : 'col-lg-4'}>
                        <div className={ranks ? 'nt-achievements__head' : undefined}>
                            <SectionHeading eyebrow={data.eyebrow} heading={data.heading} />
                        </div>
                    </div>
                    <div className={ranks ? 'col-lg-7' : 'col-lg-8'}>
                        {data.body ? <p className="nt-lead mb-4">{data.body}</p> : null}
                        <ol className={`nt-achievements ${ranks ? 'nt-achievements--ranks' : ''}`}>
                            {(data.items ?? []).map((item, i) => (
                                <li key={i} ref={(el) => (rows.current[i] = el)} style={{ '--i': i }}>
                                    <div className="nt-achievements__value">
                                        {ranks && item.logo ? <img src={item.logo.src} alt={item.logo.alt} width={item.logo.width} height={item.logo.height} loading="lazy" /> : null}
                                        <strong data-countup={/^\d{4}$/.test(item.value) ? undefined : item.value}>{item.value}</strong>
                                    </div>
                                    <div className="nt-achievements__text">
                                        <h3>{item.title}</h3>
                                        {item.text ? <p>{item.text}</p> : null}
                                    </div>
                                    <Icon name={item.icon || 'award'} strokeWidth={1.1} className="nt-achievements__icon" />
                                </li>
                            ))}
                        </ol>
                    </div>
                </div>
            </div>
        </section>
    );
}

export function Pillars({ data }) {
    const items = data.items ?? [];
    const [active, setActive] = useState(0);
    const refs = useRef([]);
    const uid = useId();

    const split = data.layout === 'split';
    const pill = data.layout === 'pill';
    const stacked = split || pill;

    useStackCover(refs, stacked, setActive, items.length);

    useEffect(() => {
        if (stacked && window.matchMedia('(min-width: 992px)').matches) return undefined;
        if (!('IntersectionObserver' in window)) return undefined;
        const io = new IntersectionObserver(
            (entries) => entries.forEach((e) => e.isIntersecting && setActive(Number(e.target.dataset.index))),
            { rootMargin: '-45% 0px -45% 0px' },
        );
        refs.current.forEach((el) => el && io.observe(el));
        return () => io.disconnect();
    }, [items.length, stacked]);

    return (
        <section className={`nt-section nt-pillars ${split || pill ? 'nt-pillars--split' : ''} ${pill ? 'nt-pillars--pill' : ''}`}>
            <div className="container">
                {split ? <SplitHead data={data} /> : null}
                <div className="row g-4">
                    <div className={split ? 'col-lg-2' : pill ? 'col-lg-3 col-xl-2' : 'col-lg-3'}>
                        <div className="nt-pillars__nav">
                            {!split && data.eyebrow ? <p className="nt-pill">{data.eyebrow}</p> : null}
                            {!split && data.heading ? <h2 className={pill ? 'visually-hidden' : 'nt-h3'}>{data.heading}</h2> : null}
                            {!split && data.body ? <p className="nt-pillars__body">{data.body}</p> : null}
                            <ol>
                                {items.map((item, i) => (
                                    <li key={i}>
                                        <a href={`#${uid}-${i}`} aria-current={i === active ? 'true' : undefined} onClick={(e) => {
                                            e.preventDefault();
                                            refs.current[i]?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                        }}>
                                            {item.title}
                                        </a>
                                    </li>
                                ))}
                            </ol>
                        </div>
                    </div>
                    <div className={split ? 'col-lg-10' : pill ? 'col-lg-9 offset-xl-1' : 'col-lg-9'}>
                        {items.map((item, i) => (
                            <article key={i} id={`${uid}-${i}`} data-index={i} ref={(el) => (refs.current[i] = el)} className={`nt-pillar ${i === active ? 'is-active' : ''}`} style={{ '--i': i }}>
                                <div className="nt-pillar__text">
                                    <h3>{item.title}</h3>
                                    {item.text ? <p>{item.text}</p> : null}
                                </div>
                                {item.image ? <Media image={item.image} sizes="(min-width: 992px) 30vw, 100vw" className="nt-pillar__media" /> : null}
                            </article>
                        ))}
                    </div>
                </div>
            </div>
        </section>
    );
}

export function DateTables({ data }) {
    return (
        <section className="nt-section">
            <div className="container">
                <SplitHead data={data} />
                <div className="nt-date-tables nt-stagger">
                    {(data.columns ?? []).map((col, i) => (
                        <div key={i} className={`nt-date-table ${col.highlight ? 'is-highlight' : ''}`}>
                            <Icon name={col.icon || 'document'} strokeWidth={1.1} />
                            <h3>{col.title}</h3>
                            <dl>
                                {(col.rows ?? []).map((row, r) => (
                                    <div key={r}>
                                        <dt>{row.label}</dt>
                                        <dd>{row.value}</dd>
                                    </div>
                                ))}
                            </dl>
                        </div>
                    ))}
                </div>
                {data.footnote ? <p className="nt-footnote">{data.footnote}</p> : null}
            </div>
        </section>
    );
}

export function Eligibility({ data }) {
    return (
        <section className="nt-section">
            <div className="container">
                <SplitHead data={data} />
                {data.criteria?.length ? (
                    <ol className="nt-criteria nt-stagger">
                        {data.criteria.map((c, i) => (
                            <li key={i}>
                                <span>{String(i + 1).padStart(2, '0')}</span>
                                <p>{c.text}</p>
                            </li>
                        ))}
                    </ol>
                ) : null}
                {data.items?.length ? (
                    <>
                        {data.list_heading ? <h3 className="nt-h3 mt-5 mb-3">{data.list_heading}</h3> : null}
                        <ul className="nt-icon-list nt-stagger">
                            {data.items.map((item, i) => (
                                <li key={i}>
                                    <Icon name={item.icon || 'gear'} strokeWidth={1.1} />
                                    {item.text}
                                </li>
                            ))}
                        </ul>
                    </>
                ) : null}
            </div>
        </section>
    );
}

export function ChecklistCards({ data }) {
    return (
        <section className={`nt-section ${toneClass(data.tone)}`}>
            <div className="container">
                <SplitHead data={data} />
                <div className="nt-checklists nt-stagger">
                    {(data.cards ?? []).map((card, i) => (
                        <div key={i} className="nt-checklist">
                            <Icon name={card.icon || 'document'} strokeWidth={1.1} />
                            <h3>{card.title}</h3>
                            <ul>
                                {(card.items ?? []).map((item, j) => (
                                    <li key={j}>
                                        <Icon name="check" strokeWidth={1.4} />
                                        {item.text}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}

export function Fees({ data }) {
    return (
        <section className="nt-section">
            <div className="container">
                <SplitHead data={data} />
                <div className="nt-fees">
                    <div className="nt-fees__table">
                        {data.fee_label ? <p className="nt-fees__label">{data.fee_label}</p> : null}
                        {data.fee_amount ? <p className="nt-fees__amount">{data.fee_amount}</p> : null}
                        <dl>
                            {(data.rows ?? []).map((row, i) => (
                                <div key={i}>
                                    <dt>{row.label}</dt>
                                    <dd>{row.value}</dd>
                                </div>
                            ))}
                        </dl>
                    </div>
                    <div className="nt-fees__scholarships on-dark">
                        {data.scholarships_title ? <p className="nt-fees__label">{data.scholarships_title}</p> : null}
                        <div className="nt-fees__grid">
                            {(data.scholarships ?? []).map((s, i) => (
                                <div key={i}>
                                    <h3>{s.title}</h3>
                                    {s.text ? <p>{s.text}</p> : null}
                                </div>
                            ))}
                        </div>
                        {data.link_label && data.link_url ? (
                            <SmartLink href={data.link_url} className="nt-fees__link">
                                {data.link_label} <Icon name="arrowRight" strokeWidth={2} />
                            </SmartLink>
                        ) : null}
                    </div>
                </div>
                {data.notes?.length ? (
                    <div className="nt-fees__notes nt-stagger">
                        {data.notes.map((n, i) => (
                            <div key={i}>
                                <Icon name={n.icon || 'people'} strokeWidth={1.1} />
                                <h3>{n.title}</h3>
                                {n.text ? <p>{n.text}</p> : null}
                            </div>
                        ))}
                    </div>
                ) : null}
            </div>
        </section>
    );
}

export function FeatureList({ data }) {
    const tabs = data.tabs ?? [];
    const [active, setActive] = useState(0);
    const id = useId();
    const tab = tabs[active];
    if (!tab) return null;
    if (data.layout === 'band') {
        return (
            <section className="nt-logo-band nt-name-band" aria-label={data.heading || 'Partners'}>
                <ul className="nt-name-band__list list-unstyled m-0 nt-stagger">
                    {(tabs[0].items ?? []).map((item, i) => (
                        <li key={i}>{item.text}</li>
                    ))}
                </ul>
            </section>
        );
    }
    return (
        <section className="nt-section">
            <div className="container">
                {data.eyebrow ? <p className="nt-pill">{data.eyebrow}</p> : null}
                {data.heading ? <h2 className={data.eyebrow || data.body ? 'nt-h2' : 'nt-h3 mb-4'}>{data.heading}</h2> : null}
                {data.body ? <p className="nt-lead mb-4">{data.body}</p> : null}
                {tabs.length > 1 ? (
                    <div className="nt-tabs nt-tabs--compact" role="tablist" aria-label={data.heading || 'Options'}>
                        {tabs.map((t, i) => (
                            <button key={i} type="button" role="tab" id={`${id}-t${i}`} aria-controls={`${id}-p`} aria-selected={i === active} tabIndex={i === active ? 0 : -1} onClick={() => setActive(i)}>
                                {t.label}
                            </button>
                        ))}
                    </div>
                ) : null}
                <div className="nt-feature-list" role={tabs.length > 1 ? 'tabpanel' : undefined} id={`${id}-p`} aria-labelledby={tabs.length > 1 ? `${id}-t${active}` : undefined}>
                    <ul key={active}>
                        {(tab.items ?? []).map((item, i) => (
                            <li key={i}>
                                <Icon name={item.icon || 'check'} strokeWidth={1.1} />
                                {item.text}
                            </li>
                        ))}
                    </ul>
                </div>
            </div>
        </section>
    );
}

export function ContactCards({ data }) {
    const people = data.layout === 'people';
    return (
        <section className="nt-section">
            <div className="container">
                <SplitHead data={data} />
                <Carousel label={data.heading || 'Contacts'} slideWidth={people ? 'min(22.1rem, 85%)' : 'min(15.5rem, 80%)'} gap={people ? '2.5rem' : '1rem'}>
                    {(data.items ?? []).map((c, i) => (
                        <article className={`nt-contact-card ${people ? 'nt-contact-card--person' : ''}`} key={i}>
                            <span className="nt-contact-card__icon"><Icon name={people ? 'user' : 'pin'} strokeWidth={1.6} /></span>
                            {c.region ? <p className="nt-contact-card__region">{c.region}</p> : null}
                            <h3>{c.name}</h3>
                            {c.phone ? <a href={`tel:${c.phone.replace(/[^+\d]/g, '')}`}>{c.phone}</a> : null}
                            {c.email ? <a href={`mailto:${c.email}`}>{c.email}</a> : null}
                        </article>
                    ))}
                </Carousel>
            </div>
        </section>
    );
}

export function ContactBand({ data }) {
    return (
        <section className="nt-section nt-tone-lavender nt-contact-band">
            <div className="container">
                <div className="row g-4 align-items-end">
                    <div className="col-lg-8">
                        <h2 className="nt-h2">{data.heading}</h2>
                        {data.body ? <p className="mb-0">{data.body}</p> : null}
                    </div>
                    <div className="col-lg-4 text-lg-end">
                        <Cta label={data.cta_label} url={data.cta_url} />
                    </div>
                </div>
                {data.contacts?.length ? (
                    <>
                        {data.contacts_label ? <p className="nt-contact-band__label">{data.contacts_label}</p> : null}
                        <ul className="nt-contact-band__people">
                            {data.contacts.map((c, i) => (
                                <li key={i}>
                                    <span>
                                        <strong>{c.name}</strong> {c.role}
                                    </span>
                                    {c.email ? <a href={`mailto:${c.email}`}>{c.email}</a> : null}
                                </li>
                            ))}
                        </ul>
                    </>
                ) : null}
            </div>
        </section>
    );
}

export function AccordionMedia({ data }) {
    const items = data.items ?? [];
    const [open, setOpen] = useState(Math.max(0, items.length - 1));
    const id = useId();
    return (
        <section className="nt-section nt-acc-section">
            <div className="container">
                {data.eyebrow ? <p className="nt-pill d-table mx-auto">{data.eyebrow}</p> : null}
                {data.heading ? <h2 className="nt-h2 text-center mb-5">{data.heading}</h2> : null}
                <div className="nt-acc-media">
                    {items.map((item, i) => (
                        <div key={i} className={`nt-acc-media__item ${i === open ? 'is-open' : ''}`}>
                            <h3 className={i === open ? 'visually-hidden' : ''}>
                                <button type="button" aria-expanded={i === open} aria-controls={`${id}-${i}`} onClick={() => setOpen(i === open ? -1 : i)}>
                                    {item.title}
                                </button>
                            </h3>
                            <div id={`${id}-${i}`} className="nt-acc-media__panel" hidden={i !== open}>
                                {item.image ? <Media image={item.image} sizes="(min-width: 992px) 25rem, 100vw" /> : null}
                                <div>
                                    <p className="nt-acc-media__title">{item.title}</p>
                                    {item.text ? <p className="mb-0">{item.text}</p> : null}
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}

export function NumberedList({ data }) {
    return (
        <section className="nt-section">
            <div className="container">
                <SplitHead data={data} />
                {data.list_label ? <p className="nt-numbered__label">{data.list_label}</p> : null}
                <ol className="nt-numbered nt-stagger">
                    {(data.items ?? []).map((item, i) => (
                        <li key={i}>
                            <span>{String(i + 1).padStart(2, '0')}</span>
                            {item.text}
                        </li>
                    ))}
                </ol>
                {data.footer ? <p className="mt-4 mb-0">{data.footer}</p> : null}
            </div>
        </section>
    );
}

export function StoryTabs({ data }) {
    const tabs = (data.tabs ?? []).filter((tab) => tab.cards?.length);
    if (!tabs.length) return null;
    const linkLabel = data.card_link_label || 'Read story';
    const carousel = (tab) => (
        <Carousel label={tab.label} slideWidth="min(54.7rem, 90%)" gap="2rem" className="nt-carousel--research">
            {tab.cards.map((card, i) => (
                <article className="nt-research-card" key={i}>
                    <Media image={card.image} sizes="(min-width: 992px) 390px, 90vw" className="nt-research-card__media" alt="" />
                    <div className="nt-research-card__body">
                        {card.date ? (
                            <p className="nt-research-card__date">
                                <Icon name="calendar" strokeWidth={1.4} />
                                {card.date}
                            </p>
                        ) : null}
                        <h3>{card.title}</h3>
                        {card.subtitle ? (
                            <div className="nt-research-card__item">
                                <h4>{card.subtitle}</h4>
                                {card.text ? <p>{card.text}</p> : null}
                            </div>
                        ) : card.text ? (
                            <p className="nt-research-card__text">{card.text}</p>
                        ) : null}
                        {card.link_url ? (
                            <SmartLink href={card.link_url} className="nt-link-arrow">
                                {linkLabel}
                                <span className="visually-hidden">: {card.title}</span> <ArrowDot />
                            </SmartLink>
                        ) : null}
                    </div>
                </article>
            ))}
        </Carousel>
    );
    return (
        <section className={`nt-section nt-divider nt-story-tabs ${toneClass(data.tone)}`}>
            <div className="container">
                <SplitHead data={data} />
                {tabs.length > 1 ? (
                    <Tabs label={data.heading || 'Tabs'} className="nt-story-tabs__tabs" tabs={tabs.map((tab, i) => ({ key: i, label: tab.label, content: carousel(tab) }))} />
                ) : (
                    carousel(tabs[0])
                )}
            </div>
        </section>
    );
}

export function CardTabs({ data }) {
    const tabs = data.tabs ?? [];
    const [active, setActive] = useState(0);
    const id = useId();
    return (
        <section className={`nt-section nt-card-tabs ${toneClass(data.tone)}`}>
            <div className="container">
                <SectionHeading eyebrow={data.eyebrow} heading={data.heading} />
                <div className="nt-card-tabs__intro">
                    {data.body ? <p className="mb-0">{data.body}</p> : <span />}
                    <Cta label={data.cta_label} url={data.cta_url} />
                </div>
                {tabs.length > 1 ? (
                    <div className="nt-tabs nt-card-tabs__tabs" role="tablist" aria-label={data.heading || 'Tabs'}>
                        {tabs.map((t, i) => (
                            <button key={i} type="button" role="tab" id={`${id}-t${i}`} aria-controls={`${id}-p`} aria-selected={i === active} tabIndex={i === active ? 0 : -1} onClick={() => setActive(i)}>
                                {t.label}
                            </button>
                        ))}
                    </div>
                ) : null}
                <div role={tabs.length > 1 ? 'tabpanel' : undefined} id={`${id}-p`} aria-labelledby={tabs.length > 1 ? `${id}-t${active}` : undefined} key={active} className="nt-fade-in">
                    <div className="nt-joined-cards" style={{ '--cols': Math.min(4, Math.max(1, tabs[active]?.cards?.length ?? 3)) }}>
                        {(tabs[active]?.cards ?? []).map((card, i) => (
                            <div className="nt-joined-card" key={i}>
                                <Icon name={card.icon || 'people'} strokeWidth={1.1} />
                                <h3>{card.title}</h3>
                                {card.text ? <p className="nt-pre-line">{card.text}</p> : null}
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </section>
    );
}

export function TabsMedia({ data }) {
    const tabs = data.tabs ?? [];
    const [active, setActive] = useState(0);
    const id = useId();
    const tab = tabs[active];
    if (!tab) return null;
    const card = data.layout === 'card';
    if (card) {
        const links = tab.links ?? [];
        const button = links.length ? links[links.length - 1] : null;
        const textLinks = links.slice(0, -1);
        return (
            <section className={`nt-section nt-tabs-media nt-tabs-media--card ${data.tabs_align === 'start' ? 'nt-tabs-media--start' : ''} ${toneClass(data.tone, 'gradient')}`}>
                <div className="container">
                    <SplitHead data={data} />
                </div>
                <div className="nt-tabs-bar">
                    <div className="container">
                        <div className="nt-tabs nt-tabs--fill" role="tablist" aria-label={data.heading || 'Tabs'}>
                            {tabs.map((t, i) => (
                                <button key={i} type="button" role="tab" id={`${id}-t${i}`} aria-controls={`${id}-p`} aria-selected={i === active} tabIndex={i === active ? 0 : -1} onClick={() => setActive(i)}>
                                    {t.label}
                                </button>
                            ))}
                        </div>
                    </div>
                </div>
                <div className="container">
                    <div className="nt-tab-card nt-fade-in" role="tabpanel" id={`${id}-p`} aria-labelledby={`${id}-t${active}`} key={active}>
                        {tab.image ? <Media image={tab.image} sizes="(min-width: 992px) 690px, 100vw" className="nt-tab-card__img" /> : null}
                        <div className="nt-tab-card__body">
                            {tab.title ? <h3>{tab.title}</h3> : null}
                            {tab.text ? <p className="nt-tab-card__text">{tab.text}</p> : null}
                            {tab.points?.length || textLinks.length ? (
                                <ul className="nt-tab-card__points">
                                    {tab.points?.map((p, i) => (
                                        <li key={`p${i}`}>
                                            {p.title ? <strong>{p.title}</strong> : null}
                                            {p.text ? <p>{p.text}</p> : null}
                                        </li>
                                    ))}
                                    {textLinks.map((l, i) => (
                                        <li key={`l${i}`}>
                                            <SmartLink href={l.url}>{l.label}</SmartLink>
                                        </li>
                                    ))}
                                </ul>
                            ) : null}
                            {button ? (
                                <SmartLink href={button.url} className="nt-btn nt-tab-card__btn">
                                    {button.label}
                                </SmartLink>
                            ) : null}
                        </div>
                    </div>
                </div>
            </section>
        );
    }
    return (
        <section className={`nt-section nt-tabs-media ${toneClass(data.tone)}`}>
            <div className="container">
                <SplitHead data={data} />
            </div>
            <div className="nt-tabs-bar">
                <div className="container">
                    <div className="nt-tabs" role="tablist" aria-label={data.heading || 'Tabs'}>
                        {tabs.map((t, i) => (
                            <button key={i} type="button" role="tab" id={`${id}-t${i}`} aria-controls={`${id}-p`} aria-selected={i === active} tabIndex={i === active ? 0 : -1} onClick={() => setActive(i)}>
                                {t.label}
                            </button>
                        ))}
                    </div>
                </div>
            </div>
            <div className="container-fluid px-0">
                <div className="nt-tab-media nt-fade-in" role="tabpanel" id={`${id}-p`} aria-labelledby={`${id}-t${active}`} key={active}>
                    {tab.image ? <Media image={tab.image} sizes="(min-width: 992px) 45vw, 100vw" className="nt-tab-media__img" /> : null}
                    <div className="nt-tab-media__panel">
                        {tab.title ? <h3>{tab.title}</h3> : null}
                        {tab.text ? <p>{tab.text}</p> : null}
                        {tab.points?.map((p, i) => (
                            <div key={i} className="nt-tab-media__point">
                                {p.title ? <h4>{p.title}</h4> : null}
                                {p.text ? <p>{p.text}</p> : null}
                            </div>
                        ))}
                        {tab.links?.length ? (
                            <ul className="nt-tab-media__links">
                                {tab.links.map((l, i) => (
                                    <li key={i}>
                                        <SmartLink href={l.url}>{l.label}</SmartLink>
                                    </li>
                                ))}
                            </ul>
                        ) : null}
                    </div>
                </div>
            </div>
        </section>
    );
}

export function ImageList({ data }) {
    return (
        <section className="nt-image-list">
            <Media image={data.image} sizes="(min-width: 992px) 50vw, 100vw" className="nt-image-list__media" />
            <div className="nt-image-list__panel">
                {data.heading ? <h2 className="nt-h3">{data.heading}</h2> : null}
                {data.body ? <p className="nt-image-list__intro">{data.body}</p> : null}
                <ul>
                    {(data.items ?? []).map((item, i) => (
                        <li key={i}>
                            {item.title ? <h3>{item.title}</h3> : null}
                            {item.text ? <p>{item.text}</p> : null}
                        </li>
                    ))}
                </ul>
            </div>
        </section>
    );
}

export function ImageMosaic({ data }) {
    const images = (data.images ?? []).map((i) => i.image).filter(Boolean);
    const hasText = data.heading || data.body;
    const mosaic = (
        <div className={`nt-mosaic nt-mosaic--${images.length}${data.quote ? ' has-quote' : ''}`}>
            {images.map((img, i) => (
                <Media key={i} image={img} sizes="(min-width: 992px) 30vw, 50vw" />
            ))}
            {data.quote ? (
                <blockquote className="nt-mosaic__quote on-dark">
                    <p>“{data.quote}”</p>
                    {data.quote_name ? (
                        <footer>
                            <strong>{data.quote_name}</strong>
                            {data.quote_role ? <span>{data.quote_role}</span> : null}
                        </footer>
                    ) : null}
                </blockquote>
            ) : null}
        </div>
    );
    if (data.layout === 'strip') {
        return (
            <section className="nt-section">
                <div className="container">
                    <SplitHead data={data} />
                    <div className={`nt-mosaic-strip${data.quote ? ' has-quote' : ''}`}>
                        {images.slice(0, 2).map((img, i) => (
                            <Media key={i} image={img} sizes={i === 0 ? '(min-width: 992px) 360px, 100vw' : '(min-width: 992px) 900px, 100vw'} />
                        ))}
                        {data.quote ? (
                            <blockquote className="nt-mosaic__quote nt-mosaic-strip__quote on-dark">
                                <p>“{data.quote}”</p>
                                {data.quote_name ? (
                                    <footer>
                                        <strong>{data.quote_name}</strong>
                                        {data.quote_role ? <span>{data.quote_role}</span> : null}
                                    </footer>
                                ) : null}
                            </blockquote>
                        ) : null}
                    </div>
                </div>
            </section>
        );
    }
    return (
        <section className="nt-section">
            <div className="container">
                {hasText ? (
                    <div className="row g-5 align-items-center">
                        <div className="col-lg-4">
                            {data.eyebrow ? <p className="nt-pill">{data.eyebrow}</p> : null}
                            {data.heading ? <h2 className="nt-h2">{data.heading}</h2> : null}
                            {data.body ? <p>{data.body}</p> : null}
                        </div>
                        <div className="col-lg-8">{mosaic}</div>
                    </div>
                ) : (
                    mosaic
                )}
            </div>
        </section>
    );
}

export function ImageCarousel({ data }) {
    return (
        <section className="nt-section">
            <div className="container">
                {data.heading ? <h2 className="nt-h3">{data.heading}</h2> : null}
                <Carousel label={data.heading || 'Photos'} slideWidth="min(15rem, 70%)" gap="0.75rem">
                    {(data.images ?? []).map((item, i) => (
                        <figure className="m-0" key={i}>
                            <Media image={item.image} sizes="260px" style={{ aspectRatio: '4 / 3' }} />
                            {item.caption ? <figcaption className="small mt-2">{item.caption}</figcaption> : null}
                        </figure>
                    ))}
                </Carousel>
            </div>
        </section>
    );
}

export function PlacementTrends({ data }) {
    const items = data.items ?? [];
    const levels = [...new Set(items.map((i) => i.level))];
    const [level, setLevel] = useState(levels[0]);
    const programs = items.filter((i) => i.level === level);
    const [program, setProgram] = useState(programs[0]?.program);
    const current = programs.find((p) => p.program === program) ?? programs[0];
    const uid = useId();
    if (!current) return null;

    return (
        <section className="nt-section">
            <div className="container">
                {data.heading ? <h2 className="nt-h2 mb-4">{data.heading}</h2> : null}
                <div className="nt-selects">
                    <label className="visually-hidden" htmlFor={`${uid}-l`}>Program level</label>
                    <select id={`${uid}-l`} className="form-select" value={level} onChange={(e) => {
                        setLevel(e.target.value);
                        setProgram(items.find((i) => i.level === e.target.value)?.program);
                    }}>
                        {levels.map((l) => <option key={l}>{l}</option>)}
                    </select>
                    <label className="visually-hidden" htmlFor={`${uid}-p`}>Program</label>
                    <select id={`${uid}-p`} className="form-select" value={current.program} onChange={(e) => setProgram(e.target.value)}>
                        {programs.map((p) => <option key={p.program}>{p.program}</option>)}
                    </select>
                </div>
                <div className="nt-trend-card nt-fade-in" key={current.program} aria-live="polite">
                    {current.school ? <p className="nt-pill">{current.school}</p> : null}
                    <h3>{current.program}</h3>
                    <div className="d-flex flex-wrap justify-content-between gap-3 align-items-center">
                        {current.text ? <p className="mb-0" style={{ maxWidth: '36rem' }}>{current.text}</p> : null}
                        {current.report?.url ? (
                            <a className="nt-btn" href={current.report.url} target="_blank" rel="noopener">
                                Download placement report
                            </a>
                        ) : null}
                    </div>
                    {current.stats?.length || data.logos?.length ? (
                        <div className="nt-trend-card__inner">
                            <dl className="nt-trend-card__stats">
                                {(current.stats ?? []).map((s, i) => (
                                    <div key={i}>
                                        <dd data-countup={s.value}>{s.value}</dd>
                                        <dt>{s.label}</dt>
                                    </div>
                                ))}
                            </dl>
                            {data.logos?.length ? (
                                <ul className="nt-logos nt-logos--compact list-unstyled m-0" aria-label="Recruiters">
                                    {data.logos.map((l) => (
                                        <li key={l.id}>
                                            <img src={l.logo?.src} alt={l.name} width={l.logo?.width} height={l.logo?.height} loading="lazy" />
                                        </li>
                                    ))}
                                </ul>
                            ) : null}
                        </div>
                    ) : null}
                </div>
            </div>
        </section>
    );
}
