import usePage from '../../hooks/usePage';
import { useId, useState } from 'react';
import Icon from '../Icon';
import SmartLink from '../SmartLink';
import NewsletterForm from './NewsletterForm';

function LinkColumn({ title, items, className = '' }) {
    const [open, setOpen] = useState(false);
    const id = useId();
    if (!items?.length) return null;

    return (
        <div className={`nt-footer__accordion ${className}`}>
            <h2>
                <button type="button" aria-expanded={open} aria-controls={id} onClick={() => setOpen((v) => !v)}>
                    {title}
                    <Icon name={open ? 'close' : 'plus'} strokeWidth={1.5} />
                </button>
            </h2>
            <ul id={id} hidden={!open}>
                {items.map((item) => (
                    <li key={item.id}>
                        <SmartLink href={item.url} newTab={item.newTab}>
                            {item.label}
                        </SmartLink>
                    </li>
                ))}
            </ul>
        </div>
    );
}

const socials = [
    ['linkedin_url', 'linkedin', 'LinkedIn'],
    ['x_url', 'x', 'X'],
    ['youtube_url', 'youtube', 'YouTube'],
    ['instagram_url', 'instagram', 'Instagram'],
    ['facebook_url', 'facebook', 'Facebook'],
];

const brandMarks = {
    linkedin: (
        <>
            <rect x="2.5" y="8.5" width="4.2" height="13" />
            <circle cx="4.6" cy="4.4" r="2.4" />
            <path d="M9.6 8.5h4v1.8c.6-1.1 2-2.1 4.1-2.1 4.2 0 4.8 2.7 4.8 6.3v7H18.3v-6.2c0-1.5 0-3.4-2.1-3.4s-2.4 1.6-2.4 3.3v6.3H9.6z" />
        </>
    ),
    x: <path d="M18.24 2.25h3.31l-7.23 8.26 8.5 11.24h-6.65l-5.21-6.82-5.97 6.82H1.68l7.73-8.84L1.25 2.25h6.83l4.71 6.23zm-1.16 17.52h1.83L7.08 4.13H5.12z" />,
    youtube: (
        <path
            fillRule="evenodd"
            d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.8zM9.6 15.6V8.4l6.2 3.6z"
        />
    ),
    instagram: (
        <>
            <rect x="3" y="3" width="18" height="18" rx="5.2" fill="none" stroke="currentColor" strokeWidth="2.2" />
            <circle cx="12" cy="12" r="4.2" fill="none" stroke="currentColor" strokeWidth="2.2" />
            <circle cx="17.4" cy="6.6" r="1.35" />
        </>
    ),
    facebook: <path d="M13.6 22v-8.2h2.8l.4-3.3h-3.2V8.4c0-.9.3-1.6 1.6-1.6h1.7V3.9c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.4H7.4v3.3h2.8V22z" />,
};

export default function Footer() {
    const { site } = usePage().props;
    const { menus = {}, settings = {} } = site ?? {};
    const phones = (settings.phones ?? []).map((p) => p.number).filter(Boolean);
    const shown = (key) => settings[key] !== false;

    return (
        <footer className="nt-footer on-dark">
            <div className="container">
                <div className="nt-footer__top">
                    <div>
                        <SmartLink href="/" className="nt-footer__logo" aria-label="NAMTECH home">
                            <img src="/images/namtech-logo-white.webp" alt="NAMTECH – Institute of Manufacturing Innovation" width="700" height="192" loading="lazy" />
                        </SmartLink>
                        {shown('show_footer_social') ? (
                            <div className="nt-footer__social">
                                {socials
                                    .filter(([key]) => settings[key])
                                    .map(([key, icon, label]) => (
                                        <a key={key} href={settings[key]} target="_blank" rel="noopener noreferrer" aria-label={`${label} (opens in a new tab)`}>
                                            {brandMarks[icon] ? (
                                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
                                                    {brandMarks[icon]}
                                                </svg>
                                            ) : (
                                                <Icon name={icon} strokeWidth={1.8} />
                                            )}
                                        </a>
                                    ))}
                            </div>
                        ) : null}
                    </div>
                    {shown('show_footer_newsletter') ? <NewsletterForm heading={settings.newsletter_heading ?? 'Subscribe to our newsletter'} consent={settings.newsletter_consent} /> : null}
                </div>

                {shown('show_footer_links') ? (
                    <nav className="nt-footer__links" aria-label="Footer">
                        <div className="nt-footer__col nt-footer__programs">
                            <LinkColumn title={settings.footer_programs_title || 'Programs'} items={menus.footer_programs} />
                        </div>
                        <div className="nt-footer__col">
                            <LinkColumn title={settings.footer_schools_title || 'Schools and Centers'} items={menus.footer_schools} />
                            <LinkColumn title={settings.footer_partnerships_title || 'Partnerships'} items={menus.footer_partnerships} className="mt-lg-4" />
                        </div>
                        <div className="nt-footer__col">
                            <LinkColumn title={settings.footer_quick_links_title || 'Quick Links'} items={menus.footer_quick_links} />
                        </div>
                    </nav>
                ) : null}

                {shown('show_footer_contact') ? (
                    <div className="nt-footer__contact">
                        {settings.address ? (
                            <div>
                                <Icon name="pin" />
                                <div>
                                    <strong>{settings.address_title || 'Address'}</strong>
                                    <address>{settings.address}</address>
                                </div>
                            </div>
                        ) : null}
                        {phones.length ? (
                            <div>
                                <Icon name="phone" />
                                <div>
                                    <strong>{settings.footer_contact_title || 'Contact'}</strong>
                                    {phones.map((phone) => (
                                        <a key={phone} href={`tel:${phone.replace(/[^+\d]/g, '')}`} className="d-block">
                                            {phone}
                                        </a>
                                    ))}
                                </div>
                            </div>
                        ) : null}
                        {settings.email ? (
                            <div>
                                <Icon name="mail" />
                                <div>
                                    <strong>Email:</strong>
                                    <a href={`mailto:${settings.email}`}>{settings.email}</a>
                                </div>
                            </div>
                        ) : null}
                    </div>
                ) : null}

                <div className="nt-footer__bottom">
                    <span>{settings.copyright}</span>
                    {(menus.legal ?? []).map((item) => (
                        <SmartLink key={item.id} href={item.url}>
                            {item.label}
                        </SmartLink>
                    ))}
                </div>
            </div>
        </footer>
    );
}
