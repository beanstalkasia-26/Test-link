import Link from '../../../router/Link';
import useForm from '../../../hooks/useForm';
import AdminLayout from '../../../components/admin/AdminLayout';

let counter = 0;
const newItem = () => ({ key: `n${++counter}`, label: '', page_id: null, url: '', opens_in_new_tab: false, is_visible: true, children: [] });

function ItemEditor({ item, path, pages, errors, onChange, onRemove, onMove, canMoveUp, canMoveDown, onIndent, onOutdent, canNest }) {
    const err = (field) => errors[`${path}.${field}`];
    const id = path.replace(/\./g, '-');
    return (
        <div className={`admin-card mb-2 p-3 ${item.is_visible ? '' : 'opacity-75'}`}>
            <div className="row g-2 align-items-end">
                <div className="col-md-3">
                    <label className="form-label small" htmlFor={`${id}-label`}>
                        Label
                    </label>
                    <input id={`${id}-label`} className={`form-control form-control-sm ${err('label') ? 'is-invalid' : ''}`} value={item.label} onChange={(e) => onChange({ ...item, label: e.target.value })} />
                    {err('label') ? <div className="invalid-feedback">{err('label')}</div> : null}
                </div>
                <div className="col-md-3">
                    <label className="form-label small" htmlFor={`${id}-page`}>
                        Link to page
                    </label>
                    <select id={`${id}-page`} className="form-select form-select-sm" value={item.page_id ?? ''} onChange={(e) => onChange({ ...item, page_id: e.target.value ? Number(e.target.value) : null })}>
                        <option value="">— Custom link —</option>
                        {pages.map((p) => (
                            <option key={p.value} value={p.value}>
                                {p.label}
                            </option>
                        ))}
                    </select>
                </div>
                <div className="col-md-3">
                    <label className="form-label small" htmlFor={`${id}-url`}>
                        Custom link
                    </label>
                    <input id={`${id}-url`} className={`form-control form-control-sm ${err('url') ? 'is-invalid' : ''}`} disabled={Boolean(item.page_id)} placeholder="/programs or https://…" value={item.page_id ? '' : item.url ?? ''} onChange={(e) => onChange({ ...item, url: e.target.value })} />
                    {err('url') ? <div className="invalid-feedback">{err('url')}</div> : null}
                </div>
                <div className="col-md-3 d-flex flex-wrap gap-1">
                    <div className="form-check form-check-inline small">
                        <input id={`${id}-tab`} type="checkbox" className="form-check-input" checked={item.opens_in_new_tab} onChange={(e) => onChange({ ...item, opens_in_new_tab: e.target.checked })} />
                        <label htmlFor={`${id}-tab`} className="form-check-label">
                            New tab
                        </label>
                    </div>
                    <div className="form-check form-check-inline small">
                        <input id={`${id}-vis`} type="checkbox" className="form-check-input" checked={item.is_visible} onChange={(e) => onChange({ ...item, is_visible: e.target.checked })} />
                        <label htmlFor={`${id}-vis`} className="form-check-label">
                            Visible
                        </label>
                    </div>
                </div>
            </div>
            <div className="btn-group btn-group-sm mt-2">
                <button type="button" className="btn btn-outline-secondary" disabled={!canMoveUp} onClick={() => onMove(-1)} aria-label="Move up">
                    ↑
                </button>
                <button type="button" className="btn btn-outline-secondary" disabled={!canMoveDown} onClick={() => onMove(1)} aria-label="Move down">
                    ↓
                </button>
                {onIndent ? (
                    <button type="button" className="btn btn-outline-secondary" disabled={!canNest} onClick={onIndent}>
                        Nest under item above →
                    </button>
                ) : null}
                {onOutdent ? (
                    <button type="button" className="btn btn-outline-secondary" onClick={onOutdent}>
                        ← Move up a level
                    </button>
                ) : null}
                <button type="button" className="btn btn-outline-danger" onClick={onRemove}>
                    Remove
                </button>
            </div>
        </div>
    );
}

const getList = (items, path) => path.reduce((list, i) => list[i].children, items);
const setList = (items, path, next) => {
    if (!path.length) return next;
    const [head, ...rest] = path;
    return items.map((item, i) => (i === head ? { ...item, children: setList(item.children ?? [], rest, next) } : item));
};
const move = (arr, i, d) => {
    const next = [...arr];
    const [x] = next.splice(i, 1);
    next.splice(i + d, 0, x);
    return next;
};
const depthOf = (item) => 1 + Math.max(0, ...(item.children ?? []).map(depthOf));

function Tree({ root, set, path, pages, errors, maxDepth }) {
    const list = getList(root, path);
    const level = path.length + 1;
    const update = (next) => set(setList(root, path, next));

    return (
        <ul className="menu-tree">
            {list.map((item, i) => {
                const prev = list[i - 1];
                const canIndent = i > 0 && level + depthOf(item) - 1 < maxDepth;
                return (
                    <li key={item.key}>
                        <ItemEditor
                            item={item}
                            path={['items', ...path.flatMap((p) => [p, 'children']), i].join('.')}
                            pages={pages}
                            errors={errors}
                            onChange={(v) => update(list.map((x, j) => (j === i ? v : x)))}
                            onRemove={() => update(list.filter((_, j) => j !== i))}
                            onMove={(d) => update(move(list, i, d))}
                            canMoveUp={i > 0}
                            canMoveDown={i < list.length - 1}
                            onIndent={maxDepth > 1 ? () => update(list.filter((_, j) => j !== i).map((x) => (x === prev ? { ...x, children: [...(x.children ?? []), item] } : x))) : null}
                            canNest={canIndent}
                            onOutdent={
                                path.length
                                    ? () => {
                                          const parentPath = path.slice(0, -1);
                                          const parentIndex = path[path.length - 1];
                                          const withoutItem = setList(root, path, list.filter((_, j) => j !== i));
                                          const parentList = getList(withoutItem, parentPath);
                                          const next = [...parentList];
                                          next.splice(parentIndex + 1, 0, item);
                                          set(setList(withoutItem, parentPath, next));
                                      }
                                    : null
                            }
                        />
                        {item.children?.length ? <Tree root={root} set={set} path={[...path, i]} pages={pages} errors={errors} maxDepth={maxDepth} /> : null}
                    </li>
                );
            })}
        </ul>
    );
}

export default function MenuEdit({ menu, items, pages, maxDepth }) {
    const form = useForm({ items });

    return (
        <AdminLayout title={`Menu: ${menu.name}`} actions={<Link href="/admin/menus" className="btn btn-sm btn-outline-secondary">← All menus</Link>}>
            <p className="text-secondary small">
                {menu.location}. Links to CMS pages update automatically when a page address changes and hide when the page is unpublished.
                {maxDepth === 3 ? ' Header menus support three levels: a top item, column headings (shown on the left of the large dropdown) and their links.' : null}
            </p>
            <form onSubmit={(e) => {
                e.preventDefault();
                form.put(`/admin/menus/${menu.id}`, { preserveScroll: true });
            }}>
                <Tree root={form.data.items} set={(next) => form.setData('items', next)} path={[]} pages={pages} errors={form.errors} maxDepth={maxDepth} />
                <div className="d-flex gap-2 mt-3">
                    <button type="button" className="btn btn-outline-primary" onClick={() => form.setData('items', [...form.data.items, newItem()])}>
                        + Add item
                    </button>
                    <button type="submit" className="btn btn-primary" disabled={form.processing}>
                        Save and publish menu
                    </button>
                    {form.isDirty ? <span className="small text-warning-emphasis align-self-center">Unsaved changes</span> : null}
                </div>
            </form>
        </AdminLayout>
    );
}
