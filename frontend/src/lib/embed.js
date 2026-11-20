export function toEmbedUrl(url) {
    try {
        const u = new URL(url);
        if (/youtube\.com$/.test(u.hostname.replace('www.', '')) && u.searchParams.get('v')) return `https://www.youtube-nocookie.com/embed/${u.searchParams.get('v')}?autoplay=1&rel=0`;
        if (u.hostname === 'youtu.be') return `https://www.youtube-nocookie.com/embed/${u.pathname.slice(1)}?autoplay=1&rel=0`;
        if (u.hostname.endsWith('vimeo.com')) {
            const id = u.pathname.split('/').filter(Boolean).pop();
            return /^\d+$/.test(id) ? `https://player.vimeo.com/video/${id}?autoplay=1&dnt=1` : null;
        }
    } catch {
        return null;
    }
    return null;
}
