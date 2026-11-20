import Link from '../../router/Link';
import AdminLayout from '../../components/admin/AdminLayout';

function Stat({ label, value, href }) {
    if (value === null || value === undefined) return null;
    const content = (
        <>
            <div className="value">{value}</div>
            <div className="label">{label}</div>
        </>
    );
    return <div className="col-6 col-lg">{href ? <Link href={href} className="stat-card d-block text-decoration-none">{content}</Link> : <div className="stat-card">{content}</div>}</div>;
}

function ItemList({ title, items, empty, meta }) {
    return (
        <div className="admin-card h-100">
            <h2 className="h6 mb-3">{title}</h2>
            {items.length ? (
                <ul className="list-unstyled mb-0">
                    {items.map((item) => (
                        <li key={item.key ?? item.id} className="border-bottom py-2 small">
                            <Link href={item.url} className="fw-medium d-block">
                                {item.title}
                            </Link>
                            <span className="text-secondary">{meta(item)}</span>
                        </li>
                    ))}
                </ul>
            ) : (
                <p className="small text-secondary mb-0">{empty}</p>
            )}
        </div>
    );
}

export default function Dashboard({ counts, awaitingReview, scheduled, drafts, enquiries, activity }) {
    return (
        <AdminLayout title="Dashboard">
            <div className="row g-3 mb-4">
                <Stat label="Published pages" value={counts.published_pages} href="/admin/content/pages?status=published" />
                <Stat label="Drafts" value={counts.draft_items} />
                <Stat label="Awaiting review" value={counts.awaiting_review} />
                <Stat label="Scheduled" value={counts.scheduled} />
                <Stat label="New enquiries" value={counts.new_enquiries} href="/admin/enquiries?status=new" />
                <Stat label="Newsletter sign-ups (30 days)" value={counts.subscribers_30d} href="/admin/newsletter" />
            </div>
            <div className="row g-4">
                <div className="col-lg-4">
                    <ItemList title="Awaiting review" items={awaitingReview} empty="Nothing is waiting for review." meta={(i) => `${i.type} · updated ${i.updated_at}${i.updated_by ? ` by ${i.updated_by}` : ''}`} />
                </div>
                <div className="col-lg-4">
                    <ItemList title="Scheduled content" items={scheduled} empty="Nothing is scheduled." meta={(i) => `${i.type} · ${i.scheduled_for}`} />
                </div>
                <div className="col-lg-4">
                    <ItemList title="Recent drafts" items={drafts} empty="No drafts." meta={(i) => `${i.type} · ${i.updated_at}`} />
                </div>
                {enquiries ? (
                    <div className="col-lg-6">
                        <ItemList
                            title="Recent enquiries"
                            items={enquiries.map((e) => ({ ...e, title: `${e.name}${e.subject ? ` – ${e.subject}` : ''}`, url: `/admin/enquiries/${e.id}` }))}
                            empty="No enquiries yet."
                            meta={(e) => `${e.type} · ${e.created_at} · ${e.status}${e.notification_failed ? ' · email notification failed' : ''}`}
                        />
                    </div>
                ) : null}
                <div className={enquiries ? 'col-lg-6' : 'col-12'}>
                    <div className="admin-card h-100">
                        <h2 className="h6 mb-3">Recent admin activity</h2>
                        <ul className="list-unstyled small mb-0">
                            {activity.map((a) => (
                                <li key={a.id} className="border-bottom py-2">
                                    {a.description}
                                    <span className="d-block text-secondary">
                                        {a.user} · {a.created_at}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>
            </div>
            <p className="small text-secondary mt-4">Website traffic is not shown here. Use your analytics provider for visitor statistics.</p>
        </AdminLayout>
    );
}
