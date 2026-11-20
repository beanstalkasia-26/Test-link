import Head from '../Head';
import usePage from '../../hooks/usePage';

export default function AuthCard({ title, children, status }) {
    const { flash } = usePage().props;
    return (
        <div className="auth-wrap">
            <Head title={`${title} · NAMTECH CMS`}>
                <meta name="robots" content="noindex, nofollow" />
            </Head>
            <main className="auth-card">
                <div className="text-center mb-4">
                    <img src="/images/namtech-logo.webp" alt="NAMTECH – Institute of Manufacturing Innovation" width="700" height="192" className="logo" />
                </div>
                <div className="admin-card p-4 shadow-sm">
                    <h1 className="h4 mb-3">{title}</h1>
                    {status || flash?.status ? <div className="alert alert-success py-2 small">{status || flash.status}</div> : null}
                    {flash?.error ? <div className="alert alert-danger py-2 small">{flash.error}</div> : null}
                    {children}
                </div>
                <p className="text-center small text-secondary mt-3">Authorised NAMTECH staff only. Activity is logged.</p>
            </main>
        </div>
    );
}

export function Input({ id, label, error, ...props }) {
    return (
        <div className="mb-3">
            <label htmlFor={id} className="form-label">
                {label}
            </label>
            <input id={id} className={`form-control ${error ? 'is-invalid' : ''}`} aria-invalid={error ? true : undefined} aria-describedby={error ? `${id}-error` : undefined} {...props} />
            {error ? (
                <div id={`${id}-error`} className="invalid-feedback">
                    {error}
                </div>
            ) : null}
        </div>
    );
}
