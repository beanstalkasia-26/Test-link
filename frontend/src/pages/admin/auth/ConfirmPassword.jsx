import useForm from '../../../hooks/useForm';
import AuthCard, { Input } from '../../../components/admin/AuthCard';

export default function ConfirmPassword() {
    const form = useForm({ password: '' });
    return (
        <AuthCard title="Confirm your password">
            <p className="small text-secondary">This is a secure area. Please confirm your password to continue.</p>
            <form onSubmit={(e) => {
                e.preventDefault();
                form.post('/admin/user/confirm-password', { onFinish: () => form.reset() });
            }} noValidate>
                <Input id="password" label="Password" type="password" autoComplete="current-password" autoFocus required value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} error={form.errors.password} />
                <button type="submit" className="btn btn-primary w-100" disabled={form.processing}>
                    Confirm
                </button>
            </form>
        </AuthCard>
    );
}
