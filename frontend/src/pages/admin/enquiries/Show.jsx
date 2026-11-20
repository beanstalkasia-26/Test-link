import Link from '../../../router/Link';
import { router } from '../../../router/navigation';
import AdminLayout from '../../../components/admin/AdminLayout';

export default function EnquiryShow({ enquiry, statuses }) {
    const rows = [
        ['Type', enquiry.type],
        ['Program', enquiry.program],
        ['Email', enquiry.email && <a href={`mailto:${enquiry.email}`}>{enquiry.email}</a>],
        ['Phone', enquiry.phone],
        ['Organisation', enquiry.organisation],
        ['Subject', enquiry.subject],
        ['Received', enquiry.created_at],
        ['Consent', enquiry.consent ? `Yes – “${enquiry.consent_text}”` : 'No'],
        ['Submitted from', enquiry.source_url],
        ['Staff notified', enquiry.notified_at ?? 'Not yet'],
    ].filter(([, v]) => v);

    return (
        <AdminLayout title={`Enquiry from ${enquiry.name}`} actions={<Link href="/admin/enquiries" className="btn btn-sm btn-outline-secondary">← All enquiries</Link>}>
            <div className="row g-4">
                <div className="col-lg-8">
                    <div className="admin-card">
                        <h2 className="h6">Message</h2>
                        <p style={{ whiteSpace: 'pre-wrap' }}>{enquiry.message}</p>
                        <dl className="row small mb-0">
                            {rows.map(([label, value]) => (
                                <div className="col-12 d-flex border-top py-2" key={label}>
                                    <dt className="fw-normal text-secondary" style={{ width: '10rem' }}>{label}</dt>
                                    <dd className="mb-0">{value}</dd>
                                </div>
                            ))}
                        </dl>
                    </div>
                </div>
                <div className="col-lg-4">
                    <div className="admin-card mb-3">
                        <label className="form-label fw-medium" htmlFor="enq-status">Status</label>
                        <select id="enq-status" className="form-select" value={enquiry.status} onChange={(e) => router.put(`/admin/enquiries/${enquiry.id}`, { status: e.target.value }, { preserveScroll: true })}>
                            {Object.entries(statuses).map(([v, l]) => <option key={v} value={v}>{l}</option>)}
                        </select>
                    </div>
                    {enquiry.notification_error ? (
                        <div className="alert alert-warning small">
                            <strong className="d-block">Email notification failed</strong>
                            {enquiry.notification_error}
                            <button type="button" className="btn btn-sm btn-outline-dark mt-2 d-block" onClick={() => router.post(`/admin/enquiries/${enquiry.id}/resend`)}>Retry notification</button>
                        </div>
                    ) : null}
                </div>
            </div>
        </AdminLayout>
    );
}
