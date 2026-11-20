import useForm from '../../hooks/useForm';
import usePage from '../../hooks/usePage';
import { useEffect, useId, useRef } from 'react';

function Field({ id, label, error, required, children, hint }) {
    return (
        <div className="mb-3">
            <label htmlFor={id} className="form-label">
                {label}
                {required ? <span aria-hidden="true"> *</span> : <span className="text-muted fw-normal"> (optional)</span>}
            </label>
            {children}
            {hint ? (
                <div id={`${id}-hint`} className="form-text">
                    {hint}
                </div>
            ) : null}
            {error ? (
                <div id={`${id}-error`} className="invalid-feedback">
                    {error}
                </div>
            ) : null}
        </div>
    );
}

export default function EnquiryForm({ type = 'contact', programs = [], defaultProgramId = null }) {
    const { flash, site } = usePage().props;
    const uid = useId();
    const started = useRef(0);
    const summary = useRef(null);
    const form = useForm({
        type,
        program_id: defaultProgramId ?? '',
        name: '',
        email: '',
        phone: '',
        organisation: '',
        subject: '',
        message: '',
        consent: false,
        website: '',
        elapsed: 0,
    });

    useEffect(() => {
        started.current = Date.now();
    }, []);

    const success = flash?.form?.form === 'enquiry' ? flash.form.message : null;
    const errors = form.errors;
    const errorList = Object.entries(errors);
    const id = (name) => `${uid}-${name}`;
    const describedBy = (name, hint = false) => [hint ? `${id(name)}-hint` : null, errors[name] ? `${id(name)}-error` : null].filter(Boolean).join(' ') || undefined;

    const validate = () => {
        const local = {};
        if (!form.data.name.trim()) local.name = 'Please enter your name.';
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.data.email)) local.email = 'Please enter a valid email address.';
        if (form.data.phone && !/^[0-9+()\-\s]{6,40}$/.test(form.data.phone)) local.phone = 'Please enter a valid phone number.';
        if (type === 'program' && !form.data.program_id) local.program_id = 'Please choose the program you are interested in.';
        if (form.data.message.trim().length < 10) local.message = 'Please tell us a little more (at least 10 characters).';
        if (!form.data.consent) local.consent = 'Please confirm that we may contact you about your enquiry.';
        return local;
    };

    const submit = (event) => {
        event.preventDefault();
        const local = validate();
        form.clearErrors();
        if (Object.keys(local).length) {
            form.setError(local);
            requestAnimationFrame(() => summary.current?.focus());
            return;
        }
        form.transform((data) => ({ ...data, elapsed: Date.now() - started.current }));
        form.post('/enquiries', {
            preserveScroll: true,
            onSuccess: () => form.reset(),
            onError: () => requestAnimationFrame(() => summary.current?.focus()),
        });
    };

    if (success) {
        return (
            <div className="nt-alert nt-alert--success" role="status" tabIndex={-1} ref={(el) => el?.focus()}>
                <strong className="d-block mb-1">Enquiry sent</strong>
                {success}
            </div>
        );
    }

    return (
        <form className="nt-form position-relative" onSubmit={submit} noValidate>
            {errorList.length ? (
                <div className="nt-alert nt-alert--error" role="alert" tabIndex={-1} ref={summary}>
                    <strong className="d-block mb-1">Please correct the following:</strong>
                    <ul className="mb-0 ps-3">
                        {errorList.map(([field, message]) => (
                            <li key={field}>
                                <a href={`#${id(field)}`} className="text-reset">
                                    {message}
                                </a>
                            </li>
                        ))}
                    </ul>
                </div>
            ) : null}

            {type === 'program' ? (
                <Field id={id('program_id')} label="Program of interest" error={errors.program_id} required>
                    <select id={id('program_id')} className={`form-select ${errors.program_id ? 'is-invalid' : ''}`} value={form.data.program_id} onChange={(e) => form.setData('program_id', e.target.value)} aria-describedby={describedBy('program_id')} aria-invalid={errors.program_id ? true : undefined} required>
                        <option value="">Select a program</option>
                        {programs.map((program) => (
                            <option key={program.id} value={program.id}>
                                {program.title}
                            </option>
                        ))}
                    </select>
                </Field>
            ) : null}

            <div className="row">
                <div className="col-md-6">
                    <Field id={id('name')} label="Full name" error={errors.name} required>
                        <input id={id('name')} className={`form-control ${errors.name ? 'is-invalid' : ''}`} autoComplete="name" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} aria-describedby={describedBy('name')} aria-invalid={errors.name ? true : undefined} required maxLength={120} />
                    </Field>
                </div>
                <div className="col-md-6">
                    <Field id={id('email')} label="Email" error={errors.email} required>
                        <input id={id('email')} type="email" className={`form-control ${errors.email ? 'is-invalid' : ''}`} autoComplete="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} aria-describedby={describedBy('email')} aria-invalid={errors.email ? true : undefined} required maxLength={255} />
                    </Field>
                </div>
                <div className="col-md-6">
                    <Field id={id('phone')} label="Phone" error={errors.phone}>
                        <input id={id('phone')} type="tel" className={`form-control ${errors.phone ? 'is-invalid' : ''}`} autoComplete="tel" value={form.data.phone} onChange={(e) => form.setData('phone', e.target.value)} aria-describedby={describedBy('phone')} aria-invalid={errors.phone ? true : undefined} maxLength={40} />
                    </Field>
                </div>
                <div className="col-md-6">
                    {type === 'contact' ? (
                        <Field id={id('organisation')} label="Organisation" error={errors.organisation}>
                            <input id={id('organisation')} className="form-control" autoComplete="organization" value={form.data.organisation} onChange={(e) => form.setData('organisation', e.target.value)} maxLength={160} />
                        </Field>
                    ) : null}
                </div>
            </div>
            {type === 'contact' ? (
                <Field id={id('subject')} label="Subject" error={errors.subject}>
                    <input id={id('subject')} className="form-control" value={form.data.subject} onChange={(e) => form.setData('subject', e.target.value)} maxLength={160} />
                </Field>
            ) : null}
            <Field id={id('message')} label="Message" error={errors.message} required>
                <textarea id={id('message')} rows={5} className={`form-control ${errors.message ? 'is-invalid' : ''}`} value={form.data.message} onChange={(e) => form.setData('message', e.target.value)} aria-describedby={describedBy('message')} aria-invalid={errors.message ? true : undefined} required minLength={10} maxLength={5000} />
            </Field>
            <div className="form-check mb-4">
                <input id={id('consent')} type="checkbox" className={`form-check-input ${errors.consent ? 'is-invalid' : ''}`} checked={form.data.consent} onChange={(e) => form.setData('consent', e.target.checked)} aria-describedby={describedBy('consent')} aria-invalid={errors.consent ? true : undefined} required />
                <label htmlFor={id('consent')} className="form-check-label">
                    {site?.settings?.enquiry_consent || 'I agree that NAMTECH may store my details and contact me about this enquiry.'} <span aria-hidden="true">*</span>
                </label>
                {errors.consent ? (
                    <div id={`${id('consent')}-error`} className="invalid-feedback">
                        {errors.consent}
                    </div>
                ) : null}
            </div>
            <div className="nt-hp" aria-hidden="true">
                <label htmlFor={id('website')}>Leave this field empty</label>
                <input id={id('website')} tabIndex={-1} autoComplete="off" value={form.data.website} onChange={(e) => form.setData('website', e.target.value)} />
            </div>
            <button type="submit" className="nt-btn" disabled={form.processing}>
                {form.processing ? 'Sending…' : 'Send enquiry'}
            </button>
            <p className="small text-muted mt-3 mb-0">Fields marked * are required.</p>
        </form>
    );
}
