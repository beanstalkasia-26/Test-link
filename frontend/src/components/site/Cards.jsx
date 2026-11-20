import Icon, { ArrowDot } from '../Icon';
import { Media } from '../ResponsiveImage';
import SmartLink from '../SmartLink';

export function ProgramCard({ item, headingLevel = 3, buttons = false }) {
    const Heading = `h${headingLevel}`;
    const facts = [
        ['Mode', item.location || item.mode, item.location ? item.mode : null],
        ['Intake', item.intake, item.deadline ? `Deadline ${item.deadline}` : null],
        ['Duration', item.duration, item.duration_detail],
    ].filter(([, value]) => value);

    return (
        <article className="nt-program-card">
            <Media className="nt-program-card__media" image={item.image} sizes="(min-width: 1200px) 560px, (min-width: 768px) 50vw, 90vw" alt="" />
            <div className="nt-program-card__panel">
                <Heading>{item.url ? <SmartLink href={item.url} className="stretched-link">{item.title}</SmartLink> : item.title}</Heading>
                {item.level || item.tagline ? <p className="nt-program-card__kicker">{item.level ?? item.tagline}</p> : null}
                {item.summary ? <p>{item.summary}</p> : null}
                {facts.length ? (
                    <dl className="nt-program-card__facts">
                        {facts.map(([label, value, detail]) => (
                            <div key={label}>
                                <dt>{label}</dt>
                                <dd>
                                    {value}
                                    {detail ? <small>{detail}</small> : null}
                                </dd>
                            </div>
                        ))}
                    </dl>
                ) : item.url && !buttons ? (
                    <span className="nt-link-arrow text-white mt-auto" aria-hidden="true">
                        Learn more <ArrowDot />
                    </span>
                ) : null}
                {buttons ? (
                    <div className="nt-program-card__buttons">
                        {item.apply_url ? (
                            <SmartLink href={item.apply_url} className="nt-btn nt-btn--light">
                                Apply now<span className="visually-hidden">: {item.title}</span>
                            </SmartLink>
                        ) : null}
                        {item.url ? (
                            <SmartLink href={item.url} className="nt-btn nt-prog-hero__ghost">
                                Learn more<span className="visually-hidden">: {item.title}</span>
                            </SmartLink>
                        ) : null}
                    </div>
                ) : null}
            </div>
        </article>
    );
}

export function StoryCard({ item, textOnly = false }) {
    return (
        <article className={`nt-story-card${textOnly ? ' nt-story-card--text' : ''}`}>
            {textOnly ? null : <Media className="nt-story-card__media" image={item.image} sizes="(min-width: 1200px) 400px, (min-width: 768px) 40vw, 90vw" alt="" />}
            <div className="nt-story-card__body">
                <h3>{item.name}</h3>
                {item.summary ? <p>{item.summary}</p> : null}
                {item.url ? (
                    <SmartLink href={item.url} className="nt-link-arrow">
                        Read story<span className="visually-hidden">: {item.name}</span> <ArrowDot />
                    </SmartLink>
                ) : null}
                {item.highlight ? (
                    <p className="nt-story-card__highlight">
                        <Icon name="trend" />
                        {item.highlight}
                    </p>
                ) : null}
            </div>
        </article>
    );
}

export function PersonCard({ person, headingLevel = 3 }) {
    const Heading = `h${headingLevel}`;
    return (
        <article className="nt-person-card">
            <Media image={person.photo} sizes="(min-width: 1200px) 290px, (min-width: 576px) 45vw, 80vw" alt="" />
            <div className="nt-person-card__info">
                <Heading>
                    <SmartLink href={person.url} className="stretched-link">
                        {person.name}
                    </SmartLink>
                </Heading>
                {person.designation ? <p className="nt-person-card__role">{person.designation}</p> : null}
                {person.phone ? <p>{person.phone}</p> : null}
                <span className="nt-person-card__plus" aria-hidden="true">
                    <Icon name="plus" strokeWidth={2.4} />
                </span>
            </div>
        </article>
    );
}

export function PostCard({ post, headingLevel = 3 }) {
    const Heading = `h${headingLevel}`;
    return (
        <article className="nt-post-card">
            <Media className="nt-post-card__media" image={post.image} sizes="(min-width: 1200px) 340px, (min-width: 768px) 33vw, 85vw" alt="" />
            <div className="nt-post-card__meta">
                {post.reading_minutes ? <span className="nt-tag">{post.reading_minutes} min{post.reading_minutes === 1 ? '' : 's'} read</span> : null}
                {!post.url && post.type ? <span className="nt-tag">{post.type}</span> : null}
                {post.date ? <time dateTime={post.datetime}>{post.date}</time> : null}
            </div>
            <Heading>
                {post.url ? <SmartLink href={post.url} className="stretched-link">
                    {post.title}
                </SmartLink> : post.title}
            </Heading>
            {post.url ? <span className="nt-link-arrow mt-auto" aria-hidden="true">Read <ArrowDot /></span> : <p>{post.excerpt}</p>}
        </article>
    );
}

export function IconCard({ icon, title, body, url, outlined = false, headingLevel = 3 }) {
    const Heading = `h${headingLevel}`;
    return (
        <div className={`nt-icon-card ${outlined ? 'nt-icon-card--outlined' : ''}`}>
            <span className="nt-icon-card__icon">
                <Icon name={icon || 'graduation'} strokeWidth={1.1} />
            </span>
            <Heading>{url ? <SmartLink href={url} className="stretched-link text-decoration-none">{title}</SmartLink> : title}</Heading>
            {body ? <p>{body}</p> : null}
        </div>
    );
}

export function SimpleCard({ item, headingLevel = 3, linkLabel }) {
    const Heading = `h${headingLevel}`;
    return (
        <article className="nt-simple-card">
            <Media image={item.image} sizes="(min-width: 1200px) 300px, (min-width: 768px) 33vw, 90vw" alt="" />
            {item.tag ? <span className="nt-tag align-self-start">{item.tag}</span> : null}
            <Heading>{item.url ? <SmartLink href={item.url} className="stretched-link">{item.title}</SmartLink> : item.title}</Heading>
            {item.summary ? <p>{item.summary}</p> : null}
            {item.url && linkLabel ? (
                <span className="nt-link-arrow mt-auto" aria-hidden="true">
                    {linkLabel} <ArrowDot />
                </span>
            ) : null}
        </article>
    );
}

export function EventCard({ event, headingLevel = 3 }) {
    const Heading = `h${headingLevel}`;
    return (
        <article className="nt-event-card">
            <Media className="nt-event-card__media" image={event.image} sizes="(min-width: 1200px) 360px, (min-width: 768px) 40vw, 85vw" alt="" />
            <div className="nt-event-card__body">
                {event.category ? <span className="nt-tag align-self-start">{event.category}</span> : null}
                <Heading>
                    <SmartLink href={event.url} className="stretched-link">
                        {event.title}
                    </SmartLink>
                </Heading>
                <ul className="nt-meta-list">
                    {event.date ? (
                        <li>
                            <Icon name="calendar" strokeWidth={1.6} />
                            <time dateTime={event.datetime}>{event.date}</time>
                        </li>
                    ) : null}
                    {event.time ? (
                        <li>
                            <Icon name="clock" strokeWidth={1.6} />
                            {event.time}
                        </li>
                    ) : null}
                    {event.venue ? (
                        <li>
                            <Icon name="pin" strokeWidth={1.6} />
                            {event.venue}
                        </li>
                    ) : null}
                </ul>
            </div>
        </article>
    );
}

export function PublicationCard({ item, headingLevel = 3 }) {
    const Heading = `h${headingLevel}`;
    return (
        <article className="nt-pub-card">
            <Media className="nt-pub-card__media" image={item.image} sizes="(min-width: 1200px) 360px, (min-width: 768px) 40vw, 85vw" alt="" />
            <div className="nt-pub-card__body">
                {item.school || item.topic ? <span className="nt-tag align-self-start">{item.topic || item.school}</span> : null}
                <Heading>
                    <SmartLink href={item.url} className="stretched-link">
                        {item.title}
                    </SmartLink>
                </Heading>
                {item.authors ? <p className="nt-pub-card__authors">{item.authors}</p> : null}
                <p className="nt-pub-card__meta">{[item.publisher, item.date].filter(Boolean).join(' · ')}</p>
            </div>
        </article>
    );
}

function FacultyCard({ person, headingLevel = 3 }) {
    const Heading = `h${headingLevel}`;
    const details = [person.designation, person.department].filter(Boolean);
    return (
        <article className="nt-faculty-card">
            <Media className="nt-faculty-card__media" image={person.photo} sizes="(min-width: 1200px) 400px, (min-width: 768px) 45vw, 90vw" alt="" />
            <Heading className="nt-faculty-card__name">
                <SmartLink href={person.url} className="stretched-link">
                    {person.name}
                </SmartLink>
            </Heading>
            {details.length ? (
                <p className="nt-faculty-card__role">
                    {details.map((text, i) => (
                        <span key={i}>
                            {i > 0 ? <span className="nt-faculty-card__sep" aria-hidden="true">|</span> : null}
                            {text}
                        </span>
                    ))}
                </p>
            ) : null}
        </article>
    );
}

export function FacultyGrid({ people, headingLevel = 3 }) {
    return (
        <div className="nt-faculty-grid nt-stagger">
            {people.map((person) => (
                <FacultyCard key={person.id} person={person} headingLevel={headingLevel} />
            ))}
        </div>
    );
}
