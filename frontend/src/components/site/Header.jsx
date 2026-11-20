import { router } from '../../router/navigation';
import usePage from '../../hooks/usePage';
import { useCallback, useEffect, useId, useRef, useState } from 'react';
import Icon, { Tour360Icon } from '../Icon';
import SmartLink from '../SmartLink';
import useStickyBars from '../../hooks/useStickyBars';

function isCurrent(url, current) {
    if (!url || !url.startsWith('/')) return false;
    const path = url.split('?')[0];
    return path === '/' ? current === '/' : current === path || current.startsWith(path + '/');
}

const hasChildren = (item) => item.children?.length > 0;

function currentIn(items, current) {
    let best = null;
    let length = -1;
    for (const item of items ?? []) {
        const path = item.url?.split('?')[0];
        if (isCurrent(item.url, current) && path.length > length) {
            best = item.id;
            length = path.length;
        }
    }
    return best;
}

const containsCurrent = (item, current) => isCurrent(item.url, current) || (item.children ?? []).some((child) => containsCurrent(child, current));
const isMega = (item) => item.children?.some(hasChildren);
const canHover = () => typeof window !== 'undefined' && window.matchMedia('(hover: hover) and (pointer: fine)').matches;
const linksTo = (item, current) => item.url?.split('?')[0] === current || item.children?.some((child) => linksTo(child, current));

function findActiveItem(items, current) {
    return (items.find((item) => isCurrent(item.url, current)) ?? items.find((item) => linksTo(item, current)))?.id;
}

function DropdownPanel({ id, item, open, current, onNavigate }) {
    const here = currentIn(item.children, current);
    return (
        <div className="nt-dropdown" id={id} data-open={open} aria-hidden={!open}>
            <ul>
                {item.children.map((child, i) => (
                    <li key={child.id} style={{ '--i': i }}>
                        <SmartLink href={child.url} newTab={child.newTab} onClick={onNavigate} tabIndex={open ? undefined : -1} aria-current={child.id === here ? 'page' : undefined}>
                            {child.label}
                        </SmartLink>
                    </li>
                ))}
            </ul>
        </div>
    );
}

function MegaPanel({ id, item, open, current, onNavigate }) {
    const currentGroup = item.children.findIndex((child) => hasChildren(child) && containsCurrent(child, current));
    const firstWithChildren = currentGroup >= 0 ? currentGroup : Math.max(0, item.children.findIndex(hasChildren));
    const [active, setActive] = useState(firstWithChildren);
    const group = item.children[active];

    useEffect(() => {
        if (!open) setActive(firstWithChildren);
    }, [open, firstWithChildren]);

    return (
        <div className="nt-mega" id={id} data-open={open} aria-hidden={!open}>
            <div className="nt-mega__inner">
                <ul className="nt-mega__groups">
                    {item.children.map((child, i) => (
                        <li key={child.id} style={{ '--i': i }}>
                            {hasChildren(child) ? (
                                <button
                                    type="button"
                                    className={`nt-mega__group${i === currentGroup ? ' is-current' : ''}`}
                                    aria-expanded={i === active}
                                    aria-controls={`${id}-links`}
                                    tabIndex={open ? undefined : -1}
                                    onMouseEnter={() => setActive(i)}
                                    onFocus={() => setActive(i)}
                                    onClick={() => setActive(i)}
                                >
                                    {child.label}
                                    <Icon name="chevronRight" strokeWidth={1.6} />
                                </button>
                            ) : (
                                <SmartLink className="nt-mega__group" aria-current={isCurrent(child.url, current) ? 'page' : undefined} href={child.url} newTab={child.newTab} onClick={onNavigate} tabIndex={open ? undefined : -1}>
                                    {child.label}
                                </SmartLink>
                            )}
                        </li>
                    ))}
                </ul>
                <div className="nt-mega__links" id={`${id}-links`}>
                    {hasChildren(group) ? (
                        <ul key={group.id} aria-label={group.label}>
                            {group.children.map((link, i) => (
                                <li key={link.id} style={{ '--i': i }}>
                                    <SmartLink href={link.url} newTab={link.newTab} onClick={onNavigate} tabIndex={open ? undefined : -1} aria-current={link.id === currentIn(group.children, current) ? 'page' : undefined}>
                                        {link.label}
                                    </SmartLink>
                                </li>
                            ))}
                        </ul>
                    ) : null}
                </div>
            </div>
        </div>
    );
}

function NavItem({ item, current, activeId, openId, setOpenId, scheduleClose, cancelClose }) {
    const id = useId();
    const open = openId === item.id;
    const active = activeId === item.id;
    const mega = isMega(item);
    const buttonRef = useRef(null);

    if (!hasChildren(item)) {
        return (
            <li className="nt-nav-item" onMouseEnter={() => canHover() && setOpenId(null)}>
                <SmartLink className="nt-nav-link" href={item.url} newTab={item.newTab} aria-current={active ? 'page' : undefined}>
                    {item.label}
                </SmartLink>
            </li>
        );
    }

    return (
        <li
            className={`nt-nav-item ${mega ? 'nt-nav-item--mega' : ''}`}
            onMouseEnter={() => {
                if (!canHover()) return;
                cancelClose();
                setOpenId(item.id, openId === null ? 90 : 0);
            }}
            onMouseLeave={() => canHover() && scheduleClose()}
            onKeyDown={(e) => {
                if (e.key === 'Escape' && open) {
                    e.stopPropagation();
                    setOpenId(null);
                    buttonRef.current?.focus();
                }
            }}
        >
            <button
                ref={buttonRef}
                type="button"
                className={`nt-nav-link ${active ? 'is-active' : ''}`}
                aria-expanded={open}
                aria-controls={id}
                onClick={() => setOpenId(open ? null : item.id)}
            >
                {item.label}
                <Icon name="chevronDown" strokeWidth={1.6} />
            </button>
            {mega ? (
                <MegaPanel id={id} item={item} open={open} current={current} onNavigate={() => setOpenId(null)} />
            ) : (
                <DropdownPanel id={id} item={item} open={open} current={current} onNavigate={() => setOpenId(null)} />
            )}
        </li>
    );
}

function MobileList({ items, onClose, current, level = 1 }) {
    const [openId, setOpenId] = useState(() => items.find((item) => hasChildren(item) && containsCurrent(item, current))?.id ?? null);
    const here = currentIn(items, current);
    return (
        <ul className={level === 1 ? 'nt-mobile-menu__list' : `nt-mobile-menu__sub nt-mobile-menu__sub--${level}`}>
            {items.map((item) => {
                const open = openId === item.id;
                const href = item.url ?? item.children?.[0]?.url;
                return (
                    <li key={item.id}>
                        <div className="nt-mobile-menu__row">
                            {href ? (
                                <SmartLink href={href} newTab={item.newTab} onClick={onClose} aria-current={item.id === here && !hasChildren(item) ? 'page' : undefined} className={hasChildren(item) && containsCurrent(item, current) ? 'is-active' : undefined}>
                                    {item.label}
                                </SmartLink>
                            ) : (
                                <span>{item.label}</span>
                            )}
                            {hasChildren(item) ? (
                                <button type="button" aria-expanded={open} aria-controls={`m-sub-${item.id}`} aria-label={`${item.label} submenu`} onClick={() => setOpenId(open ? null : item.id)}>
                                    <Icon name="chevronDown" strokeWidth={2} />
                                </button>
                            ) : null}
                        </div>
                        {hasChildren(item) ? (
                            <div id={`m-sub-${item.id}`} hidden={!open}>
                                <MobileList items={item.children} onClose={onClose} current={current} level={level + 1} />
                            </div>
                        ) : null}
                    </li>
                );
            })}
        </ul>
    );
}

function MobileMenu({ menus, onClose, settings, current }) {
    const panelRef = useRef(null);
    const closeRef = useRef(onClose);
    closeRef.current = onClose;

    useEffect(() => {
        const previous = document.activeElement;
        document.body.style.overflow = 'hidden';
        panelRef.current?.querySelector('button')?.focus();
        const onKey = (e) => {
            if (e.key === 'Escape') closeRef.current();
            if (e.key === 'Tab') {
                const focusable = panelRef.current.querySelectorAll('a[href], button:not([disabled])');
                const visible = [...focusable].filter((el) => el.offsetParent !== null);
                const first = visible[0];
                const last = visible[visible.length - 1];
                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
        };
        document.addEventListener('keydown', onKey);
        return () => {
            document.body.style.overflow = '';
            document.removeEventListener('keydown', onKey);
            previous?.focus?.();
        };
    }, []);

    return (
        <div className="nt-mobile-menu" role="dialog" aria-modal="true" aria-label="Site menu" ref={panelRef}>
            <div className="nt-mobile-menu__top">
                <span className="nt-logo">
                    <img src="/images/namtech-logo.webp" alt="NAMTECH – Institute of Manufacturing Innovation" width="700" height="192" />
                </span>
                <button type="button" className="nt-icon-btn nt-mobile-menu__close" onClick={onClose} aria-label="Close menu">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" aria-hidden="true" focusable="false">
                        <path d="M6.3 6.3l11.4 11.4M17.7 6.3 6.3 17.7" />
                    </svg>
                </button>
            </div>
            <nav aria-label="Mobile">
                <MobileList items={[...(menus.primary ?? []), ...(menus.utility ?? [])]} onClose={onClose} current={current} />
            </nav>
            <div className="nt-mobile-menu__secondary">
                {settings.show_search !== false ? (
                    <SmartLink href="/search" onClick={onClose}>
                        Search the site
                    </SmartLink>
                ) : null}
                {settings.show_virtual_tour !== false ? (
                    <SmartLink href={settings.virtual_tour_url || '/campus/virtual-tour'} newTab={Boolean(settings.virtual_tour_url)} onClick={onClose}>
                        360° virtual tour
                    </SmartLink>
                ) : null}
                <SmartLink href="/contact" onClick={onClose}>
                    Contact us
                </SmartLink>
            </div>
        </div>
    );
}

function Announcements({ items }) {
    const [index, setIndex] = useState(0);
    if (!items?.length) return null;
    const item = items[index % items.length];
    const multiple = items.length > 1;

    return (
        <div className="nt-announcement" role="region" aria-label="Announcements">
            <div className="container">
                {multiple ? (
                    <button type="button" className="nt-announcement__nav" aria-label="Previous announcement" onClick={() => setIndex((index - 1 + items.length) % items.length)}>
                        <Icon name="chevronLeft" strokeWidth={2} />
                    </button>
                ) : (
                    <span className="nt-announcement__nav" aria-hidden="true">
                        <Icon name="chevronLeft" strokeWidth={2} />
                    </span>
                )}
                <p className="m-0 d-flex align-items-center gap-2" aria-live="polite">
                    {item.label ? <span className="nt-announcement__label">{item.label}</span> : null}
                    {item.url ? <SmartLink href={item.url}>{item.text}</SmartLink> : <span>{item.text}</span>}
                </p>
                {multiple ? (
                    <button type="button" className="nt-announcement__nav" aria-label="Next announcement" onClick={() => setIndex((index + 1) % items.length)}>
                        <Icon name="chevronRight" strokeWidth={2} />
                    </button>
                ) : (
                    <span className="nt-announcement__nav" aria-hidden="true">
                        <Icon name="chevronRight" strokeWidth={2} />
                    </span>
                )}
            </div>
        </div>
    );
}

function useHideOnScroll(headerRef, pinned) {
    const [hidden, setHidden] = useState(false);
    const [scrolled, setScrolled] = useState(false);

    useEffect(() => {
        let last = window.scrollY;
        let frame = 0;
        const measure = () => {
            const header = headerRef.current;
            const navbar = header?.querySelector('.nt-mainbar');
            if (header && navbar) header.style.setProperty('--nt-header-collapse', `${navbar.offsetTop}px`);
        };
        measure();
        window.addEventListener('resize', measure);
        const onScroll = () => {
            cancelAnimationFrame(frame);
            frame = requestAnimationFrame(() => {
                const y = Math.max(0, window.scrollY);
                const height = headerRef.current?.offsetHeight ?? 150;
                setScrolled(y > 8);
                if (y < height) setHidden(false);
                else if (y > last + 6) setHidden(true);
                else if (y < last - 6) setHidden(false);
                last = y;
            });
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
        return () => {
            window.removeEventListener('scroll', onScroll);
            window.removeEventListener('resize', measure);
            cancelAnimationFrame(frame);
        };
    }, [headerRef]);

    return { hidden: hidden && !pinned, scrolled };
}

export default function Header() {
    const page = usePage();
    const current = (page.url || '/').split('?')[0];
    const { menus = {}, settings = {}, announcements = [] } = page.props.site ?? {};
    const shown = (key) => settings[key] !== false;
    const [mobileOpen, setMobileOpen] = useState(false);
    const [searchOpen, setSearchOpen] = useState(false);
    const [openId, setOpenIdState] = useState(null);
    const [focusWithin, setFocusWithin] = useState(false);
    const headerRef = useRef(null);
    const searchRef = useRef(null);
    const timers = useRef({ open: 0, close: 0 });

    const cancelClose = useCallback(() => clearTimeout(timers.current.close), []);
    const setOpenId = useCallback((id, delay = 0) => {
        clearTimeout(timers.current.open);
        clearTimeout(timers.current.close);
        if (delay) timers.current.open = setTimeout(() => setOpenIdState(id), delay);
        else setOpenIdState(id);
    }, []);
    const scheduleClose = useCallback(() => {
        clearTimeout(timers.current.open);
        clearTimeout(timers.current.close);
        timers.current.close = setTimeout(() => setOpenIdState(null), 220);
    }, []);

    const { hidden, scrolled } = useHideOnScroll(headerRef, openId !== null || searchOpen || focusWithin);
    useStickyBars(headerRef, hidden);

    useEffect(() => {
        if (searchOpen) searchRef.current?.focus();
    }, [searchOpen]);

    useEffect(
        () =>
            router.on('navigate', () => {
                setMobileOpen(false);
                setOpenIdState(null);
                setSearchOpen(false);
            }),
        [],
    );

    useEffect(() => {
        if (openId === null) return undefined;
        const onKey = (e) => e.key === 'Escape' && setOpenIdState(null);
        const onDown = (e) => !headerRef.current?.contains(e.target) && setOpenIdState(null);
        document.addEventListener('keydown', onKey);
        document.addEventListener('pointerdown', onDown);
        return () => {
            document.removeEventListener('keydown', onKey);
            document.removeEventListener('pointerdown', onDown);
        };
    }, [openId]);

    const activeId = findActiveItem([...(menus.primary ?? []), ...(menus.utility ?? [])], current);
    const navProps = { current, activeId, openId, setOpenId, scheduleClose, cancelClose };

    return (
        <>
            <div className={`nt-overlay ${openId !== null ? 'is-visible' : ''}`} aria-hidden="true" onClick={() => setOpenIdState(null)} />
            <header
                ref={headerRef}
                className={`nt-header ${scrolled ? 'is-scrolled' : ''} ${hidden ? 'is-hidden' : ''}`}
                onFocus={(e) => setFocusWithin(e.target.matches(':focus-visible'))}
                onBlur={(e) => !headerRef.current?.contains(e.relatedTarget) && (setFocusWithin(false), setOpenIdState(null))}
            >
                {shown('show_announcement_bar') ? <Announcements items={announcements} /> : null}
                {shown('show_utility_bar') ? (
                    <div className="nt-utility">
                        <div className="container">
                            <nav aria-label="Secondary">
                                <ul className="nt-nav-list">
                                    {(menus.utility ?? []).map((item) => (
                                        <NavItem key={item.id} item={item} {...navProps} />
                                    ))}
                                </ul>
                            </nav>
                            <div className="nt-utility__tools">
                                {shown('show_virtual_tour') ? (
                                    <SmartLink
                                        className="nt-icon-btn"
                                        href={settings.virtual_tour_url || '/campus/virtual-tour'}
                                        newTab={Boolean(settings.virtual_tour_url)}
                                        aria-label="360° virtual campus tour"
                                    >
                                        <Tour360Icon className="nt-tour-icon" />
                                    </SmartLink>
                                ) : null}
                                {shown('show_search') ? (
                                    <button type="button" className="nt-icon-btn" aria-label="Search" aria-expanded={searchOpen} aria-controls="site-search" onClick={() => setSearchOpen((v) => !v)}>
                                        <Icon name="search" />
                                    </button>
                                ) : null}
                            </div>
                        </div>
                    </div>
                ) : null}
                <div className="nt-mainbar">
                    <div className="container">
                        <SmartLink href="/" className="nt-logo" aria-label="NAMTECH home">
                            <img src="/images/namtech-logo.webp" alt="NAMTECH – Institute of Manufacturing Innovation" width="700" height="192" />
                        </SmartLink>
                        <nav className="nt-mainbar__nav" aria-label="Main">
                            <ul className="nt-nav-list">
                                {(menus.primary ?? []).map((item) => (
                                    <NavItem key={item.id} item={item} {...navProps} />
                                ))}
                            </ul>
                        </nav>
                        <div className={`d-flex align-items-center ${shown('show_utility_bar') ? 'd-xl-none' : ''}`}>
                            {shown('show_search') ? (
                                <SmartLink className="nt-icon-btn" href="/search" aria-label="Search">
                                    <Icon name="search" />
                                </SmartLink>
                            ) : null}
                            <button type="button" className="nt-icon-btn nt-menu-toggle" aria-label="Open menu" aria-expanded={mobileOpen} onClick={() => setMobileOpen(true)}>
                                <Icon name="menu" />
                            </button>
                        </div>
                    </div>
                </div>
                {searchOpen ? (
                    <div className="nt-search-panel" id="site-search">
                        <div className="container">
                            <form action="/search" method="get" role="search" onSubmit={(e) => {
                                e.preventDefault();
                                const q = new FormData(e.currentTarget).get('q');
                                setSearchOpen(false);
                                router.get('/search', { q });
                            }}>
                                <label htmlFor="header-search" className="visually-hidden">
                                    Search NAMTECH
                                </label>
                                <input ref={searchRef} id="header-search" name="q" type="search" className="form-control" placeholder="Search programs, faculty, news…" required minLength={2} />
                                <button type="submit" className="nt-btn">
                                    Search
                                </button>
                            </form>
                        </div>
                    </div>
                ) : null}
            </header>
            {mobileOpen ? <MobileMenu menus={menus} settings={settings} current={current} onClose={() => setMobileOpen(false)} /> : null}
        </>
    );
}
