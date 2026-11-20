import useForm from '../../hooks/useForm';
import usePage from '../../hooks/usePage';
import { useEffect, useRef } from 'react';

export default function NewsletterForm({ heading, consent }) {
    const { flash } = usePage().props;
    const started = useRef(0);
    const form = useForm({ newsletter_email: '', newsletter_consent: false, newsletter_website: '', newsletter_elapsed: 0 });

    useEffect(() => {
        started.current = Date.now();
    }, []);

    const success = flash?.form?.form === 'newsletter' ? flash.form.message : null;

    const submit = (event) => {
        event.preventDefault();
        form.transform((data) => ({ ...data, newsletter_elapsed: Date.now() - started.current }));
        form.post('/newsletter', { preserveScroll: true, onSuccess: () => form.reset() });
    };

    return (
        <div className="nt-footer__newsletter">
            <h2 id="newsletter-heading">{heading}</h2>
            {success ? (
                <p className="nt-alert nt-alert--success" role="status">
                    {success}
                </p>
            ) : null}
            <form onSubmit={submit} noValidate aria-labelledby="newsletter-heading" className="nt-form">
                <div className="d-flex gap-3 flex-wrap flex-sm-nowrap align-items-start">
                    <div className="flex-grow-1">
                        <label htmlFor="newsletter-email" className="visually-hidden">
                            Email address
                        </label>
                        <input
                            id="newsletter-email"
                            type="email"
                            className={`form-control ${form.errors.newsletter_email ? 'is-invalid' : ''}`}
                            placeholder="Enter your Email ID"
                            autoComplete="email"
                            required
                            value={form.data.newsletter_email}
                            onChange={(e) => form.setData('newsletter_email', e.target.value)}
                            aria-invalid={form.errors.newsletter_email ? true : undefined}
                            aria-describedby={form.errors.newsletter_email ? 'newsletter-email-error' : undefined}
                        />
                        {form.errors.newsletter_email ? (
                            <div id="newsletter-email-error" className="invalid-feedback">
                                {form.errors.newsletter_email}
                            </div>
                        ) : null}
                    </div>
                    <button type="submit" className="nt-btn nt-btn--light" disabled={form.processing}>
                        {form.processing ? 'Subscribing…' : 'Subscribe'}
                    </button>
                </div>
                <div className="form-check mt-3">
                    <input
                        id="newsletter-consent"
                        type="checkbox"
                        className={`form-check-input ${form.errors.newsletter_consent ? 'is-invalid' : ''}`}
                        checked={form.data.newsletter_consent}
                        onChange={(e) => form.setData('newsletter_consent', e.target.checked)}
                        aria-describedby={form.errors.newsletter_consent ? 'newsletter-consent-error' : undefined}
                        required
                    />
                    <label htmlFor="newsletter-consent" className="form-check-label">
                        {consent || 'I agree to receive email updates from NAMTECH.'}
                    </label>
                    {form.errors.newsletter_consent ? (
                        <div id="newsletter-consent-error" className="invalid-feedback">
                            {form.errors.newsletter_consent}
                        </div>
                    ) : null}
                </div>
                <div className="nt-hp" aria-hidden="true">
                    <label htmlFor="newsletter-website">Website</label>
                    <input id="newsletter-website" tabIndex={-1} autoComplete="off" value={form.data.newsletter_website} onChange={(e) => form.setData('newsletter_website', e.target.value)} />
                </div>
            </form>
        </div>
    );
}
