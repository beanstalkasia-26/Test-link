import useForm from '../../../hooks/useForm';
import AuthCard, { Input } from '../../../components/admin/AuthCard';

export default function ResetPassword({ email, token }) {
    const form = useForm({ token, email: email ?? '', password: '', password_confirmation: '' });
    return (
        <AuthCard title="Choose a new password">
            <form onSubmit={(e) => {
                e.preventDefault();
                form.post('/admin/reset-password', { onFinish: () => form.reset('password', 'password_confirmation') });
            }} noValidate>
                <Input id="email" label="Email" type="email" autoComplete="username" required value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} error={form.errors.email} />
                <Input id="password" label="New password" type="password" autoComplete="new-password" required value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} error={form.errors.password} />
                <Input id="password_confirmation" label="Confirm new password" type="password" autoComplete="new-password" required value={form.data.password_confirmation} onChange={(e) => form.setData('password_confirmation', e.target.value)} />
                <p className="small text-secondary">Use at least 12 characters with upper and lower case letters, a number and a symbol.</p>
                <button type="submit" className="btn btn-primary w-100" disabled={form.processing}>
                    Save password
                </button>
            </form>
        </AuthCard>
    );
}
