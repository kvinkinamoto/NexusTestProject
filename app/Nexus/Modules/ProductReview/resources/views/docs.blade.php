<div>
    <p>Approved (<code>publish</code>) reviews from a verified purchaser award a REVIEW bonus once per completed purchase of that product — see <code>ReviewService::addBonusesForReview()</code>, wired through <code>ReviewObserver</code> on create/update.</p>
    <p><code>images</code>/<code>comments</code>/<code>reactions</code> are satellite tables, not their own admin module — comments auto-publish on creation, so there's no moderation queue to build a screen for.</p>
    <p>Frontend API: <code>GET/POST /api/reviews</code>, <code>DELETE /api/reviews/{id}</code> (auth), <code>POST /api/reviews/comments</code> (throttled), <code>POST /api/reviews/reactions/toggle</code> (throttled).</p>
</div>
