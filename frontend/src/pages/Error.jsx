import Head from '../components/Head';
import Layout from '../components/site/Layout';
import SmartLink from '../components/SmartLink';

const messages = {
    403: ['Access denied', 'You do not have permission to view this page.'],
    404: ['Page not found', 'The page you are looking for may have moved or no longer exists.'],
    429: ['Too many requests', 'Please wait a moment and try again.'],
    500: ['Something went wrong', 'An unexpected error occurred. Our team has been notified. Please try again shortly.'],
    503: ['Down for maintenance', 'We are making improvements and will be back shortly.'],
};

function Content({ status, admin }) {
    const [title, body] = messages[status] ?? messages[500];
    return (
        <section className="nt-error container">
            <Head title={`${title} | NAMTECH`}>
                <meta name="robots" content="noindex" />
            </Head>
            <p className="nt-error__code">{status}</p>
            <h1 className="nt-h2">{title}</h1>
            <p className="mx-auto" style={{ maxWidth: '32rem' }}>
                {body}
            </p>
            <div className="d-flex flex-wrap gap-3 justify-content-center mt-4">
                <SmartLink href={admin ? '/admin' : '/'} className="nt-btn">
                    {admin ? 'Back to dashboard' : 'Go to the homepage'}
                </SmartLink>
                {!admin ? (
                    <SmartLink href="/search" className="nt-btn nt-btn--outline">
                        Search the site
                    </SmartLink>
                ) : null}
            </div>
        </section>
    );
}

export default function Error(props) {
    return props.admin ? <Content {...props} /> : <Layout>{<Content {...props} />}</Layout>;
}
