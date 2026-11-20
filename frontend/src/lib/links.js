export function isInternalPage(url) {
    return Boolean(url) && url.startsWith('/') && !url.startsWith('//') && !/\.(pdf|xml|txt|jpg|png|zip)$/i.test(url);
}
