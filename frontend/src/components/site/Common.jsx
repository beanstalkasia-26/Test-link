import usePage from '../../hooks/usePage';
import SmartLink from '../SmartLink';

export function SectionHeading({ eyebrow, heading, as = 'h2', className = '', id }) {
    const Tag = as;
    if (!heading && !eyebrow) return null;
    return (
        <>
            {eyebrow ? <p className="nt-pill">{eyebrow}</p> : null}
            {heading ? (
                <Tag className={`nt-h2 ${className}`} id={id}>
                    {heading}
                </Tag>
            ) : null}
        </>
    );
}

export function Cta({ label, url, variant = '', className = '' }) {
    if (!label || !url) return null;
    return (
        <SmartLink href={url} className={`nt-btn ${variant ? `nt-btn--${variant}` : ''} ${className}`}>
            {label}
        </SmartLink>
    );
}

export function RichText({ html, className = '' }) {
    if (!html) return null;
    return <div className={`nt-prose ${className}`} dangerouslySetInnerHTML={{ __html: html }} />;
}

export function blockSwitch(hidden) {
    const off = new Set(hidden ?? []);
    return (key) => !off.has(key);
}

export function Breadcrumbs({ items }) {
    const { site } = usePage().props;
    if (!items?.length || site?.settings?.show_breadcrumbs === false) return null;
    return (
        <nav className="nt-breadcrumbs container" aria-label="Breadcrumb">
            <ol>
                <li>
                    <SmartLink href="/">Home</SmartLink>
                </li>
                {items.map((item, i) =>
                    i === items.length - 1 ? (
                        <li key={item.url} aria-current="page">
                            {item.label}
                        </li>
                    ) : (
                        <li key={item.url}>
                            <SmartLink href={item.url}>{item.label}</SmartLink>
                        </li>
                    ),
                )}
            </ol>
        </nav>
    );
}

export function Pagination({ meta }) {
    if (!meta || meta.last_page <= 1) return null;
    return (
        <nav className="nt-pagination" aria-label="Pagination">
            {meta.links.map((link, i) => {
                const label = link.label.replace('&laquo; ', '').replace(' &raquo;', '');
                if (!link.url) return <span key={i} aria-hidden="true">{label}</span>;
                return (
                    <SmartLink key={i} href={link.url} aria-current={link.active ? 'page' : undefined} aria-label={/^\d+$/.test(label) ? `Page ${label}` : label}>
                        {label}
                    </SmartLink>
                );
            })}
        </nav>
    );
}
