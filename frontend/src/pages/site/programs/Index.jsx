import { router } from '../../../router/navigation';
import { ProgramCard } from '../../../components/site/Cards';
import Layout from '../../../components/site/Layout';
import ListingShell from '../../../components/site/ListingShell';

export default function ProgramsIndex({ title, sections, breadcrumbs, programs, levels, schools, filters }) {
    const update = (key, value) => router.get('/programs', { ...filters, [key]: value || undefined }, { preserveScroll: true, preserveState: true, replace: true });

    if (sections?.some((section) => section.type === 'program_levels')) {
        return <ListingShell title={title} sections={sections} breadcrumbs={breadcrumbs} />;
    }

    return (
        <ListingShell title={title} sections={sections} breadcrumbs={breadcrumbs}>
            <form className="nt-filters nt-form" role="search" aria-label="Filter programs" onSubmit={(e) => e.preventDefault()}>
                <div>
                    <label className="form-label" htmlFor="filter-level">
                        Level
                    </label>
                    <select id="filter-level" className="form-select" value={filters.level ?? ''} onChange={(e) => update('level', e.target.value)}>
                        <option value="">All levels</option>
                        {Object.entries(levels).map(([value, label]) => (
                            <option key={value} value={value}>
                                {label}
                            </option>
                        ))}
                    </select>
                </div>
                <div>
                    <label className="form-label" htmlFor="filter-school">
                        School
                    </label>
                    <select id="filter-school" className="form-select" value={filters.school ?? ''} onChange={(e) => update('school', e.target.value)}>
                        <option value="">All schools</option>
                        {Object.entries(schools).map(([value, label]) => (
                            <option key={value} value={value}>
                                {label}
                            </option>
                        ))}
                    </select>
                </div>
            </form>
            <p className="visually-hidden" role="status">
                {programs.length} programs found
            </p>
            {programs.length ? (
                <div className="d-grid gap-4">
                    {programs.map((program) => (
                        <ProgramCard key={program.id} item={program} headingLevel={2} />
                    ))}
                </div>
            ) : (
                <p>No programs match these filters.</p>
            )}
        </ListingShell>
    );
}

ProgramsIndex.layout = (page) => <Layout>{page}</Layout>;
