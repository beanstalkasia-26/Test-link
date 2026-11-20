import { useState } from 'react';
import Icon from '../../../components/Icon';
import { Breadcrumbs, RichText } from '../../../components/site/Common';
import Layout from '../../../components/site/Layout';
import { JobApplyModal } from '../../../components/site/sections/ListingSections';
import SmartLink from '../../../components/SmartLink';

export default function JobShow({ job, breadcrumbs }) {
    const [applying, setApplying] = useState(null);
    return (
        <>
            <section className="nt-plain-hero">
                <div className="container">
                    {job.category_label ? <p className="nt-pill nt-hero-in">{job.category_label}</p> : null}
                    <h1 className="nt-hero-in" style={{ '--d': 1 }}>{job.title}</h1>
                    {job.department ? <p className="nt-plain-hero__body nt-hero-in" style={{ '--d': 2 }}>{job.department}</p> : null}
                    <ul className="nt-meta-list nt-meta-list--row nt-page-hero__meta">
                        {job.closes ? <li><Icon name="calendar" strokeWidth={1.6} />Apply by {job.closes}</li> : null}
                        {job.mode ? <li><Icon name="portfolio" strokeWidth={1.6} />{job.mode}</li> : null}
                    </ul>
                    {job.is_open ? (
                        <button type="button" className="nt-btn mt-3" onClick={() => setApplying(job)}>Apply Now</button>
                    ) : (
                        <p className="nt-tag mt-3">Applications for this position are closed</p>
                    )}
                </div>
            </section>
            <Breadcrumbs items={breadcrumbs} />
            <article className="nt-section">
                <div className="container" style={{ maxWidth: '52rem' }}>
                    {job.summary ? <p className="nt-lead">{job.summary}</p> : null}
                    <RichText html={job.description} />
                    <p className="mt-5"><SmartLink href="/careers#open-positions" className="nt-link-arrow">See all open positions</SmartLink></p>
                </div>
            </article>
            <JobApplyModal job={applying} onClose={() => setApplying(null)} />
        </>
    );
}

JobShow.layout = (page) => <Layout>{page}</Layout>;
