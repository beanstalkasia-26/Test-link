import { router } from '../../../router/navigation';
import { useState } from 'react';
import AdminLayout, { Paginator } from '../../../components/admin/AdminLayout';

export default function NewsletterIndex({ subscribers, filters, canExport }) {
    const [q, setQ] = useState(filters.q ?? '');
    const params = Object.fromEntries(Object.entries({ q, status: filters.status }).filter(([, v]) => v));
    return (
        <AdminLayout title="Newsletter subscribers" actions={canExport ? <a className="btn btn-sm btn-outline-primary" href={`/admin/newsletter/export?${new URLSearchParams(params)}`}>Export CSV</a> : null}>
            <form className="d-flex flex-wrap gap-2 mb-3" onSubmit={(e) => {
                e.preventDefault();
                router.get('/admin/newsletter', params, { preserveState: true });
            }}>
                <label className="visually-hidden" htmlFor="nl-q">Search</label>
                <input id="nl-q" type="search" className="form-control" style={{ maxWidth: '20rem' }} placeholder="Search email" value={q} onChange={(e) => setQ(e.target.value)} />
                <label className="visually-hidden" htmlFor="nl-status">Status</label>
                <select id="nl-status" className="form-select" style={{ maxWidth: '12rem' }} value={filters.status ?? ''} onChange={(e) => router.get('/admin/newsletter', { ...params, status: e.target.value || undefined })}>
                    <option value="">All</option>
                    <option value="subscribed">Subscribed</option>
                    <option value="unsubscribed">Unsubscribed</option>
                </select>
                <button className="btn btn-outline-secondary" type="submit">Search</button>
            </form>
            <div className="table-responsive">
                <table className="table table-admin">
                    <thead>
                        <tr>
                            <th scope="col">Email</th>
                            <th scope="col">Status</th>
                            <th scope="col">Subscribed</th>
                            <th scope="col" />
                        </tr>
                    </thead>
                    <tbody>
                        {subscribers.data.map((s) => (
                            <tr key={s.id}>
                                <td>{s.email}</td>
                                <td><span className={`status-badge status-badge--${s.status === 'subscribed' ? 'published' : 'unpublished'}`}>{s.status}</span></td>
                                <td className="small">{s.created_at}</td>
                                <td className="text-end">
                                    {s.status === 'subscribed' ? (
                                        <button type="button" className="btn btn-sm btn-link text-danger" onClick={() => window.confirm(`Unsubscribe ${s.email}?`) && router.post(`/admin/newsletter/${s.id}/unsubscribe`, {}, { preserveScroll: true })}>
                                            Unsubscribe
                                        </button>
                                    ) : null}
                                </td>
                            </tr>
                        ))}
                        {!subscribers.data.length ? <tr><td colSpan={4} className="text-center text-secondary py-4">No subscribers found.</td></tr> : null}
                    </tbody>
                </table>
            </div>
            <Paginator meta={subscribers} />
        </AdminLayout>
    );
}
