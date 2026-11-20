import { useState } from 'react';
import Icon from '../../../components/Icon';
import { Pagination } from '../../../components/site/Common';
import { CheckboxGroup, FilterTabs, SidebarToggle, visit } from '../../../components/site/Filters';
import Layout from '../../../components/site/Layout';
import ListingShell from '../../../components/site/ListingShell';
import { Media } from '../../../components/ResponsiveImage';
import SmartLink from '../../../components/SmartLink';

export default function EventsIndex({ title, sections, breadcrumbs, categories, category, events, units, filters }) {
    const [range, setRange] = useState({ from: filters.from ?? '', to: filters.to ?? '' });
    const apply = (params) => visit('/events', { category, ...filters, ...params, page: undefined });

    return (
        <ListingShell title={title} sections={sections} breadcrumbs={breadcrumbs}>
            <FilterTabs label="Event type" items={Object.entries(categories)} active={category} href={(key) => `/events?category=${key}`} />
            <div className="row g-5 mt-1">
                <aside className="col-lg-3" aria-label="Filters">
                    <SidebarToggle>
                        <CheckboxGroup legend="Schools & Centres" name="unit" options={Object.entries(units)} selected={filters.units} onChange={(v) => apply({ units: v })} />
                        <fieldset className="nt-checkgroup">
                            <legend>Dates</legend>
                            <label className="form-label small" htmlFor="ev-from">From</label>
                            <input id="ev-from" type="date" className="form-control mb-2" value={range.from} onChange={(e) => setRange({ ...range, from: e.target.value })} />
                            <label className="form-label small" htmlFor="ev-to">To</label>
                            <input id="ev-to" type="date" className="form-control mb-3" value={range.to} onChange={(e) => setRange({ ...range, to: e.target.value })} />
                            <button type="button" className="nt-btn nt-btn--outline w-100" onClick={() => apply({ from: range.from || undefined, to: range.to || undefined })}>Apply dates</button>
                        </fieldset>
                    </SidebarToggle>
                </aside>
                <div className="col-lg-9">
                    <p className="visually-hidden" role="status">{events.total} events</p>
                    {events.data.length ? (
                        <ul className="nt-event-rows list-unstyled">
                            {events.data.map((e) => (
                                <li key={e.id} className="nt-event-row">
                                    <Media image={e.image} sizes="(min-width: 992px) 18rem, 90vw" alt="" className="nt-event-row__media" />
                                    <div className="nt-event-row__body">
                                        <h2><SmartLink href={e.url} className="stretched-link">{e.title}</SmartLink></h2>
                                        {e.summary ? <p>{e.summary}</p> : null}
                                        <ul className="nt-meta-list nt-meta-list--row">
                                            {e.date ? <li><Icon name="calendar" strokeWidth={1.6} /><time dateTime={e.datetime}>{e.date}</time></li> : null}
                                            {e.time ? <li><Icon name="clock" strokeWidth={1.6} />{e.time}</li> : null}
                                            {e.venue ? <li><Icon name="pin" strokeWidth={1.6} />{e.venue}</li> : null}
                                        </ul>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    ) : (
                        <p className="nt-empty">There are no {categories[category]?.toLowerCase() ?? 'events'} scheduled for these filters.</p>
                    )}
                    <Pagination meta={events} />
                </div>
            </div>
        </ListingShell>
    );
}

EventsIndex.layout = (page) => <Layout>{page}</Layout>;
