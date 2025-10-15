<template>
    <div class="flex items-center justify-between my-8">

        <button @click="openModalAddPolicyCategory"
            class="flex items-center justify-between px-4 py-2 mx-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-green-500 border border-transparent rounded-lg active:bg-purple-600 hover:bg-green-700 focus:outline-none focus:shadow-outline-purple">
            <svg class="w-4 h-4 mr-2 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>เพิ่ม</span>
        </button>
    </div>
    <!-- ตารางแสดงประเภทนโยบาย -->
    <div class="w-full overflow-hidden rounded-lg shadow-xs">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border">
                        <th class="px-4 py-3">ประเภทของนโยบาย</th>
                        <th class="px-4 py-3">ดำเนินการ</th>
                    </tr>

                </thead>
                <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                    <tr v-for="policyCategory in policyCategorys.data" :key="policyCategory.id"
                        class="text-gray-700 dark:text-gray-400 hover:bg-blue-100">

                        <td class="px-4 py-3">
                            <div class="flex items-center text-sm">
                                <div class="relative hidden w-full h-8 mr-3 rounded-full md:block">
                                    <p>{{ policyCategory.name }}</p>
                                </div>
                            </div>
                        </td>

                        <td class="px-4 py-3 text-sm">
                            <button @click="openModalEditPolicyCategory(policyCategory)"
                                class="px-4 py-2 mx-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-yellow-400 border border-transparent rounded-lg active:bg-yellow-300 hover:bg-yellow-500 focus:outline-none">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                            <button @click="confirmDelete(policyCategory.id, policyCategory.name)"
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
            <VueAwesomePaginate v-model="currentPage" :total-items="total" :items-per-page="perPage"
                :max-pages-shown="7" :on-click="onPageChanged" />
        </div>
    </div>

    <!-- Popup สำหรับเพิ่มประเภทนโยบายใหม่-->
    <div v-if="showAddModal" class="fixed top-0 left-0 flex items-center justify-center w-full h-full modal">
        <div class="z-50 w-11/12 p-5 mx-auto overflow-y-auto bg-white rounded shadow-lg modal-container md:max-w-md">

            <!--Header-->
            <div class="flex items-center justify-center w-full h-auto">
                <div class="flex items-start justify-start w-full h-auto py-2 text-xl font-bold">
                    <h3 class="text-lg font-semibold">
                        {{ mode === 'create' ? 'เพิ่มประเภทนโยบาย' : 'แก้ไขประเภทนโยบาย' }}
                    </h3>
                </div>

                <div @click="closeAddPolicyCategory"
                    class="flex items-center justify-center w-1/12 h-auto cursor-pointer">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </div>
                <!-- Header End -->
            </div>

            <!-- Modal Content -->
            <div class="w-full h-auto mb-4">
                <form ref="addPolicyCategoryForm" @submit.prevent="onSubmit">
                    <label for="name" class="block my-3 text-gray-700 text-md">ประเภทนโยบาย</label>
                    <input v-model="form.name" type="text"
                        class="w-full px-3 py-2 leading-tight text-gray-700 border rounded shadow"
                        placeholder="กรุณากรอกข้อมูล" />
                    <div v-if="v$.form.name.$error" class="mt-2 text-sm text-red-500">
                        {{ v$.form.name.$error[0].$message }}
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2">
                            <button type="submit"
                                class="w-full px-4 py-2 mt-4 font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 rounded-lg hover:bg-blue-700">
                                บันทึก
                            </button>
                        </div>
                        <div>
                            <button type="button" @click="onResetForm"
                                class="w-full px-4 py-2 mt-4 font-medium leading-5 text-white transition-colors duration-150 bg-gray-500 rounded-lg hover:bg-gray-700">
                                ล้าง
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import http from "../../services/BackendService.js";
import useVuelidate from "@vuelidate/core";
import { required, helpers } from "@vuelidate/validators";

export default {
    data() {
        return {
            // query
            policyCategorys: { data: [] },
            currentPage: 1,
            perPage: 25,
            total: 0,
            lastPage: 1,

            // modal
            showAddModal: false,
            mode: 'create',
            editingId: null,
            deletingId: null,

            form: {
                code: "",
                name: "",
                is_mandatory: true
            },
        };
    },

    setup() {
        return { v$: useVuelidate() };
    },

    validations() {
        return {
            form: {
                name: {
                    required: helpers.withMessage("ป้อนประเภทนโยบายก่อนทำการบันทึก", required),
                },
            }
        };
    },

    methods: {
        async getPolicyCategory(page = this.currentPage) {
            let response = await http.get(`/admin/policy-categories`, {
                params: { page, per: this.perPage }
            });
            const paged = response.data;
            this.policyCategorys = paged;
            this.currentPage = paged.current_page;
            this.perPage = Number(paged.per_page);
            this.total = paged.total;
            this.lastPage = paged.last_page;
        },

        async onPageChanged(page) {
            this.currentPage = page
            await this.getPolicyCategory(page)
        },

        openModalAddPolicyCategory() {
            this.mode = 'create';
            this.editingId = null;
            this.form = { code: "", name: "", is_mandatory: true };
            this.v$.$reset();
            this.showAddModal = true;
        },

        openModalEditPolicyCategory(row) {
            if (!row) return;
            this.mode = 'edit';
            this.editingId = row.id;
            this.form = {
                code: row.code ?? "",
                name: row.name ?? "",
                is_mandatory: Boolean(row.is_mandatory),
            };
            this.v$.$reset();
            this.showAddModal = true;
        },

        confirmDelete(id, label) {
            if (id == null) return;
            if (confirm(`ยืนยันการลบประเภทนโยบาย: ${label ?? id} ใช่หรือไม่`)) {
                this.deletePolicyCategory(id);
            }
        },

        async deletePolicyCategory(id) {
            const idStr = String(id);

            this.deletingId = idStr;
            try {
                await http.delete(`/admin/policy-categories/${encodeURIComponent(idStr)}`);
                const isLastItemOnPage = (this.companys?.data?.length || 1) === 1;
                const nextPage = isLastItemOnPage && this.currentPage > 1 ? this.currentPage - 1 : this.currentPage;

                await this.getPolicyCategory(nextPage);
                alert("ลบสำเร็จ");
            } catch (err) {
                const status = err?.response?.status;

                if (status === 403) {
                    alert('คุณไม่มีสิทธิ์ในการลบรายการนี้');
                } else if (status === 409) {
                    alert('ลบไม่ได้: มีการอ้างอิงข้อมูลนี้อยู่ (Foreign key).');
                } else {
                    alert('เกิดข้อผิดพลาดขณะลบ');
                }
            } finally {
                this.deletingId = null;
            }
        },

        closeAddPolicyCategory() {
            this.showAddModal = false;
            this.onResetForm();
        },

        onResetForm() {
            this.$refs.addPolicyCategoryForm?.reset();
            this.form = { code: "", name: "", is_mandatory: true };
            this.v$.$reset();
        },

        async onSubmit() {
            const ok = await this.v$.$validate();
            if (!ok) return;

            try {
                if (this.mode === 'create') {
                    await http.post('/admin/policy-categories', this.form);
                } else {
                    await http.put(`/admin/policy-categories/${this.editingId}`, this.form);
                }
                alert('บันทึกสำเร็จ');
                this.closeAddPolicyCategory();

                await this.getPolicyCategory(this.currentPage);

            } catch (err) {
                const first = Object.values(err.response?.data?.errors || {})[0]?.[0];
                if (first) alert(first);
            }
        },

        goTo(page) {
            if (page < 1 || page > this.lastPage) return;
            this.currentPage = page;
            this.getPolicyCategory(page);
        },
    },

    mounted() {
        this.getPolicyCategory(1);
    },
}
</script>

<style>
.pagination-container {
    margin-top: 10px;
    background-color: #f0f0f0;
    border-radius: 5px;
    padding: 10px 0px;
}

.paginate-buttons {
    width: 30px;
    height: 30px;
    margin-inline: 5px;
    cursor: pointer;
    border: none;
    background-color: transparent;
    border-radius: 2px;
}

.back-button {
    width: 30px;
}

.next-button {
    width: 30px;
}

.back-button svg {
    transform: rotate(180deg);
}

.active-page {
    background-color: #2980b9;
    color: #fff;
}

.paginate-buttons:hover {
    background-color: #e5e5e5;
}

.active-page:hover {
    background-color: #3b8cc3;
    color: #fff;
}

.back-button:active,
.next-button:active {
    background-color: #dedede;
}
</style>