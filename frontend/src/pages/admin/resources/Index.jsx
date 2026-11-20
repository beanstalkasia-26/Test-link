import Link from '../../../router/Link';
import { router } from '../../../router/navigation';
import { useState } from 'react';
import AdminLayout, { Paginator, StatusBadge } from '../../../components/admin/AdminLayout';

export default function ResourceIndex({ resource, records, filters, can }) {
    const [q, setQ] = useState(filters.q ?? '');
    const base = `/admin/content/${resource.slug}`;
    const apply = (params) => router.get(base, { ...filters, ...params }, { preserveState: true, replace: true });

    return (
        <AdminLayout
            title={resource.label}
            actions={
                can.edit ? (
                    <Link href={`${base}/create`} className="btn btn-primary">
                        + New {resource.singular.toLowerCase()}
                    </Link>
                ) : null
            }
        >
            <form className="d-flex flex-wrap gap-2 mb-3" role="search" onSubmit={(e) => {
                e.preventDefault();
                apply({ q: q || undefined, page: undefined });
            }}>
                <label htmlFor="resource-q" className="visually-hidden">
                    Search {resource.label}
                </label>
                <input id="resource-q" type="search" className="form-control" style={{ maxWidth: '22rem' }} placeholder={`Search ${resource.label.toLowerCase()}…`} value={q} onChange={(e) => setQ(e.target.value)} />
                {resource.publishable ? (
                    <>
                        <label htmlFor="resource-status" className="visually-hidden">
                            Status
                        </label>
                        <select id="resource-status" className="form-select" style={{ maxWidth: '14rem' }} value={filters.status ?? ''} onChange={(e) => apply({ status: e.target.value || undefined, page: undefined })}>
                            <option value="">All statuses</option>
                            {resource.statuses.map((s) => (
                                <option key={s.value} value={s.value}>
                                    {s.label}
                                </option>
                            ))}
                            <option value="review">Awaiting review</option>
                            <option value="pending">Has unpublished changes</option>
                        </select>
                    </>
                ) : null}
                <button type="submit" className="btn btn-outline-secondary">
                    Search
                </button>
            </form>
            <div className="table-responsive">
                <table className="table table-hover table-admin">
                    <thead>
                        <tr>
                            {resource.columns.map((c) => (
                                <th key={c.key} scope="col">
                                    {c.label}
                                </th>
                            ))}
                            {resource.publishable ? <th scope="col">Status</th> : null}
                            <th scope="col">Updated</th>
                            <th scope="col" className="text-end">
                                <span className="visually-hidden">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {records.data.map((row) => (
                            <tr key={row.id}>
                                {resource.columns.map((c, i) => (
                                    <td key={c.key}>{i === 0 ? <Link href={`${base}/${row.id}/edit`} className="fw-medium">{row[c.key] || '(untitled)'}</Link> : row[c.key] ?? '—'}</td>
                                ))}
                                {resource.publishable ? (
                                    <td>
                                        <StatusBadge status={row.status} label={row.status_label} review={row.review_state} pending={row.has_pending_changes} />
                                        {row.scheduled_for ? <div className="small text-secondary">Scheduled {row.scheduled_for}</div> : null}
                                    </td>
                                ) : null}
                                <td className="small text-secondary">{row.updated_at}</td>
                                <td className="text-end text-nowrap">
                                    {row.url ? (
                                        <a href={row.url} target="_blank" rel="noopener" className="btn btn-sm btn-link">
                                            View
                                        </a>
                                    ) : null}
                                    <Link href={`${base}/${row.id}/edit`} className="btn btn-sm btn-outline-primary">
                                        Edit
                                    </Link>
                                </td>
                            </tr>
                        ))}
                        {!records.data.length ? (
                            <tr>
                                <td colSpan={resource.columns.length + 3} className="text-center text-secondary py-4">
                                    Nothing found.
                                </td>
                            </tr>
                        ) : null}
                    </tbody>
                </table>
            </div>
            <Paginator meta={records} />
        </AdminLayout>
    );
}
