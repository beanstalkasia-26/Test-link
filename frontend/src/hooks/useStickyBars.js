import { useEffect } from 'react';

export default function useStickyBars(headerRef, hidden) {
    useEffect(() => {
        const header = headerRef.current;
        if (!header) return undefined;
        const place = () => {
            const collapse = hidden ? (header.querySelector('.nt-mainbar')?.offsetTop ?? 0) : 0;
            document.documentElement.style.setProperty('--nt-header-offset', `${header.offsetHeight - collapse}px`);
        };
        place();
        const observer = new ResizeObserver(place);
        observer.observe(header);
        return () => observer.disconnect();
    }, [headerRef, hidden]);

    useEffect(() => {
        const onClick = (event) => {
            const bar = event.target.closest?.('.nt-tabs');
            if (!bar || !event.target.closest('a, button')) return;
            const position = bar.style.position;
            bar.style.position = 'static';
            const natural = bar.getBoundingClientRect().top + window.scrollY;
            bar.style.position = position;
            const above = (headerRef.current?.offsetHeight ?? 0) + (document.querySelector('.nt-subnav')?.offsetHeight ?? 0);
            const target = natural - above;
            if (window.scrollY > target) window.scrollTo({ top: target, behavior: 'auto' });
        };
        document.addEventListener('click', onClick);
        return () => document.removeEventListener('click', onClick);
    }, [headerRef]);
}
