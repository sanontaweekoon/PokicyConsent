<template>
    <div class="flex items-center justify-between my-8">

        <button
            class="flex items-center justify-between px-4 py-2 mx-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-green-500 border border-transparent rounded-lg active:bg-purple-600 hover:bg-green-700 focus:outline-none focus:shadow-outline-purple">
            <svg class="w-4 h-4 mr-2 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>สร้างกลุ่มใหม่</span>
        </button>
    </div>
    <!-- ตารางแสดงประเภทนโยบาย -->
    <div class="w-full overflow-hidden rounded-lg shadow-xs">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border">
                        <th class="px-4 py-3">ชื่อกลุ่ม</th>
                            <th class="px-4 py-3">ประเภท</th>
                            <th class="px-4 py-3">จำนวนสมาชิก</th>
                            <th class="px-4 py-3">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                    <tr
                        class="text-gray-700 dark:text-gray-400 hover:bg-blue-100">

                        <td class="px-4 py-3">
                            <div class="flex items-center text-sm">
                                <div class="relative hidden w-full h-8 mr-3 rounded-full md:block">
                                    <p></p>
                                </div>
                            </div>
                        </td>

                        <td class="px-4 py-3 text-sm">
                            <button
                                class="px-4 py-2 mx-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-yellow-400 border border-transparent rounded-lg active:bg-yellow-300 hover:bg-yellow-500 focus:outline-none">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                            <button
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
            <!-- <VueAwesomePaginate v-model="currentPage" :total-items="total" :items-per-page="perPage"
                :max-pages-shown="7" :on-click="onPageChanged" /> -->
        </div>
    </div>

    <!-- Popup สำหรับเพิ่มประเภทนโยบายใหม่-->
    <div  class="fixed top-0 left-0 flex items-center justify-center w-full h-full modal">
        <div class="z-50 w-11/12 p-5 mx-auto overflow-y-auto bg-white rounded shadow-lg modal-container md:max-w-md">

            <!--Header-->
            <div class="flex items-center justify-center w-full h-auto">
                <div class="flex items-start justify-start w-full h-auto py-2 text-xl font-bold">
                    <h3 class="text-lg font-semibold">
                       
                    </h3>
                </div>

                <div
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
                <form ref="addPolicyCategoryForm">
                    <label for="name" class="block my-3 text-gray-700 text-md">ประเภทนโยบาย</label>
                    <input type="text"
                        class="w-full px-3 py-2 leading-tight text-gray-700 border rounded shadow"
                        placeholder="กรุณากรอกข้อมูล" />
                    <div class="mt-2 text-sm text-red-500">
                       
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2">
                            <button type="submit"
                                class="w-full px-4 py-2 mt-4 font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 rounded-lg hover:bg-blue-700">
                                บันทึก
                            </button>
                        </div>
                        <div>
                            <button type="button"
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

<script setup>
import { ref, onMounted } from 'vue';
import http from '../../services/BackendService';
import Swal from 'sweetalert2';

const groups = ref([]);
const showModal = ref(false);
const isEdit = ref(false);
const searchQuery = ref('');
const searchResults = ref([]);
const showMemberModal = ref(false);
const viewingGroup = ref(null);
const viewingMembers = ref([]);

const form = ref({
    id: null,
    name: '',
    type: 'custom',
    members: []
});


</script>