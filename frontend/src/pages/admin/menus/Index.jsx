import Link from '../../../router/Link';
import AdminLayout from '../../../components/admin/AdminLayout';

export default function MenusIndex({ menus }) {
    return (
        <AdminLayout title="Menus">
            <div className="table-responsive">
                <table className="table table-admin">
                    <thead>
                        <tr>
                            <th scope="col">Menu</th>
                            <th scope="col">Location</th>
                            <th scope="col">Items</th>
                            <th scope="col" />
                        </tr>
                    </thead>
                    <tbody>
                        {menus.map((menu) => (
                            <tr key={menu.id}>
                                <td className="fw-medium">{menu.name}</td>
                                <td className="small text-secondary">{menu.location}</td>
                                <td>{menu.items_count}</td>
                                <td className="text-end">
                                    <Link className="btn btn-sm btn-outline-primary" href={`/admin/menus/${menu.id}`}>
                                        Edit
                                    </Link>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
