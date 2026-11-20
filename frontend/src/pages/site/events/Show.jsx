import Carousel from '../../../components/Carousel';
import Icon from '../../../components/Icon';
import { EventCard } from '../../../components/site/Cards';
import { blockSwitch, Breadcrumbs, RichText } from '../../../components/site/Common';
import Layout from '../../../components/site/Layout';
import { Media } from '../../../components/ResponsiveImage';
import SmartLink from '../../../components/SmartLink';

export default function EventShow({ event, related, breadcrumbs, hidden }) {
    const show = blockSwitch(hidden);
    const meta = [
        ['calendar', event.date],
        ['clock', event.time],
        ['pin', event.venue],
    ].filter(([, v]) => v);
    const speaker = event.speaker;

    return (
        <>
            <section className="nt-plain-hero nt-plain-hero--event">
                <div className="container">
                    {event.category ? <p className="nt-pill nt-hero-in">{event.category}</p> : null}
                    <h1 className="nt-hero-in" style={{ '--d': 1 }}>{event.title}</h1>
                    {event.subtitle ? <p className="nt-plain-hero__body nt-hero-in" style={{ '--d': 2 }}>{event.subtitle}</p> : null}
                    <ul className="nt-meta-list nt-meta-list--row nt-page-hero__meta nt-hero-in" style={{ '--d': 2 }}>
                        {meta.map(([icon, text]) => <li key={icon}><Icon name={icon} strokeWidth={1.6} />{text}</li>)}
                    </ul>
                    {event.registration_url ? <SmartLink href={event.registration_url} className="nt-btn mt-3 nt-hero-in" style={{ '--d': 3 }}>Register Now</SmartLink> : null}
                </div>
                {event.image && show('hero_image') ? (
                    <div className="container mt-4">
                        <Media image={event.image} sizes="100vw" priority className="nt-plain-hero__media" />
                    </div>
                ) : null}
            </section>
            <Breadcrumbs items={breadcrumbs} />
            {(event.overview || event.summary) && show('overview') ? (
                <section className="nt-section">
                    <div className="container">
                        <div className="row g-5">
                            <div className="col-lg-4"><h2 className="nt-h2">Overview</h2></div>
                            <div className="col-lg-8">{event.overview ? <RichText html={event.overview} /> : <p>{event.summary}</p>}</div>
                        </div>
                    </div>
                </section>
            ) : null}
            {event.outcomes?.length && show('outcomes') ? (
                <section className="nt-section nt-tone-lavender">
                    <div className="container">
                        <h2 className="nt-h2">What you’ll learn</h2>
                        <ol className="nt-numbered nt-stagger">
                            {event.outcomes.map((text, i) => (
                                <li key={i}><span>{String(i + 1).padStart(2, '0')}</span>{text}</li>
                            ))}
                        </ol>
                    </div>
                </section>
            ) : null}
            {speaker?.name && show('speaker') ? (
                <section className="nt-section">
                    <div className="container">
                        <p className="nt-pill">Speaker</p>
                        <div className="nt-speaker">
                            {speaker.photo ? <Media image={speaker.photo} sizes="(min-width: 992px) 24rem, 90vw" alt="" className="nt-speaker__media" /> : null}
                            <div>
                                <h2 className="nt-h2 mb-1">{speaker.name}</h2>
                                {speaker.role ? <p className="nt-speaker__role">{speaker.role}</p> : null}
                                {speaker.quote ? <blockquote className="nt-speaker__quote">“{speaker.quote}”</blockquote> : null}
                                <RichText html={speaker.bio} />
                            </div>
                        </div>
                    </div>
                </section>
            ) : null}
            {event.registration_url && show('registration') ? (
                <section className="nt-cta-band">
                    <div className="container">
                        <div><h2>Reserve your place</h2><p className="m-0">Seats are limited. Register to attend {event.title}.</p></div>
                        <SmartLink href={event.registration_url} className="nt-btn">Register Now</SmartLink>
                    </div>
                </section>
            ) : null}
            {related.length && show('related') ? (
                <section className="nt-section">
                    <div className="container">
                        <h2 className="nt-h2">Other Events</h2>
                        <Carousel label="Other events" slideWidth="min(22rem, 85%)" gap="1.5rem">
                            {related.map((e) => <EventCard key={e.id} event={e} />)}
                        </Carousel>
                    </div>
                </section>
            ) : null}
        </>
    );
}

EventShow.layout = (page) => <Layout>{page}</Layout>;
