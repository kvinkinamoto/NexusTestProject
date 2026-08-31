import {createApp} from 'vue/dist/vue.esm-bundler';
import { i18nVue } from 'laravel-vue-i18n';
import WishlistButtonAdd from '@/nexus/Components/wishlist/WishlistButtonAdd.vue';
import WishlistButtonOpen from '@/nexus/Components/wishlist/WishlistButtonOpen.vue';
import WishlistTable from '@/nexus/Components/wishlist/WishlistTable.vue';
import CartButtonAdd from '@/nexus/Components/cart/CartButtonAdd.vue';
import CartButtonOpen from '@/nexus/Components/cart/CartButtonOpen.vue';
import CartTable from '@/nexus/Components/cart/CartTable.vue';
import CartTotals from '@/nexus/Components/cart/CartTotals.vue';
import ProductsGrid from '@/nexus/Components/shop/ProductsGrid.vue';
import SearchBar from '@/nexus/Components/shop/SearchBar.vue';
import SubscribeForm from '@/nexus/Components/subscribe/SubscribeForm.vue';
import ErrorMessage from '@/nexus/Components/common/ErrorMessage.vue';
import BaseModal from '@/nexus/Components/modals/BaseModal.vue';
import SuccessModal from '@/nexus/Components/modals/SuccessModal.vue';

const lang = document.getElementById('app')?.getAttribute('data-lang') ?? 'en';

// const app = createApp({});
const app = createApp({
    mounted() {
        this.$nextTick(() => {
            // Перевіряємо чи jQuery та OwlCarousel взагалі існують в DOM, щоб не було помилок
            if (typeof window.$ !== 'undefined' && $.fn.owlCarousel) {
                $(".header-carousel").owlCarousel({
                    items: 1,
                    autoplay: true,
                    smartSpeed: 2000,
                    center: false,
                    dots: false,
                    loop: true,
                    margin: 0,
                    nav : true,
                    navText : [
                        '<i class="bi bi-arrow-left"></i>',
                        '<i class="bi bi-arrow-right"></i>'
                    ]
                });
            } else {
                console.warn('jQuery або OwlCarousel не знайдені в момент монтування Vue.');
            }
        });
    }
});

app.use(i18nVue, {
    lang,
    resolve: async (lang) => {
        const langs = import.meta.glob('../../../lang/*.json');
        return await langs[`../../../lang/${lang}.json`]();
    },
});

app.component('wishlist-button-add', WishlistButtonAdd);
app.component('wishlist-button-open', WishlistButtonOpen);
app.component('wishlist-table', WishlistTable);
app.component('cart-button-add', CartButtonAdd);
app.component('cart-button-open', CartButtonOpen);
app.component('cart-table', CartTable);
app.component('cart-totals', CartTotals);
app.component('products-grid', ProductsGrid);
app.component('search-bar', SearchBar);
app.component('subscribe-form', SubscribeForm);
app.component('error-message', ErrorMessage);
app.component('base-modal', BaseModal);
app.component('success-modal', SuccessModal);

app.config.globalProperties.$formatPrice = (amount) => {
    const symbol = window.CurrencyConfig?.symbol || '$';
    const position = window.CurrencyConfig?.position || 'after';
    const num = Number(amount || 0).toFixed(2);
    return position === 'before' ? `${symbol}${num}` : `${num} ${symbol}`;
};

app.mount('#app');

const tplScript = document.createElement('script');
tplScript.src = '/shop/js/main.js';
document.body.appendChild(tplScript);
