import { router } from '../../router/navigation';
import { useState } from 'react';
import Layout from '../../components/site/Layout';
import SmartLink from '../../components/SmartLink';

export default function Search({ query, results }) {
    const [q, setQ] = useState(query ?? '');

    return (
        <>
            <header className="nt-page-hero nt-page-hero--plain on-dark">
                <div className="container">
                    <h1>Search</h1>
                </div>
            </header>
            <section className="nt-section">
                <div className="container" style={{ maxWidth: '52rem' }}>
                    <form role="search" className="d-flex gap-3 nt-form mb-5" onSubmit={(e) => {
                        e.preventDefault();
                        router.get('/search', { q }, { preserveState: true });
                    }}>
                        <label htmlFor="search-q" className="visually-hidden">
                            Search NAMTECH
                        </label>
                        <input id="search-q" type="search" className="form-control" value={q} onChange={(e) => setQ(e.target.value)} placeholder="Search programs, faculty, news…" minLength={2} required />
                        <button className="nt-btn" type="submit">
                            Search
                        </button>
                    </form>
                    {query ? (
                        <p role="status" className="text-muted">
                            {results.length} result{results.length === 1 ? '' : 's'} for “{query}”
                        </p>
                    ) : null}
                    <ol className="list-unstyled d-grid gap-4">
                        {results.map((result) => (
                            <li key={result.url} className="border-bottom pb-4">
                                <span className="nt-tag mb-2">{result.type}</span>
                                <h2 className="h5 mt-2 mb-1">
                                    <SmartLink href={result.url}>{result.title}</SmartLink>
                                </h2>
                                {result.excerpt ? <p className="mb-0 small text-muted">{result.excerpt}</p> : null}
                            </li>
                        ))}
                    </ol>
                    {query && !results.length ? <p>No results. Try a different word, or browse the <SmartLink href="/programs">programs</SmartLink>.</p> : null}
                </div>
            </section>
        </>
    );
}

Search.layout = (page) => <Layout>{page}</Layout>;
