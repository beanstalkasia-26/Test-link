import { router } from '../../router/navigation';
import { useState } from 'react';
import AdminLayout, { Paginator } from '../../components/admin/AdminLayout';

export default function AuditLog({ logs, filters }) {
    const [q, setQ] = useState(filters.q ?? '');
    return (
        <AdminLayout title="Audit log">
            <form className="d-flex flex-wrap gap-2 mb-3" onSubmit={(e) => {
                e.preventDefault();
                router.get('/admin/audit', { q: q || undefined, action: filters.action || undefined }, { preserveState: true });
            }}>
                <label className="visually-hidden" htmlFor="al-q">Search</label>
                <input id="al-q" type="search" className="form-control" style={{ maxWidth: '22rem' }} value={q} onChange={(e) => setQ(e.target.value)} placeholder="Search descriptions" />
                <label className="visually-hidden" htmlFor="al-action">Action</label>
                <select id="al-action" className="form-select" style={{ maxWidth: '14rem' }} value={filters.action ?? ''} onChange={(e) => router.get('/admin/audit', { q: q || undefined, action: e.target.value || undefined })}>
                    <option value="">All actions</option>
                    <option value="auth">Sign-ins</option>
                    <option value="content">Content</option>
                    <option value="media">Media</option>
                    <option value="navigation">Navigation</option>
                    <option value="enquiry">Enquiries</option>
                    <option value="newsletter">Newsletter</option>
                    <option value="settings">Settings</option>
                    <option value="user">Users</option>
                </select>
                <button type="submit" className="btn btn-outline-secondary">Search</button>
            </form>
            <div className="table-responsive">
                <table className="table table-admin table-sm">
                    <thead>
                        <tr>
                            <th scope="col">When</th>
                            <th scope="col">User</th>
                            <th scope="col">Action</th>
                            <th scope="col">Details</th>
                            <th scope="col">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        {logs.data.map((l) => (
                            <tr key={l.id}>
                                <td className="small text-nowrap">{l.created_at}</td>
                                <td className="small">{l.user}</td>
                                <td><code className="small">{l.action}</code></td>
                                <td className="small">{l.description}</td>
                                <td className="small text-secondary">{l.ip_address}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <Paginator meta={logs} />
        </AdminLayout>
    );
}
