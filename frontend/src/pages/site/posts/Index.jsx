import { router } from '../../../router/navigation';
import { PostCard } from '../../../components/site/Cards';
import { Pagination } from '../../../components/site/Common';
import Layout from '../../../components/site/Layout';
import ListingShell from '../../../components/site/ListingShell';

export default function PostsIndex({ title, sections, breadcrumbs, posts, categories, filters }) {
    const base = breadcrumbs?.[0]?.url ?? '/news';
    return (
        <ListingShell title={title} sections={sections} breadcrumbs={breadcrumbs}>
            {Object.keys(categories).length > 1 ? (
                <form className="nt-filters nt-form" aria-label="Filter by category" onSubmit={(e) => e.preventDefault()}>
                    <div>
                        <label className="form-label" htmlFor="post-category">
                            Category
                        </label>
                        <select id="post-category" className="form-select" value={filters.category ?? ''} onChange={(e) => router.get(base, { category: e.target.value || undefined }, { preserveScroll: true, replace: true })}>
                            <option value="">All categories</option>
                            {Object.entries(categories).map(([value, label]) => (
                                <option key={value} value={value}>
                                    {label}
                                </option>
                            ))}
                        </select>
                    </div>
                </form>
            ) : null}
            {posts.data.length ? (
                <div className="nt-card-grid">
                    {posts.data.map((post) => (
                        <PostCard key={post.id} post={post} headingLevel={2} />
                    ))}
                </div>
            ) : (
                <p>There are no posts here yet.</p>
            )}
            <Pagination meta={posts} />
        </ListingShell>
    );
}

PostsIndex.layout = (page) => <Layout>{page}</Layout>;
