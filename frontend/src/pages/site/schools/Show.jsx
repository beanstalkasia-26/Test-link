import { FacultyGrid, ProgramCard, SimpleCard } from '../../../components/site/Cards';
import { blockSwitch, Breadcrumbs, RichText } from '../../../components/site/Common';
import Layout from '../../../components/site/Layout';
import { Media } from '../../../components/ResponsiveImage';

export default function SchoolShow({ unit, programs, centres, faculty, breadcrumbs, hidden }) {
    const show = blockSwitch(hidden);
    return (
        <>
            <section className="nt-page-hero on-dark">
                <div className="nt-page-hero__grid">
                    <div className="nt-page-hero__content">
                        <p className="nt-pill align-self-start">{unit.kind === 'Centre' ? 'MET Innovation Centre' : 'MET Innovation School'}</p>
                        <h1>{unit.title}</h1>
                        {unit.tagline ? <p className="nt-page-hero__body">{unit.tagline}</p> : null}
                    </div>
                    {unit.image && show('hero_image') ? (
                        <div className="nt-page-hero__media">
                            <Media image={unit.image} sizes="(min-width: 992px) 50vw, 100vw" priority />
                        </div>
                    ) : null}
                </div>
            </section>
            <Breadcrumbs items={breadcrumbs} />
            {show('about') ? (
                <section className="nt-section">
                    <div className="container" style={{ maxWidth: '52rem' }}>
                        <h2 className="nt-h2">About the {unit.kind === 'Centre' ? 'centre' : 'school'}</h2>
                        {unit.summary ? <p className="nt-lead">{unit.summary}</p> : null}
                        {unit.body ? <RichText html={unit.body} /> : !unit.summary ? <p className="text-muted">More information will be published soon.</p> : null}
                    </div>
                </section>
            ) : null}
            {programs.length && show('programs') ? (
                <section className="nt-section nt-divider">
                    <div className="container">
                        <h2 className="nt-h2">Programs</h2>
                        <div className="d-grid gap-4">
                            {programs.map((program) => (
                                <ProgramCard key={program.id} item={program} />
                            ))}
                        </div>
                    </div>
                </section>
            ) : null}
            {centres.length && show('centres') ? (
                <section className="nt-section nt-divider">
                    <div className="container">
                        <h2 className="nt-h2">Centres</h2>
                        <div className="nt-card-grid">
                            {centres.map((centre) => (
                                <SimpleCard key={centre.id} item={centre} />
                            ))}
                        </div>
                    </div>
                </section>
            ) : null}
            {faculty.length && show('faculty') ? (
                <section className="nt-section nt-faculty">
                    <div className="container">
                        <h2 className="nt-h2">Faculty</h2>
                        <FacultyGrid people={faculty} />
                    </div>
                </section>
            ) : null}
        </>
    );
}

SchoolShow.layout = (page) => <Layout>{page}</Layout>;
