<meta name="robots" content="noindex, nofollow">
<style>
    /* The page is inert during preview so a stray click can't lose the unsaved job. */
    body > :not(.job-preview-bar) { pointer-events: none; }
    body { padding-bottom: 120px; }
    .back-to-top { display: none !important; }
    .job-preview-bar {
        position: fixed; left: 0; right: 0; bottom: 0; z-index: 1100;
        background: var(--gst-navy-900); color: var(--gst-white);
        box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.2); padding: 14px 0;
    }
    .job-preview-bar__inner { display: flex; flex-wrap: wrap; gap: 12px 24px; align-items: center; justify-content: space-between; }
    .job-preview-bar__actions { display: flex; flex-wrap: wrap; gap: 12px; }
    .job-preview-bar p { margin: 0; font-size: 14px; }
</style>
