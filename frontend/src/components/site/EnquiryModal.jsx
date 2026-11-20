import useForm from '../../hooks/useForm';
import usePage from '../../hooks/usePage';
import { useEffect, useRef, useState } from 'react';
import Icon from '../Icon';

const VARIANTS = {
    partnership: { title: 'Start a Partner Conversation', type: 'partnership', message: true, submit: 'Submit' },
    'case-study': { title: 'Unlock Full Case Study', type: 'case_study', message: false, submit: 'Submit' },
    brochure: { title: 'Download Brochure', type: 'brochure', message: false, brochure: true, submit: 'Submit' },
};

const PARTNERSHIP_TYPES = ['Strategic & Founding Partnership', 'Schools, Centers, CoEs & Lab Partnerships', 'Sponsored Research', 'Academic Partnership', 'Other Partnership'];
const COUNTRIES = [
    ['+91', 'India'], ['+1', 'United States / Canada'], ['+44', 'United Kingdom'], ['+49', 'Germany'],
    ['+61', 'Australia'], ['+65', 'Singapore'], ['+971', 'United Arab Emirates'], ['+81', 'Japan'],
];

export default function EnquiryModal() {
    const dialog = useRef(null);
    const [variant, setVariant] = useState(null);
    const [completed, setCompleted] = useState(false);
    const started = useRef(0);
    const { flash, site, program, programs } = usePage().props;
    const form = useForm({ type: 'partnership', first_name: '', last_name: '', email: '', country: '+91', phone: '', organisation: '', subject: '', message: '', grade: '', city: '', program_id: '', consent: false, website: '' });

    useEffect(() => {
        const onClick = (e) => {
            const link = e.target.closest?.('a[href^="#enquiry:"]');
            if (!link) return;
            e.preventDefault();
            const key = link.getAttribute('href').split(':')[1];
            const v = VARIANTS[key] ?? VARIANTS.partnership;
            setVariant(v);
            setCompleted(false);
            form.clearErrors();
            form.setData((d) => ({ ...d, type: v.type, country: v.type === 'partnership' ? '' : '+91', program_id: v.brochure ? String(program?.id ?? '') : '' }));
            started.current = Date.now();
            requestAnimationFrame(() => dialog.current?.showModal());
        };
        document.addEventListener('click', onClick);
        return () => document.removeEventListener('click', onClick);
    }, []);

    const partnership = variant?.type === 'partnership';
    const success = completed && flash?.form?.form === 'enquiry' && variant;

    const submit = (e) => {
        e.preventDefault();
        form.transform((d) => ({
            type: d.type,
            name: `${d.first_name} ${d.last_name}`.trim(),
            email: d.email,
            phone: d.phone ? `${d.country} ${d.phone}`.trim() : '',
            organisation: d.organisation,
            subject: partnership ? d.subject : variant?.title,
            message: variant?.message ? d.message : undefined,
            ...(variant?.brochure ? { grade: d.grade, city: d.city, program_id: d.program_id || null } : {}),
            consent: d.consent,
            website: d.website,
            elapsed: Date.now() - started.current,
        }));
        form.post('/enquiries', {
            preserveScroll: true,
            onSuccess: () => {
                setCompleted(true);
                form.reset();
            },
        });
    };

    const err = (name) => form.errors[name];

    return (
        <dialog ref={dialog} className={`nt-modal${partnership ? ' nt-modal--partnership' : ''}`} aria-labelledby="enquiry-modal-title" onClose={() => setVariant(null)}>
            <div className="nt-modal__head">
                <h2 id="enquiry-modal-title">{variant?.title ?? 'Enquiry'}</h2>
                <button type="button" className="nt-modal__close" aria-label="Close" onClick={() => dialog.current.close()}>
                    <Icon name="close" />
                </button>
            </div>
            <div className="nt-modal__body">
                {success ? (
                    <div className="nt-modal__done" role="status">
                        <Icon name={partnership ? 'check' : 'sparkle'} />
                        <h3>{partnership ? 'Thanks for reaching out!' : 'Thank you'}</h3>
                        <p>{partnership ? 'We appreciate your patience and will respond to your inquiry within approximately three business days.' : flash.form.message}</p>
                        {variant.brochure && flash.form.download ? (
                            <a className="nt-btn" href={flash.form.download} target="_blank" rel="noopener" download>
                                Download brochure
                            </a>
                        ) : null}
                    </div>
                ) : (
                    <form onSubmit={submit} noValidate={!partnership} className="nt-form nt-modal__form">
                        <div className="nt-modal__grid">
                            <label className="visually-hidden" htmlFor="em-first">First name</label>
                            <input id="em-first" className={`form-control ${err('name') ? 'is-invalid' : ''}`} placeholder="*First Name" autoComplete="given-name" value={form.data.first_name} onChange={(e) => form.setData('first_name', e.target.value)} required />
                            <label className="visually-hidden" htmlFor="em-last">Last name</label>
                            <input id="em-last" className="form-control" placeholder="*Last Name" autoComplete="family-name" value={form.data.last_name} onChange={(e) => form.setData('last_name', e.target.value)} required />
                        </div>
                        {err('name') ? <div className="invalid-feedback d-block">{err('name')}</div> : null}
                        <label className="visually-hidden" htmlFor="em-email">Email</label>
                        <input id="em-email" type="email" className={`form-control ${err('email') ? 'is-invalid' : ''}`} placeholder="*Email" autoComplete="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} required />
                        {err('email') ? <div className="invalid-feedback d-block">{err('email')}</div> : null}
                        <div className="nt-modal__phone">
                            <label className="visually-hidden" htmlFor="em-country">Country code</label>
                            <select id="em-country" className="form-select" value={form.data.country} onChange={(e) => form.setData('country', e.target.value)}>
                                {partnership ? <option value="">Country</option> : null}
                                {COUNTRIES.map(([code, name]) => <option key={code} value={code}>{partnership ? `${name} (${code})` : code}</option>)}
                            </select>
                            <label className="visually-hidden" htmlFor="em-phone">Phone number</label>
                            <input id="em-phone" type="tel" className={`form-control ${err('phone') ? 'is-invalid' : ''}`} placeholder="Phone no." autoComplete="tel-national" value={form.data.phone} onChange={(e) => form.setData('phone', e.target.value)} />
                        </div>
                        {err('phone') ? <div className="invalid-feedback d-block">{err('phone')}</div> : null}
                        {variant?.brochure ? (
                            <>
                                <label className="visually-hidden" htmlFor="em-grade">Student grade or class</label>
                                <input id="em-grade" className={`form-control ${err('grade') ? 'is-invalid' : ''}`} placeholder="*Student Grade/Class" value={form.data.grade} onChange={(e) => form.setData('grade', e.target.value)} required />
                                {err('grade') ? <div className="invalid-feedback d-block">{err('grade')}</div> : null}
                                <label className="visually-hidden" htmlFor="em-city">City</label>
                                <input id="em-city" className={`form-control ${err('city') ? 'is-invalid' : ''}`} placeholder="*City" autoComplete="address-level2" value={form.data.city} onChange={(e) => form.setData('city', e.target.value)} required />
                                {err('city') ? <div className="invalid-feedback d-block">{err('city')}</div> : null}
                                <label className="visually-hidden" htmlFor="em-program">Program interested in</label>
                                <select id="em-program" className={`form-select ${err('program_id') ? 'is-invalid' : ''}`} value={form.data.program_id} onChange={(e) => form.setData('program_id', e.target.value)}>
                                    <option value="">Program Interested In</option>
                                    {(programs ?? []).map((p) => <option key={p.id} value={p.id}>{p.title}</option>)}
                                </select>
                                {err('program_id') ? <div className="invalid-feedback d-block">{err('program_id')}</div> : null}
                            </>
                        ) : (
                            <>
                                <label className="visually-hidden" htmlFor="em-org">Company or foundation name</label>
                                <input id="em-org" className="form-control" placeholder="*Company or Foundation Name" autoComplete="organization" value={form.data.organisation} onChange={(e) => form.setData('organisation', e.target.value)} required={partnership} />
                            </>
                        )}
                        {partnership ? (
                            <>
                                <label className="visually-hidden" htmlFor="em-partnership-type">Partnership type</label>
                                <select id="em-partnership-type" className={`form-select ${err('subject') ? 'is-invalid' : ''}`} value={form.data.subject} onChange={(e) => form.setData('subject', e.target.value)} required>
                                    <option value="" disabled>*I’m interested in this Partnership type</option>
                                    {PARTNERSHIP_TYPES.map((type) => <option key={type} value={type}>{type}</option>)}
                                </select>
                                {err('subject') ? <div className="invalid-feedback d-block">{err('subject')}</div> : null}
                            </>
                        ) : null}
                        {variant?.message ? (
                            <>
                                <label className="visually-hidden" htmlFor="em-msg">Details of your enquiry</label>
                                <textarea id="em-msg" rows={4} className={`form-control ${err('message') ? 'is-invalid' : ''}`} placeholder="Tell us how you’re interested in partnering with NAMTECH" value={form.data.message} onChange={(e) => form.setData('message', e.target.value)} required={partnership} />
                                {err('message') ? <div className="invalid-feedback d-block">{err('message')}</div> : null}
                            </>
                        ) : null}
                        <div className="form-check">
                            <input id="em-consent" type="checkbox" className={`form-check-input ${err('consent') ? 'is-invalid' : ''}`} checked={form.data.consent} onChange={(e) => form.setData('consent', e.target.checked)} required={partnership} />
                            <label htmlFor="em-consent" className="form-check-label small">{site?.settings?.enquiry_consent || 'I agree that NAMTECH may contact me about this enquiry.'}</label>
                            {err('consent') ? <div className="invalid-feedback d-block">{err('consent')}</div> : null}
                        </div>
                        <div className="nt-hp" aria-hidden="true">
                            <label htmlFor="em-website">Leave empty</label>
                            <input id="em-website" tabIndex={-1} autoComplete="off" value={form.data.website} onChange={(e) => form.setData('website', e.target.value)} />
                        </div>
                        <button type="submit" className="nt-btn w-100" disabled={form.processing}>{form.processing ? 'Sending…' : variant?.submit ?? 'Submit'}</button>
                    </form>
                )}
            </div>
        </dialog>
    );
}
