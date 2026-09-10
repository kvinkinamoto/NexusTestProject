<div>
    <p>A self-joining tree (via <code>parent_id</code>/kalnoy/nestedset, same mechanism as ShopCategory) — a reply is just another row with <code>parent_id</code> set. Root questions default to unpublished pending moderation; replies (created with a <code>parent_id</code>) auto-publish.</p>
    <p>Frontend API: <code>POST /api/questions</code> (ask, guest-allowed), <code>DELETE /api/questions/{id}</code> (auth), <code>POST /api/questions/reactions/toggle</code> (like/dislike, throttled).</p>
</div>
