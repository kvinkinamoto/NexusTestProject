<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.02]">
    <h3 class="mb-2 text-lg font-semibold text-gray-800 dark:text-white/90">Demo module</h3>
    <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
        Living example of the Nexus field system — every tab below exercises a distinct set of field
        types/options against the Livewire create/edit form.
    </p>

    <ul class="mb-4 list-disc space-y-1 pl-5 text-sm text-gray-600 dark:text-gray-300">
        <li><strong>basic</strong> — string, translated string, text (with <code>editor: true</code> — CKEditor), number, email, password (hidden from infolist), boolean, date, datetime.</li>
        <li><strong>media</strong> — single image, single video (elFinder picker), plus multi-image and multi-video galleries (<code>images</code>/<code>videos</code> — a plain array-of-paths column, add/remove one at a time via the same elFinder popup).</li>
        <li><strong>choices</strong> — enum (backed PHP enum) and select (options populated by an <code>AdminFormBuilding</code> listener).</li>
        <li><strong>relations</strong> — 3 independent belongsToMany pickers against the real <code>permissions</code> table, demonstrating the 3 ajax/preload combinations (<code>ajax: false</code>, <code>ajax: true</code> search-only, <code>ajax: true, ajaxMode: 'load'</code>), plus a single belongsTo and a read-only relationManager list.</li>
        <li><strong>repeater</strong> — a hasMany child table (#[RepeaterField]).</li>
        <li><strong>advanced</strong> — a custom field type (<code>ratingStars</code>, unknown to the package), an overridden built-in type (<code>phone</code>, module-scoped override), a <code>view</code>-slot virtual field, conditional visibility (<code>showWhen</code>), a read-only json field, an <code>apiExpose</code> field, and a field <code>disabled</code> on edit.</li>
    </ul>

    <div class="rounded-lg border border-dashed border-gray-300 p-3 text-xs text-gray-500 dark:border-gray-700 dark:text-gray-400">
        Deliberately left out: field types hardcoded to other business models (<code>gender</code>, <code>wishlist_table</code>, <code>addresses_table</code>, <code>cart_table</code>, <code>attribute_options_field</code>, <code>custom_history</code>, <code>custom_delivery_*</code>, <code>custom_payment_method</code>, <code>wishlistable_type_field</code>, <code>causer</code>) — including them here would either error or mislead, since each is hardwired to a specific unrelated model. Every other type documented on <code>#[Field]</code> (including the previously-unported <code>date</code>, <code>view</code>, <code>images</code>, <code>videos</code>, and <code>editor</code>) is now demonstrated somewhere on this page.
    </div>
</div>
