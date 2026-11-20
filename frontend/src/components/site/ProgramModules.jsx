import { useEffect, useId, useRef, useState } from 'react';
import Carousel from '../Carousel';
import Icon, { ArrowDot } from '../Icon';
import { Media } from '../ResponsiveImage';
import SmartLink from '../SmartLink';
import { toEmbedUrl } from '../../lib/embed';
import { RichText } from './Common';
import { FaqItems } from './sections/ContentSections';

const has = (list) => Array.isArray(list) && list.length > 0;

function ModuleHeading({ lead, children, intro }) {
    const Tag = lead ? 'h2' : 'h3';
    return (
        <div className="nt-prog__head">
            <Tag className={lead ? 'nt-prog__title' : 'nt-prog__subtitle'}>{children}</Tag>
            {intro ? <p className="nt-prog__intro">{intro}</p> : null}
        </div>
    );
}

function IconGrid({ items, columns = 3, className = '' }) {
    return (
        <ul className={`nt-prog-grid nt-prog-grid--${columns} list-unstyled nt-stagger ${className}`}>
            {items.map((item, i) => (
                <li key={i}>
                    {item.icon ? <Icon name={item.icon} strokeWidth={1.1} className="nt-prog-grid__icon" /> : null}
                    <h4>{item.title}</h4>
                    {item.text ? <p>{item.text}</p> : null}
                </li>
            ))}
        </ul>
    );
}

function MixBars({ items, palette }) {
    let start = 0;
    return (
        <ul className="nt-mix list-unstyled">
            {items.map((item, i) => {
                const percent = Math.max(0, Math.min(100, Number(item.percent) || 0));
                const left = Math.min(start, 100 - percent);
                start += percent;
                return (
                    <li key={i}>
                        <span className="nt-mix__label">{item.label}</span>
                        <span className="nt-mix__value">{item.display || `${percent}%`}</span>
                        <span className="nt-mix__track" aria-hidden="true">
                            <span className="nt-mix__fill" style={{ left: `${left}%`, width: `${percent}%`, '--c': palette?.[i % palette.length] }} />
                        </span>
                    </li>
                );
            })}
        </ul>
    );
}

export function Snapshot({ program, data }) {
    const facts = [
        ['clock', 'Duration', [program.duration, program.duration_detail].filter(Boolean).join(' · ')],
        ['calendar', 'Start date', data?.start_date || program.intake],
        ['calendar', 'Deadline', data?.deadline || program.deadline],
        ['pin', 'Mode', [program.mode, program.location].filter(Boolean).join(' · ')],
    ].filter(([, , value]) => value);
    return (
        <aside className="nt-snapshot" aria-label="Program snapshot">
            <p className="nt-snapshot__title">Program Snapshot</p>
            <dl className="nt-snapshot__facts">
                {facts.map(([icon, label, value]) => (
                    <div key={label}>
                        <Icon name={icon} strokeWidth={1.4} />
                        <dt>{label}</dt>
                        <dd>{value}</dd>
                    </div>
                ))}
                {data?.eligibility ? (
                    <div className="nt-snapshot__wide">
                        <Icon name="cap" strokeWidth={1.4} />
                        <dt>Eligibility</dt>
                        <dd>
                            {data.eligibility}
                            {data.eligibility_note ? <small>({data.eligibility_note})</small> : null}
                        </dd>
                    </div>
                ) : null}
            </dl>
            <div className="nt-snapshot__actions">
                {program.apply_url ? (
                    <SmartLink href={program.apply_url} className="nt-btn" newTab={/^https?:/.test(program.apply_url)}>
                        Apply now
                    </SmartLink>
                ) : null}
                <SmartLink href="/admissions" className="nt-btn nt-btn--outline">
                    Explore admissions
                </SmartLink>
            </div>
        </aside>
    );
}

export function WhyChoose({ data }) {
    return (
        <div className="nt-prog__block">
            <ModuleHeading>{data.heading || 'Why Choose this Program?'}</ModuleHeading>
            <IconGrid items={data.items} />
        </div>
    );
}

export function ClassProfile({ data }) {
    return (
        <div className="nt-prog__block nt-class">
            {data.image || has(data.mix) ? (
                <div className={`nt-class__top ${data.image ? '' : 'nt-class__top--text'}`}>
                    {data.image ? <Media image={data.image} sizes="(min-width: 992px) 55vw, 100vw" className="nt-class__media" /> : null}
                    <div className="nt-class__panel">
                        <h3>{data.heading || 'Class of 2026'}</h3>
                        {data.intro ? <p>{data.intro}</p> : null}
                        {has(data.mix) ? (
                            <>
                                <p className="nt-class__label">{data.mix_label || 'Student Profile Mix'}</p>
                                <MixBars items={data.mix} />
                            </>
                        ) : null}
                    </div>
                </div>
            ) : (
                <>
                    <h3 className="nt-prog__subtitle">{data.heading || 'Class of 2026'}</h3>
                    {data.intro ? <p>{data.intro}</p> : null}
                </>
            )}
            {has(data.stats) ? (
                <ul className="nt-class__stats list-unstyled">
                    {data.stats.map((stat, i) => (
                        <li key={i}>
                            {stat.icon ? <Icon name={stat.icon} strokeWidth={1.1} /> : null}
                            <strong className={String(stat.value).length > 6 ? 'is-long' : ''} data-countup={stat.value}>{stat.value}</strong>
                            {stat.text ? <span>{stat.text}</span> : null}
                        </li>
                    ))}
                </ul>
            ) : null}
        </div>
    );
}

const initials = (text) =>
    String(text ?? '')
        .replace(/^[^:]*:\s*/, '')
        .split(/\s+/)
        .filter((w) => /^[A-Z0-9]/.test(w))
        .slice(0, 2)
        .map((w) => w[0])
        .join('');

function ProgramPreview({ id, title, image, closeLabel, onClose, children }) {
    const dialog = useRef(null);

    useEffect(() => {
        if (title) dialog.current?.showModal();
    }, [title]);

    return (
        <dialog ref={dialog} id={id} className="nt-modal" aria-labelledby={`${id}-title`} onClose={onClose}>
            <div className="nt-modal__head">
                <h2 id={`${id}-title`}>{title}</h2>
                <button type="button" className="nt-modal__close" aria-label={closeLabel} onClick={() => dialog.current.close()}>
                    <Icon name="close" />
                </button>
            </div>
            <div className="nt-modal__body nt-program-preview">
                {image ? <Media image={image} className="nt-program-preview__photo" sizes="(min-width: 36rem) 32rem, 85vw" alt="" /> : null}
                {children}
            </div>
        </dialog>
    );
}

export function CardCarousel({ heading, intro, items, lead, cta, variant }) {
    const [storyPreview, setStoryPreview] = useState(null);
    const storyId = useId();
    const stories = variant === 'innovations' || variant === 'awards';

    return (
        <div className={`nt-prog__block${stories ? ` nt-prog-stories nt-prog-${variant}` : ''}`}>
            <div className="nt-prog__headrow">
                <ModuleHeading lead={lead} intro={intro}>
                    {heading}
                </ModuleHeading>
                {cta}
            </div>
            <Carousel label={heading} slideWidth={stories ? 'var(--nt-story-slide)' : 'min(21.25rem, 82%)'} gap={stories ? 'var(--nt-story-gap)' : '2.5rem'}>
                {items.map((item, i) => {
                    const [, prefix, name] = String(item.title ?? '').match(/^([^:]{3,40}):\s*(.+)$/) ?? [null, null, item.title];
                    const label = item.date || (!stories && (item.tag || prefix));
                    const action = variant === 'awards' && item.link_url ? 'Read' : 'View';
                    const view = <>{action} <ArrowDot /></>;
                    return (
                        <article key={i} className="nt-prog-card">
                            {item.image ? (
                                <Media image={item.image} sizes="340px" className="nt-prog-card__media" alt="" />
                            ) : (
                                <div className="nt-prog-card__media nt-prog-card__placeholder" aria-hidden="true">
                                    {item.logo ? <Media image={item.logo} sizes="200px" alt="" /> : <span>{initials(item.title)}</span>}
                                </div>
                            )}
                            {label || !stories ? <p className="nt-prog-card__date">{label || '\u00a0'}</p> : null}
                            {(item.logo && item.image) || !stories ? (
                                <div className="nt-prog-card__logo">{item.image ? <Media image={item.logo} sizes="160px" alt="" /> : null}</div>
                            ) : null}
                            <h4 title={name}>{!stories && item.link_url ? <SmartLink href={item.link_url} className="stretched-link">{name}</SmartLink> : name}</h4>
                            <p className="nt-prog-card__text">{item.text}</p>
                            {stories ? (
                                item.link_url ? (
                                    <SmartLink href={item.link_url} className="nt-link-arrow stretched-link" aria-label={`${action} ${name}`}>{view}</SmartLink>
                                ) : (
                                    <button type="button" className="nt-link-arrow" aria-label={`View ${name}`} aria-haspopup="dialog" aria-controls={storyId} onClick={() => setStoryPreview({ ...item, title: name })}>
                                        {view}
                                    </button>
                                )
                            ) : (
                                <span className={`nt-link-arrow ${item.link_url ? '' : 'is-placeholder'}`} aria-hidden="true">{view}</span>
                            )}
                        </article>
                    );
                })}
            </Carousel>
            {stories ? (
                <ProgramPreview id={storyId} title={storyPreview?.title} image={storyPreview?.image} closeLabel="Close story" onClose={() => setStoryPreview(null)}>
                    {storyPreview?.date ? <p className="nt-prog-card__date">{storyPreview.date}</p> : null}
                    {storyPreview?.text ? <p>{storyPreview.text}</p> : null}
                </ProgramPreview>
            ) : null}
        </div>
    );
}

function Courses({ semester }) {
    const lines = String(semester.courses ?? '')
        .split('\n')
        .map((line) => line.trim())
        .filter(Boolean);
    const columns = String(semester.columns ?? '')
        .split(',')
        .map((column) => column.trim())
        .filter(Boolean);

    if (!columns.length) {
        return <ul>{lines.map((line, i) => <li key={i}>{line}</li>)}</ul>;
    }

    return (
        <div className="nt-semesters__table">
            <table>
                <thead>
                    <tr>{columns.map((column) => <th key={column} scope="col">{column}</th>)}</tr>
                </thead>
                <tbody>
                    {lines.map((line, i) => {
                        const cells = line.split('|').map((cell) => cell.trim());
                        return (
                            <tr key={i} className={/^total\b/i.test(cells[0]) ? 'is-total' : undefined}>
                                {columns.map((column, j) => <td key={column}>{cells[j]}</td>)}
                            </tr>
                        );
                    })}
                </tbody>
            </table>
        </div>
    );
}

export function Structure({ data, lead }) {
    return (
        <div className="nt-prog__block">
            <ModuleHeading lead={lead} intro={data.intro}>
                {data.heading || 'Program Structure'}
            </ModuleHeading>
            <div className="nt-semesters">
                {data.semesters.map((semester, i) => (
                    <details key={i} open={i === 0}>
                        <summary>
                            {semester.title}
                            <Icon name="chevronDown" strokeWidth={1.8} />
                        </summary>
                        <Courses semester={semester} />
                    </details>
                ))}
            </div>
        </div>
    );
}

const learningPalette = ['#b25cf7', '#5d24fc', '#2e1363', '#c9b6aa', '#bd57ff', '#8a6cff'];

function Donut({ items }) {
    const total = items.reduce((sum, item) => sum + (Number(item.percent) || 0), 0) || 1;
    const r = 70;
    const circumference = 2 * Math.PI * r;
    let offset = 0;
    return (
        <svg className="nt-donut" viewBox="0 0 200 200" role="img" aria-label={items.map((i) => `${i.label} ${i.percent}%`).join(', ')}>
            {items.map((item, i) => {
                const length = ((Number(item.percent) || 0) / total) * circumference;
                const gap = Math.min(4, length / 4);
                const segment = (
                    <circle
                        key={i}
                        cx="100"
                        cy="100"
                        r={r}
                        fill="none"
                        stroke={learningPalette[i % learningPalette.length]}
                        strokeWidth="34"
                        strokeDasharray={`${Math.max(0, length - gap)} ${circumference}`}
                        strokeDashoffset={-offset}
                        transform="rotate(-90 100 100)"
                    />
                );
                offset += length;
                return segment;
            })}
        </svg>
    );
}

export function LearningModel({ data, lead }) {
    return (
        <div className="nt-prog__block">
            <ModuleHeading lead={lead} intro={data.intro}>
                {data.heading || 'NAMTECH’s Learning Model'}
            </ModuleHeading>
            {has(data.cards) ? <IconGrid items={data.cards} columns={Math.min(3, data.cards.length) || 3} /> : null}
            {has(data.mix) ? (
                <div className="nt-learning-mix">
                    <Donut items={data.mix} />
                    <MixBars items={data.mix} palette={learningPalette} />
                </div>
            ) : null}
            {data.notes ? <RichText html={data.notes} className="nt-prog__notes" /> : null}
        </div>
    );
}

export function Labs({ data, lead, perLab = false }) {
    const [active, setActive] = useState(0);
    const current = data.items[active] ?? data.items[0];
    return (
        <div className="nt-prog__block nt-labs-mod">
            <div className="nt-labs-mod__head" aria-live={perLab ? 'polite' : undefined}>
                {perLab ? (
                    <ModuleHeading lead={lead} intro={current.text}>
                        {`${current.title}:`}
                    </ModuleHeading>
                ) : (
                    <ModuleHeading lead={lead} intro={data.intro}>
                        {data.heading || 'Labs & Industry Immersion'}
                    </ModuleHeading>
                )}
            </div>
            <Carousel
                label={data.heading || 'Labs'}
                slideWidth="min(63.3rem, 86vw)"
                gap="3rem"
                align="center"
                firstSlideAtStart
                className="nt-labs-mod__carousel"
                onIndexChange={setActive}
            >
                {data.items.map((item, i) => (
                    <figure key={i} className={`nt-labs-mod__slide ${i === active ? 'is-active' : ''}`}>
                        <Media image={item.image} sizes="(min-width: 992px) 1013px, 86vw" alt="" />
                        <figcaption>{item.title}</figcaption>
                    </figure>
                ))}
            </Carousel>
        </div>
    );
}

export function AdmissionSteps({ data, lead }) {
    const [active, setActive] = useState(0);
    return (
        <div className="nt-prog__block">
            <ModuleHeading lead={lead} intro={data.intro}>
                {data.heading || 'Admission Process'}
            </ModuleHeading>
            <div className="nt-timeline-steps">
                <p className="nt-timeline-steps__title">Steps</p>
                <ol className="list-unstyled">
                    {data.steps.map((step, i) => (
                        <li key={i} className={i === active ? 'is-active' : ''} onMouseEnter={() => setActive(i)}>
                            <span className="nt-timeline-steps__num">{String(i + 1).padStart(2, '0')}</span>
                            <span className="nt-timeline-steps__dot" aria-hidden="true" />
                            <p>{step.text}</p>
                        </li>
                    ))}
                </ol>
            </div>
        </div>
    );
}

export function EligibilityModule({ data, lead }) {
    const Tag = lead ? 'h2' : 'h3';
    const branchesOnly = !has(data.criteria) && !data.intro;
    return (
        <div className={`nt-prog__block nt-elig ${branchesOnly ? 'nt-elig--branches' : ''}`}>
            <div>
                <Tag className={lead ? 'nt-prog__title' : 'nt-prog__subtitle nt-elig__title'}>{data.heading || 'Eligibility Criteria'}</Tag>
                {data.intro ? <p className="nt-prog__intro">{data.intro}</p> : null}
                {has(data.criteria) ? (
                    <ol className="nt-elig__criteria list-unstyled">
                        {data.criteria.map((item, i) => (
                            <li key={i}>
                                <span>{String(i + 1).padStart(2, '0')}</span>
                                <p>{item.text}</p>
                            </li>
                        ))}
                    </ol>
                ) : null}
            </div>
            {has(data.branches) ? (
                <div>
                    <h3 className="nt-elig__branches-title">{data.branches_heading || 'Eligible Engineering Branches'}</h3>
                    <ul className="nt-elig__branches list-unstyled">
                        {data.branches.map((item, i) => (
                            <li key={i}>
                                <Icon name={item.icon || 'graduation'} strokeWidth={1.1} />
                                {item.text}
                            </li>
                        ))}
                    </ul>
                </div>
            ) : null}
        </div>
    );
}

export function Documents({ data, lead }) {
    return (
        <div className="nt-prog__block">
            <ModuleHeading lead={lead} intro={data.intro}>
                {data.heading || 'Required documents'}
            </ModuleHeading>
            <IconGrid items={data.items} columns={Math.min(4, Math.max(2, data.items.length))} className="nt-prog-grid--docs" />
        </div>
    );
}

export function Outcomes({ data, lead }) {
    return (
        <div className="nt-prog__block">
            <ModuleHeading lead={lead} intro={data.intro}>
                {data.heading || 'Key Outcomes & Impact'}
            </ModuleHeading>
            {has(data.stats) ? (
                <div className="nt-outcomes">
                    <ul className="nt-outcomes__stats list-unstyled">
                        {(data.stats ?? []).map((stat, i) => (
                            <li key={i}>
                                {stat.icon ? <Icon name={stat.icon} strokeWidth={1.1} /> : null}
                                <strong data-countup={stat.value}>{stat.value}</strong>
                                {stat.label ? <span className="nt-outcomes__label">{stat.label}</span> : null}
                                {stat.text ? <small>{stat.text}</small> : null}
                            </li>
                        ))}
                    </ul>
                    <a
                        className="nt-outcomes__brochure"
                        href={data.brochure ? data.brochure.url : '#enquiry:brochure'}
                        {...(data.brochure ? { target: '_blank', rel: 'noopener' } : {})}
                    >
                        <Icon name="download" strokeWidth={1.4} />
                        <span>Download Placement Brochure</span>
                    </a>
                </div>
            ) : null}
            {has(data.logos) ? (
                <div className="nt-outcomes__logos">
                    <p>{data.logos_heading || 'Top companies hiring our graduates across industries'}</p>
                    <ul className="list-unstyled">
                        {data.logos.map((logo, i) => (
                            <li key={i}>
                                {logo.image ? <Media image={logo.image} sizes="160px" alt={logo.name} /> : <span>{logo.name}</span>}
                            </li>
                        ))}
                    </ul>
                </div>
            ) : null}
        </div>
    );
}

export function Roles({ data, lead }) {
    return (
        <div className="nt-prog__block">
            <ModuleHeading lead={lead} intro={data.intro}>
                {data.heading || 'Build Your Future in These Roles'}
            </ModuleHeading>
            <ul className="nt-roles list-unstyled">
                {data.items.map((item, i) => (
                    <li key={i}>
                        <Icon name={item.icon || 'portfolio'} strokeWidth={1.1} />
                        <div>
                            <h4>{item.title}</h4>
                            {item.text ? <p>{item.text}</p> : null}
                        </div>
                    </li>
                ))}
            </ul>
        </div>
    );
}

export function PeopleCarousel({ heading, items, lead }) {
    const profileId = useId();
    const [personPreview, setPersonPreview] = useState(null);

    return (
        <div className="nt-prog__block">
            <ModuleHeading lead={lead}>{heading}</ModuleHeading>
            <Carousel label={heading} className="nt-alumni-carousel" slideWidth="min(22.2rem, 82vw)" gap="0.75rem">
                {items.map((person, i) => (
                    <article key={i} className="nt-alum">
                        <Media image={person.photo} sizes="356px" alt="" className="nt-alum__media" />
                        <div className="nt-alum__info">
                            <h4>
                                {person.link_url ? (
                                    <SmartLink href={person.link_url} className="stretched-link">
                                        {person.name}
                                        <span className="nt-alum__link-icon" aria-hidden="true"><Icon name="chevronDown" strokeWidth={1.4} /></span>
                                    </SmartLink>
                                ) : person.name}
                            </h4>
                            {person.class ? <p className="nt-alum__class">{person.class}</p> : null}
                            {person.role ? <p className="nt-alum__role">{person.role}</p> : null}
                            {!person.link_url ? (
                                <button type="button" className="nt-alum__link-icon" aria-label={`View profile of ${person.name}`} aria-haspopup="dialog" aria-controls={profileId} onClick={() => setPersonPreview(person)}>
                                    <Icon name="chevronDown" strokeWidth={1.4} />
                                </button>
                            ) : null}
                        </div>
                    </article>
                ))}
            </Carousel>
            <ProgramPreview id={profileId} title={personPreview?.name} image={personPreview?.photo} closeLabel="Close profile" onClose={() => setPersonPreview(null)}>
                {personPreview?.class ? <p>{personPreview.class}</p> : null}
                {personPreview?.role ? <p>{personPreview.role}</p> : null}
            </ProgramPreview>
        </div>
    );
}

function RecruiterQuoteCard({ item }) {
    return (
        <figure className="nt-quote-card">
            <div className="nt-quote-card__top">
                {item.logo ? <Media image={item.logo} sizes="160px" alt={item.name} className="nt-quote-card__logo" /> : <strong>{item.name}</strong>}
                <Icon name="quoteClose" strokeWidth={0.65} />
            </div>
            <blockquote>“{item.quote}”</blockquote>
        </figure>
    );
}

export function RecruiterQuotes({ data, lead }) {
    const heading = data.heading || 'Hear From Recruiters Around the World';
    const midpoint = Math.ceil(data.items.length / 2);
    const rows = [data.items.slice(0, midpoint), data.items.slice(midpoint)].filter(has);

    return (
        <div className="nt-prog__block nt-recruiter-quotes">
            <ModuleHeading lead={lead}>{heading}</ModuleHeading>
            <div className="nt-recruiter-quotes__desktop d-none d-lg-grid">
                {rows.map((items, row) => (
                    <Carousel key={row} label={`${heading}, row ${row + 1}`} slideWidth="23.5rem" gap="1.5rem" showDots={false} showArrows={false}>
                        {items.map((item, i) => <RecruiterQuoteCard item={item} key={i} />)}
                    </Carousel>
                ))}
            </div>
            <Carousel label={heading} className="nt-recruiter-quotes__mobile d-lg-none" slideWidth="min(23.5rem, 82vw)" gap="1.5rem" showArrows={false}>
                {data.items.map((item, i) => <RecruiterQuoteCard item={item} key={i} />)}
            </Carousel>
        </div>
    );
}

export function Testimonials({ data, lead }) {
    return (
        <div className="nt-prog__block">
            <ModuleHeading lead={lead}>{data.heading || 'Student Testimonials'}</ModuleHeading>
            <Carousel label={data.heading || 'Student testimonials'} slideWidth="min(23.5rem, 85%)" gap="1.5rem">
                {data.items.map((item, i) => (
                    <figure key={i} className="nt-quote-card">
                        <div className="nt-quote-card__top">
                            <Icon name="quote" strokeWidth={1.2} />
                        </div>
                        <blockquote>“{item.quote}”</blockquote>
                        <figcaption>
                            <strong>{item.name}</strong>
                            {item.role ? <span>{item.role}</span> : null}
                        </figcaption>
                    </figure>
                ))}
            </Carousel>
        </div>
    );
}

export function Fees({ data, lead }) {
    return (
        <div className="nt-prog__block">
            <ModuleHeading lead={lead} intro={data.intro}>
                {data.heading || 'Fees and Scholarship'}
            </ModuleHeading>
            {data.total || has(data.rows) || has(data.scholarships) ? (
                <div className={`nt-fees ${has(data.scholarships) ? '' : 'nt-fees--single'}`}>
                    <div className="nt-fees__panel">
                        <p className="nt-fees__label">{data.fee_label || 'Program Fee'}</p>
                        {data.total ? <p className="nt-fees__total">{data.total}</p> : null}
                        {has(data.rows) ? (
                            <dl className="nt-fees__rows">
                                {data.rows.map((row, i) => (
                                    <div key={i}>
                                        <dt>{row.label}</dt>
                                        <dd>{row.value}</dd>
                                    </div>
                                ))}
                            </dl>
                        ) : null}
                    </div>
                    {has(data.scholarships) ? (
                        <div className="nt-fees__scholarships on-dark">
                            <p className="nt-fees__label">Scholarships</p>
                            <ul className="list-unstyled">
                                {data.scholarships.map((item, i) => (
                                    <li key={i}>
                                        <h4>{item.title}</h4>
                                        {item.text ? <p>{item.text}</p> : null}
                                    </li>
                                ))}
                            </ul>
                            {data.scholarships_url ? (
                                <SmartLink href={data.scholarships_url} className="nt-fees__link">
                                    {data.scholarships_label || 'Learn about scholarships'} <Icon name="arrowRight" strokeWidth={2} />
                                </SmartLink>
                            ) : null}
                        </div>
                    ) : null}
                </div>
            ) : null}
            {has(data.notes) ? <IconGrid items={data.notes.map((n) => ({ ...n, title: n.title || '' }))} columns={Math.min(3, data.notes.length)} /> : null}
        </div>
    );
}

export function Loans({ data, lead }) {
    const providers = data.providers.filter((p) => has(p.items));
    const [active, setActive] = useState(0);
    const id = useId();
    const current = providers[Math.min(active, providers.length - 1)];
    return (
        <div className="nt-prog__block">
            <ModuleHeading lead={lead} intro={data.intro}>
                {data.heading || 'Education Loan Assistance'}
            </ModuleHeading>
            {providers.length > 1 ? (
                <div className="nt-loans__tabs" role="tablist" aria-label="Loan partners">
                    {providers.map((p, i) => (
                        <button key={i} type="button" role="tab" id={`${id}-t${i}`} aria-selected={i === active} aria-controls={`${id}-p`} onClick={() => setActive(i)}>
                            {p.name || `Partner ${i + 1}`}
                        </button>
                    ))}
                </div>
            ) : null}
            <ul className="nt-loans list-unstyled" id={`${id}-p`} role={providers.length > 1 ? 'tabpanel' : undefined} aria-labelledby={providers.length > 1 ? `${id}-t${active}` : undefined}>
                {current.items.map((item, i) => (
                    <li key={i}>
                        <Icon name={item.icon || 'check'} strokeWidth={1.1} />
                        <span>{item.text}</span>
                    </li>
                ))}
            </ul>
        </div>
    );
}

export function LifeVideo({ data, lead }) {
    const [playing, setPlaying] = useState(false);
    const embed = data.video_url ? toEmbedUrl(data.video_url) : null;
    const feature = data.image || data.video_url || data.title;
    return (
        <div className="nt-prog__block">
            <ModuleHeading lead={lead} intro={data.intro}>
                {data.heading || 'Student Life'}
            </ModuleHeading>
            {feature ? (
                <div className="nt-life-video">
                    {playing && embed ? (
                        <iframe src={`${embed}${embed.includes('?') ? '&' : '?'}autoplay=1`} title={data.title || 'Video'} allow="autoplay; encrypted-media; picture-in-picture" allowFullScreen />
                    ) : (
                        <>
                            {data.image ? <Media image={data.image} sizes="(min-width: 1200px) 1280px, 100vw" alt="" /> : null}
                            <div className="nt-life-video__text on-dark">
                                {data.video_url ? (
                                    embed ? (
                                        <button type="button" className="nt-life-video__play" onClick={() => setPlaying(true)} aria-label={`Play video${data.title ? `: ${data.title}` : ''}`}>
                                            <Icon name="play" />
                                        </button>
                                    ) : (
                                        <a className="nt-life-video__play" href={data.video_url} target="_blank" rel="noopener noreferrer" aria-label="Watch the video (opens in a new tab)">
                                            <Icon name="play" />
                                        </a>
                                    )
                                ) : null}
                                {data.title ? <h3>{data.title}</h3> : null}
                                {data.text ? <p>{data.text}</p> : null}
                            </div>
                        </>
                    )}
                </div>
            ) : null}
        </div>
    );
}

export function Clubs({ data }) {
    return (
        <div className={`nt-clubs ${data.image ? '' : 'nt-clubs--text'}`}>
            {data.image ? <Media image={data.image} sizes="(min-width: 992px) 60vw, 100vw" alt="" className="nt-clubs__media" /> : null}
            <div className="nt-clubs__panel on-dark">
                <Icon name="handshake" strokeWidth={1.2} />
                <h3>{data.title || 'Student Clubs'}</h3>
                <ul className="list-unstyled">
                    {data.items.map((item, i) => (
                        <li key={i}>{item.text}</li>
                    ))}
                </ul>
                {data.link_url ? (
                    <SmartLink href={data.link_url} className="nt-clubs__link">
                        View all clubs <Icon name="external" strokeWidth={1.6} />
                    </SmartLink>
                ) : null}
            </div>
        </div>
    );
}

export function Faqs({ data, lead }) {
    return (
        <div className="nt-prog__block">
            <ModuleHeading lead={lead} intro={data.intro}>
                {data.heading || 'Frequently Asked Questions'}
            </ModuleHeading>
            <div className="nt-prog-faq">
                <FaqItems items={data.items} openFirst />
            </div>
        </div>
    );
}
