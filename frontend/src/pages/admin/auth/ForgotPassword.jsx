import Link from '../../../router/Link';
import useForm from '../../../hooks/useForm';
import AuthCard, { Input } from '../../../components/admin/AuthCard';

export default function ForgotPassword({ status }) {
    const form = useForm({ email: '' });
    return (
        <AuthCard title="Reset your password" status={status}>
            <p className="small text-secondary">Enter your work email and we will send a link to choose a new password.</p>
            <form onSubmit={(e) => {
                e.preventDefault();
                form.post('/admin/forgot-password');
            }} noValidate>
                <Input id="email" label="Email" type="email" autoComplete="username" autoFocus required value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} error={form.errors.email} />
                <button type="submit" className="btn btn-primary w-100" disabled={form.processing}>
                    Email reset link
                </button>
            </form>
            <p className="mt-3 mb-0 small text-center">
                <Link href="/admin/login">Back to sign in</Link>
            </p>
        </AuthCard>
    );
}
