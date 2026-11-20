export default function ResponsiveImage({ image, sizes = '100vw', priority = false, alt, className }) {
    if (!image) {
        return null;
    }

    return (
        <picture className={className}>
            <img
                src={image.src}
                srcSet={image.srcset}
                sizes={sizes}
                width={image.width}
                height={image.height}
                alt={alt ?? image.alt ?? ''}
                loading={priority ? 'eager' : 'lazy'}
                decoding={priority ? 'sync' : 'async'}
                fetchPriority={priority ? 'high' : undefined}
            />
        </picture>
    );
}

export function Media({ image, sizes, priority, alt, className = '', style }) {
    return (
        <div className={`nt-media ${className}`} style={style}>
            <ResponsiveImage image={image} sizes={sizes} priority={priority} alt={alt} />
        </div>
    );
}
