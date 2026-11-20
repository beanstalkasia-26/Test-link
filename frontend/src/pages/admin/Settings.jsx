import useForm from '../../hooks/useForm';
import { useMemo, useState } from 'react';
import AdminLayout from '../../components/admin/AdminLayout';
import { FieldList } from '../../components/admin/Fields';

export default function Settings({ fields, values, media: initialMedia }) {
    const form = useForm(values);
    const [media, setMedia] = useState(initialMedia ?? {});
    const groups = useMemo(() => {
        const r = {};
        fields.forEach((f) => (r[f.group] ??= []).push(f));
        return r;
    }, [fields]);

    return (
        <AdminLayout title="Global settings">
            <form onSubmit={(e) => {
                e.preventDefault();
                form.put('/admin/settings', { preserveScroll: true, onSuccess: () => form.setDefaults() });
            }} noValidate>
                {Object.entries(groups).map(([group, groupFields]) => (
                    <fieldset className="admin-card mb-4" key={group}>
                        <legend className="h6">{group}</legend>
                        <FieldList fields={groupFields} values={form.data} onChange={(name, v) => form.setData(name, v)} errors={form.errors} media={media} onMediaLoaded={(m) => setMedia({ ...media, [m.id]: m })} />
                    </fieldset>
                ))}
                <button type="submit" className="btn btn-primary" disabled={form.processing}>Save settings</button>
            </form>
        </AdminLayout>
    );
}
