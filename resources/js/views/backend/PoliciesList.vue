<template>
    <div class="flex items-center justify-between my-8">

        <div class="flex justify-center flex-1 lgmr-32">
            <div class="relative w-full max-w-xl ml-4 mr-6 focus-within:text-gray-500">
                <div class="absolute inset-y-0 flex items-center pl-2">
                    <svg class="w-4 h-4" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                            clip-rule="evenodd"></path>
                    </svg>
                </div>

                <form>
                    <input
                        class="w-full py-2 pl-8 pr-2 text-sm text-gray-700 placeholder-gray-600 bg-gray-200 border-0 rounded-md"
                        type="text" placeholder="ป้อนนโยบายที่ต้องการค้นหา" aria-label="Search" />
                    <button type="submit" class="hidden">
                        Submit
                    </button>
                </form>
            </div>

            <div>
                <button
                    class="flex items-center justify-between px-4 py-1.5 mx-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-500 border border-transparent rounded-lg active:bg-blue-700 hover:bg-blue-600 focus:outline-none focus:shadow-outline-blue">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>ค้นหา</span>
                </button>
            </div>

            <div>
                <button
                    class="flex items-center justify-between px-4 py-1.5 mx-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-yellow-500 border border-transparent rounded-lg active:bg-yellow-700 hover:bg-yellow-600 focus:outline-none focus:shadow-outline-yellow">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>ล้าง</span>
                </button>
            </div>
        </div>

        <button @click="$router.push({ name: 'PolicyCreate' })"
            class="flex items-center justify-between px-4 py-2 mx-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-green-500 border border-transparent rounded-lg active:bg-green-700 hover:bg-green-600 focus:outline-none focus:shadow-outline-green">
            <svg class="w-4 h-4 mr-2 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>เพิ่ม</span>
        </button>

    </div>

    <div class="mb-4 flex border-b-2 z-50">
        <button @click="changeTab('scheduled')" type="button" :class="[
            'px-4 py-2 text-sm border-2 -mb-[2px]',
            selectedTab === 'scheduled'
                ? 'border-gray-200 text-red-800 bg-gray-50'
                : 'border-transparent bg-gray-50 text-gray-700 mb-[2px]',
            'border-b-gray-50'
        ]">กำหนดเวลา</button>

        <div @click="changeTab('active')" class="flex gap-1">
            <button type="button" :class="[
                'px-4 py-2 text-sm border-2 -mb-[2px]',
                selectedTab === 'active'
                    ? 'border-gray-200 text-red-800 bg-gray-50'
                    : 'border-transparent bg-gray-50 text-gray-700 mb-[2px]',
                'border-b-gray-50'
            ]">ประกาศแล้ว</button>
        </div>

        <div @click="changeTab('draft')" class="flex gap-1">
            <button type="button" :class="[
                'px-4 py-2 text-sm border-2 -mb-[2px]',
                selectedTab === 'draft'
                    ? 'border-gray-200 text-red-800 bg-gray-50'
                    : 'border-transparent bg-gray-50 text-gray-700 mb-[2px]',
                'border-b-gray-50'
            ]">ฉบับร่าง</button>
        </div>
    </div>

    <!-- ตารางแสดงสินค้า -->
    <div class="w-full overflow-hidden shadow-xs">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border">
                        <th class="px-4 py-3">DocNO.</th>
                        <th class="px-4 py-3">หัวข้อ</th>
                        <th class="px-4 py-3">ผู้รับนโยบาย</th>
                        <th class="px-4 py-3">เวลาประกาศ</th>
                        <th class="px-4 py-3">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                    <tr v-for="policy in paginatePolicies" :key="policy.id"
                        class="text-gray-700 dark:text-gray-400 hover:bg-gray-100">
                        <td class="px-4 py-3 text-sm">{{ policy.code ?? '-' }}</td>

                        <td class="px-4 py-3 text-sm">
                            <div class="w-60 truncate">
                                {{ policy.title ?? '-' }}
                            </div>
                        </td>

                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center text-sm">
                                <div>
                                    {{ policy.owner_user_id ?? '-' }}
                                </div>
                            </div>
                        </td>

                        <td class="px-4 py-3 text-sm">
                            {{ formatPublishAt(policy.publish_at) }}
                        </td>

                        <td class="px-4 py-3 text-sm">
                            <button @click="editPolicy(policy.id)"
                                class="px-4 py-2 mx-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-yellow-400 border border-transparent rounded-lg active:bg-yellow-300 hover:bg-yellow-500 focus:outline-none">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                            <button @click="deletePolicy(policy.id)"
                                class="px-4 py-2 mx-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-red-600 border border-transparent rounded-lg active:bg-red-500 hover:bg-red-700 focus:outline-none">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="example-one">
                <VueAwesomePaginate v-model="currentPage" :total-items="filteredTotal" :items-per-page="perPage"
                    :max-pages-shown="7" :on-click="onPageChanged" />
            </div>
        </div>
    </div>
</template>

<script>
import http from '../../services/BackendService';
import Swal from 'sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'

export default {
    data() {
        return {
            policies: [],
            selectedTab: 'active',
            pagination: {},
            currentPage: 1,
            perPage: 10,
        }
    },
    computed: {
        filteredPolicies() {
            if (!Array.isArray(this.policies)) {
                return [];
            }

            const tab = this.selectedTab;
            const now = Date.now();

            if (tab === 'draft') {
                return this.policies.filter(payload => String(payload.status || '').toLowerCase() === 'draft');
            }

            if (tab === 'active') {
                return this.policies.filter(payload => String(payload.status || '').toLowerCase() === 'active');
            }

            if (tab === 'scheduled') {
                return this.policies.filter(payload => String(payload.status || '').toLowerCase() === 'scheduled');
            }
            return this.policies
        },

        paginatePolicies() {
            const list = this.filteredPolicies
            const page = Math.max(1, Number(this.currentPage || 1))
            const per = Math.max(1, Number(this.perPage || 10))
            const start = (page - 1) * per
            return list.slice(start, start + per)
        },

        filteredTotal() {
            return Array.isArray(this.filteredPolicies) ? this.filteredPolicies.length : 0
        }
    },

    methods: {
        async loadPolicies(tab = null, page = 1) {
            try {
                const params = { per_page: this.perPage, page };

                if (tab) {
                    params.status = tab;
                }

                const response = await http.get('/admin/policies', { params })
                //console.log('loadPolicies: response.data', response.data)

                if (response.data && Array.isArray(response.data.data)) {
                    this.policies = response.data.data;
                    this.pagination = {
                        current_page: response.data.current_page,
                        last_page: response.data.last_page,
                        total: response.data.total,
                        per_page: response.data.per_page
                    }
                    this.currentPage = response.data.current_page || page;
                } else {
                    this.policies = Array.isArray(response.data) ? response.data : (response.data?.data || [])
                    this.pagination = {}
                    this.currentPage = page
                }
            } catch (e) {
                console.error('loadPolicies failed', e)
            }
        },

        async onPageChanged(page) {
            await this.loadPolicies(this.selectedTab, page)
        },


        changeTab(tab) {
            this.selectedTab = tab
            this.currentPage = 1
            this.loadPolicies(tab, 1)
        },

        formatPublishAt(value) {
            if (!value) return '-'
            const s = String(value).trim();

            const m = s.match(/^(\d{4})-(\d{2})-(\d{2})(?:[T\s](\d{2}):(\d{2})(?::\d{2}(?:\.\d+)?)?)?/);
            if (!m) return s;

            const [, y, mo, d, h, mi] = m;

            return (h !== undefined && mi !== undefined)
                ? `${d}/${mo}/${y} ${h}:${mi}`
                : `${d}/${mo}/${y}`;
        },

        editPolicy(id) {
            this.$router.push({ name: 'PolicyEdit', params: { id } })
        },

        async deletePolicy(id) {
            const confirm = await Swal.fire({
                title: 'ยืนยันการลบ?',
                text: "การลบจะไม่สามารถย้อนกลับได้",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                confirmButtonText: "ใช่, ลบเลย",
                cancelButtonColor: "#d33",
                cancelButtonText: "ยกเลิก"
            })
            if (!confirm.isConfirmed) {
                return;
            }

            try {
                Swal.fire({
                    title: 'กำลังลบ...',
                    didOpen: () =>
                        Swal.showLoading(),
                    allowOutsideClick: false,
                    showConfirmButton: false
                })
                await http.delete(`admin/policies/${id}`);
                Swal.fire('ลบสำเร็จ', 'นโยบายถูกลบเรียบร้อยแล้ว', 'success')
                this.loadPolicies();
            } catch (e) {
                console.error('deletePolicy failed', e)
                Swal.fire('ลบนโยบายไม่สำเร็จ', 'ไม่สามารถลบข้อมูลได้', 'error')
            }
        }
    },
    mounted() {
        this.loadPolicies(this.selectedTab, this.currentPage);
    }
}

</script>


<style>
.example-one .pagination-container {
    column-gap: 5px;
    background-color: #F9FAFB;
}

.example-one .paginate-buttons {
    height: 30px;
    width: 30px;
    border-radius: 20px;
    cursor: pointer;
    background-color: rgb(242, 242, 242);
    border: 1px solid rgb(217, 217, 217);
    color: black;
}

.example-one .paginate-buttons:hover {
    background-color: #d8d8d8;
}

.example-one .active-page {
    background-color: #991B1B;
    border: 1px solid #991B1B;
    color: white;
}

.example-one .active-page:hover {
    background-color: #b81b1b;
    border: 1px solid #b81b1b;
}

.example-one .back-button:active,
.example-one .next-button:active {
    background-color: #c4c4c4;
}
</style>