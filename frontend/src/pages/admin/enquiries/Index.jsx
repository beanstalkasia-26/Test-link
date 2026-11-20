import Link from '../../../router/Link';
import { router } from '../../../router/navigation';
import { useState } from 'react';
import AdminLayout, { Paginator } from '../../../components/admin/AdminLayout';

export default function EnquiriesIndex({ enquiries, filters, types, statuses, canExport }) {
    const [f, setF] = useState({ q: '', type: '', status: '', from: '', to: '', ...filters });
    const clean = Object.fromEntries(Object.entries(f).filter(([, v]) => v));
    const exportUrl = `/admin/enquiries/export?${new URLSearchParams(clean)}`;

    return (
        <AdminLayout title="Enquiries" actions={canExport ? <a className="btn btn-outline-primary btn-sm" href={exportUrl}>Export CSV</a> : null}>
            <form className="row g-2 mb-3 align-items-end" onSubmit={(e) => {
                e.preventDefault();
                router.get('/admin/enquiries', clean, { preserveState: true });
            }}>
                <div className="col-md-3">
                    <label className="form-label small" htmlFor="eq-q">Search</label>
                    <input id="eq-q" className="form-control form-control-sm" value={f.q} onChange={(e) => setF({ ...f, q: e.target.value })} placeholder="Name, email, message…" />
                </div>
                <div className="col-md-2">
                    <label className="form-label small" htmlFor="eq-type">Type</label>
                    <select id="eq-type" className="form-select form-select-sm" value={f.type} onChange={(e) => setF({ ...f, type: e.target.value })}>
                        <option value="">All</option>
                        {Object.entries(types).map(([v, l]) => <option key={v} value={v}>{l}</option>)}
                    </select>
                </div>
                <div className="col-md-2">
                    <label className="form-label small" htmlFor="eq-status">Status</label>
                    <select id="eq-status" className="form-select form-select-sm" value={f.status} onChange={(e) => setF({ ...f, status: e.target.value })}>
                        <option value="">All</option>
                        {Object.entries(statuses).map(([v, l]) => <option key={v} value={v}>{l}</option>)}
                    </select>
                </div>
                <div className="col-md-2">
                    <label className="form-label small" htmlFor="eq-from">From</label>
                    <input id="eq-from" type="date" className="form-control form-control-sm" value={f.from} onChange={(e) => setF({ ...f, from: e.target.value })} />
                </div>
                <div className="col-md-2">
                    <label className="form-label small" htmlFor="eq-to">To</label>
                    <input id="eq-to" type="date" className="form-control form-control-sm" value={f.to} onChange={(e) => setF({ ...f, to: e.target.value })} />
                </div>
                <div className="col-md-1">
                    <button className="btn btn-sm btn-primary w-100" type="submit">Filter</button>
                </div>
            </form>
            <div className="table-responsive">
                <table className="table table-admin table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Received</th>
                            <th scope="col">Name</th>
                            <th scope="col">Type</th>
                            <th scope="col">Subject / program</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {enquiries.data.map((e) => (
                            <tr key={e.id}>
                                <td className="small text-nowrap">{e.created_at}</td>
                                <td>
                                    <Link href={`/admin/enquiries/${e.id}`} className="fw-medium">{e.name}</Link>
                                    <div className="small text-secondary">{e.email}</div>
                                </td>
                                <td className="small">{e.type}</td>
                                <td className="small">{e.program ?? e.subject ?? '—'}</td>
                                <td>
                                    <span className={`status-badge status-badge--${e.status === 'new' ? 'review' : e.status === 'closed' ? 'published' : 'scheduled'}`}>{statuses[e.status]}</span>
                                    {e.notification_failed ? <span className="status-badge status-badge--unpublished ms-1">Email failed</span> : null}
                                </td>
                            </tr>
                        ))}
                        {!enquiries.data.length ? <tr><td colSpan={5} className="text-center text-secondary py-4">No enquiries found.</td></tr> : null}
                    </tbody>
                </table>
            </div>
            <Paginator meta={enquiries} />
        </AdminLayout>
    );
}
