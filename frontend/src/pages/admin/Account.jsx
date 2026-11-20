import { router } from '../../router/navigation';
import useForm from '../../hooks/useForm';
import { useEffect, useState } from 'react';
import AdminLayout from '../../components/admin/AdminLayout';
import { http } from '../../lib/http';

function PasswordForm() {
    const form = useForm({ current_password: '', password: '', password_confirmation: '' });
    const input = (name, label, autoComplete) => (
        <div className="mb-3">
            <label className="form-label" htmlFor={`pw-${name}`}>{label}</label>
            <input id={`pw-${name}`} type="password" autoComplete={autoComplete} className={`form-control ${form.errors[name] ? 'is-invalid' : ''}`} value={form.data[name]} onChange={(e) => form.setData(name, e.target.value)} />
            {form.errors[name] ? <div className="invalid-feedback">{form.errors[name]}</div> : null}
        </div>
    );
    return (
        <form className="admin-card" onSubmit={(e) => {
            e.preventDefault();
            form.put('/admin/user/password', { errorBag: 'updatePassword', preserveScroll: true, onSuccess: () => form.reset() });
        }}>
            <h2 className="h6">Change password</h2>
            {input('current_password', 'Current password', 'current-password')}
            {input('password', 'New password', 'new-password')}
            {input('password_confirmation', 'Confirm new password', 'new-password')}
            <button className="btn btn-primary" type="submit" disabled={form.processing}>Update password</button>
        </form>
    );
}

function TwoFactor({ twoFactor }) {
    const [qr, setQr] = useState(null);
    const [secret, setSecret] = useState(null);
    const [codes, setCodes] = useState(null);
    const form = useForm({ code: '' });

    useEffect(() => {
        if (twoFactor.enabled && !twoFactor.confirmed) {
            http('/admin/user/two-factor-qr-code').then((d) => setQr(d.svg)).catch(() => router.visit('/admin/user/confirm-password'));
            http('/admin/user/two-factor-secret-key').then((d) => setSecret(d.secretKey)).catch(() => {});
        }
    }, [twoFactor.enabled, twoFactor.confirmed]);

    const showCodes = () => http('/admin/user/two-factor-recovery-codes').then(setCodes).catch(() => router.visit('/admin/user/confirm-password'));

    return (
        <div className="admin-card">
            <h2 className="h6">Two-factor authentication</h2>
            {twoFactor.required && !twoFactor.confirmed ? <div className="alert alert-warning small">Your role requires two-factor authentication. You cannot use the CMS until it is set up.</div> : null}
            {!twoFactor.enabled ? (
                <>
                    <p className="small">Protect your account with a 6-digit code from an authenticator app (such as Microsoft Authenticator or Google Authenticator) in addition to your password.</p>
                    <button type="button" className="btn btn-primary" onClick={() => router.post('/admin/user/two-factor-authentication', {}, { preserveScroll: true })}>Set up two-factor authentication</button>
                </>
            ) : !twoFactor.confirmed ? (
                <>
                    <p className="small">Scan this QR code with your authenticator app, then enter the 6-digit code to finish.</p>
                    {qr ? <div className="qr mb-2" dangerouslySetInnerHTML={{ __html: qr }} /> : <p className="small text-secondary">Loading QR code…</p>}
                    {secret ? <p className="small">Or enter this key manually: <code>{secret}</code></p> : null}
                    <form className="d-flex gap-2 align-items-start" onSubmit={(e) => {
                        e.preventDefault();
                        form.post('/admin/user/confirmed-two-factor-authentication', { errorBag: 'confirmTwoFactorAuthentication', preserveScroll: true, onSuccess: showCodes });
                    }}>
                        <div>
                            <label className="visually-hidden" htmlFor="tf-code">Authentication code</label>
                            <input id="tf-code" inputMode="numeric" autoComplete="one-time-code" className={`form-control ${form.errors.code ? 'is-invalid' : ''}`} placeholder="123456" value={form.data.code} onChange={(e) => form.setData('code', e.target.value)} />
                            {form.errors.code ? <div className="invalid-feedback">{form.errors.code}</div> : null}
                        </div>
                        <button className="btn btn-primary" type="submit">Confirm</button>
                    </form>
                </>
            ) : (
                <>
                    <p className="small"><span className="status-badge status-badge--published">Enabled</span> Your account is protected with two-factor authentication.</p>
                    <div className="d-flex flex-wrap gap-2">
                        <button type="button" className="btn btn-sm btn-outline-secondary" onClick={showCodes}>Show recovery codes</button>
                        <button type="button" className="btn btn-sm btn-outline-secondary" onClick={() => http('/admin/user/two-factor-recovery-codes', { method: 'POST' }).then(showCodes).catch(() => router.visit('/admin/user/confirm-password'))}>Regenerate recovery codes</button>
                        {!twoFactor.required ? (
                            <button type="button" className="btn btn-sm btn-outline-danger" onClick={() => window.confirm('Turn off two-factor authentication?') && router.delete('/admin/user/two-factor-authentication', { preserveScroll: true })}>Disable</button>
                        ) : null}
                    </div>
                </>
            )}
            {codes ? (
                <div className="mt-3">
                    <p className="small mb-1">Store these recovery codes somewhere safe. Each can be used once if you lose your device.</p>
                    <pre className="bg-light p-2 rounded small mb-0">{codes.join('\n')}</pre>
                </div>
            ) : null}
        </div>
    );
}

export default function Account({ twoFactor }) {
    return (
        <AdminLayout title="Account & security">
            <div className="row g-4">
                <div className="col-lg-6"><TwoFactor twoFactor={twoFactor} /></div>
                <div className="col-lg-6"><PasswordForm /></div>
            </div>
        </AdminLayout>
    );
}
