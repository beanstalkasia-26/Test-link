import useForm from '../../../hooks/useForm';
import { useState } from 'react';
import AuthCard, { Input } from '../../../components/admin/AuthCard';

export default function TwoFactorChallenge() {
    const [recovery, setRecovery] = useState(false);
    const form = useForm({ code: '', recovery_code: '' });
    return (
        <AuthCard title="Two-factor authentication">
            <p className="small text-secondary">{recovery ? 'Enter one of your emergency recovery codes.' : 'Enter the 6-digit code from your authenticator app.'}</p>
            <form onSubmit={(e) => {
                e.preventDefault();
                form.post('/admin/two-factor-challenge');
            }} noValidate>
                {recovery ? (
                    <Input id="recovery_code" label="Recovery code" autoComplete="one-time-code" autoFocus value={form.data.recovery_code} onChange={(e) => form.setData('recovery_code', e.target.value)} error={form.errors.recovery_code} />
                ) : (
                    <Input id="code" label="Authentication code" inputMode="numeric" pattern="[0-9]*" autoComplete="one-time-code" autoFocus value={form.data.code} onChange={(e) => form.setData('code', e.target.value)} error={form.errors.code} />
                )}
                <button type="submit" className="btn btn-primary w-100" disabled={form.processing}>
                    Verify
                </button>
            </form>
            <button type="button" className="btn btn-link btn-sm w-100 mt-2" onClick={() => {
                setRecovery(!recovery);
                form.reset();
                form.clearErrors();
            }}>
                {recovery ? 'Use an authentication code' : 'Use a recovery code'}
            </button>
        </AuthCard>
    );
}
