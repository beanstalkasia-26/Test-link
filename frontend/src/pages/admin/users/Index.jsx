import Link from '../../../router/Link';
import AdminLayout from '../../../components/admin/AdminLayout';

export default function UsersIndex({ users }) {
    return (
        <AdminLayout title="Administrators" actions={<Link href="/admin/users/create" className="btn btn-primary">+ Invite administrator</Link>}>
            <div className="table-responsive">
                <table className="table table-admin">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Role</th>
                            <th scope="col">Two-factor</th>
                            <th scope="col">Last sign-in</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {users.map((u) => (
                            <tr key={u.id}>
                                <td>
                                    <Link href={`/admin/users/${u.id}/edit`} className="fw-medium">{u.name}</Link>
                                    <div className="small text-secondary">{u.email}</div>
                                </td>
                                <td>{u.role}</td>
                                <td>{u.two_factor ? <span className="status-badge status-badge--published">Enabled</span> : <span className="status-badge status-badge--unpublished">Not set up</span>}</td>
                                <td className="small">{u.last_login_at}</td>
                                <td>{u.is_active ? 'Active' : 'Deactivated'}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
