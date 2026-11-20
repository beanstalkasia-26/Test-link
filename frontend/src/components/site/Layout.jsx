import { router } from '../../router/navigation';
import usePage from '../../hooks/usePage';
import { useEffect } from 'react';
import { enterPage, initCountUp, initReveal } from '../../lib/motion';
import EnquiryModal from './EnquiryModal';
import Icon from '../Icon';
import Seo from '../Seo';
import SmartLink from '../SmartLink';
import Footer from './Footer';
import Header from './Header';

export default function Layout({ children, seo }) {
    const page = usePage();
    const { site, preview } = page.props;
    const settings = site?.settings ?? {};

    useEffect(() => {
        let cleanups = [];
        const run = () => {
            cleanups.forEach((c) => c());
            requestAnimationFrame(() => (cleanups = [initReveal(), initCountUp()]));
        };
        run();
        let pathname = window.location.pathname;
        const off = router.on('navigate', (event) => {
            const next = new URL(event.detail.page.url, window.location.origin).pathname;
            if (next !== pathname) enterPage();
            pathname = next;
            run();
        });
        return () => {
            off();
            cleanups.forEach((c) => c());
        };
    }, []);

    return (
        <>
            <Seo seo={seo ?? page.props.seo} />
            <a href="#main" className="skip-link">
                Skip to main content
            </a>
            {preview ? (
                <div className="nt-preview-bar" role="status">
                    Preview – this shows unpublished changes and is only visible to signed-in editors.
                </div>
            ) : null}
            <Header />
            <main id="main" tabIndex={-1}>
                {children}
            </main>
            <Footer />
            <EnquiryModal />
            {settings.call_url && settings.show_floating_contact !== false ? (
                <SmartLink href={settings.call_url} className="nt-floating-contact" aria-label="Contact NAMTECH">
                    <Icon name="phoneChat" />
                </SmartLink>
            ) : null}
        </>
    );
}
