import { PublicationCard } from '../../../components/site/Cards';
import { Pagination } from '../../../components/site/Common';
import { visit } from '../../../components/site/Filters';
import Layout from '../../../components/site/Layout';
import ListingShell from '../../../components/site/ListingShell';

export default function PublicationsIndex({ title, sections, breadcrumbs, publications, schools, topics, filters }) {
    const apply = (params) => visit('/research-and-consultancy/publications', { ...filters, ...params, page: undefined });
    return (
        <ListingShell title={title} sections={sections} breadcrumbs={breadcrumbs}>
            <form className="nt-filters nt-form" aria-label="Filter publications" onSubmit={(e) => e.preventDefault()}>
                <div>
                    <label className="visually-hidden" htmlFor="pub-school">School</label>
                    <select id="pub-school" className="form-select" value={filters.school ?? ''} onChange={(e) => apply({ school: e.target.value || undefined })}>
                        <option value="">All schools</option>
                        {Object.entries(schools).map(([v, l]) => <option key={v} value={v}>{l}</option>)}
                    </select>
                </div>
                {topics.length ? (
                    <div>
                        <label className="visually-hidden" htmlFor="pub-topic">Topic</label>
                        <select id="pub-topic" className="form-select" value={filters.topic ?? ''} onChange={(e) => apply({ topic: e.target.value || undefined })}>
                            <option value="">All topics</option>
                            {topics.map((t) => <option key={t} value={t}>{t}</option>)}
                        </select>
                    </div>
                ) : null}
                <div>
                    <label className="visually-hidden" htmlFor="pub-sort">Sort</label>
                    <select id="pub-sort" className="form-select" value={filters.sort} onChange={(e) => apply({ sort: e.target.value === 'recent' ? undefined : e.target.value })}>
                        <option value="recent">Most recent</option>
                        <option value="oldest">Oldest first</option>
                    </select>
                </div>
            </form>
            <p className="visually-hidden" role="status">{publications.total} publications</p>
            {publications.data.length ? (
                <div className="nt-card-grid nt-card-grid--3 nt-stagger">
                    {publications.data.map((p) => <PublicationCard key={p.id} item={p} headingLevel={2} />)}
                </div>
            ) : (
                <p className="nt-empty">No publications match these filters.</p>
            )}
            <Pagination meta={publications} />
        </ListingShell>
    );
}

PublicationsIndex.layout = (page) => <Layout>{page}</Layout>;
