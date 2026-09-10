<div>
    <p>Order statuses drive the checkout/fulfillment lifecycle. <code>slug</code> values <code>confirmed</code>, <code>cancelled</code> and <code>delivered</code> are read by <code>App\Nexus\Modules\Order\Observers\OrderObserver</code> to trigger stock and history side-effects — keep those three slugs when seeding statuses.</p>
</div>
