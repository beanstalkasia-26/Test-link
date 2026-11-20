import { useState } from 'react';
import Icon from '../../components/Icon';
import { blockSwitch, Breadcrumbs, RichText } from '../../components/site/Common';
import Layout from '../../components/site/Layout';
import { Media } from '../../components/ResponsiveImage';

export function ReadMore({ html, summary }) {
    const [open, setOpen] = useState(false);
    if (!html) return summary ? <p className="nt-lead">{summary}</p> : null;
    return (
        <div className={`nt-readmore ${open ? 'is-open' : ''}`}>
            <RichText html={html} />
            <button type="button" className="nt-readmore__toggle" aria-expanded={open} onClick={() => setOpen(!open)}>
                {open ? 'Read less' : 'Read more'} <Icon name="chevronDown" strokeWidth={2} />
            </button>
        </div>
    );
}

export default function Leader({ person, breadcrumbs, hidden }) {
    const show = blockSwitch(hidden);
    return (
        <>
            <section className="nt-profile-hero">
                <div className="container">
                    <div className="nt-profile-hero__grid">
                        <Media image={person.photo} sizes="(min-width: 992px) 26rem, 90vw" priority alt="" className="nt-profile-hero__media" />
                        <div className="nt-profile-hero__text">
                            {person.group ? <p className="nt-pill nt-hero-in">{person.group}</p> : null}
                            <h1 className="nt-hero-in" style={{ '--d': 1 }}>{person.name}</h1>
                            {person.designation ? <p className="nt-profile-hero__role nt-hero-in" style={{ '--d': 2 }}>{person.designation}</p> : null}
                            {person.organisation ? <p className="mb-0 nt-hero-in" style={{ '--d': 2 }}>{person.organisation}</p> : null}
                        </div>
                    </div>
                </div>
            </section>
            <Breadcrumbs items={breadcrumbs} />
            {show('biography') ? (
                <section className="nt-section">
                    <div className="container">
                        <div className="row g-5">
                            <div className="col-lg-4"><h2 className="nt-h2">Biography</h2></div>
                            <div className="col-lg-8"><ReadMore html={person.bio} summary={person.summary} /></div>
                        </div>
                    </div>
                </section>
            ) : null}
            {person.achievements?.length && show('achievements') ? (
                <section className="nt-section nt-tone-light">
                    <div className="container">
                        <div className="row g-5">
                            <div className="col-lg-4"><h2 className="nt-h2">Awards & Achievements</h2></div>
                            <div className="col-lg-8">
                                <ol className="nt-achievements">
                                    {person.achievements.map((a, i) => (
                                        <li key={i}>
                                            <div className="nt-achievements__value"><strong>{a.year}</strong></div>
                                            <div className="nt-achievements__text">
                                                <h3>{a.title}</h3>
                                                {a.text ? <p>{a.text}</p> : null}
                                            </div>
                                            <Icon name="award" strokeWidth={1.1} className="nt-achievements__icon" />
                                        </li>
                                    ))}
                                </ol>
                            </div>
                        </div>
                    </div>
                </section>
            ) : null}
        </>
    );
}

Leader.layout = (page) => <Layout>{page}</Layout>;
