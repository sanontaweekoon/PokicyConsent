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
                    class="flex items-center justify-between px-4 py-1.5 mx-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-zinc-400 border border-transparent rounded-lg active:bg-zinc-700 hover:bg-zinc-600 focus:outline-none focus:shadow-outline-zinc">
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
                        <th class="px-4 py-3">เวลาประกาศ</th>
                        <th class="px-4 py-3">สถานะ</th>
                        <th class="px-4 py-3">QR Code</th>
                        <th class="px-4 py-3">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                    <tr v-for="policy in paginatePolicies" :key="policy.id"
                        class="text-gray-700 dark:text-gray-400 hover:bg-gray-100">
                        <td class="px-4 py-3 text-sm">{{ policy.code ?? '-' }}</td>

                        <td class="px-4 py-3 text-sm">
                            <div class="max-w-lg truncate" :title="policy.title">
                                {{ policy.title ?? '-' }}
                            </div>
                        </td>

                        <td class="px-4 py-3 text-sm">
                            {{ formatPublishAt(policy.publish_at) }}
                        </td>

                        <td>
                            <div class="flex items-center gap-2">
                                <button @click="toggleWindow(policy.policy_windows[0])" :class="[
                                    'px-3 py-1.5 text-xs font-medium rounded-full transition-colors',
                                    policy.policy_windows[0]?.is_open
                                        ? 'bg-green-100 text-green-800 hover:bg-green-200'
                                        : 'bg-red-100 text-red-800 hover:bg-red-200'  
                                ]">
                                {{  policy.policy_windows[0]?.is_open ? 'เปิดการรับทราบ' : 'ปิด' }}
                                </button>
                            </div>
                        </td>

                        <td class="px-4 py-3 text-sm">
                            <div v-if="hasQrCode(policy)" class="flex gap-1">
                                <button @click="viewQr(policy.policy_windows[0])"
                                    class="px-3 py-1.5 text-xs font-medium text-white bg-stone-100 rounded hover:bg-stone-200"
                                    title="QR Code">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                        <path fill="#000000"
                                            d="M64 160l64 0 0-64-64 0 0 64zM0 80C0 53.5 21.5 32 48 32l96 0c26.5 0 48 21.5 48 48l0 96c0 26.5-21.5 48-48 48l-96 0c-26.5 0-48-21.5-48-48L0 80zM64 416l64 0 0-64-64 0 0 64zM0 336c0-26.5 21.5-48 48-48l96 0c26.5 0 48 21.5 48 48l0 96c0 26.5-21.5 48-48 48l-96 0c-26.5 0-48-21.5-48-48l0-96zM320 96l0 64 64 0 0-64-64 0zM304 32l96 0c26.5 0 48 21.5 48 48l0 96c0 26.5-21.5 48-48 48l-96 0c-26.5 0-48-21.5-48-48l0-96c0-26.5 21.5-48 48-48zM288 352a32 32 0 1 1 0-64 32 32 0 1 1 0 64zm0 64c17.7 0 32 14.3 32 32s-14.3 32-32 32-32-14.3-32-32 14.3-32 32-32zm96 32c0-17.7 14.3-32 32-32s32 14.3 32 32-14.3 32-32 32-32-14.3-32-32zm32-96a32 32 0 1 1 0-64 32 32 0 1 1 0 64zm-32 32a32 32 0 1 1 -64 0 32 32 0 1 1 64 0z" />
                                    </svg>
                                </button>
                            </div>
                            <span v-else-if="policy.status === 'draft'" class="text-gray-400 text-xs">📝
                                ฉบับร่าง</span>
                            <span v-else-if="policy.status === 'scheduled'" class="text-orange-500 text-xs">⏰
                                รอประกาศ</span>
                            <span v-else class="text-gray-400 text-xs">-</span>
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

    <Teleport to="body">
        <div v-if="showQrModal" @click="showQrModal = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div @click.stop class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">QR Code สำหรับรับทราบนโยบาย</h3>
                    <button @click="showQrModal = false" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="text-center space-y-4">
                    <img :src="currentQrUrl" alt="QR Code" class="w-full max-w-sm mx-auto border rounded-lg p-4" />
                    <div class="text-sm text-gray-600 break-all bg-gray-50 p-3 rounded">{{ currentQrLink }}</div>
                    <div class="flex gap-2 justify-center">
                        <button @click="copyQrLink"
                            class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">คัดลอก</button>
                        <a :href="currentQrUrl" download="qr-code.svg"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">ดาวน์โหลด</a>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
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
            showQrModal: false,
            currentQrUrl: '',
            currentQrLink: '',
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

        hasQrCode(policy) {
            return policy.status === 'active' &&
                policy.policy_windows &&
                policy.policy_windows.length > 0 &&
                policy.policy_windows[0]?.is_open === true;
        },

        async toggleWindow(policyWindow) {
            if (!policyWindow?.id) {
                Swal.fire('ข้อผิดพลาด', 'ไม่พบข้อมูล หน้าต่างนโยบาย', 'error')
                return;
            }
            const action = policyWindow.is_open ? 'ปิด' : 'เปิด';
            const confirm = await Swal.fire({
                title: `${action}การรับทราบ?`,
                text: policyWindow.is_open
                    ? 'การปิดจะทำให้พนักงานไม่สามารถรับทราบนโยบายได้อีก'
                    : 'พนักงานจะสามารถรับทราบนโยบายได้แล้ว',
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: policyWindow.is_open ? '#dc2626' : '#16a34a',
                confirmButtonText: `ใช่, ${action}เลย`,
                cancelButtonText: 'ยกเลิก'
            });

            if (!confirm.isConfirmed) {
                return;
            }

            try {
                Swal.fire({
                    title: `กำลัง${action}`,
                    didOpen: () => Swal.showLoading(),
                    allowOutsideClick: false,
                    showConfirmButton: false
                });

                const response = await http.put(`/admin/policy-windows/${policyWindow.id}/toggle`);

                await Swal.fire({
                    icon: 'success',
                    title: response.data?.message || `${action}การรับทราบสำเร็จ`,
                    timer: 2000,
                    showConfirmButton: false
                });

                await this.loadPolicies(this.selectedTab, this.currentPage);
            } catch (e) {
                console.error('Toggle window failed', e);
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: e.response?.data?.message || `ไม่สามารถ${action}การรับทราบได้`
                });
            }
        },

        viewQr(policyWindow) {
            if (!policyWindow || !policyWindow.id) {
                Swal.fire('ข้อผิดพลาด', 'ไม่พบข้อมูล หน้าต่างนโยบาย', 'error')
                return;
            }

            this.currentQrUrl = `/api/policy-windows/${policyWindow.id}/qr`;
            this.currentQrLink = `${window.location.origin}/ack/${policyWindow.id}`;
            this.showQrModal = true;
        },

        copyQrLink() {
            navigator.clipboard.writeText(this.currentQrLink);
            Swal.fire({
                icon: 'success',
                title: 'คัดลอกลิงก์สำเร็จ',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
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