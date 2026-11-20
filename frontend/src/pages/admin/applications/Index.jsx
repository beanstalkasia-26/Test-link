import Link from '../../../router/Link';
import { router } from '../../../router/navigation';
import { useState } from 'react';
import AdminLayout, { Paginator } from '../../../components/admin/AdminLayout';

export default function ApplicationsIndex({ applications, jobs, statuses, filters }) {
    const [q, setQ] = useState(filters.q ?? '');
    const apply = (params) => router.get('/admin/applications', { ...filters, ...params }, { preserveState: true, replace: true });

    return (
        <AdminLayout title="Job applications">
            <form className="d-flex flex-wrap gap-2 mb-3" onSubmit={(e) => {
                e.preventDefault();
                apply({ q: q || undefined, page: undefined });
            }}>
                <label className="visually-hidden" htmlFor="ap-q">Search</label>
                <input id="ap-q" type="search" className="form-control" style={{ maxWidth: '20rem' }} placeholder="Name or email" value={q} onChange={(e) => setQ(e.target.value)} />
                <label className="visually-hidden" htmlFor="ap-job">Position</label>
                <select id="ap-job" className="form-select" style={{ maxWidth: '18rem' }} value={filters.job ?? ''} onChange={(e) => apply({ job: e.target.value || undefined, page: undefined })}>
                    <option value="">All positions</option>
                    {Object.entries(jobs).map(([id, title]) => <option key={id} value={id}>{title}</option>)}
                </select>
                <label className="visually-hidden" htmlFor="ap-status">Status</label>
                <select id="ap-status" className="form-select" style={{ maxWidth: '12rem' }} value={filters.status ?? ''} onChange={(e) => apply({ status: e.target.value || undefined, page: undefined })}>
                    <option value="">All statuses</option>
                    {Object.entries(statuses).map(([v, l]) => <option key={v} value={v}>{l}</option>)}
                </select>
                <button type="submit" className="btn btn-outline-secondary">Search</button>
            </form>
            <div className="table-responsive">
                <table className="table table-admin table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Received</th>
                            <th scope="col">Applicant</th>
                            <th scope="col">Position</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {applications.data.map((a) => (
                            <tr key={a.id}>
                                <td className="small text-nowrap">{a.created_at}</td>
                                <td>
                                    <Link href={`/admin/applications/${a.id}`} className="fw-medium">{a.name}</Link>
                                    <div className="small text-secondary">{a.email}</div>
                                </td>
                                <td className="small">{a.job ?? '—'}</td>
                                <td><span className="status-badge">{statuses[a.status]}</span></td>
                            </tr>
                        ))}
                        {!applications.data.length ? <tr><td colSpan={4} className="text-center text-secondary py-4">No applications yet.</td></tr> : null}
                    </tbody>
                </table>
            </div>
            <Paginator meta={applications} />
        </AdminLayout>
    );
}
