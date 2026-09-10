<div>
    <p>Each row is a locale: <code>language</code> is the display name ("English", "Українська"), <code>code</code> the key used everywhere else in this app's <code>resources/lang/{code}/translate.php</code> convention and by every <code>Spatie\Translatable</code> field.</p>
    <p>"Use for admin" sets which locale the current admin sees the panel's own UI text in (stored in the session, independent of any future public-site locale). It's read by <code>App\Nexus\Modules\Language\Http\Middleware\SetAdminLocale</code>, registered on every admin request.</p>
    <p>Renaming a row's <code>code</code> automatically rekeys that old locale key to the new one inside every other module's translated (JSON) columns — see <code>Listeners/RekeyTranslationsOnLocaleRename</code> — so existing translated content isn't orphaned under a code nothing reads anymore.</p>
    <p>Published languages are also readable at <code>GET /api/languages</code> for a future storefront's language switcher.</p>
</div>
