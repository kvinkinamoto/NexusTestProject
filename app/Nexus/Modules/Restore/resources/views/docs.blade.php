<div>
    <p>Rows are populated only by scanning the disk/path configured on the Backup module's own "Settings" screen — there is no manual create form. The list auto-rescans every time you open it, or click "Scan backups" to force it. A file's type (<code>db</code>/<code>code</code>/<code>storage</code>) is inferred from its filename suffix (<code>_db.sql</code>/<code>_code.zip</code>/<code>_storage.zip</code>).</p>
    <p>Click "Run restore" on a row to queue a <code>RunRestoreJob</code>. A <strong>database</strong> restore drops every existing table before importing the dump — there is no undo. A <strong>code</strong> or <strong>storage</strong> restore copies the live directory aside, extracts the archive into a fresh copy, then swaps directory names, with a best-effort rollback if the swap itself fails partway.</p>
    <p>PostgreSQL restore is not implemented (dump support exists, but no working PostgreSQL import path — same gap the old project had).</p>
</div>
