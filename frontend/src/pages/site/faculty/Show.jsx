import { blockSwitch, Breadcrumbs, RichText } from '../../../components/site/Common';
import Layout from '../../../components/site/Layout';
import { Media } from '../../../components/ResponsiveImage';
import SmartLink from '../../../components/SmartLink';
import { ReadMore } from '../Leader';

function Entries({ items }) {
    return (
        <ol className="nt-entries">
            {items.map((q, i) => (
                <li key={i}>
                    {q.period || q.year ? <span className="nt-entries__period">{q.period || q.year}</span> : null}
                    <div>
                        <h4>{q.title}</h4>
                        {q.institution ? <p className="nt-entries__org">{q.institution}</p> : null}
                        {q.text ? <p className="mb-0">{q.text}</p> : null}
                    </div>
                </li>
            ))}
        </ol>
    );
}

export default function FacultyShow({ person, breadcrumbs, hidden }) {
    const show = blockSwitch(hidden);
    const panels = [
        ['Education', person.qualifications?.length ? <Entries items={person.qualifications} /> : null],
        ['Experience', person.experience?.length ? <Entries items={person.experience} /> : null],
        ['Research', person.research ? <RichText html={person.research} /> : person.research_interests ? <p>{person.research_interests}</p> : null],
        ['Publications', person.publications ? <RichText html={person.publications} /> : null],
        ['Awards', person.awards?.length ? <Entries items={person.awards} /> : null],
    ].filter(([, content]) => content);

    return (
        <>
            <section className="nt-profile-hero">
                <div className="container">
                    <div className="nt-profile-hero__grid">
                        <Media image={person.photo} sizes="(min-width: 992px) 26rem, 90vw" priority alt="" className="nt-profile-hero__media" />
                        <div className="nt-profile-hero__text">
                            <p className="nt-pill nt-hero-in">{person.category ? `${person.category} Faculty` : 'Faculty'}</p>
                            <h1 className="nt-hero-in" style={{ '--d': 1 }}>{person.name}</h1>
                            {person.designation ? <p className="nt-profile-hero__role nt-hero-in" style={{ '--d': 2 }}>{person.designation}</p> : null}
                            {person.department ? <p className="mb-2 nt-hero-in" style={{ '--d': 2 }}>{person.department}</p> : null}
                            {person.school ? (
                                <p className="mb-3 nt-hero-in" style={{ '--d': 2 }}>
                                    <SmartLink href={person.school.url}>{person.school.title}</SmartLink>
                                </p>
                            ) : null}
                            {show('contact') ? (
                                <div className="d-flex flex-wrap gap-3 nt-hero-in" style={{ '--d': 3 }}>
                                    {person.email ? <a className="nt-btn" href={`mailto:${person.email}`}>Email</a> : null}
                                    {person.profile_url ? <SmartLink className="nt-btn nt-btn--outline" href={person.profile_url} newTab>Profile</SmartLink> : null}
                                </div>
                            ) : null}
                        </div>
                    </div>
                </div>
            </section>
            <Breadcrumbs items={breadcrumbs} />
            {show('biography') || (panels.length && show('details')) || (person.programs?.length && show('programs')) ? (
                <section className="nt-section">
                    <div className="container">
                        <div className="row g-5">
                            <div className="col-lg-4"><h2 className="nt-h2">{show('biography') ? 'Biography' : 'Profile'}</h2></div>
                            <div className="col-lg-8">
                                {show('biography') ? (person.bio ? <ReadMore html={person.bio} /> : person.summary ? <p className="nt-lead">{person.summary}</p> : <p className="text-muted">A full biography will be published soon.</p>) : null}
                                {panels.length && show('details') ? (
                                    <div className={`nt-faq nt-profile-acc ${show('biography') ? 'mt-5' : ''}`}>
                                        {panels.map(([label, content], i) => (
                                            <details key={label} open={i === 0}>
                                                <summary>{label}</summary>
                                                <div>{content}</div>
                                            </details>
                                        ))}
                                    </div>
                                ) : null}
                                {person.programs?.length && show('programs') ? (
                                    <div className="mt-5">
                                        <h2 className="nt-h3">Teaches on</h2>
                                        <ul className="ps-3">
                                            {person.programs.map((p) => (
                                                <li key={p.url}><SmartLink href={p.url}>{p.title}</SmartLink></li>
                                            ))}
                                        </ul>
                                    </div>
                                ) : null}
                            </div>
                        </div>
                    </div>
                </section>
            ) : null}
        </>
    );
}

FacultyShow.layout = (page) => <Layout>{page}</Layout>;
