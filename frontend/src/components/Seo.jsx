import Head from './Head';

export default function Seo({ seo }) {
    if (!seo) return null;

    return (
        <Head>
            <title>{seo.title}</title>
            {seo.description ? <meta head-key="description" name="description" content={seo.description} /> : null}
            {seo.canonical ? <link head-key="canonical" rel="canonical" href={seo.canonical} /> : null}
            <meta head-key="robots" name="robots" content={seo.noindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large'} />
            <meta head-key="og:title" property="og:title" content={seo.title} />
            <meta head-key="og:type" property="og:type" content={seo.type ?? 'website'} />
            <meta head-key="og:site_name" property="og:site_name" content={seo.siteName ?? 'NAMTECH'} />
            <meta head-key="og:locale" property="og:locale" content="en_IN" />
            {seo.description ? <meta head-key="og:description" property="og:description" content={seo.description} /> : null}
            {seo.canonical ? <meta head-key="og:url" property="og:url" content={seo.canonical} /> : null}
            {seo.image ? <meta head-key="og:image" property="og:image" content={seo.image} /> : null}
            {seo.article?.published_time ? <meta head-key="article:published_time" property="article:published_time" content={seo.article.published_time} /> : null}
            {seo.article?.modified_time ? <meta head-key="article:modified_time" property="article:modified_time" content={seo.article.modified_time} /> : null}
            <meta head-key="twitter:card" name="twitter:card" content={seo.image ? 'summary_large_image' : 'summary'} />
            <meta head-key="twitter:title" name="twitter:title" content={seo.title} />
            {seo.description ? <meta head-key="twitter:description" name="twitter:description" content={seo.description} /> : null}
            {seo.image ? <meta head-key="twitter:image" name="twitter:image" content={seo.image} /> : null}
            {(seo.jsonLd ?? []).map((data, i) => (
                <script key={i} head-key={`jsonld-${i}`} type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(data).replace(/</g, '\\u003c') }} />
            ))}
        </Head>
    );
}
