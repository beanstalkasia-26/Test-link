import Layout from '../../components/site/Layout';
import { Breadcrumbs } from '../../components/site/Common';
import SectionRenderer from '../../components/site/sections/SectionRenderer';

export default function Page({ page, sections }) {
    const isHome = page.template === 'home';
    const isPartnerships = page.path?.split('/')[0] === 'partnerships';
    const firstIsHero = sections?.[0]?.type === 'page_hero' || sections?.[0]?.type === 'home_hero';
    const bandAfterHero = ['logo_strip', 'feature_list'].includes(sections?.[1]?.type) && sections[1]?.data?.layout === 'band';
    const heroCount = firstIsHero && bandAfterHero ? 2 : 1;

    return (
        <div className={`nt-page nt-page--${page.template || 'default'}${isPartnerships ? ' nt-page--partnerships' : ''}`}>
            {!firstIsHero ? (
                <header className={`nt-page-hero nt-page-hero--plain on-dark`}>
                    <div className="container">
                        <h1>{page.title}</h1>
                    </div>
                </header>
            ) : null}
            {!isHome && firstIsHero ? (
                <>
                    <SectionRenderer sections={sections.slice(0, heroCount)} page={page} />
                    <Breadcrumbs items={page.breadcrumbs} />
                    <SectionRenderer sections={sections.slice(heroCount)} page={page} />
                </>
            ) : (
                <>
                    {!isHome ? <Breadcrumbs items={page.breadcrumbs} /> : null}
                    <SectionRenderer sections={sections} page={page} />
                </>
            )}
        </div>
    );
}

Page.layout = (page) => <Layout>{page}</Layout>;
