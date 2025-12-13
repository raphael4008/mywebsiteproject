/**
 * Fetches HTML content from a URL, caches it in sessionStorage, and injects it into a target element.
 * @param {string} url The URL of the HTML partial to fetch.
 * @param {HTMLElement} targetElement The DOM element to inject the HTML into.
 * @param {string} cacheKey The key to use for sessionStorage.
 * @returns {Promise<string|null>} The HTML content as a string, or null on error.
 */
export async function loadAndCacheHTML(url, targetElement, cacheKey) {
    const cachedHtml = sessionStorage.getItem(cacheKey);
    if (cachedHtml) {
        targetElement.innerHTML = cachedHtml;
        return cachedHtml;
    }

    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error(`Failed to fetch ${url}: ${response.statusText}`);
        const fetchedHtml = await response.text();
        sessionStorage.setItem(cacheKey, fetchedHtml);
        targetElement.innerHTML = fetchedHtml;
        return fetchedHtml;
    } catch (error) {
        console.error(`Failed to load content for ${targetElement.id || 'element'}:`, error);
        targetElement.innerHTML = `<p class="text-danger">Error loading content.</p>`;
        return null;
    }
}