import { useEffect, useRef, useState } from 'react';
import { http } from '../../lib/http';

export function UploadForm({ onUploaded, compact = false, kind = 'any' }) {
    const accept = { image: 'image/jpeg,image/png,image/webp,image/avif', document: 'application/pdf' }[kind] ?? 'image/jpeg,image/png,image/webp,image/avif,application/pdf';
    const fileLabel = { image: 'Image (JPEG, PNG, WebP or AVIF)', document: 'PDF document' }[kind] ?? 'Image (JPEG, PNG, WebP, AVIF) or PDF';
    const [file, setFile] = useState(null);
    const [alt, setAlt] = useState('');
    const [caption, setCaption] = useState('');
    const [errors, setErrors] = useState({});
    const [busy, setBusy] = useState(false);
    const input = useRef(null);

    const submit = async (e) => {
        e.preventDefault();
        e.stopPropagation();
        setBusy(true);
        setErrors({});
        const body = new FormData();
        if (file) body.append('file', file);
        body.append('alt', alt);
        body.append('caption', caption);
        try {
            const media = await http('/admin/media', { method: 'POST', body });
            setFile(null);
            setAlt('');
            setCaption('');
            if (input.current) input.current.value = '';
            onUploaded?.(media);
        } catch (error) {
            setErrors(error.errors && Object.keys(error.errors).length ? error.errors : { file: [error.message] });
        } finally {
            setBusy(false);
        }
    };

    return (
        <form onSubmit={submit} className={compact ? 'row g-2 align-items-end' : 'row g-3 align-items-end'} noValidate>
            <div className="col-md-4">
                <label className="form-label small" htmlFor="upload-file">
                    {fileLabel}
                </label>
                <input ref={input} id="upload-file" type="file" accept={accept} className={`form-control form-control-sm ${errors.file ? 'is-invalid' : ''}`} onChange={(e) => setFile(e.target.files?.[0] ?? null)} />
                {errors.file ? <div className="invalid-feedback">{errors.file[0]}</div> : null}
            </div>
            <div className="col-md-4">
                <label className="form-label small" htmlFor="upload-alt">
                    {kind === 'document' ? 'Document title' : 'Alt text (describe the image)'}
                </label>
                <input id="upload-alt" className={`form-control form-control-sm ${errors.alt ? 'is-invalid' : ''}`} value={alt} onChange={(e) => setAlt(e.target.value)} maxLength={255} />
                {errors.alt ? <div className="invalid-feedback">{errors.alt[0]}</div> : null}
            </div>
            <div className="col-md-2">
                <label className="form-label small" htmlFor="upload-caption">
                    Caption
                </label>
                <input id="upload-caption" className="form-control form-control-sm" value={caption} onChange={(e) => setCaption(e.target.value)} maxLength={500} />
            </div>
            <div className="col-md-2">
                <button type="submit" className="btn btn-primary btn-sm w-100" disabled={busy || !file}>
                    {busy ? 'Uploading…' : 'Upload'}
                </button>
            </div>
        </form>
    );
}

export function PickerDialog({ onSelect, onClose, kind = 'image' }) {
    const dialog = useRef(null);
    const [items, setItems] = useState([]);
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [q, setQ] = useState('');
    const [loading, setLoading] = useState(false);

    const load = async (nextPage = 1, query = q) => {
        setLoading(true);
        try {
            const data = await http(`/admin/media/browse?page=${nextPage}&kind=${kind}&q=${encodeURIComponent(query)}`);
            setItems(nextPage === 1 ? data.data : (current) => [...current, ...data.data]);
            setPage(data.current_page);
            setLastPage(data.last_page);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        dialog.current?.showModal();
        load(1, '');
    }, []);

    return (
        <dialog ref={dialog} className="border-0 rounded-3 p-0 shadow" style={{ width: 'min(64rem, 95vw)' }} onClose={onClose} aria-label={kind === 'document' ? 'Choose a document' : 'Choose an image'}>
            <div className="p-3 border-bottom d-flex justify-content-between align-items-center gap-3">
                <h2 className="h5 m-0">{kind === 'document' ? 'Choose a PDF document' : 'Choose an image'}</h2>
                <button type="button" className="btn-close" aria-label="Close" onClick={() => dialog.current.close()} />
            </div>
            <div className="p-3 border-bottom bg-light">
                <UploadForm compact kind={kind} onUploaded={(media) => onSelect(media)} />
            </div>
            <div className="p-3">
                <form className="d-flex gap-2 mb-3" onSubmit={(e) => {
                    e.preventDefault();
                    load(1, q);
                }}>
                    <label htmlFor="media-search" className="visually-hidden">
                        Search {kind === 'document' ? 'documents' : 'images'}
                    </label>
                    <input id="media-search" className="form-control form-control-sm" placeholder="Search by file name or alt text" value={q} onChange={(e) => setQ(e.target.value)} />
                    <button className="btn btn-sm btn-outline-secondary" type="submit">
                        Search
                    </button>
                </form>
                <div className="media-grid" style={{ maxHeight: '55vh', overflowY: 'auto' }}>
                    {items.map((item) => (
                        <button key={item.id} type="button" onClick={() => onSelect(item)} aria-label={`Select ${item.alt || item.original_name}`}>
                            {item.kind === 'document' ? <span className="media-thumb d-flex align-items-center justify-content-center fw-medium text-danger">PDF</span> : item.thumb ? <img src={item.thumb} alt="" className="media-thumb" loading="lazy" /> : <span className="media-thumb d-grid place-items-center small">Processing…</span>}
                            <small className="mt-1">{item.original_name}</small>
                        </button>
                    ))}
                </div>
                {page < lastPage ? (
                    <button type="button" className="btn btn-sm btn-outline-secondary mt-3" disabled={loading} onClick={() => load(page + 1)}>
                        Load more
                    </button>
                ) : null}
            </div>
        </dialog>
    );
}

export default function MediaPicker({ id, value, media, onChange, onMediaLoaded, invalid, kind = 'image' }) {
    const isDocument = kind === 'document';
    const [open, setOpen] = useState(false);
    const current = value ? media?.[value] : null;

    return (
        <div className={`d-flex align-items-center gap-3 p-2 border rounded ${invalid ? 'border-danger' : ''}`} id={id}>
            <div style={{ width: '7rem', flex: 'none' }}>
                {isDocument && value ? (
                    <span className="media-thumb d-flex align-items-center justify-content-center fw-medium text-danger">PDF</span>
                ) : current?.thumb ? (
                    <img src={current.thumb} alt={current.alt || ''} className="media-thumb" />
                ) : (
                    <span className="media-thumb d-flex align-items-center justify-content-center small text-secondary">{value ? `#${value}` : isDocument ? 'No file' : 'No image'}</span>
                )}
            </div>
            <div className="small flex-grow-1 min-w-0">
                {current ? (
                    <>
                        <div className="text-truncate fw-medium">{current.original_name}</div>
                        {isDocument ? (
                            <a className="small" href={current.document_url} target="_blank" rel="noopener">
                                {current.alt || 'Open document'}
                            </a>
                        ) : (
                            <div className="text-secondary text-truncate">Alt: {current.alt || <em>none – decorative</em>}</div>
                        )}
                    </>
                ) : null}
                <div className="d-flex flex-wrap gap-2 mt-2">
                    <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => setOpen(true)}>
                        {value ? 'Replace' : isDocument ? 'Choose PDF' : 'Choose image'}
                    </button>
                    {value ? (
                        <button type="button" className="btn btn-sm btn-outline-secondary" onClick={() => onChange(null)}>
                            Remove
                        </button>
                    ) : null}
                </div>
            </div>
            {open ? (
                <PickerDialog
                    kind={kind}
                    onClose={() => setOpen(false)}
                    onSelect={(item) => {
                        onMediaLoaded?.(item);
                        onChange(item.id);
                        setOpen(false);
                    }}
                />
            ) : null}
        </div>
    );
}
