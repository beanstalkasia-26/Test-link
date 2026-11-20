import { useEffect, useId, useRef, useState } from 'react';

export default function Tabs({ tabs, initial = 0, label, className = '' }) {
    const [active, setActive] = useState(Math.min(initial, Math.max(0, tabs.length - 1)));
    const refs = useRef([]);
    const id = useId();

    useEffect(() => {
        if (!tabs.some((tab) => tab.anchor)) return undefined;
        const reveal = () => {
            const hash = window.location.hash.slice(1);
            const index = tabs.findIndex((tab) => tab.anchor === hash);
            if (index < 0) return;
            setActive(index);
        };
        reveal();
        window.addEventListener('hashchange', reveal);
        return () => window.removeEventListener('hashchange', reveal);
    }, [tabs]);

    const focus = (i) => {
        const next = (i + tabs.length) % tabs.length;
        setActive(next);
        refs.current[next]?.focus();
    };

    return (
        <div className={className}>
            <div className="nt-tabs" role="tablist" aria-label={label}>
                {tabs.map((tab, i) => (
                    <button
                        key={tab.key}
                        ref={(el) => (refs.current[i] = el)}
                        type="button"
                        role="tab"
                        id={`${id}-tab-${i}`}
                        aria-selected={i === active}
                        aria-controls={`${id}-panel-${i}`}
                        tabIndex={i === active ? 0 : -1}
                        onClick={() => setActive(i)}
                        onKeyDown={(e) => {
                            if (e.key === 'ArrowRight') focus(i + 1);
                            else if (e.key === 'ArrowLeft') focus(i - 1);
                            else if (e.key === 'Home') focus(0);
                            else if (e.key === 'End') focus(tabs.length - 1);
                            else return;
                            e.preventDefault();
                        }}
                    >
                        {tab.label}
                    </button>
                ))}
            </div>
            {tabs.map((tab, i) => (
                <div key={tab.key} role="tabpanel" id={`${id}-panel-${i}`} aria-labelledby={`${id}-tab-${i}`} hidden={i !== active} tabIndex={0}>
                    {tab.content}
                </div>
            ))}
        </div>
    );
}
