import { useCallback, useEffect, useRef, useState } from 'react';
import { router } from '../../router/navigation';
import Icon from '../Icon';

export function visit(url, params) {
    router.get(url, params, { preserveScroll: true, preserveState: true, replace: true });
}

export function FilterTabs({ items, active, href, label }) {
    const listRef = useRef(null);
    const [more, setMore] = useState({ left: false, right: false });

    const update = useCallback(() => {
        const el = listRef.current;
        if (!el) return;
        setMore({ left: el.scrollLeft > 4, right: el.scrollLeft + el.clientWidth < el.scrollWidth - 4 });
    }, []);

    useEffect(() => {
        const el = listRef.current;
        el?.querySelector('[aria-current]')?.scrollIntoView?.({ block: 'nearest', inline: 'center' });
        update();
        window.addEventListener('resize', update);
        return () => window.removeEventListener('resize', update);
    }, [update]);

    const scroll = (dir) => listRef.current?.scrollBy({ left: dir * listRef.current.clientWidth * 0.6, behavior: 'smooth' });

    return (
        <div className={`nt-filter-tabs ${more.left ? 'has-left' : ''} ${more.right ? 'has-right' : ''}`}>
            <nav className="nt-tabs nt-tabs--links" aria-label={label} ref={listRef} onScroll={update}>
                {items.map(([key, text]) => (
                    <a key={key || 'all'} href={href(key)} aria-current={active === key ? 'page' : undefined} onClick={(e) => {
                        e.preventDefault();
                        router.get(href(key), {}, { preserveScroll: true, preserveState: false });
                    }}>
                        {text}
                    </a>
                ))}
            </nav>
            {more.left ? (
                <button type="button" className="nt-filter-tabs__arrow nt-filter-tabs__arrow--left" aria-label="Show earlier tabs" onClick={() => scroll(-1)}>
                    <Icon name="chevronDown" strokeWidth={1.6} />
                </button>
            ) : null}
            {more.right ? (
                <button type="button" className="nt-filter-tabs__arrow" aria-label="Show more tabs" onClick={() => scroll(1)}>
                    <Icon name="chevronDown" strokeWidth={1.6} />
                </button>
            ) : null}
        </div>
    );
}

export function CheckboxGroup({ legend, name, options, selected, onChange }) {
    if (!options.length) return null;
    const toggle = (value) => onChange(selected.includes(value) ? selected.filter((v) => v !== value) : [...selected, value]);
    return (
        <fieldset className="nt-checkgroup">
            <legend>{legend}</legend>
            {options.map(([value, label]) => (
                <div className="form-check" key={value}>
                    <input className="form-check-input" type="checkbox" id={`${name}-${value}`} checked={selected.includes(String(value))} onChange={() => toggle(String(value))} />
                    <label className="form-check-label" htmlFor={`${name}-${value}`}>{label}</label>
                </div>
            ))}
        </fieldset>
    );
}

export function SidebarToggle({ children, label = 'Filter' }) {
    return (
        <details className="nt-sidebar-filter" open>
            <summary>
                <Icon name="gear" strokeWidth={1.6} />
                {label}
            </summary>
            <div className="nt-sidebar-filter__body">{children}</div>
        </details>
    );
}
