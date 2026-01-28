<script setup>
import { useInvestStore } from '@/service/stores/investStorage';
import Carousel from 'primevue/carousel';
import { useToast } from 'primevue/usetoast';
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';

const dataInvest = ref(null);
const route = useRoute();
const investStore = useInvestStore();
const toast = useToast();

const getDetailInvest = async () => {
    try {
        const kode = route.params.kode_investasi;
        const response = await investStore.getInvestbyId(kode);
        dataInvest.value = response.data.data;
    } catch {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Gagal memuat data investasi',
            life: 3000
        });
    }
};

const formatCurrency = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);

const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });

const getStatusBadgeClass = (status) => ({ Diterima: 'success', Pending: 'warning', Ditolak: 'danger', Mati: 'danger' })[status] || 'info';

const totalDomba = computed(() => dataInvest.value?.products?.length || 0);
const dombaTerjual = computed(() => dataInvest.value?.products?.filter((p) => p.status === 'Terjual')?.length || 0);
const proyeksiKeuntungan = computed(() => {
    if (!dataInvest.value?.products) return 0;
    return dataInvest.value.products.reduce((total, product) => {
        const keuntungan = parseFloat(product.harga_jual || 0) - parseFloat(product.harga_beli || 0);
        return total + keuntungan;
    }, 0);
});

onMounted(getDetailInvest);
</script>

<template>
    <div v-if="dataInvest" class="min-h-screen bg-linear-to-br from-slate-50 to-slate-100 p-6">
        <!-- HEADER -->
        <div class="mb-8">
            <div class="flex justify-between items-start flex-wrap gap-4">
                <div>
                    <h1 class="text-5xl font-black text-slate-900 mb-2">Detail Investasi</h1>
                    <p class="text-slate-500 text-base font-medium">{{ dataInvest.kode_investasi }}</p>
                </div>
            </div>
        </div>

        <!-- MAIN CONTENT: 2 COLUMN LAYOUr -->
        <div class="grid mb-8">
            <!-- LEFT: BUKTI PEMBAYARAN -->
            <div class="col-12 lg:col-1 mr-3">
                <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b">
                        <h3 class="font-semibold text-slate-800">Bukti Pembayaran</h3>
                    </div>

                    <div class="p-5 flex justify-center">
                        <img v-if="dataInvest.bukti_pembayaran" :src="dataInvest.bukti_pembayaran" class="max-h-[520px] object-contain" />
                        <div v-else class="text-slate-400 text-sm">Tidak ada bukti pembayaran</div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: DATA INFORMASI -->
            <div class="col-12 lg:col-2 space-y-5">
                <!-- DATA CARD -->
                <div class="bg-white border rounded-xl shadow-sm">
                    <div class="px-5 py-4 border-b">
                        <h3 class="font-semibold text-slate-800">Detail Investasi</h3>
                    </div>

                    <div class="p-5 space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Kode Investasi</span>
                            <span class="font-medium">{{ dataInvest.kode_investasi }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-slate-500">Investor</span>
                            <span class="font-medium">{{ dataInvest.users?.nama_users }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-slate-500">Jumlah Terbilang</span>
                            <span class="font-semibold text-green-600">
                                {{ dataInvest.jumlah_inves_terbilang }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-slate-500">Jumlah Investasi</span>
                            <span class="font-semibold text-green-600">
                                {{ formatCurrency(dataInvest.jumlah_inves) }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-slate-500">Metode Pembayaran</span>
                            <span class="font-medium">{{ dataInvest.metode_pembayaran }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-slate-500">Tanggal</span>
                            <span class="font-medium">{{ formatDate(dataInvest.tanggal_investasi) }}</span>
                        </div>
                    </div>
                </div>

                <!-- ACTION BUTTONS -->
                <div class="flex gap-3 flex-wrap">
                    <Button label="Cetak Kuitansi" icon="pi pi-print" class="p-button-success flex-1" @click="printReceipt" />
                </div>
            </div>
        </div>

        <!-- STATISTIK SECTION -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-slate-900 mb-5">Statistik Investasi</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-blue-500">
                    <div class="flex items-center gap-4">
                        <div class="bg-blue-100 rounded-xl p-4">
                            <i class="pi pi-sitemap text-3xl text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-slate-600 text-sm font-medium mb-1">Jumlah Domba</p>
                            <p class="text-4xl font-black text-blue-600">{{ totalDomba }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-green-500">
                    <div class="flex items-center gap-4">
                        <div class="bg-green-100 rounded-xl p-4">
                            <i class="pi pi-check-circle text-3xl text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-slate-600 text-sm font-medium mb-1">Domba Terjual</p>
                            <p class="text-4xl font-black text-green-600">{{ dombaTerjual }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-purple-500">
                    <div class="flex items-center gap-4">
                        <div class="bg-purple-100 rounded-xl p-4">
                            <i class="pi pi-chart-line text-3xl text-purple-600"></i>
                        </div>
                        <div>
                            <p class="text-slate-600 text-sm font-medium mb-1">Proyeksi Keuntungan</p>
                            <p class="text-2xl font-black text-purple-600">{{ formatCurrency(proyeksiKeuntungan) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PRODUK SECTION -->
        <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-5">Data Domba</h2>

            <!-- GRID 3 KOLOM -->
            <div v-if="dataInvest.products?.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="(product, index) in dataInvest.products" :key="index">
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-slate-200">
                        <!-- CAROUSEL FOTO -->
                        <div class="relative h-80 bg-slate-100 overflow-hidden">
                            <Carousel :value="[product.photo1, product.photo2, product.photo3].filter(Boolean)" :numVisible="1" :numScroll="1" circular :autoplayInterval="4000" class="h-full" containerClass="h-full" contentClass="h-full">
                                <template #item="slotProps">
                                    <img :src="slotProps.data" class="w-full h-80 object-cover" :alt="product.kode_product" />
                                </template>
                            </Carousel>

                            <!-- STATUS -->
                            <div class="absolute top-4 right-4 z-10">
                                <Tag :value="product.status" :severity="getStatusBadgeClass(product.status)" class="text-sm font-bold" />
                            </div>

                            <!-- JENIS -->
                            <div v-if="product.jenis_product" class="absolute bottom-4 left-4 z-10">
                                <span class="bg-white/95 px-4 py-2 rounded-full text-xs font-black text-slate-900 shadow">
                                    {{ product.jenis_product.toUpperCase() }}
                                </span>
                            </div>
                        </div>

                        <!-- CONTENT -->
                        <div class="p-6">
                            <!-- KODE & TIPE -->
                            <h3 class="text-3xl font-black text-slate-900 mb-1">
                                {{ product.kode_product }}
                            </h3>
                            <p class="text-blue-600 font-bold text-base mb-5">
                                {{ product.type_product }}
                            </p>

                            <!-- INFO GRID -->
                            <div class="grid grid-cols-2 gap-4 mb-5 pb-5 border-b border-slate-200">
                                <div>
                                    <p class="text-slate-600 text-xs font-medium mb-1">Kelamin</p>
                                    <p class="flex items-center gap-2 font-bold text-slate-900">
                                        <i :class="product.gender === 'male' ? 'pi pi-mars text-blue-600 text-xl' : 'pi pi-venus text-pink-600 text-xl'" />
                                        {{ product.gender === 'male' ? 'Jantan' : 'Betina' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-slate-600 text-xs font-medium mb-1">Bobot</p>
                                    <p class="font-black text-slate-900">{{ product.bobot }} kg</p>
                                </div>

                                <div class="col-span-2">
                                    <p class="text-slate-600 text-xs font-medium mb-1">Tgl Lahir</p>
                                    <p class="text-slate-900 font-bold">
                                        {{ formatDate(product.birth_date) }}
                                    </p>
                                </div>
                            </div>

                            <!-- HARGA -->
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-600 text-sm font-medium"> Harga Beli </span>
                                    <span class="font-black text-green-600 text-lg">
                                        {{ formatCurrency(product.harga_beli) }}
                                    </span>
                                </div>

                                <div class="flex justify-between items-center pb-3 border-b border-slate-200">
                                    <span class="text-slate-600 text-sm font-medium"> Harga Jual </span>
                                    <span class="font-black text-blue-600 text-lg">
                                        {{ formatCurrency(product.harga_jual) }}
                                    </span>
                                </div>
                            </div>

                            <!-- KEUNTUNGAN -->
                            <div v-if="product.harga_jual" class="bg-linear-to-r from-purple-50 to-purple-100 rounded-xl p-4 border-l-4 border-purple-500">
                                <p class="text-slate-900 font-bold text-sm mb-1">Keuntungan</p>
                                <p class="text-3xl font-black text-purple-600">
                                    {{ formatCurrency(parseFloat(product.harga_jual) - parseFloat(product.harga_beli)) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EMPTY STATE -->
            <div v-else class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-lg">
                <i class="pi pi-inbox text-7xl text-slate-300 mb-4"></i>
                <p class="text-slate-500 text-xl font-bold">Tidak ada produk yang diinvestasikan</p>
            </div>
        </div>
    </div>

    <div v-else class="flex justify-center items-center min-h-screen bg-linear-to-br from-slate-50 to-slate-100">
        <ProgressSpinner />
    </div>
</template>

<style scoped>
.bukti-container {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 500px;
}

.bukti-image {
    width: 100%;
    height: 500px;
    object-fit: contain;
}

.bukti-placeholder {
    width: 100%;
    height: 500px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background-color: #f9fafb;
}

:deep(.p-tag) {
    font-size: 13px !important;
    padding: 0.6rem 1rem !important;
    font-weight: 600;
}

@media (max-width: 1024px) {
    .bukti-image,
    .bukti-placeholder {
        height: 400px;
    }
}

@media (max-width: 640px) {
    .bukti-image,
    .bukti-placeholder {
        height: 300px;
    }
}
</style>
