import { Breadcrumbs } from '../../components/site/Common';
import Layout from '../../components/site/Layout';
import { GalleryGrid, TourEmbed } from '../../components/site/sections/MediaEmbeds';

export default function Gallery({ gallery, breadcrumbs }) {
    return (
        <>
            <header className="nt-page-hero nt-page-hero--plain on-dark">
                <div className="container">
                    <h1>{gallery.title}</h1>
                    {gallery.description ? <p className="nt-page-hero__body mt-3 mb-0">{gallery.description}</p> : null}
                </div>
            </header>
            <Breadcrumbs items={breadcrumbs} />
            <section className="nt-section">
                <div className="container">
                    {gallery.virtual_tour_url ? (
                        <div className="mb-5">
                            <TourEmbed gallery={gallery} />
                        </div>
                    ) : null}
                    <GalleryGrid items={gallery.items} />
                </div>
            </section>
        </>
    );
}

Gallery.layout = (page) => <Layout>{page}</Layout>;
