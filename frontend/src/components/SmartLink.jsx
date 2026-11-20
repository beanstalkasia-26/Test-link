import Link from '../router/Link';
import { isInternalPage } from '../lib/links';

export default function SmartLink({ href, newTab = false, children, ...props }) {
    if (!href) {
        return <span {...props}>{children}</span>;
    }

    if (isInternalPage(href) && !newTab) {
        return (
            <Link href={href} prefetch="hover" {...props}>
                {children}
            </Link>
        );
    }

    const external = /^https?:\/\//i.test(href);

    return (
        <a href={href} target={newTab ? '_blank' : undefined} rel={external ? 'noopener noreferrer' : undefined} {...props}>
            {children}
            {newTab ? <span className="visually-hidden"> (opens in a new tab)</span> : null}
        </a>
    );
}
