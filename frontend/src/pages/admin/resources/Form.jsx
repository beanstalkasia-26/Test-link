import Link from '../../../router/Link';
import { router } from '../../../router/navigation';
import useForm from '../../../hooks/useForm';
import { useEffect, useMemo, useRef, useState } from 'react';
import AdminLayout, { StatusBadge } from '../../../components/admin/AdminLayout';
import { FieldList } from '../../../components/admin/Fields';
import SectionBuilder from '../../../components/admin/SectionBuilder';

function WorkflowPanel({ resource, record, can, dirty }) {
    const [when, setWhen] = useState('');
    const base = `/admin/content/${resource.slug}/${record.id}`;
    const post = (action, data = {}, confirmText) => {
        if (confirmText && !window.confirm(confirmText)) return;
        router.post(`${base}/${action}`, data, { preserveScroll: true });
    };
    const isLive = record.status === 'published';

    return (
        <div className="admin-card mb-3">
            <h2 className="h6">Publishing</h2>
            <p className="mb-2">
                <StatusBadge status={record.status} label={record.status_label} review={record.review_state} pending={record.has_pending_changes} />
            </p>
            {record.published_at ? <p className="small text-secondary mb-1">First published {record.published_at}</p> : null}
            {record.scheduled_for ? <p className="small text-warning-emphasis mb-1">Scheduled for {record.scheduled_for}</p> : null}
            {record.has_pending_changes ? <p className="small mb-2">This item has saved changes that are not live yet.</p> : null}
            {dirty ? <div className="alert alert-warning py-2 small">You have unsaved edits. Save before publishing or previewing.</div> : null}
            <div className="d-grid gap-2">
                {record.preview_url ? (
                    <a className={`btn btn-outline-secondary btn-sm ${dirty ? 'disabled' : ''}`} href={record.preview_url} target="_blank" rel="noopener">
                        Preview {record.has_pending_changes || !isLive ? 'draft' : ''}
                    </a>
                ) : null}
                {record.public_url ? (
                    <a className="btn btn-outline-secondary btn-sm" href={record.public_url} target="_blank" rel="noopener">
                        View live page ↗
                    </a>
                ) : null}
                {can.submit && !can.publish && record.review_state !== 'pending' && (!isLive || record.has_pending_changes) ? (
                    <button type="button" className="btn btn-primary btn-sm" disabled={dirty} onClick={() => post('submit')}>
                        Submit for review
                    </button>
                ) : null}
                {can.publish && (!isLive || record.has_pending_changes) ? (
                    <button type="button" className="btn btn-success btn-sm" disabled={dirty} onClick={() => post('publish', {}, isLive ? 'Publish the pending changes to the live site?' : 'Publish this item to the live site now?')}>
                        {isLive ? 'Publish changes' : 'Publish now'}
                    </button>
                ) : null}
                {can.publish && (!isLive || record.has_pending_changes) ? (
                    <div className="input-group input-group-sm">
                        <label className="visually-hidden" htmlFor="schedule-at">
                            Publish at
                        </label>
                        <input id="schedule-at" type="datetime-local" className="form-control" value={when} onChange={(e) => setWhen(e.target.value)} />
                        <button type="button" className="btn btn-outline-primary" disabled={!when || dirty} onClick={() => post('schedule', { scheduled_for: when })}>
                            Schedule
                        </button>
                    </div>
                ) : null}
                {record.has_pending_changes && can.edit ? (
                    <button type="button" className="btn btn-outline-warning btn-sm" onClick={() => post('discard', {}, 'Discard all unpublished changes? The live version stays as it is.')}>
                        Discard pending changes
                    </button>
                ) : null}
                {can.publish && isLive ? (
                    <button type="button" className="btn btn-outline-danger btn-sm" onClick={() => post('unpublish', {}, 'Remove this item from the live site?')}>
                        Unpublish
                    </button>
                ) : null}
            </div>
            {!can.publish ? <p className="small text-secondary mt-2 mb-0">Publishers review and publish submitted content.</p> : null}
        </div>
    );
}

function Revisions({ resource, record, revisions, dirty }) {
    if (!revisions.length) return null;
    return (
        <div className="admin-card">
            <h2 className="h6">Revision history</h2>
            <ol className="list-unstyled small mb-0" style={{ maxHeight: '22rem', overflowY: 'auto' }}>
                {revisions.map((rev) => (
                    <li key={rev.id} className="border-bottom py-2 d-flex justify-content-between gap-2">
                        <span>
                            <strong className="d-block">{rev.kind === 'working' ? 'Pending changes' : rev.note}</strong>
                            <span className="text-secondary">
                                {rev.created_at}
                                {rev.user ? ` · ${rev.user}` : ''}
                            </span>
                        </span>
                        {rev.kind === 'snapshot' ? (
                            <button
                                type="button"
                                className="btn btn-link btn-sm p-0"
                                disabled={dirty}
                                onClick={() => window.confirm('Restore this revision? It will be saved as a draft for review.') && router.post(`/admin/content/${resource.slug}/${record.id}/revisions/${rev.id}/restore`, {}, { preserveScroll: true })}
                            >
                                Restore
                            </button>
                        ) : null}
                    </li>
                ))}
            </ol>
        </div>
    );
}

export default function ResourceForm({ resource, fields, sectionTypes, record, values, media: initialMedia, revisions, can }) {
    const form = useForm(values);
    const [media, setMedia] = useState(initialMedia ?? {});
    const lastValues = useRef(JSON.stringify(values));

    useEffect(() => {
        const next = JSON.stringify(values);
        if (next !== lastValues.current) {
            lastValues.current = next;
            form.load(values);
            setMedia((current) => ({ ...current, ...(initialMedia ?? {}) }));
        }
    }, [values]);
    const groups = useMemo(() => {
        const result = {};
        fields.forEach((f) => (result[f.group ?? 'Content'] ??= []).push(f));
        return result;
    }, [fields]);
    const groupNames = Object.keys(groups);
    const [tab, setTab] = useState(values?.sections?.length ? 'Sections' : groupNames[0]);
    const errorGroups = new Set(Object.keys(form.errors).map((key) => fields.find((f) => key === f.name || key.startsWith(f.name + '.'))?.group ?? 'Content'));
    const onMediaLoaded = (item) => setMedia((current) => ({ ...current, [item.id]: item }));
    const base = `/admin/content/${resource.slug}`;

    const submit = (e) => {
        e.preventDefault();
        const options = {
            preserveScroll: true,
            onError: (errors) => {
                const first = Object.keys(errors)[0];
                const group = fields.find((f) => first === f.name || first?.startsWith(f.name + '.'))?.group ?? 'Content';
                setTab(group);
            },
            onSuccess: (page) => {
                const saved = page?.props?.values;
                if (saved) {
                    lastValues.current = JSON.stringify(saved);
                    form.load(saved);
                } else {
                    form.setDefaults();
                }
            },
        };
        record ? form.put(`${base}/${record.id}`, options) : form.post(base, options);
    };

    return (
        <AdminLayout
            title={record ? `Edit ${resource.singular.toLowerCase()}: ${record.title}` : `New ${resource.singular.toLowerCase()}`}
            actions={
                <Link href={base} className="btn btn-outline-secondary btn-sm">
                    ← All {resource.label.toLowerCase()}
                </Link>
            }
        >
            <form onSubmit={submit} noValidate>
                <div className="row g-4">
                    <div className={resource.publishable && record ? 'col-xl-9' : 'col-12'}>
                        {Object.keys(form.errors).length ? (
                            <div className="alert alert-danger" role="alert">
                                Please correct the highlighted fields ({Object.keys(form.errors).length}).
                            </div>
                        ) : null}
                        {groupNames.length > 1 ? (
                            <ul className="nav form-tabs" role="tablist">
                                {groupNames.map((name) => (
                                    <li className="nav-item" key={name} role="presentation">
                                        <button type="button" role="tab" aria-selected={tab === name} className={`nav-link ${tab === name ? 'active' : ''}`} onClick={() => setTab(name)}>
                                            {name}
                                            {errorGroups.has(name) ? <span className="badge text-bg-danger ms-1">!</span> : null}
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        ) : null}
                        {groupNames.map((name) => (
                            <div key={name} hidden={tab !== name} role="tabpanel" className={name === 'Sections' ? '' : 'admin-card'}>
                                {name === 'Sections' ? (
                                    <SectionBuilder sections={form.data.sections} onChange={(v) => form.setData('sections', v)} types={sectionTypes} errors={form.errors} media={media} onMediaLoaded={onMediaLoaded} />
                                ) : (
                                    <FieldList fields={groups[name]} values={form.data} onChange={(field, v) => form.setData(field, v)} errors={form.errors} media={media} onMediaLoaded={onMediaLoaded} />
                                )}
                            </div>
                        ))}
                        <div className="d-flex flex-wrap gap-2 align-items-center mt-3 position-sticky bottom-0 bg-body py-3 border-top">
                            <button type="submit" className="btn btn-primary" disabled={form.processing || !can.edit}>
                                {form.processing ? 'Saving…' : record?.status === 'published' ? 'Save changes as draft' : 'Save'}
                            </button>
                            {form.isDirty ? <span className="small text-warning-emphasis">Unsaved changes</span> : <span className="small text-secondary">All changes saved</span>}
                            {record && can.delete ? (
                                <button
                                    type="button"
                                    className="btn btn-link text-danger ms-auto"
                                    onClick={() => window.confirm(`Delete this ${resource.singular.toLowerCase()} permanently?`) && router.delete(`${base}/${record.id}`)}
                                >
                                    Delete
                                </button>
                            ) : null}
                        </div>
                    </div>
                    {resource.publishable && record ? (
                        <div className="col-xl-3">
                            <div className="workflow-panel">
                                <WorkflowPanel resource={resource} record={record} can={can} dirty={form.isDirty} />
                                <Revisions resource={resource} record={record} revisions={revisions} dirty={form.isDirty} />
                            </div>
                        </div>
                    ) : null}
                </div>
            </form>
        </AdminLayout>
    );
}
