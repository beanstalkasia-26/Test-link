import Head from '../Head';
import Link from '../../router/Link';
import { router } from '../../router/navigation';
import usePage from '../../hooks/usePage';
import { useEffect, useState } from 'react';

function Toasts() {
    const { flash } = usePage().props;
    const [items, setItems] = useState([]);

    useEffect(() => {
        const next = [];
        if (flash?.success) next.push({ id: Date.now() + 's', type: 'success', text: flash.success });
        if (flash?.error) next.push({ id: Date.now() + 'e', type: 'danger', text: flash.error });
        if (flash?.status) next.push({ id: Date.now() + 't', type: 'info', text: flash.status });
        if (next.length) {
            setItems((current) => [...current, ...next]);
            const timer = setTimeout(() => setItems((current) => current.filter((i) => !next.includes(i))), 7000);
            return () => clearTimeout(timer);
        }
        return undefined;
    }, [flash]);

    return (
        <div className="toast-stack" role="status" aria-live="polite">
            {items.map((item) => (
                <div key={item.id} className={`alert alert-${item.type} shadow-sm mb-0 d-flex justify-content-between gap-3`}>
                    <span>{item.text}</span>
                    <button type="button" className="btn-close" aria-label="Dismiss" onClick={() => setItems((c) => c.filter((i) => i.id !== item.id))} />
                </div>
            ))}
        </div>
    );
}

export default function AdminLayout({ title, actions, children }) {
    const { auth, navigation } = usePage().props;
    const [open, setOpen] = useState(false);
    const path = typeof window === 'undefined' ? '' : window.location.pathname;

    useEffect(() => router.on('navigate', () => setOpen(false)), []);

    return (
        <div className="admin-shell">
            <Head title={`${title ? `${title} · ` : ''}NAMTECH CMS`} />
            <a href="#admin-main" className="visually-hidden-focusable position-absolute top-0 start-0 p-2 bg-white z-3">
                Skip to content
            </a>
            <aside className={`admin-sidebar ${open ? 'is-open' : ''}`} aria-label="Admin navigation">
                <div className="brand">
                    <img src="/images/namtech-logo-white.webp" alt="NAMTECH" width="700" height="192" />
                    <span className="badge text-bg-light">CMS</span>
                </div>
                <nav>
                    <Link href="/admin" aria-current={path === '/admin' ? 'page' : undefined}>
                        Dashboard
                    </Link>
                    {(navigation ?? []).map((group) => (
                        <div key={group.group}>
                            <h2>{group.group}</h2>
                            {group.items.map((item) => (
                                <Link key={item.url} href={item.url} aria-current={path.startsWith('/' + item.match) ? 'page' : undefined}>
                                    {item.label}
                                </Link>
                            ))}
                        </div>
                    ))}
                    <h2>Website</h2>
                    <a href="/" target="_blank" rel="noopener">
                        View live site ↗
                    </a>
                </nav>
            </aside>
            {open ? <div className="offcanvas-backdrop fade show d-lg-none" onClick={() => setOpen(false)} /> : null}
            <div className="d-flex flex-column min-w-0">
                <header className="admin-topbar">
                    <div className="d-flex align-items-center gap-2 min-w-0">
                        <button type="button" className="btn btn-outline-secondary btn-sm d-lg-none" onClick={() => setOpen(true)} aria-label="Open navigation">
                            ☰
                        </button>
                        <span className="fw-medium text-truncate">{title}</span>
                    </div>
                    <div className="d-flex align-items-center gap-2 gap-sm-3 flex-shrink-0">
                        <span className="small text-secondary d-none d-md-inline">
                            {auth?.user?.name} · {auth?.user?.role}
                        </span>
                        <Link href="/admin/account" className="btn btn-sm btn-outline-secondary text-nowrap">
                            <span className="d-none d-sm-inline">Account & security</span>
                            <span className="d-sm-none">Account</span>
                        </Link>
                        <Link href="/admin/logout" method="post" as="button" className="btn btn-sm btn-outline-danger text-nowrap">
                            Sign out
                        </Link>
                    </div>
                </header>
                <main id="admin-main" className="admin-main" tabIndex={-1}>
                    {title || actions ? (
                        <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                            <h1 className="h3 mb-0">{title}</h1>
                            {actions ? <div className="d-flex flex-wrap gap-2">{actions}</div> : null}
                        </div>
                    ) : null}
                    {children}
                </main>
            </div>
            <Toasts />
        </div>
    );
}

export function StatusBadge({ status, label, review, pending }) {
    return (
        <span className="d-inline-flex flex-wrap gap-1">
            {status ? <span className={`status-badge status-badge--${status}`}>{label ?? status}</span> : null}
            {review === 'pending' ? <span className="status-badge status-badge--review">In review</span> : null}
            {pending ? <span className="status-badge status-badge--pending">Unpublished changes</span> : null}
        </span>
    );
}

export function Paginator({ meta }) {
    if (!meta || meta.last_page <= 1) return null;
    return (
        <nav aria-label="Pagination" className="mt-3">
            <ul className="pagination pagination-sm flex-wrap">
                {meta.links.map((link, i) => (
                    <li key={i} className={`page-item ${link.active ? 'active' : ''} ${!link.url ? 'disabled' : ''}`}>
                        {link.url ? (
                            <Link className="page-link" href={link.url} preserveScroll dangerouslySetInnerHTML={{ __html: link.label }} />
                        ) : (
                            <span className="page-link" dangerouslySetInnerHTML={{ __html: link.label }} />
                        )}
                    </li>
                ))}
            </ul>
        </nav>
    );
}
