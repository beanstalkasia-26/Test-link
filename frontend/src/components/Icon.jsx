const paths = {
    arrowRight: <path d="M5 12h14M13 6l6 6-6 6" />,
    arrowLeft: <path d="M19 12H5M11 6l-6 6 6 6" />,
    chevronDown: <path d="m6 9 6 6 6-6" />,
    chevronLeft: <path d="m15 6-6 6 6 6" />,
    chevronRight: <path d="m9 6 6 6-6 6" />,
    search: (
        <>
            <circle cx="11" cy="11" r="7" />
            <path d="m20 20-3.5-3.5" />
        </>
    ),
    menu: <path d="M4 7h16M4 12h16M4 17h16" />,
    close: <path d="M6 6l12 12M18 6 6 18" />,
    download: <path d="M12 4v11m0 0-4.5-4.5M12 15l4.5-4.5M5 19h14" />,
    plus: <path d="M12 5v14M5 12h14" />,
    play: <path d="M8 5v14l11-7z" fill="currentColor" />,
    pause: <path d="M9 5v14M15 5v14" />,
    phone: <path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2" />,
    phoneChat: (
        <>
            <path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2" />
            <path d="M14 3h7v5h-4l-3 2z" />
        </>
    ),
    mail: (
        <>
            <rect x="3" y="5" width="18" height="14" rx="2" />
            <path d="m3 7 9 6 9-6" />
        </>
    ),
    pin: (
        <>
            <path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21Z" />
            <circle cx="12" cy="9.5" r="2.5" />
        </>
    ),
    trend: <path d="M3 17l6-6 4 4 8-8M15 7h6v6" />,
    image: (
        <>
            <rect x="3" y="5" width="18" height="14" rx="2" />
            <path d="m3 16 5-5 4 4 3-3 6 6" />
        </>
    ),
    tour: (
        <>
            <ellipse cx="12" cy="13" rx="9" ry="4" />
            <path d="M16 6.5A9 4 0 0 1 21 9" />
            <path d="m19 6 2 3-3 1" />
        </>
    ),
    document: (
        <>
            <path d="M7 3h7l5 5v13H7z" />
            <path d="M14 3v5h5M10 13h6M10 17h6" />
        </>
    ),
    external: <path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5" />,
    graduation: (
        <>
            <path d="M2 9.5 12 5l10 4.5L12 14z" />
            <path d="M6 11.5V16c3 2.5 9 2.5 12 0v-4.5M22 9.5V15" />
        </>
    ),
    book: (
        <>
            <path d="M3 5.5C6 4.5 9 4.5 12 6c3-1.5 6-1.5 9-.5V19c-3-1-6-1-9 .5-3-1.5-6-1.5-9-.5z" />
            <path d="M12 6v13.5M5.5 8.5h4M5.5 11.5h4M14.5 8.5h4M14.5 11.5h4" />
        </>
    ),
    user: (
        <>
            <circle cx="12" cy="8" r="4" />
            <path d="M4 21c0-4 3.6-7 8-7s8 3 8 7" />
        </>
    ),
    people: (
        <>
            <circle cx="8" cy="5" r="2" />
            <circle cx="16" cy="5" r="2" />
            <path d="M6 9h4v12H6zM14 21l1-12h2l3 12" />
        </>
    ),
    factory: <path d="M3 21V10l5 3V10l5 3V10l5 3V4h3v17zM7 17h2M12 17h2M17 17h2" />,
    chip: (
        <>
            <rect x="7" y="7" width="10" height="10" rx="1" />
            <path d="M10 3v4M14 3v4M10 17v4M14 17v4M3 10h4M3 14h4M17 10h4M17 14h4" />
        </>
    ),
    robot: <path d="M4 21h8M8 21v-5l5-5 4 2M13 11l-2-4 3-3 3 3M17 13l2 3" />,
    leaf: <path d="M5 19c0-8 5-14 15-14 0 10-6 15-14 15M5 19l7-7" />,
    globe: (
        <>
            <circle cx="12" cy="12" r="9" />
            <path d="M3 12h18M12 3c3 3 3 15 0 18M12 3c-3 3-3 15 0 18" />
        </>
    ),
    handshake: <path d="m3 11 4-4 4 2 2-2 4 1 4 4-6 6-3-3-2 2-3-3-2 2zM10 9l3 3" />,
    chart: <path d="M4 20V4M4 20h16M8 16v-4M12 16V8M16 16v-6" />,
    lightbulb: <path d="M9 18h6M10 21h4M12 3a6 6 0 0 0-4 10.5c.8.8 1 1.5 1 2.5h6c0-1 .2-1.7 1-2.5A6 6 0 0 0 12 3" />,
    gear: (
        <>
            <circle cx="12" cy="12" r="3" />
            <path d="M12 2v3M12 19v3M4.9 4.9 7 7M17 17l2.1 2.1M2 12h3M19 12h3M4.9 19.1 7 17M17 7l2.1-2.1" />
        </>
    ),
    flask: <path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 2 3h10a2 2 0 0 0 2-3l-5-9V3M7.5 14h9" />,
    award: (
        <>
            <circle cx="12" cy="9" r="5" />
            <path d="m9 13.5-2 7.5 5-3 5 3-2-7.5" />
        </>
    ),
    building: <path d="M4 21V5l8-2v18M12 7l8 2v12M3 21h18M7 8h2M7 12h2M7 16h2M15 12h2M15 16h2" />,
    linkedin: <path d="M6 9v9M6 6v.01M10 18v-5a3 3 0 0 1 6 0v5M10 9v9" />,
    x: <path d="m4 4 16 16M20 4 4 20" />,
    youtube: (
        <>
            <rect x="3" y="6" width="18" height="12" rx="4" />
            <path d="m10 9.5 5 2.5-5 2.5z" fill="currentColor" />
        </>
    ),
    instagram: (
        <>
            <rect x="4" y="4" width="16" height="16" rx="5" />
            <circle cx="12" cy="12" r="3.5" />
            <path d="M17 7v.01" />
        </>
    ),
    calendar: (
        <>
            <rect x="3.5" y="5" width="17" height="15" rx="2" />
            <path d="M3.5 10h17M8 3v4M16 3v4" />
        </>
    ),
    clock: (
        <>
            <circle cx="12" cy="12" r="8.5" />
            <path d="M12 7.5V12l3 2" />
        </>
    ),
    check: (
        <>
            <circle cx="12" cy="12" r="8.5" />
            <path d="m8.5 12 2.5 2.5 4.5-5" />
        </>
    ),
    bank: <path d="M3 9.5 12 4l9 5.5M5 10v8M9.5 10v8M14.5 10v8M19 10v8M3 20.5h18" />,
    rupee: (
        <>
            <circle cx="12" cy="12" r="8.5" />
            <path d="M9 8h6M9 10.5h6M11 8c3 0 3 5 0 5H9l4.5 3.5" />
        </>
    ),
    id: (
        <>
            <rect x="3" y="5" width="18" height="14" rx="2" />
            <circle cx="9" cy="11" r="2" />
            <path d="M6 16c.6-1.6 1.7-2.4 3-2.4s2.4.8 3 2.4M14.5 10h4M14.5 13h3" />
        </>
    ),
    portfolio: <path d="M4 8h16v11H4zM9 8V5.5h6V8M4 12.5h16" />,
    sparkle: <path d="M12 3c.6 4.5 2.5 6.4 7 7-4.5.6-6.4 2.5-7 7-.6-4.5-2.5-6.4-7-7 4.5-.6 6.4-2.5 7-7ZM18.5 16.5c.2 1.4.8 2 2.2 2.2-1.4.2-2 .8-2.2 2.2-.2-1.4-.8-2-2.2-2.2 1.4-.2 2-.8 2.2-2.2Z" />,
    cap: (
        <>
            <path d="M2 9.5 12 5l10 4.5L12 14z" />
            <path d="M6 11.5V16c3 2.5 9 2.5 12 0v-4.5" />
        </>
    ),
    bolt: <path d="M13 3 5 13.5h6L10 21l8-10.5h-6z" />,
    drone: (
        <>
            <circle cx="5.5" cy="6" r="2.5" />
            <circle cx="18.5" cy="6" r="2.5" />
            <path d="M7.5 7.5 10 10h4l2.5-2.5M10 10v4h4v-4M9 18l1-4M15 18l-1-4" />
        </>
    ),
    magnet: <path d="M6 4v8a6 6 0 0 0 12 0V4h-4v8a2 2 0 0 1-4 0V4zM6 8h4M14 8h4" />,
    code: <path d="m8 8-4 4 4 4M16 8l4 4-4 4M13.5 5l-3 14" />,
    quote: <path d="M5 17c0-4 1-7 5-9M14 17c0-4 1-7 5-9M5 17h4v-4H5zM14 17h4v-4h-4z" />,
    quoteClose: <path d="M3 3h5a1 1 0 0 1 1 1v9c0 5-2 8-7 8v-4c2 0 3-1 3-4H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Zm12 0h5a1 1 0 0 1 1 1v9c0 5-2 8-7 8v-4c2 0 3-1 3-4h-2a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z" />,
    facebook: <path d="M14 21v-8h3l.5-3.5H14V7.5c0-1 .4-1.5 1.5-1.5H18V3h-3a4 4 0 0 0-4 4v2.5H8V13h3v8" />,
};

export default function Icon({ name, title, className, strokeWidth = 1.6 }) {
    const content = paths[name] ?? paths.document;

    return (
        <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth={strokeWidth}
            strokeLinecap="round"
            strokeLinejoin="round"
            className={className}
            aria-hidden={title ? undefined : true}
            role={title ? 'img' : undefined}
            focusable="false"
        >
            {title ? <title>{title}</title> : null}
            {content}
        </svg>
    );
}

export function ArrowDot() {
    return (
        <span className="nt-arrow-dot" aria-hidden="true">
            <Icon name="arrowRight" strokeWidth={2.2} />
        </span>
    );
}

export function Tour360Icon({ className }) {
    return (
        <svg viewBox="0 0 36 20" fill="none" stroke="currentColor" strokeWidth="1.15" strokeLinecap="round" strokeLinejoin="round" className={className} aria-hidden="true" focusable="false">
            <text x="18.5" y="9" textAnchor="middle" fontSize="9.5" fontWeight="500" fontFamily="Poppins, system-ui, sans-serif" fill="currentColor" stroke="none" letterSpacing="-0.2">
                360°
            </text>
            <path d="M6.2 8.4C2.2 10 2.4 15.7 17.5 16.2C29.8 16.5 35.2 13.9 32.3 10.3" />
            <path d="M14.3 14.2L16.9 16.2L14.3 18.2" />
        </svg>
    );
}
