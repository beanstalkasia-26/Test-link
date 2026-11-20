import Link from '../../../router/Link';
import useForm from '../../../hooks/useForm';
import AuthCard, { Input } from '../../../components/admin/AuthCard';

export default function Login({ status }) {
    const form = useForm({ email: '', password: '', remember: false });

    return (
        <AuthCard title="Sign in to the CMS" status={status}>
            <form onSubmit={(e) => {
                e.preventDefault();
                form.post('/admin/login', { onFinish: () => form.reset('password') });
            }} noValidate>
                <Input id="email" label="Email" type="email" autoComplete="username" autoFocus required value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} error={form.errors.email} />
                <Input id="password" label="Password" type="password" autoComplete="current-password" required value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} error={form.errors.password} />
                <div className="d-flex justify-content-between align-items-center mb-4">
                    <div className="form-check">
                        <input id="remember" type="checkbox" className="form-check-input" checked={form.data.remember} onChange={(e) => form.setData('remember', e.target.checked)} />
                        <label htmlFor="remember" className="form-check-label small">
                            Keep me signed in
                        </label>
                    </div>
                    <Link href="/admin/forgot-password" className="small">
                        Forgot password?
                    </Link>
                </div>
                <button type="submit" className="btn btn-primary w-100" disabled={form.processing}>
                    {form.processing ? 'Signing in…' : 'Sign in'}
                </button>
            </form>
        </AuthCard>
    );
}
