import { useEffect } from 'react';

export default function useStackCover(refs, enabled, onActive, key) {
    useEffect(() => {
        const desktop = window.matchMedia('(min-width: 992px)');
        if (!enabled || !desktop.matches) return undefined;
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
        let frame = 0;
        const update = () => {
            frame = 0;
            const items = refs.current.filter(Boolean);
            let current = 0;
            items.forEach((item, i) => {
                const style = getComputedStyle(item);
                const stick = parseFloat(style.top) || 0;
                if (item.getBoundingClientRect().top <= stick + 2) current = i;
                const next = items[i + 1];
                let cover = 0;
                if (next && !reduced.matches) {
                    const distance = next.getBoundingClientRect().top - (parseFloat(getComputedStyle(next).top) || 0);
                    const travel = item.offsetHeight + (parseFloat(style.marginBottom) || 0);
                    cover = Math.min(1, Math.max(0, 1 - distance / travel));
                }
                item.style.setProperty('--cover', cover.toFixed(3));
            });
            onActive?.(current);
        };
        const onScroll = () => {
            if (!frame) frame = requestAnimationFrame(update);
        };
        update();
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onScroll);
        return () => {
            window.removeEventListener('scroll', onScroll);
            window.removeEventListener('resize', onScroll);
            if (frame) cancelAnimationFrame(frame);
        };
    }, [enabled, key]);
}
