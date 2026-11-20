import { Children, useCallback, useEffect, useId, useRef, useState } from 'react';
import Icon from './Icon';

export default function Carousel({
    label,
    children,
    slideWidth = '85%',
    gap = '2rem',
    className = '',
    trackClassName = '',
    showDots = true,
    showArrows = true,
    controlsClassName = '',
    align = 'start',
    firstSlideAtStart = false,
    onIndexChange,
    autoplay = 0,
}) {
    const slides = Children.toArray(children);
    const rootRef = useRef(null);
    const trackRef = useRef(null);
    const [index, setIndex] = useState(0);
    const [atStart, setAtStart] = useState(true);
    const [atEnd, setAtEnd] = useState(slides.length <= 1);
    const [held, setHeld] = useState({ hover: false, focus: false });
    const [onScreen, setOnScreen] = useState(false);
    const [tail, setTail] = useState(0);
    const [pageVisible, setPageVisible] = useState(true);
    const [reducedMotion, setReducedMotion] = useState(false);
    const id = useId();

    const update = useCallback(() => {
        const track = trackRef.current;
        if (!track) return;
        const items = Array.from(track.children).filter((child) => !child.dataset.spacer);
        const left = track.scrollLeft;
        const trackLeft = track.getBoundingClientRect().left;
        let nearest = 0;
        let best = Infinity;
        items.forEach((item, i) => {
            const offset = firstSlideAtStart && i === 0
                ? 0
                : item.getBoundingClientRect().left - trackLeft + left - (align === 'center' ? (track.clientWidth - item.clientWidth) / 2 : 0);
            const distance = Math.abs(offset - left);
            if (distance < best) {
                best = distance;
                nearest = i;
            }
        });
        setIndex(nearest);
        onIndexChange?.(nearest);
        setAtStart(left <= 2);
        setAtEnd(left + track.clientWidth >= track.scrollWidth - 2);
    }, [align, firstSlideAtStart, onIndexChange]);

    const measureTail = useCallback(() => {
        const track = trackRef.current;
        if (!track || align !== 'start') return;
        const items = Array.from(track.children).filter((child) => !child.dataset.spacer);
        if (items.length < 2) return setTail(0);
        const spacer = track.querySelector('[data-spacer]');
        const maxWithout = track.scrollWidth - track.clientWidth - (spacer ? spacer.offsetWidth : 0);
        if (maxWithout <= 1) return setTail(0);
        const first = items[0].offsetLeft;
        const stop = items.map((item) => item.offsetLeft - first).find((start) => start >= maxWithout - 1);
        setTail(stop === undefined ? 0 : Math.ceil(stop - maxWithout));
    }, [align]);

    useEffect(() => {
        const track = trackRef.current;
        if (!track) return undefined;
        measureTail();
        update();
        let frame;
        const onScroll = () => {
            cancelAnimationFrame(frame);
            frame = requestAnimationFrame(update);
        };
        const onResize = () => {
            measureTail();
            onScroll();
        };
        track.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onResize);
        return () => {
            track.removeEventListener('scroll', onScroll);
            window.removeEventListener('resize', onResize);
            cancelAnimationFrame(frame);
        };
    }, [update, measureTail, slides.length]);

    const goTo = (i) => {
        const track = trackRef.current;
        const target = Math.max(0, Math.min(i, slides.length - 1));
        const item = track?.children[target];
        if (!track || !item) return;
        const offset = firstSlideAtStart && target === 0
            ? 0
            : item.offsetLeft - track.offsetLeft - (align === 'center' ? (track.clientWidth - item.clientWidth) / 2 : 0);
        track.scrollTo({ left: offset, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
    };

    const multiple = slides.length > 1;
    const autoplays = autoplay > 0 && multiple && !reducedMotion;

    useEffect(() => {
        if (!(autoplay > 0)) return undefined;
        const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
        const onMotion = () => setReducedMotion(motion.matches);
        const onVisibility = () => setPageVisible(document.visibilityState === 'visible');
        const observer = new IntersectionObserver(([entry]) => setOnScreen(entry.isIntersecting), { threshold: 0.4 });
        onMotion();
        onVisibility();
        if (rootRef.current) observer.observe(rootRef.current);
        motion.addEventListener('change', onMotion);
        document.addEventListener('visibilitychange', onVisibility);
        return () => {
            observer.disconnect();
            motion.removeEventListener('change', onMotion);
            document.removeEventListener('visibilitychange', onVisibility);
        };
    }, [autoplay]);

    useEffect(() => {
        if (!autoplays || held.hover || held.focus || !onScreen || !pageVisible) return undefined;
        const timer = window.setTimeout(() => goTo(atEnd ? 0 : index + 1), autoplay);
        return () => window.clearTimeout(timer);
    });

    const hold = (key, value) => setHeld((current) => (current[key] === value ? current : { ...current, [key]: value }));

    return (
        <div
            ref={rootRef}
            className={`nt-carousel ${className}`}
            role="region"
            aria-roledescription="carousel"
            aria-label={label}
            onPointerEnter={autoplays ? (event) => event.pointerType === 'mouse' && hold('hover', true) : undefined}
            onPointerLeave={autoplays ? () => hold('hover', false) : undefined}
            onFocus={autoplays ? (event) => hold('focus', event.target.matches(':focus-visible')) : undefined}
            onBlur={autoplays ? (event) => !rootRef.current?.contains(event.relatedTarget) && hold('focus', false) : undefined}
        >
            <div
                ref={trackRef}
                id={id}
                className={`nt-carousel__track ${trackClassName}`}
                style={{ '--slide': slideWidth, '--gap': gap }}
                tabIndex={multiple ? 0 : undefined}
                aria-label={multiple ? `${label} – scroll horizontally` : undefined}
                onKeyDown={(event) => {
                    if (event.target !== trackRef.current) return;
                    if (event.key === 'ArrowRight') {
                        event.preventDefault();
                        goTo(index + 1);
                    }
                    if (event.key === 'ArrowLeft') {
                        event.preventDefault();
                        goTo(index - 1);
                    }
                }}
            >
                {slides.map((slide, i) => (
                    <div key={slide.key ?? i} role="group" aria-roledescription="slide" aria-label={`${i + 1} of ${slides.length}`}>
                        {slide}
                    </div>
                ))}
                {tail > 0 ? <div data-spacer="" aria-hidden="true" className="nt-carousel__spacer" style={{ flex: `0 0 ${tail}px`, marginLeft: 'calc(-1 * var(--gap))' }} /> : null}
            </div>
            {multiple && (showDots || showArrows || autoplays) ? (
                <div className={`nt-carousel__controls ${controlsClassName}`}>
                    {showDots ? (
                        <div className="nt-carousel__dots">
                            {slides.map((_, i) => (
                                <button
                                    key={i}
                                    type="button"
                                    aria-controls={id}
                                    aria-label={`Show slide ${i + 1}`}
                                    aria-current={i === index ? 'true' : undefined}
                                    onClick={() => goTo(i)}
                                />
                            ))}
                        </div>
                    ) : (
                        <span />
                    )}
                    {showArrows ? (
                        <div className="nt-carousel__arrows">
                            {showArrows ? (
                                <>
                                    <button type="button" className="nt-carousel__arrow" aria-controls={id} aria-label="Previous slide" disabled={atStart} onClick={() => goTo(index - 1)}>
                                        <Icon name="arrowLeft" strokeWidth={2} />
                                    </button>
                                    <button type="button" className="nt-carousel__arrow" aria-controls={id} aria-label="Next slide" disabled={atEnd} onClick={() => goTo(index + 1)}>
                                        <Icon name="arrowRight" strokeWidth={2} />
                                    </button>
                                </>
                            ) : null}
                        </div>
                    ) : null}
                </div>
            ) : null}
        </div>
    );
}
