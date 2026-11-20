import { useEffect, useRef, useState } from 'react';
import Icon from '../../Icon';
import { Media } from '../../ResponsiveImage';
import { toEmbedUrl } from '../../../lib/embed';

export function TourButton({ gallery, label }) {
    const url = gallery?.virtual_tour_url;
    if (!url) {
        return (
            <a className="nt-btn nt-btn--outline" href="/campus/virtual-tour">
                {label}
            </a>
        );
    }
    return (
        <a className="nt-btn nt-btn--outline" href={url} target="_blank" rel="noopener noreferrer">
            {label}
            <span className="visually-hidden"> (opens in a new tab)</span>
        </a>
    );
}

export function TourEmbed({ gallery }) {
    const [active, setActive] = useState(false);
    if (!gallery?.virtual_tour_url) {
        return (
            <div className="nt-embed">
                <Media image={gallery?.cover} sizes="100vw" alt="" />
                <div className="nt-embed__cta">
                    <Icon name="tour" className="mb-1" />
                    <p className="m-0">The 360° virtual tour will be available soon.</p>
                </div>
            </div>
        );
    }
    if (!gallery.tour_embeddable) {
        return (
            <div className="nt-embed">
                <Media image={gallery.cover} sizes="100vw" alt="" />
                <div className="nt-embed__cta">
                    <TourButton gallery={gallery} label="Open the 360° virtual tour" />
                </div>
            </div>
        );
    }
    return (
        <div className="nt-embed">
            {active ? (
                <iframe src={gallery.virtual_tour_url} title={`${gallery.title} – 360° virtual tour`} allow="fullscreen; xr-spatial-tracking; gyroscope; accelerometer" allowFullScreen />
            ) : (
                <>
                    <Media image={gallery.cover} sizes="100vw" alt="" />
                    <div className="nt-embed__cta">
                        <button type="button" className="nt-btn nt-btn--light" onClick={() => setActive(true)}>
                            <Icon name="tour" /> Start the 360° virtual tour
                        </button>
                        <small>Loads content from {new URL(gallery.virtual_tour_url).host}</small>
                    </div>
                </>
            )}
        </div>
    );
}

export function VideoEmbed({ url, poster, title }) {
    const [active, setActive] = useState(false);
    const embed = toEmbedUrl(url);
    if (!embed) {
        return (
            <a className="nt-embed d-grid text-white" href={url} target="_blank" rel="noopener noreferrer">
                <Media image={poster} sizes="100vw" alt="" />
                <span className="nt-embed__cta">
                    <Icon name="play" /> Watch video<span className="visually-hidden"> (opens in a new tab)</span>
                </span>
            </a>
        );
    }
    return (
        <div className="nt-embed">
            {active ? (
                <iframe src={embed} title={title || 'Video'} allow="autoplay; encrypted-media; picture-in-picture" allowFullScreen />
            ) : (
                <>
                    <Media image={poster} sizes="(min-width: 1200px) 1100px, 100vw" alt="" />
                    <div className="nt-embed__cta">
                        <button type="button" className="nt-lab-slide__play position-static" onClick={() => setActive(true)} aria-label={`Play video${title ? `: ${title}` : ''}`}>
                            <Icon name="play" />
                        </button>
                    </div>
                </>
            )}
        </div>
    );
}

export function GalleryGrid({ items }) {
    const dialog = useRef(null);
    const [index, setIndex] = useState(null);

    useEffect(() => {
        if (index === null) return undefined;
        const onKey = (e) => {
            if (e.key === 'ArrowRight') setIndex((i) => (i + 1) % items.length);
            if (e.key === 'ArrowLeft') setIndex((i) => (i - 1 + items.length) % items.length);
        };
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, [index, items.length]);

    const open = (i) => {
        setIndex(i);
        dialog.current?.showModal();
    };
    const current = index !== null ? items[index] : null;

    return (
        <>
            <div className="nt-gallery-grid">
                {items.map((item, i) => (
                    <button key={item.id} type="button" onClick={() => open(i)} aria-label={`Open image ${i + 1} of ${items.length}${item.image?.alt ? `: ${item.image.alt}` : ''}`}>
                        <Media image={item.image} sizes="(min-width: 768px) 33vw, 50vw" alt="" />
                    </button>
                ))}
            </div>
            <dialog ref={dialog} className="nt-lightbox" aria-label="Image viewer" onClose={() => setIndex(null)} onClick={(e) => e.target === dialog.current && dialog.current.close()}>
                {current ? (
                    <>
                        <figure>
                            <img src={current.image.src} srcSet={current.image.srcset} sizes="92vw" alt={current.image.alt} />
                            {current.caption ? <figcaption className="mt-2">{current.caption}</figcaption> : null}
                        </figure>
                        <div className="nt-lightbox__nav">
                            <button type="button" className="nt-carousel__arrow" onClick={() => setIndex((index - 1 + items.length) % items.length)} aria-label="Previous image">
                                <Icon name="arrowLeft" />
                            </button>
                            <span className="text-white align-self-center">
                                {index + 1} / {items.length}
                            </span>
                            <button type="button" className="nt-carousel__arrow" onClick={() => setIndex((index + 1) % items.length)} aria-label="Next image">
                                <Icon name="arrowRight" />
                            </button>
                        </div>
                        <button type="button" className="nt-icon-btn nt-lightbox__close bg-white" onClick={() => dialog.current.close()} aria-label="Close image viewer" autoFocus>
                            <Icon name="close" />
                        </button>
                    </>
                ) : null}
            </dialog>
        </>
    );
}
