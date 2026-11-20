import { ProgramCard } from '../../../components/site/Cards';
import Layout from '../../../components/site/Layout';
import ListingShell from '../../../components/site/ListingShell';
import usePage from '../../../hooks/usePage';

export default function SchoolsIndex({ title, sections, breadcrumbs, schools, centres }) {
    const settings = usePage().props.site?.settings ?? {};
    return (
        <ListingShell title={title} sections={sections} breadcrumbs={breadcrumbs}>
            <h2 className="nt-h2">{settings.schools_listing_heading || 'MET Innovation Schools'}</h2>
            <div className="d-grid gap-4 mb-5">
                {schools.map((school) => (
                    <ProgramCard key={school.id} item={school} />
                ))}
            </div>
            {centres.length ? (
                <>
                    <h2 className="nt-h2 mt-5">{settings.centres_listing_heading || 'MET Innovation Centres'}</h2>
                    <div className="d-grid gap-4">
                        {centres.map((centre) => (
                            <ProgramCard key={centre.id} item={centre} />
                        ))}
                    </div>
                </>
            ) : null}
        </ListingShell>
    );
}

SchoolsIndex.layout = (page) => <Layout>{page}</Layout>;
