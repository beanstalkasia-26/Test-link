import Link from '../../../router/Link';
import { router } from '../../../router/navigation';
import useForm from '../../../hooks/useForm';
import AdminLayout from '../../../components/admin/AdminLayout';

export default function UserForm({ user, roles }) {
    const form = useForm({ name: user?.name ?? '', email: user?.email ?? '', role: user?.role ?? 'editor', is_active: user?.is_active ?? true });
    const field = (name, label, type = 'text') => (
        <div className="mb-3">
            <label className="form-label" htmlFor={`u-${name}`}>{label}</label>
            <input id={`u-${name}`} type={type} className={`form-control ${form.errors[name] ? 'is-invalid' : ''}`} value={form.data[name]} onChange={(e) => form.setData(name, e.target.value)} />
            {form.errors[name] ? <div className="invalid-feedback">{form.errors[name]}</div> : null}
        </div>
    );

    return (
        <AdminLayout title={user ? `Edit ${user.name}` : 'Invite administrator'} actions={<Link href="/admin/users" className="btn btn-sm btn-outline-secondary">← All administrators</Link>}>
            <div className="row g-4">
                <form className="col-lg-6 admin-card" onSubmit={(e) => {
                    e.preventDefault();
                    user ? form.put(`/admin/users/${user.id}`) : form.post('/admin/users');
                }} noValidate>
                    {field('name', 'Full name')}
                    {field('email', 'Work email', 'email')}
                    <div className="mb-3">
                        <label className="form-label" htmlFor="u-role">Role</label>
                        <select id="u-role" className={`form-select ${form.errors.role ? 'is-invalid' : ''}`} value={form.data.role} onChange={(e) => form.setData('role', e.target.value)}>
                            {roles.map((r) => <option key={r.value} value={r.value}>{r.label}</option>)}
                        </select>
                        <div className="form-text">Editors create and edit content and submit it for review. Publishers review, publish, schedule and unpublish. Super Admins also manage users and settings.</div>
                    </div>
                    {user ? (
                        <div className="form-check form-switch mb-3">
                            <input id="u-active" type="checkbox" role="switch" className="form-check-input" checked={form.data.is_active} onChange={(e) => form.setData('is_active', e.target.checked)} />
                            <label className="form-check-label" htmlFor="u-active">Account active (deactivating signs the user out)</label>
                        </div>
                    ) : (
                        <p className="small text-secondary">The new administrator receives an email link to set their own password. There is no public registration.</p>
                    )}
                    <button className="btn btn-primary" type="submit" disabled={form.processing}>{user ? 'Save' : 'Create and send invitation'}</button>
                </form>
                {user ? (
                    <div className="col-lg-5">
                        <div className="admin-card">
                            <h2 className="h6">Security</h2>
                            <p className="small">Two-factor authentication: {user.two_factor ? 'enabled' : 'not set up'}.</p>
                            <div className="d-flex flex-wrap gap-2">
                                <button type="button" className="btn btn-sm btn-outline-secondary" onClick={() => router.post(`/admin/users/${user.id}/send-reset`)}>Send password reset link</button>
                                {user.two_factor ? (
                                    <button type="button" className="btn btn-sm btn-outline-danger" onClick={() => window.confirm('Reset two-factor authentication for this user? They will need to enrol again.') && router.post(`/admin/users/${user.id}/reset-two-factor`)}>
                                        Reset two-factor
                                    </button>
                                ) : null}
                            </div>
                        </div>
                    </div>
                ) : null}
            </div>
        </AdminLayout>
    );
}
