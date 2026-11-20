import { useMemo, useState } from 'react';
import MediaPicker from './MediaPicker';
import RichTextEditor from './RichTextEditor';

const slugify = (text) =>
    String(text ?? '')
        .toLowerCase()
        .normalize('NFKD')
        .replace(/[̀-ͯ]/g, '')
        .replace(/&/g, ' and ')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

function emptyValue(field) {
    if (field.type === 'repeater' || field.type === 'belongsToMany' || field.type === 'blocks') return [];
    if (field.type === 'boolean') return false;
    return null;
}

export function emptyItem(fields) {
    return Object.fromEntries(fields.map((f) => [f.name, emptyValue(f)]));
}

function MultiSelect({ id, options, value, onChange }) {
    const [filter, setFilter] = useState('');
    const selected = new Set((value ?? []).map(Number));
    const visible = options.filter((o) => o.label.toLowerCase().includes(filter.toLowerCase()));
    return (
        <div className="border rounded p-2" id={id}>
            {options.length > 8 ? <input className="form-control form-control-sm mb-2" placeholder="Filter…" aria-label="Filter options" value={filter} onChange={(e) => setFilter(e.target.value)} /> : null}
            <div style={{ maxHeight: '12rem', overflowY: 'auto' }}>
                {visible.map((option) => (
                    <div className="form-check" key={option.value}>
                        <input
                            className="form-check-input"
                            type="checkbox"
                            id={`${id}-${option.value}`}
                            checked={selected.has(Number(option.value))}
                            onChange={(e) => {
                                const next = new Set(selected);
                                e.target.checked ? next.add(Number(option.value)) : next.delete(Number(option.value));
                                onChange([...next]);
                            }}
                        />
                        <label className="form-check-label small" htmlFor={`${id}-${option.value}`}>
                            {option.label}
                        </label>
                    </div>
                ))}
                {!visible.length ? <p className="small text-secondary m-0">No options.</p> : null}
            </div>
        </div>
    );
}

function BlockSwitches({ id, options, value, onChange }) {
    const off = new Set(value ?? []);
    return (
        <div className="border rounded p-3 pb-1" id={id}>
            {options.map((option) => (
                <div className="form-check form-switch mb-2" key={option.value}>
                    <input
                        className="form-check-input"
                        type="checkbox"
                        role="switch"
                        id={`${id}-${option.value}`}
                        checked={!off.has(option.value)}
                        onChange={(e) => {
                            const next = new Set(off);
                            e.target.checked ? next.delete(option.value) : next.add(option.value);
                            onChange(options.map((o) => o.value).filter((v) => next.has(v)));
                        }}
                    />
                    <label className="form-check-label" htmlFor={`${id}-${option.value}`}>
                        {option.label}
                    </label>
                </div>
            ))}
        </div>
    );
}

function Repeater({ field, value, onChange, path, errors, media, onMediaLoaded, hideItems }) {
    const items = Array.isArray(value) ? value : [];
    const max = field.maxItems ?? 50;
    const update = (index, next) => onChange(items.map((item, i) => (i === index ? next : item)));
    const move = (index, delta) => {
        const next = [...items];
        const [item] = next.splice(index, 1);
        next.splice(index + delta, 0, item);
        onChange(next);
    };

    return (
        <div>
            {items.map((item, index) => (
                <div className={`repeater-item ${item?._hidden ? 'is-hidden' : ''}`} key={index}>
                    <div className="d-flex justify-content-between align-items-center mb-2">
                        <span className="small fw-medium text-secondary">
                            {field.label} {index + 1}
                            {item?._hidden ? <span className="badge text-bg-secondary ms-2">Hidden</span> : null}
                        </span>
                        <div className="btn-group btn-group-sm">
                            {hideItems ? (
                                <button type="button" className="btn btn-outline-secondary" onClick={() => update(index, { ...item, _hidden: !item?._hidden })} aria-pressed={Boolean(item?._hidden)} aria-label={`${item?._hidden ? 'Show' : 'Hide'} item ${index + 1} on the website`}>
                                    {item?._hidden ? 'Show' : 'Hide'}
                                </button>
                            ) : null}
                            <button type="button" className="btn btn-outline-secondary" disabled={index === 0} onClick={() => move(index, -1)} aria-label={`Move item ${index + 1} up`}>
                                ↑
                            </button>
                            <button type="button" className="btn btn-outline-secondary" disabled={index === items.length - 1} onClick={() => move(index, 1)} aria-label={`Move item ${index + 1} down`}>
                                ↓
                            </button>
                            <button type="button" className="btn btn-outline-danger" onClick={() => onChange(items.filter((_, i) => i !== index))} aria-label={`Remove item ${index + 1}`}>
                                Remove
                            </button>
                        </div>
                    </div>
                    <FieldList fields={field.fields} values={item ?? {}} onChange={(name, v) => update(index, { ...item, [name]: v })} pathPrefix={`${path}.${index}`} errors={errors} media={media} onMediaLoaded={onMediaLoaded} hideItems={hideItems} compact />
                </div>
            ))}
            {items.length < max ? (
                <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => onChange([...items, emptyItem(field.fields)])}>
                    + Add {field.label.toLowerCase().replace(/s$/, '')}
                </button>
            ) : (
                <p className="small text-secondary">Maximum of {max} items reached.</p>
            )}
        </div>
    );
}

export function Field({ field, value, onChange, path, errors, media, onMediaLoaded, allValues, sectionTypes, hideItems }) {
    const id = `f-${path.replace(/\./g, '-')}`;
    const error = errors?.[path];
    const nestedError = !error && Object.keys(errors ?? {}).some((key) => key.startsWith(path + '.'));
    const describedBy = [field.help ? `${id}-help` : null, error ? `${id}-error` : null].filter(Boolean).join(' ') || undefined;
    const common = { id, 'aria-describedby': describedBy, 'aria-invalid': error ? true : undefined };
    const required = field.required ? <span className="text-danger" aria-hidden="true"> *</span> : null;

    let control;
    switch (field.type) {
        case 'textarea':
            control = <textarea {...common} className={`form-control ${error ? 'is-invalid' : ''}`} rows={field.max && field.max > 600 ? 6 : 3} maxLength={field.max} value={value ?? ''} onChange={(e) => onChange(e.target.value)} />;
            break;
        case 'richtext':
            control = <RichTextEditor id={id} value={value} onChange={onChange} invalid={Boolean(error)} describedBy={describedBy} />;
            break;
        case 'image':
            control = <MediaPicker id={id} value={value} media={media} onChange={onChange} onMediaLoaded={onMediaLoaded} invalid={Boolean(error)} />;
            break;
        case 'file':
            control = <MediaPicker id={id} kind="document" value={value} media={media} onChange={onChange} onMediaLoaded={onMediaLoaded} invalid={Boolean(error)} />;
            break;
        case 'group':
            control = (
                <div className="border rounded p-3 pb-0 bg-light-subtle" id={id}>
                    <FieldList fields={field.fields} values={value ?? {}} onChange={(name, v) => onChange({ ...(value ?? {}), [name]: v })} pathPrefix={path} errors={errors} media={media} onMediaLoaded={onMediaLoaded} hideItems={hideItems || field.hideItems} compact />
                </div>
            );
            break;
        case 'select':
        case 'reference':
        case 'belongsTo':
            control = (
                <select {...common} className={`form-select ${error ? 'is-invalid' : ''}`} value={value ?? ''} onChange={(e) => onChange(e.target.value === '' ? null : e.target.value)}>
                    <option value="">{field.required ? 'Select…' : '— None —'}</option>
                    {(field.options ?? []).map((option) => (
                        <option key={option.value} value={option.value}>
                            {option.label}
                        </option>
                    ))}
                </select>
            );
            break;
        case 'belongsToMany':
            control = <MultiSelect id={id} options={field.options ?? []} value={value} onChange={onChange} />;
            break;
        case 'blocks':
            control = <BlockSwitches id={id} options={field.options ?? []} value={value} onChange={onChange} />;
            break;
        case 'boolean':
            return (
                <div className="form-check form-switch mb-3">
                    <input {...common} type="checkbox" role="switch" className="form-check-input" checked={Boolean(value)} onChange={(e) => onChange(e.target.checked)} />
                    <label className="form-check-label" htmlFor={id}>
                        {field.label}
                    </label>
                    {field.help ? (
                        <div id={`${id}-help`} className="form-text">
                            {field.help}
                        </div>
                    ) : null}
                    {error ? <div className="invalid-feedback d-block">{error}</div> : null}
                </div>
            );
        case 'number':
            control = <input {...common} type="number" className={`form-control ${error ? 'is-invalid' : ''}`} min={field.min} max={field.max} value={value ?? ''} onChange={(e) => onChange(e.target.value === '' ? null : Number(e.target.value))} style={{ maxWidth: '10rem' }} />;
            break;
        case 'date':
            control = <input {...common} type="date" className={`form-control ${error ? 'is-invalid' : ''}`} value={value ?? ''} onChange={(e) => onChange(e.target.value)} style={{ maxWidth: '14rem' }} />;
            break;
        case 'datetime':
            control = <input {...common} type="datetime-local" className={`form-control ${error ? 'is-invalid' : ''}`} value={value ?? ''} onChange={(e) => onChange(e.target.value)} style={{ maxWidth: '16rem' }} />;
            break;
        case 'repeater':
            control = <Repeater field={field} value={value} onChange={onChange} path={path} errors={errors} media={media} onMediaLoaded={onMediaLoaded} hideItems={hideItems} />;
            break;
        case 'slug': {
            const source = allValues?.[field.from ?? 'title'];
            control = (
                <div className="input-group">
                    <input {...common} className={`form-control ${error ? 'is-invalid' : ''}`} value={value ?? ''} placeholder={slugify(source)} onChange={(e) => onChange(slugify(e.target.value) || e.target.value.toLowerCase())} />
                    <button type="button" className="btn btn-outline-secondary" onClick={() => onChange(slugify(source))}>
                        Generate
                    </button>
                </div>
            );
            break;
        }
        case 'url':
            control = <input {...common} type="text" inputMode="url" className={`form-control ${error ? 'is-invalid' : ''}`} placeholder="/page or https://…" value={value ?? ''} onChange={(e) => onChange(e.target.value)} />;
            break;
        case 'email':
            control = <input {...common} type="email" className={`form-control ${error ? 'is-invalid' : ''}`} value={value ?? ''} onChange={(e) => onChange(e.target.value)} />;
            break;
        default:
            control = (
                <>
                    <input {...common} type="text" className={`form-control ${error ? 'is-invalid' : ''}`} maxLength={field.max} value={value ?? ''} onChange={(e) => onChange(e.target.value)} />
                    {field.max && (value ?? '').length > field.max * 0.85 ? <div className="form-text">{(value ?? '').length}/{field.max}</div> : null}
                </>
            );
    }

    return (
        <div className={`mb-3 ${nestedError ? 'border-start border-danger border-3 ps-2' : ''}`}>
            <label className="form-label fw-medium" htmlFor={id}>
                {field.label}
                {required}
            </label>
            {control}
            {field.help ? (
                <div id={`${id}-help`} className="form-text">
                    {field.help}
                </div>
            ) : null}
            {error ? (
                <div id={`${id}-error`} className="invalid-feedback d-block">
                    {error}
                </div>
            ) : null}
        </div>
    );
}

export function FieldList({ fields, values, onChange, pathPrefix = '', errors, media, onMediaLoaded, compact = false, hideItems = false }) {
    const halfTypes = useMemo(() => new Set(['text', 'url', 'select', 'reference', 'belongsTo', 'number', 'date', 'datetime', 'email', 'slug']), []);
    return (
        <div className="row g-0 column-gap-3">
            {fields.map((field) => {
                const path = pathPrefix ? `${pathPrefix}.${field.name}` : field.name;
                const half = compact && halfTypes.has(field.type);
                return (
                    <div key={field.name} className={half ? 'col-md' : 'col-12'} style={half ? { minWidth: '14rem' } : undefined}>
                        <Field field={field} value={values?.[field.name]} onChange={(v) => onChange(field.name, v)} path={path} errors={errors} media={media} onMediaLoaded={onMediaLoaded} allValues={values} hideItems={hideItems} />
                    </div>
                );
            })}
        </div>
    );
}
