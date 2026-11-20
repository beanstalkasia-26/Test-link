import { Breadcrumbs, RichText } from '../../../components/site/Common';
import Layout from '../../../components/site/Layout';
import { PageHero } from '../../../components/site/sections/ContentSections';
import SmartLink from '../../../components/SmartLink';

export default function PublicationShow({ publication, breadcrumbs }) {
    return (
        <>
            <PageHero first data={{ eyebrow: publication.topic || publication.school || 'Publication', title: publication.title, body: publication.authors, image: publication.image }} />
            <Breadcrumbs items={breadcrumbs} />
            <article className="nt-section">
                <div className="container" style={{ maxWidth: '52rem' }}>
                    <dl className="nt-facts-inline">
                        {publication.publisher ? <div><dt>Published in</dt><dd>{publication.publisher}</dd></div> : null}
                        {publication.date ? <div><dt>Date</dt><dd>{publication.date}</dd></div> : null}
                        {publication.school ? <div><dt>School</dt><dd>{publication.school}</dd></div> : null}
                    </dl>
                    {publication.summary ? <p className="nt-lead">{publication.summary}</p> : null}
                    <RichText html={publication.body} />
                    {publication.external_url ? <SmartLink href={publication.external_url} className="nt-btn mt-4" newTab>Read the paper</SmartLink> : null}
                </div>
            </article>
        </>
    );
}

PublicationShow.layout = (page) => <Layout>{page}</Layout>;
