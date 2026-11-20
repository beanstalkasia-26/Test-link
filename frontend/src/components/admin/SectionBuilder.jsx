import { useMemo, useRef, useState } from 'react';
import { emptyItem, FieldList } from './Fields';

export default function SectionBuilder({ sections, onChange, types, errors, media, onMediaLoaded }) {
    const list = Array.isArray(sections) ? sections : [];
    const [open, setOpen] = useState(() => new Set(list.length <= 3 ? list.map((_, i) => i) : []));
    const [adding, setAdding] = useState(false);
    const dialog = useRef(null);
    const byType = useMemo(() => Object.fromEntries(types.map((t) => [t.type, t])), [types]);
    const groups = useMemo(() => {
        const result = {};
        types.forEach((t) => (result[t.group] ??= []).push(t));
        return result;
    }, [types]);

    const update = (index, patch) => onChange(list.map((s, i) => (i === index ? { ...s, ...patch } : s)));
    const move = (index, delta) => {
        const next = [...list];
        const [item] = next.splice(index, 1);
        next.splice(index + delta, 0, item);
        onChange(next);
        setOpen(new Set());
    };
    const toggle = (index) => {
        const next = new Set(open);
        next.has(index) ? next.delete(index) : next.add(index);
        setOpen(next);
    };
    const add = (type) => {
        onChange([...list, { type: type.type, label: null, anchor: null, is_visible: true, data: emptyItem(type.fields) }]);
        setOpen(new Set([...open, list.length]));
        dialog.current?.close();
    };

    return (
        <div>
            {list.length === 0 ? <p className="text-secondary">This page has no sections yet.</p> : null}
            {list.map((section, index) => {
                const type = byType[section.type];
                const isOpen = open.has(index);
                const hasError = Object.keys(errors ?? {}).some((key) => key.startsWith(`sections.${index}.`));
                const summary = section.data?.heading || section.data?.title || section.data?.slides?.[0]?.title || '';
                return (
                    <div key={index} className={`section-card ${isOpen ? 'is-open' : ''} ${section.is_visible === false ? 'is-hidden' : ''} ${hasError ? 'has-error' : ''}`}>
                        <div className="section-card__head">
                            <button type="button" className="btn btn-link text-start text-decoration-none flex-grow-1 p-0 text-body" aria-expanded={isOpen} onClick={() => toggle(index)}>
                                <span className="badge text-bg-light me-2">{index + 1}</span>
                                <strong>{section.label || type?.label || section.type}</strong>
                                {summary ? <span className="text-secondary small ms-2">– {String(summary).slice(0, 60)}</span> : null}
                                {section.is_visible === false ? <span className="badge text-bg-secondary ms-2">Hidden</span> : null}
                                {hasError ? <span className="badge text-bg-danger ms-2">Check fields</span> : null}
                            </button>
                            <div className="btn-group btn-group-sm" role="group" aria-label={`Section ${index + 1} actions`}>
                                <button type="button" className="btn btn-outline-secondary" disabled={index === 0} onClick={() => move(index, -1)} aria-label="Move section up">
                                    ↑
                                </button>
                                <button type="button" className="btn btn-outline-secondary" disabled={index === list.length - 1} onClick={() => move(index, 1)} aria-label="Move section down">
                                    ↓
                                </button>
                                <button type="button" className="btn btn-outline-secondary" onClick={() => update(index, { is_visible: section.is_visible === false })}>
                                    {section.is_visible === false ? 'Show' : 'Hide'}
                                </button>
                                <button type="button" className="btn btn-outline-secondary" onClick={() => onChange([...list.slice(0, index + 1), structuredClone(section), ...list.slice(index + 1)])}>
                                    Duplicate
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-outline-danger"
                                    onClick={() => window.confirm('Remove this section? You can restore it from revision history.') && onChange(list.filter((_, i) => i !== index))}
                                >
                                    Remove
                                </button>
                            </div>
                        </div>
                        {isOpen ? (
                            <div className="section-card__body">
                                {type ? <p className="small text-secondary">{type.description}</p> : <div className="alert alert-warning">Unknown section type “{section.type}”. It will not be displayed.</div>}
                                <div className="row g-3 mb-2">
                                    <div className="col-md-6">
                                        <label className="form-label small" htmlFor={`s-${index}-label`}>
                                            Admin label (optional)
                                        </label>
                                        <input id={`s-${index}-label`} className="form-control form-control-sm" value={section.label ?? ''} onChange={(e) => update(index, { label: e.target.value })} maxLength={120} />
                                    </div>
                                    <div className="col-md-6">
                                        <label className="form-label small" htmlFor={`s-${index}-anchor`}>
                                            Anchor (for links like #apply)
                                        </label>
                                        <input
                                            id={`s-${index}-anchor`}
                                            className={`form-control form-control-sm ${errors?.[`sections.${index}.anchor`] ? 'is-invalid' : ''}`}
                                            value={section.anchor ?? ''}
                                            onChange={(e) => update(index, { anchor: e.target.value.toLowerCase().replace(/[^a-z0-9-]/g, '-') })}
                                            maxLength={80}
                                        />
                                    </div>
                                </div>
                                {type ? (
                                    <FieldList
                                        fields={type.fields}
                                        values={section.data ?? {}}
                                        onChange={(name, value) => update(index, { data: { ...section.data, [name]: value } })}
                                        pathPrefix={`sections.${index}.data`}
                                        errors={errors}
                                        media={media}
                                        onMediaLoaded={onMediaLoaded}
                                        hideItems
                                    />
                                ) : null}
                            </div>
                        ) : null}
                    </div>
                );
            })}
            <button
                type="button"
                className="btn btn-outline-primary"
                onClick={() => {
                    setAdding(true);
                    requestAnimationFrame(() => dialog.current?.showModal());
                }}
            >
                + Add section
            </button>
            {adding ? (
                <dialog ref={dialog} className="border-0 rounded-3 shadow p-0" style={{ width: 'min(48rem, 95vw)' }} onClose={() => setAdding(false)} aria-label="Add a section">
                    <div className="p-3 border-bottom d-flex justify-content-between">
                        <h2 className="h5 m-0">Add a section</h2>
                        <button type="button" className="btn-close" aria-label="Close" onClick={() => dialog.current.close()} />
                    </div>
                    <div className="p-3" style={{ maxHeight: '70vh', overflowY: 'auto' }}>
                        {Object.entries(groups).map(([group, items]) => (
                            <div key={group} className="mb-3">
                                <h3 className="h6 text-uppercase text-secondary small">{group}</h3>
                                <div className="list-group">
                                    {items.map((type) => (
                                        <button key={type.type} type="button" className="list-group-item list-group-item-action" onClick={() => add(type)}>
                                            <strong className="d-block">{type.label}</strong>
                                            <span className="small text-secondary">{type.description}</span>
                                        </button>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </div>
                </dialog>
            ) : null}
        </div>
    );
}
