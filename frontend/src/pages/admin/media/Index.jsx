import { router } from '../../../router/navigation';
import { useRef, useState } from 'react';
import AdminLayout, { Paginator } from '../../../components/admin/AdminLayout';
import { UploadForm } from '../../../components/admin/MediaPicker';
import { http } from '../../../lib/http';

function Details({ item, onClose }) {
    const dialog = useRef(null);
    const [data, setData] = useState({ alt: item.alt ?? '', caption: item.caption ?? '', credit: item.credit ?? '' });
    const [usages, setUsages] = useState(null);

    const open = async (el) => {
        if (!el || el.open) return;
        dialog.current = el;
        el.showModal();
        const detail = await http(`/admin/media/${item.id}`);
        setUsages(detail.usages);
    };

    return (
        <dialog ref={open} className="border-0 rounded-3 shadow p-0" style={{ width: 'min(46rem, 95vw)' }} onClose={onClose} aria-label="Image details">
            <div className="p-3 border-bottom d-flex justify-content-between">
                <h2 className="h5 m-0 text-truncate">{item.original_name}</h2>
                <button type="button" className="btn-close" aria-label="Close" onClick={() => dialog.current.close()} />
            </div>
            <div className="p-3 row g-3">
                <div className="col-md-5">
                    <img src={item.thumb ?? item.url} alt={item.alt ?? ''} className="media-thumb" />
                    <p className="small text-secondary mt-2 mb-0">
                        {item.width}×{item.height}px · {(item.size / 1024).toFixed(0)} KB · {item.mime_type}
                    </p>
                    <p className="small text-secondary">Responsive AVIF, WebP and JPEG versions are generated automatically.</p>
                </div>
                <form className="col-md-7" onSubmit={(e) => {
                    e.preventDefault();
                    router.put(`/admin/media/${item.id}`, data, { preserveScroll: true, onSuccess: () => dialog.current.close() });
                }}>
                    <label className="form-label small" htmlFor="m-alt">
                        Alt text
                    </label>
                    <input id="m-alt" className="form-control form-control-sm mb-2" value={data.alt} onChange={(e) => setData({ ...data, alt: e.target.value })} />
                    <label className="form-label small" htmlFor="m-caption">
                        Caption
                    </label>
                    <input id="m-caption" className="form-control form-control-sm mb-2" value={data.caption} onChange={(e) => setData({ ...data, caption: e.target.value })} />
                    <label className="form-label small" htmlFor="m-credit">
                        Credit / licence
                    </label>
                    <input id="m-credit" className="form-control form-control-sm mb-3" value={data.credit} onChange={(e) => setData({ ...data, credit: e.target.value })} />
                    <button type="submit" className="btn btn-primary btn-sm">
                        Save details
                    </button>
                </form>
                <div className="col-12">
                    <h3 className="h6">Where this image is used</h3>
                    {usages === null ? (
                        <p className="small text-secondary">Loading…</p>
                    ) : usages.length ? (
                        <ul className="small">
                            {usages.map((u, i) => (
                                <li key={i}>
                                    {u.url ? <a href={u.url}>{u.title}</a> : u.title} <span className="text-secondary">({u.type})</span>
                                </li>
                            ))}
                        </ul>
                    ) : (
                        <p className="small text-secondary">Not used anywhere.</p>
                    )}
                    <button
                        type="button"
                        className="btn btn-outline-danger btn-sm"
                        disabled={usages === null || usages.length > 0}
                        title={usages?.length ? 'Replace the image where it is used before deleting it.' : undefined}
                        onClick={() => window.confirm('Delete this image permanently?') && router.delete(`/admin/media/${item.id}`, { onSuccess: () => dialog.current.close() })}
                    >
                        Delete image
                    </button>
                    {usages?.length ? <span className="small text-secondary ms-2">In-use images cannot be deleted.</span> : null}
                </div>
            </div>
        </dialog>
    );
}

export default function MediaIndex({ media, filters }) {
    const [q, setQ] = useState(filters.q ?? '');
    const [selected, setSelected] = useState(null);

    return (
        <AdminLayout title="Media library">
            <div className="admin-card mb-4">
                <h2 className="h6">Upload an image</h2>
                <UploadForm onUploaded={() => router.reload({ only: ['media'] })} />
            </div>
            <form className="d-flex gap-2 mb-3" onSubmit={(e) => {
                e.preventDefault();
                router.get('/admin/media', { q: q || undefined }, { preserveState: true });
            }}>
                <label htmlFor="media-q" className="visually-hidden">
                    Search images
                </label>
                <input id="media-q" type="search" className="form-control" style={{ maxWidth: '22rem' }} placeholder="Search by file name or alt text" value={q} onChange={(e) => setQ(e.target.value)} />
                <button className="btn btn-outline-secondary" type="submit">
                    Search
                </button>
            </form>
            <div className="media-grid">
                {media.data.map((item) => (
                    <button key={item.id} type="button" onClick={() => setSelected(item)} aria-label={`Details for ${item.original_name}`}>
                        {item.thumb ? <img src={item.thumb} alt="" className="media-thumb" loading="lazy" /> : <span className="media-thumb d-flex align-items-center justify-content-center small">{item.status}</span>}
                        <small className="mt-1">{item.original_name}</small>
                        <small className="text-secondary">{item.usages_count ? `Used in ${item.usages_count}` : 'Unused'}</small>
                    </button>
                ))}
            </div>
            <Paginator meta={media} />
            {selected ? <Details key={selected.id} item={selected} onClose={() => setSelected(null)} /> : null}
        </AdminLayout>
    );
}
