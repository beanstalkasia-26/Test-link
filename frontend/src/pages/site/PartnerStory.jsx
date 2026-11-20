import { Breadcrumbs, RichText } from '../../components/site/Common';
import Layout from '../../components/site/Layout';
import { Media } from '../../components/ResponsiveImage';
import SmartLink from '../../components/SmartLink';

export default function PartnerStory({ partner, breadcrumbs }) {
    return (
        <>
            <section className="nt-page-hero on-dark">
                <div className="nt-page-hero__grid">
                    <div className="nt-page-hero__content">
                        <p className="nt-pill align-self-start">{partner.category || 'Partnership'}</p>
                        <h1>{partner.name}</h1>
                        {partner.summary ? <p className="nt-page-hero__body">{partner.summary}</p> : null}
                        {partner.website_url ? (
                            <SmartLink href={partner.website_url} newTab className="nt-btn nt-btn--light align-self-start mt-3">
                                Visit {partner.name}
                            </SmartLink>
                        ) : null}
                    </div>
                    {partner.image ? (
                        <div className="nt-page-hero__media">
                            <Media image={partner.image} sizes="(min-width: 992px) 50vw, 100vw" priority />
                        </div>
                    ) : null}
                </div>
            </section>
            <Breadcrumbs items={breadcrumbs} />
            <article className="nt-section">
                <div className="container" style={{ maxWidth: '48rem' }}>
                    <RichText html={partner.story} />
                </div>
            </article>
        </>
    );
}

PartnerStory.layout = (page) => <Layout>{page}</Layout>;
