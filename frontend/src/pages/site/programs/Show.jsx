import { useEffect, useId, useRef, useState } from 'react';
import { FacultyGrid } from '../../../components/site/Cards';
import { blockSwitch, RichText } from '../../../components/site/Common';
import EnquiryForm from '../../../components/site/EnquiryForm';
import Layout from '../../../components/site/Layout';
import * as M from '../../../components/site/ProgramModules';
import SectionRenderer from '../../../components/site/sections/SectionRenderer';
import { Media } from '../../../components/ResponsiveImage';
import SmartLink from '../../../components/SmartLink';

const has = (list) => Array.isArray(list) && list.length > 0;

export default function ProgramShow({ program, details = {}, tabs, faculty, sections, hidden, breadcrumbs, programs }) {
    const show = blockSwitch(hidden);
    const d = details;
    const hasBrochure = show('brochure');
    const hasOverview = (show('overview') && Boolean(program.body)) || show('snapshot');
    const whyInOverview = hasOverview && show('_overview') && show('why_choose') && has(d.why_choose?.items);

    const modules = {
        overview: [
            ['_overview', hasOverview, (lead) => (
                <Overview
                    key="overview"
                    program={program}
                    snapshot={show('snapshot') ? d.snapshot : null}
                    showBody={show('overview')}
                    lead={lead}
                    why={whyInOverview ? <M.WhyChoose data={d.why_choose} /> : null}
                />
            )],
            ['why_choose', has(d.why_choose?.items) && !whyInOverview, () => <M.WhyChoose key="why" data={d.why_choose} />],
            ['class_profile', Boolean(d.class_profile?.image) || has(d.class_profile?.mix) || has(d.class_profile?.stats), () => <M.ClassProfile key="class" data={d.class_profile} />],
            ['partners', has(d.partners?.items), (lead) => <M.CardCarousel key="partners" heading={d.partners.heading || 'Program Partners'} items={d.partners.items} lead={lead} />],
            ['faculty', has(faculty), () => (
                <div key="faculty" className="nt-prog__block">
                    <h3 className="nt-prog__subtitle">Faculty</h3>
                    <FacultyGrid people={faculty} />
                </div>
            )],
        ],
        structure: [
            ['structure', has(d.structure?.semesters), (lead) => <M.Structure key="structure" data={d.structure} lead={lead} />],
            ['learning_model', has(d.learning_model?.cards) || has(d.learning_model?.mix) || Boolean(d.learning_model?.notes), (lead) => <M.LearningModel key="learning" data={d.learning_model} lead={lead} />],
            ['labs', has(d.labs?.items), (lead) => <M.Labs key="labs" data={d.labs} lead={lead} perLab={program.url?.startsWith('/school-of-technical-education/iptp')} />],
        ],
        admissions: [
            ['admission_steps', has(d.admission_steps?.steps), (lead) => <M.AdmissionSteps key="steps" data={d.admission_steps} lead={lead} />],
            ['eligibility', has(d.eligibility?.criteria) || has(d.eligibility?.branches) || Boolean(d.eligibility?.intro), (lead) => <M.EligibilityModule key="elig" data={d.eligibility} lead={lead} />],
            ['documents', has(d.documents?.items), (lead) => <M.Documents key="docs" data={d.documents} lead={lead} />],
        ],
        outcomes: [
            ['outcomes', has(d.outcomes?.stats) || has(d.outcomes?.logos) || Boolean(d.outcomes?.brochure), (lead) => <M.Outcomes key="outcomes" data={d.outcomes} lead={lead} />],
            ['roles', has(d.roles?.items), (lead) => <M.Roles key="roles" data={d.roles} lead={lead} />],
            ['alumni', has(d.alumni?.items), (lead) => <M.PeopleCarousel key="alumni" heading={d.alumni.heading || 'Where Our Alumni Work'} items={d.alumni.items} lead={lead} />],
            ['recruiter_quotes', has(d.recruiter_quotes?.items), (lead) => <M.RecruiterQuotes key="quotes" data={d.recruiter_quotes} lead={lead} />],
            ['innovations', has(d.innovations?.items), (lead) => (
                <M.CardCarousel
                    key="innovations"
                    heading={d.innovations.heading || 'Student innovation and fruitful research'}
                    intro={d.innovations.intro || 'Built, not just taught. Discover how our students are turning bold ideas into real-world breakthroughs.'}
                    items={d.innovations.items}
                    lead={lead}
                    cta={<SmartLink href={d.innovations.cta_url || '/research-and-consultancy/publications'} className="nt-btn">View all</SmartLink>}
                    variant="innovations"
                />
            )],
            ['awards', has(d.awards?.items), (lead) => (
                <M.CardCarousel
                    key="awards"
                    heading={d.awards.heading || 'Awards & Recognitions'}
                    intro={d.awards.intro || 'Explore the awards and recognitions our students have earned for their work in engineering, innovation, and applied research.'}
                    items={d.awards.items}
                    lead={lead}
                    variant="awards"
                />
            )],
        ],
        fees: [
            ['fees', Boolean(d.fees?.total) || has(d.fees?.rows) || has(d.fees?.scholarships) || has(d.fees?.notes), (lead) => <M.Fees key="fees" data={d.fees} lead={lead} />],
            ['loans', (d.loans?.providers ?? []).some((p) => has(p.items)), (lead) => <M.Loans key="loans" data={d.loans} lead={lead} />],
        ],
        life: [
            ['life_video', Boolean(d.life_video?.image || d.life_video?.video_url || d.life_video?.title || d.life_video?.intro), (lead) => <M.LifeVideo key="video" data={d.life_video} lead={lead} />],
            ['clubs', has(d.clubs?.items), () => <M.Clubs key="clubs" data={d.clubs} />],
            ['life_alumni', has(d.life_alumni?.items), (lead) => <M.PeopleCarousel key="lalumni" heading={d.life_alumni.heading || 'Hear From Our Alumni'} items={d.life_alumni.items} lead={lead} />],
            ['testimonials', has(d.testimonials?.items), (lead) => <M.Testimonials key="testimonials" data={d.testimonials} lead={lead} />],
        ],
        faqs: [
            ['faqs', has(d.faqs?.items), (lead) => <M.Faqs key="faqs" data={d.faqs} lead={lead} />],
            ['enquire', !(has(d.faqs?.items) && show('faqs')), (lead) => (
                <div key="enquire" className="nt-prog__block nt-prog__enquire" id="enquire">
                    {lead ? <h2 className="nt-prog__title">Enquire about this program</h2> : <h3 className="nt-prog__subtitle">Enquire about this program</h3>}
                    <EnquiryForm type="program" programs={programs} defaultProgramId={program.id} />
                </div>
            )],
        ],
    };

    const visibleTabs = Object.entries(tabs)
        .map(([key, label]) => ({ key, label, items: (modules[key] ?? []).filter(([block, content]) => content && show(block)) }))
        .filter((tab) => tab.items.length);

    const [active, setActive] = useState(visibleTabs[0]?.key);
    const barRef = useRef(null);
    const id = useId();

    const select = (key) => {
        if (!visibleTabs.some((t) => t.key === key)) return;
        setActive(key);
        window.history.replaceState(window.history.state, '', `#${key}`);
        const bar = barRef.current;
        const start = bar?.previousElementSibling;
        if (start) {
            const top = start.getBoundingClientRect().bottom + window.scrollY - (parseFloat(getComputedStyle(bar).top) || 0);
            if (window.scrollY > top) window.scrollTo({ top, behavior: 'auto' });
        }
    };

    useEffect(() => {
        const fromHash = () => {
            const key = window.location.hash.slice(1);
            if (visibleTabs.some((t) => t.key === key)) setActive(key);
        };
        fromHash();
        window.addEventListener('hashchange', fromHash);
        return () => window.removeEventListener('hashchange', fromHash);
    }, [visibleTabs.map((t) => t.key).join()]);

    useEffect(() => {
        const button = document.getElementById(`${id}-tab-${active}`);
        const list = button?.parentElement;
        if (list && list.scrollWidth > list.clientWidth) list.scrollTo({ left: button.offsetLeft - 16, behavior: 'smooth' });
    }, [active]);

    const listedUnderPrograms = program.url?.startsWith('/programs/') ?? true;
    const eyebrow = [program.school?.title, listedUnderPrograms ? 'Programs' : null].filter(Boolean).join(' / ');

    return (
        <>
            <section className="nt-page-hero nt-prog-hero on-dark">
                <div className="nt-page-hero__grid">
                    <div className="nt-page-hero__content">
                        <p className="nt-pill align-self-start nt-hero-in">{eyebrow}</p>
                        <h1 className="nt-hero-in" style={{ '--d': 1 }}>{program.title}</h1>
                        {program.summary ? <p className="nt-page-hero__body nt-hero-in" style={{ '--d': 2 }}>{program.summary}</p> : null}
                        <div className="d-flex flex-wrap gap-3 mt-3 nt-hero-in" style={{ '--d': 3 }}>
                            {program.apply_url ? (
                                <SmartLink href={program.apply_url} className="nt-btn nt-btn--light" newTab={/^https?:/.test(program.apply_url)}>
                                    Apply now
                                </SmartLink>
                            ) : null}
                            {hasBrochure ? (
                                <a href="#enquiry:brochure" className="nt-btn nt-prog-hero__ghost">
                                    Download brochure
                                </a>
                            ) : null}
                        </div>
                    </div>
                    {program.image && show('hero_image') ? (
                        <div className="nt-page-hero__media">
                            <Media image={program.image} sizes="(min-width: 992px) 50vw, 100vw" priority />
                        </div>
                    ) : null}
                </div>
            </section>

            {visibleTabs.length ? (
                <>
                    <nav className="nt-prog-tabs" ref={barRef} aria-label="Program sections">
                        <div className="container">
                            <div className="nt-prog-tabs__list" role="tablist">
                                {visibleTabs.map((tab) => (
                                    <a
                                        key={tab.key}
                                        href={`#${tab.key}`}
                                        id={`${id}-tab-${tab.key}`}
                                        role="tab"
                                        aria-selected={tab.key === active}
                                        aria-controls={`${id}-${tab.key}`}
                                        aria-current={tab.key === active ? 'true' : undefined}
                                        onClick={(e) => {
                                            e.preventDefault();
                                            select(tab.key);
                                        }}
                                    >
                                        {tab.label}
                                    </a>
                                ))}
                            </div>
                            {program.apply_url ? (
                                <SmartLink href={program.apply_url} className="nt-btn nt-prog-tabs__apply" newTab={/^https?:/.test(program.apply_url)}>
                                    Apply now
                                </SmartLink>
                            ) : null}
                        </div>
                    </nav>
                    {visibleTabs
                        .filter((tab) => tab.key === active)
                        .map((tab) => (
                            <section className="nt-section nt-prog nt-fade-in" id={`${id}-${tab.key}`} role="tabpanel" aria-labelledby={`${id}-tab-${tab.key}`} key={tab.key}>
                                <div className="container">{tab.items.map(([, , render], i) => render(i === 0))}</div>
                            </section>
                        ))}
                </>
            ) : null}

            <SectionRenderer sections={sections} page={{ title: program.title }} />
        </>
    );
}

function Overview({ program, snapshot, showBody, lead, why }) {
    return (
        <div className="nt-prog__block nt-prog-overview">
            <div className="nt-prog-overview__main">
                {lead ? <h2 className="nt-prog__title">Program Overview</h2> : null}
                {showBody ? program.body ? <RichText html={program.body} /> : <p className="text-muted">Detailed program information will be published soon. Please send an enquiry for the latest details.</p> : null}
                {why}
            </div>
            {snapshot !== null ? <M.Snapshot program={program} data={snapshot} /> : null}
        </div>
    );
}

ProgramShow.layout = (page) => <Layout>{page}</Layout>;
