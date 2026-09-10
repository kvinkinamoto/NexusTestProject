<div>
    <p><code>key</code> is what <code>Order.delivery_method</code> stores. Two keys are recognized by the shipment tracker (<code>App\Nexus\Modules\Shipment\Services\TrackingManager::TRACKABLE_CARRIERS</code>): <code>nova-poshta</code> and <code>ukr-poshta</code> — any other key (e.g. <code>pickup</code>, <code>taxi</code>) is a delivery method with no automated tracking.</p>
</div>
