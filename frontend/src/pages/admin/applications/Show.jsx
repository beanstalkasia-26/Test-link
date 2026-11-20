import Link from '../../../router/Link';
import { router } from '../../../router/navigation';
import AdminLayout from '../../../components/admin/AdminLayout';

export default function ApplicationShow({ application, statuses }) {
    const rows = [
        ['Position', application.job],
        ['Email', application.email && <a href={`mailto:${application.email}`}>{application.email}</a>],
        ['Phone', application.phone],
        ['Highest qualification', [application.qualification, application.qualification_year].filter(Boolean).join(', ')],
        ['Institution', application.institution],
        ['Experience', application.experience_years],
        ['Current company', application.current_company],
        ['Location', application.location],
        ['Received', application.created_at],
    ].filter(([, v]) => v);

    return (
        <AdminLayout title={`${application.first_name} ${application.last_name}`} actions={<Link href="/admin/applications" className="btn btn-sm btn-outline-secondary">← All applications</Link>}>
            <div className="row g-4">
                <div className="col-lg-8">
                    <div className="admin-card">
                        <dl className="mb-0">
                            {rows.map(([label, value]) => (
                                <div className="d-flex border-bottom py-2" key={label}>
                                    <dt className="fw-normal text-secondary" style={{ width: '12rem' }}>{label}</dt>
                                    <dd className="mb-0">{value}</dd>
                                </div>
                            ))}
                        </dl>
                        {application.has_resume ? (
                            <a className="btn btn-primary mt-3" href={`/admin/applications/${application.id}/resume`}>Download CV ({application.resume_name})</a>
                        ) : null}
                    </div>
                </div>
                <div className="col-lg-4">
                    <div className="admin-card">
                        <label className="form-label fw-medium" htmlFor="app-status">Status</label>
                        <select id="app-status" className="form-select" value={application.status} onChange={(e) => router.put(`/admin/applications/${application.id}`, { status: e.target.value }, { preserveScroll: true })}>
                            {Object.entries(statuses).map(([v, l]) => <option key={v} value={v}>{l}</option>)}
                        </select>
                        {application.notification_error ? <div className="alert alert-warning small mt-3 mb-0">Email notification failed: {application.notification_error}</div> : null}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
