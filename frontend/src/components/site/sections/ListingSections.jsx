import useForm from '../../../hooks/useForm';
import usePage from '../../../hooks/usePage';
import { useEffect, useId, useMemo, useRef, useState } from 'react';
import Carousel from '../../Carousel';
import Icon from '../../Icon';
import SmartLink from '../../SmartLink';
import { Media } from '../../ResponsiveImage';
import Tabs from '../../Tabs';
import { EventCard, ProgramCard, PublicationCard } from '../Cards';

import { SplitHead, toneClass } from './InnerSections';

export function ProgramsTabs({ data }) {
    const levels = data.levels ?? [];
    if (!levels.length) return null;
    return (
        <section className={`nt-section nt-tone-${data.tone === 'white' ? 'white' : 'light'}`}>
            <div className="container">
                <div className="text-center mx-auto mb-4" style={{ maxWidth: '62rem' }}>
                    {data.eyebrow ? <p className="nt-pill d-table mx-auto">{data.eyebrow}</p> : null}
                    {data.heading ? <h2 className="nt-h2">{data.heading}</h2> : null}
                </div>
                <Tabs
                    label={data.heading || 'Programs'}
                    className="nt-tabs-center"
                    tabs={levels.map((level) => ({
                        key: level.key,
                        label: level.label,
                        content: (
                            <Carousel label={level.label} slideWidth="min(58.75rem, 88%)">
                                {level.programs.map((p) => (
                                    <ProgramCard key={p.id} item={p} buttons={Boolean(data.card_buttons)} />
                                ))}
                            </Carousel>
                        ),
                    }))}
                />
            </div>
        </section>
    );
}

function WideEventCard({ event }) {
    const external = /^https?:/.test(event.registration_url || '');
    return (
        <article className="nt-event-wide">
            <Media image={event.image} sizes="(min-width: 992px) 450px, 90vw" className="nt-event-wide__media" alt="" />
            <div className="nt-event-wide__body">
                <h3>{event.title}</h3>
                {event.summary ? <p className="nt-event-wide__summary">{event.summary}</p> : null}
                <p className="nt-event-wide__meta">
                    {event.date ? (
                        <span>
                            <Icon name="calendar" strokeWidth={1.5} />
                            <time dateTime={event.datetime}>{event.date}</time>
                        </span>
                    ) : null}
                    {event.venue ? (
                        <span>
                            <Icon name="pin" strokeWidth={1.5} />
                            {event.venue}
                        </span>
                    ) : null}
                </p>
                <div className="nt-event-wide__actions">
                    <SmartLink href={event.url} className="nt-btn">
                        Learn more<span className="visually-hidden">: {event.title}</span>
                    </SmartLink>
                    {event.registration_url ? (
                        <SmartLink href={event.registration_url} className="nt-btn nt-btn--outline" newTab={external}>
                            Register now<span className="visually-hidden">: {event.title}</span>
                        </SmartLink>
                    ) : null}
                </div>
            </div>
        </article>
    );
}

export function EventsCarousel({ data }) {
    const events = data.events ?? [];
    if (!events.length) return null;
    return (
        <section className={`nt-section ${toneClass(data.tone)}`}>
            <div className="container">
                <SplitHead data={data} />
                {data.layout === 'wide' ? (
                    <Carousel label={data.heading || 'Upcoming events'} slideWidth="min(61.9rem, 90%)" gap="2rem">
                        {events.map((e) => (
                            <WideEventCard key={e.id} event={e} />
                        ))}
                    </Carousel>
                ) : (
                    <Carousel label={data.heading || 'Upcoming events'} slideWidth="min(22rem, 85%)" gap="1.5rem">
                        {events.map((e) => (
                            <EventCard key={e.id} event={e} />
                        ))}
                    </Carousel>
                )}
            </div>
        </section>
    );
}

export function PublicationsCarousel({ data }) {
    const items = data.publications ?? [];
    if (!items.length) return null;
    return (
        <section className="nt-section">
            <div className="container">
                <SplitHead data={data} />
                <Carousel label={data.heading || 'Publications'} slideWidth="min(22rem, 85%)" gap="1.5rem">
                    {items.map((p) => (
                        <PublicationCard key={p.id} item={p} />
                    ))}
                </Carousel>
            </div>
        </section>
    );
}

export function ProgramFinder({ data }) {
    const schools = data.schools ?? [];
    const programs = data.programs ?? [];
    const [school, setSchool] = useState('');
    const options = useMemo(() => programs.filter((p) => !school || String(p.school_id) === school), [programs, school]);
    const [programId, setProgramId] = useState('');
    const program = options.find((p) => String(p.id) === programId) ?? options[0];
    const uid = useId();
    if (!programs.length) return null;

    const facts = program
        ? [
              ['pin', 'Location', program.location],
              ['portfolio', 'Mode', program.mode],
              ['calendar', 'Intake', program.intake],
              ['clock', 'Application deadline', program.deadline],
              ['cap', 'Duration', program.duration],
          ].filter(([, , v]) => v)
        : [];

    return (
        <section className={`nt-section nt-finder ${toneClass(data.tone, 'lavender')}`}>
            <div className="container">
                {data.heading ? <h2 className="nt-h3 mb-3">{data.heading}</h2> : null}
                {data.body
                    ? data.body.split(/\n\s*\n/).map((para, i) => (
                          <p key={i} className="nt-finder__intro">
                              {para}
                          </p>
                      ))
                    : null}
                <div className="nt-selects">
                    <label className="visually-hidden" htmlFor={`${uid}-s`}>School</label>
                    <select id={`${uid}-s`} className="form-select" value={school} onChange={(e) => {
                        setSchool(e.target.value);
                        setProgramId('');
                    }}>
                        <option value="">All schools</option>
                        {schools.map((s) => <option key={s.id} value={s.id}>{s.name}</option>)}
                    </select>
                    <label className="visually-hidden" htmlFor={`${uid}-p`}>Program</label>
                    <select id={`${uid}-p`} className="form-select" value={program ? String(program.id) : ''} onChange={(e) => setProgramId(e.target.value)}>
                        {options.map((p) => <option key={p.id} value={p.id}>{p.title}</option>)}
                    </select>
                </div>
                {program ? (
                    <div className="nt-finder__result nt-fade-in" key={program.id} aria-live="polite">
                        <div>
                            <h3>{program.title}</h3>
                            {facts.length ? (
                                <ul className="nt-meta-list nt-meta-list--row">
                                    {facts.map(([icon, label, value]) => (
                                        <li key={label}>
                                            <Icon name={icon} strokeWidth={1.6} />
                                            <span className="visually-hidden">{label}: </span>
                                            {value}
                                        </li>
                                    ))}
                                </ul>
                            ) : null}
                        </div>
                        <div className="nt-finder__actions">
                            {program.apply_url ? <SmartLink href={program.apply_url} className="nt-btn">Apply Now</SmartLink> : null}
                            <SmartLink href={program.url} className="nt-btn nt-btn--outline">Learn more</SmartLink>
                        </div>
                    </div>
                ) : null}
            </div>
        </section>
    );
}

export function JobListings({ data }) {
    const jobs = data.jobs ?? [];
    const categories = data.categories ?? {};
    const [query, setQuery] = useState('');
    const [category, setCategory] = useState('');
    const [applying, setApplying] = useState(null);
    const uid = useId();

    const visible = jobs.filter((j) => (!category || j.category === category) && (!query || `${j.title} ${j.department ?? ''} ${j.mode ?? ''}`.toLowerCase().includes(query.trim().toLowerCase())));
    const tabs = [['', 'All Roles'], ...Object.entries(categories)];
    const list = useRef(null);

    return (
        <section className="nt-section nt-divider" id="open-positions">
            <div className="container">
                {data.heading ? <h2 className="nt-h2 mb-4">{data.heading}</h2> : null}
                <form
                    className="nt-job-search"
                    role="search"
                    onSubmit={(e) => {
                        e.preventDefault();
                        list.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }}
                >
                    <Icon name="search" strokeWidth={1.8} />
                    <label className="visually-hidden" htmlFor={`${uid}-q`}>Search positions by keyword</label>
                    <input id={`${uid}-q`} type="search" placeholder="Search by Keyword" value={query} onChange={(e) => setQuery(e.target.value)} />
                    <button type="submit" className="nt-btn">Search Jobs</button>
                </form>
                <div className="nt-tabs nt-job-tabs" role="group" aria-label="Filter by role type">
                    {tabs.map(([key, label]) => (
                        <button key={key || 'all'} type="button" aria-pressed={category === key} onClick={() => setCategory(key)}>
                            {label}
                        </button>
                    ))}
                </div>
                <p className="visually-hidden" aria-live="polite">{visible.length} positions shown</p>
                <div ref={list} className="nt-job-list">
                    {visible.length ? (
                        <ul className="list-unstyled m-0">
                            {visible.map((job) => (
                                <li key={job.id} className="nt-job-card">
                                    <div className="nt-job-card__main">
                                        <p className="nt-job-card__dept">{job.department || 'NAMTECH'}</p>
                                        <h3>{job.title}</h3>
                                        <dl className="nt-job-card__facts">
                                            {job.closes ? (
                                                <div>
                                                    <span className="nt-job-card__icon" aria-hidden="true"><Icon name="calendar" strokeWidth={1.6} /></span>
                                                    <dt>Last Date</dt>
                                                    <dd>{job.closes}</dd>
                                                </div>
                                            ) : null}
                                            {job.mode ? (
                                                <div>
                                                    <span className="nt-job-card__icon" aria-hidden="true"><Icon name="pin" strokeWidth={1.6} /></span>
                                                    <dt>Mode</dt>
                                                    <dd>{job.mode}</dd>
                                                </div>
                                            ) : null}
                                        </dl>
                                    </div>
                                    <div className="nt-job-card__actions">
                                        <SmartLink href={job.url} className="nt-job-card__more">
                                            Know More<span className="visually-hidden"> about {job.title}</span>
                                        </SmartLink>
                                        <button type="button" className="nt-btn" onClick={() => setApplying(job)}>
                                            Apply Now<span className="visually-hidden">: {job.title}</span>
                                        </button>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    ) : (
                        <p className="nt-empty">{query ? 'No positions match your search.' : jobs.length ? 'There are no open positions in this category at the moment.' : 'There are no open positions at the moment. Please check back soon.'}</p>
                    )}
                </div>
            </div>
            <JobApplyModal job={applying} onClose={() => setApplying(null)} />
        </section>
    );
}

export function JobApplyModal({ job, onClose }) {
    const dialog = useRef(null);
    const [step, setStep] = useState(1);
    const { flash, site } = usePage().props;
    const form = useForm({
        job_opening_id: '', first_name: '', last_name: '', email: '', phone: '', location: '',
        qualification: '', qualification_year: '', institution: '', experience_years: '', current_company: '',
        resume: null, consent: false, website: '',
    });

    useEffect(() => {
        if (!job) return;
        form.clearErrors();
        form.setData('job_opening_id', job.id);
        setStep(1);
        dialog.current?.showModal();
    }, [job]);

    const done = flash?.form?.form === 'job' && job;
    const err = (name) => form.errors[name];
    const stepOneFields = ['first_name', 'last_name', 'email', 'phone', 'location'];

    useEffect(() => {
        if (Object.keys(form.errors).some((k) => stepOneFields.includes(k))) setStep(1);
    }, [form.errors]);

    const next = (e) => {
        e.preventDefault();
        const missing = {};
        if (!form.data.first_name.trim()) missing.first_name = 'Please enter your first name.';
        if (!form.data.last_name.trim()) missing.last_name = 'Please enter your last name.';
        if (!/^\S+@\S+\.\S+$/.test(form.data.email)) missing.email = 'Please enter a valid email address.';
        form.clearErrors();
        if (Object.keys(missing).length) {
            form.setError(missing);
            return;
        }
        setStep(2);
    };

    const submit = (e) => {
        e.preventDefault();
        form.post('/careers/apply', { preserveScroll: true, forceFormData: true, onSuccess: () => form.reset() });
    };

    const input = (name, placeholder, props = {}) => (
        <>
            <label className="visually-hidden" htmlFor={`ja-${name}`}>{placeholder.replace('*', '')}</label>
            <input id={`ja-${name}`} className={`form-control ${err(name) ? 'is-invalid' : ''}`} placeholder={placeholder} value={form.data[name]} onChange={(e) => form.setData(name, e.target.value)} {...props} />
            {err(name) ? <div className="invalid-feedback d-block">{err(name)}</div> : null}
        </>
    );

    return (
        <dialog ref={dialog} className="nt-modal" aria-labelledby="job-modal-title" onClose={onClose}>
            <div className="nt-modal__head">
                <div>
                    <h2 id="job-modal-title">{done ? 'Application received' : 'Apply Now'}</h2>
                    {job && !done ? <p className="nt-modal__sub">{job.title}</p> : null}
                </div>
                <button type="button" className="nt-modal__close" aria-label="Close" onClick={() => dialog.current.close()}>
                    <Icon name="close" />
                </button>
            </div>
            <div className="nt-modal__body">
                {done ? (
                    <div className="nt-modal__done" role="status">
                        <Icon name="sparkle" />
                        <h3>Thank you for applying</h3>
                        <p>{flash.form.message}</p>
                        <button type="button" className="nt-btn" onClick={() => dialog.current.close()}>Close</button>
                    </div>
                ) : (
                    <form onSubmit={step === 1 ? next : submit} noValidate className="nt-form nt-modal__form">
                        <ol className="nt-steps-indicator" aria-label="Application steps">
                            <li aria-current={step === 1 ? 'step' : undefined}>Personal details</li>
                            <li aria-current={step === 2 ? 'step' : undefined}>Qualifications & CV</li>
                        </ol>
                        {step === 1 ? (
                            <>
                                <div className="nt-modal__grid">
                                    <div>{input('first_name', '*First Name', { autoComplete: 'given-name' })}</div>
                                    <div>{input('last_name', '*Last Name', { autoComplete: 'family-name' })}</div>
                                </div>
                                {input('email', '*Email', { type: 'email', autoComplete: 'email' })}
                                {input('phone', 'Phone no.', { type: 'tel', autoComplete: 'tel' })}
                                {input('location', 'Current location', { autoComplete: 'address-level2' })}
                                <button type="submit" className="nt-btn w-100">Next <Icon name="arrowRight" strokeWidth={2} /></button>
                            </>
                        ) : (
                            <>
                                <div className="nt-modal__grid">
                                    <div>{input('qualification', 'Highest qualification')}</div>
                                    <div>{input('qualification_year', 'Year of passing', { inputMode: 'numeric' })}</div>
                                </div>
                                {input('institution', 'Institution / University')}
                                <div className="nt-modal__grid">
                                    <div>{input('experience_years', 'Years of experience')}</div>
                                    <div>{input('current_company', 'Current organisation')}</div>
                                </div>
                                <label className="nt-file" htmlFor="ja-resume">
                                    <Icon name="document" strokeWidth={1.4} />
                                    <span>{form.data.resume ? form.data.resume.name : '*Upload CV (PDF or Word, max 5 MB)'}</span>
                                    <input id="ja-resume" type="file" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" onChange={(e) => form.setData('resume', e.target.files?.[0] ?? null)} />
                                </label>
                                {err('resume') ? <div className="invalid-feedback d-block">{err('resume')}</div> : null}
                                <div className="form-check">
                                    <input id="ja-consent" type="checkbox" className={`form-check-input ${err('consent') ? 'is-invalid' : ''}`} checked={form.data.consent} onChange={(e) => form.setData('consent', e.target.checked)} />
                                    <label htmlFor="ja-consent" className="form-check-label small">{site?.settings?.application_consent || 'I agree that NAMTECH may store and process my application and CV for recruitment purposes.'}</label>
                                    {err('consent') ? <div className="invalid-feedback d-block">{err('consent')}</div> : null}
                                </div>
                                <div className="nt-hp" aria-hidden="true">
                                    <label htmlFor="ja-website">Leave empty</label>
                                    <input id="ja-website" tabIndex={-1} autoComplete="off" value={form.data.website} onChange={(e) => form.setData('website', e.target.value)} />
                                </div>
                                {err('job_opening_id') ? <div className="invalid-feedback d-block">This position is no longer open.</div> : null}
                                <div className="d-flex gap-2">
                                    <button type="button" className="nt-btn nt-btn--outline" onClick={() => setStep(1)}>Back</button>
                                    <button type="submit" className="nt-btn flex-grow-1" disabled={form.processing}>{form.processing ? 'Submitting…' : 'Submit application'}</button>
                                </div>
                            </>
                        )}
                    </form>
                )}
            </div>
        </dialog>
    );
}

export function ProgramLevels({ data }) {
    return (
        <section className="nt-section">
            <div className="container d-grid gap-5">
                {(data.items ?? []).map((item, i) => (
                    <article key={i} className={`nt-level ${item.image ? '' : 'nt-level--text'}`}>
                        {item.image ? <Media image={item.image} sizes="(min-width: 992px) 40vw, 100vw" alt="" className="nt-level__media" /> : null}
                        <div className="nt-level__panel">
                            <h2>{item.heading}</h2>
                            {item.body ? <p>{item.body}</p> : null}
                            {item.programs?.length ? (
                                <ul className="list-unstyled">
                                    {item.programs.map((program) => (
                                        <li key={program.url}>
                                            <SmartLink href={program.url}>{program.title}</SmartLink>
                                        </li>
                                    ))}
                                </ul>
                            ) : null}
                        </div>
                    </article>
                ))}
            </div>
        </section>
    );
}
