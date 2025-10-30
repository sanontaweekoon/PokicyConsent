<template>
    <div class="flex items-center justify-between my-8">
        <button @click="openCreateModal"
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
                        <th class="px-4 py-3">สร้างเมื่อ</th>
                        <th class="px-4 py-3">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y">
                    <tr v-for="group in groups" :key="group.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium"> {{ group.name }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full" :class="{
                                'bg-blue-100 text-blue-800': group.type === 'department',
                                'bg-green-100 text-green-800': group.type === 'level',
                                'bg-gray-100 text-gray-800': group.type === 'custom',
                            }">
                                {{ getTypeLabel(group.type) }}
                            </span>
                        </td>

                        <td class="px-4 py-3"> {{ group.members_count }} คน</td>

                        <td class="px-4 py-3 text-sm"> {{ formatDate(group.created_at) }}</td>

                        <td class="px-4 py-3">
                            <div class="flex gap-1">

                                <button @click="viewMembers(group)"
                                    class="px-2 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 flex items-center gap-1"
                                    title="ดูสมาชิก">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                                        <path fill="#ffffff"
                                            d="M320 16a104 104 0 1 1 0 208 104 104 0 1 1 0-208zM96 88a72 72 0 1 1 0 144 72 72 0 1 1 0-144zM0 416c0-70.7 57.3-128 128-128 12.8 0 25.2 1.9 36.9 5.4-32.9 36.8-52.9 85.4-52.9 138.6l0 16c0 11.4 2.4 22.2 6.7 32L32 480c-17.7 0-32-14.3-32-32l0-32zm521.3 64c4.3-9.8 6.7-20.6 6.7-32l0-16c0-53.2-20-101.8-52.9-138.6 11.7-3.5 24.1-5.4 36.9-5.4 70.7 0 128 57.3 128 128l0 32c0 17.7-14.3 32-32 32l-86.7 0zM472 160a72 72 0 1 1 144 0 72 72 0 1 1 -144 0zM160 432c0-88.4 71.6-160 160-160s160 71.6 160 160l0 16c0 17.7-14.3 32-32 32l-256 0c-17.7 0-32-14.3-32-32l0-16z" />
                                    </svg>
                                    <p>สมาชิก</p>
                                </button>



                                <button @click="editGroup(group)"
                                    class="px-3 py-2 text-white bg-yellow-500 rounded hover:bg-yellow-600 flex items-center gap-1"
                                    title="แก้ไข">

                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                                        <path fill="#fafcff"
                                            d="M256.1 248a120 120 0 1 0 0-240 120 120 0 1 0 0 240zm-29.7 56c-98.5 0-178.3 79.8-178.3 178.3 0 16.4 13.3 29.7 29.7 29.7l196.5 0 10.9-54.5c4.3-21.7 15-41.6 30.6-57.2l67.3-67.3c-28-18.3-61.4-28.9-97.4-28.9l-59.4 0zM332.3 466.9l-11.9 59.6c-.2 .9-.3 1.9-.3 2.9 0 8 6.5 14.6 14.6 14.6 1 0 1.9-.1 2.9-.3l59.6-11.9c12.4-2.5 23.8-8.6 32.7-17.5l118.9-118.9-80-80-118.9 118.9c-8.9 8.9-15 20.3-17.5 32.7zm267.8-123c22.1-22.1 22.1-57.9 0-80s-57.9-22.1-80 0l-28.8 28.8 80 80 28.8-28.8z" />
                                    </svg>
                                    <p>แก้ไข</p>
                                </button>

                                <button @click="deleteGroup(group.id)"
                                    class="px-3 py-2 text-white bg-red-600 rounded hover:bg-red-700 flex items-center gap-1" title="ลบ">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 448 512">
                                        <path fill="#ffffff"
                                            d="M136.7 5.9L128 32 32 32C14.3 32 0 46.3 0 64S14.3 96 32 96l384 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-96 0-8.7-26.1C306.9-7.2 294.7-16 280.9-16L167.1-16c-13.8 0-26 8.8-30.4 21.9zM416 144L32 144 53.1 467.1C54.7 492.4 75.7 512 101 512L347 512c25.3 0 46.3-19.6 47.9-44.9L416 144z" />
                                    </svg>
                                    <p>ลบ</p>
                                </button>
                            </div>

                        </td>
                    </tr>

                    <tr v-if="groups.length === 0">
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            ยังไม่มีกลุ่มผู้รับนโยบาย
                        </td>
                    </tr>
                </tbody>
            </table>
            <!-- <VueAwesomePaginate v-model="currentPage" :total-items="total" :items-per-page="perPage"
                :max-pages-shown="7" :on-click="onPageChanged" /> -->
        </div>
    </div>

    <!-- Modal สร้าง/แก้ไขกลุ่มผู้รับ -->
    <Teleport to="body">
        <div v-if="showModal" @click="closeModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div @click.stop
                class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">

                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-xl font-medium">{{ isEdit ? 'แก้ไขกลุ่มผู้รับนโยบาย' : 'สร้างกลุ่มผู้รับนโยบาย'
                    }}
                    </h3>

                    <button @click="closeModal" class="text-2xl text-gray-500 hover:text-gray-700">
                        ✕
                    </button>
                </div>

                <!-- Content -->
                <div class="flex-1 p-6 overflow-y-auto">
                    <div class="space-y-4">
                        <!-- ชื่อกลุ่ม -->
                        <div>
                            <label class="block mb-1 text-sm font-medium">
                                ชื่อกลุ่ม <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.name" type="text"
                                class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-red-200"
                                placeholder="เช่น แผนก ICT, ผู้จัดการทุกแผนก" />
                        </div>

                        <!-- ประเภทกลุ่ม -->
                        <div>
                            <label class="block mb-1 text-sm font-medium">ประเภทกลุ่ม</label>
                            <select v-model="form.type"
                                class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-red-200">
                                <option value="custom">กำหนดเอง</option>
                                <option value="department">ตามแผนก</option>
                                <option value="level">ตามระดับ</option>
                            </select>
                        </div>

                        <!-- เพิ่มสมาชิก -->
                        <div class="pt-4 border-t">
                            <label class="block mb-2 text-sm font-medium">เพิ่มสมาชิกในกลุ่ม</label>

                            <!-- Tabs -->
                            <div class="flex mb-3 border-b">
                                <button @click="memberTab = 'search'" :class="[
                                    'px-4 py-2 font-medium',
                                    memberTab === 'search'
                                        ? 'border-b-2 border-red-600 text-red-600'
                                        : 'text-gray-500'
                                ]">
                                    ค้นหา
                                </button>

                                <button @click="memberTab = 'department'" :class="[
                                    'px-4 py-2 font-medium',
                                    memberTab === 'department'
                                        ? 'border-b-2 border-red-600 text-red-600'
                                        : 'text-gray-500'
                                ]">
                                    ตามแผนก
                                </button>

                                <button @click="loadAllEmployees" :class="[
                                    'px-4 py-2 font-medium',
                                    memberTab === 'all'
                                        ? 'border-b-2 border-red-600 text-red-600'
                                        : 'text-gray-500'
                                ]">
                                    ทั้งหมด
                                </button>
                            </div>

                            <!-- Tab ค้นหา -->
                            <div v-if="memberTab === 'search'" class="space-y-3">
                                <div class="flex gap-2">
                                    <input v-model="searchQuery" type="text" @keyup.enter="searchEmployees"
                                        class="flex-1 px-3 py-2 border rounded focus:ring-2 focus:ring-blue-100"
                                        placeholder="ค้นหาด้วย ชื่อ, Email, รหัสพนักงาน" />

                                    <button @click="searchEmployees"
                                        class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700">
                                        ค้นหา
                                    </button>
                                </div>
                            </div>

                            <!-- Tab: ตามแผนก -->
                            <div v-if="memberTab === 'department'" class="space-y-3">
                                <select v-model="selectedDepartment" @change="loadByDepartment"
                                    class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-red-200">
                                    <option value="">-- เลือกแผนก --</option>
                                    <option v-for="dept in departments" :key="dept" :value="dept">
                                        {{ dept }}
                                    </option>
                                </select>
                            </div>

                            <!-- ผลการค้นหา -->
                            <div v-if="searchResults.length > 0"
                                class="p-3 mb-3 overflow-y-auto bg-gray-50 border rounded max-h-64">
                                <div class="flex item-center justify-between mb-2">
                                    <p class="text-sm font-medium">
                                        ผลการค้นหา ({{ searchResults.length }} คน)
                                    </p>
                                    <button @click="addAllSearchResults"
                                        class="px-2 py-1 text-xs text-white bg-green-600 rounded hover:bg-green-700">
                                        เพิ่มทั้งหมด
                                    </button>
                                </div>
                                <div v-for="emp in searchResults" :key="emp.EmpCode"
                                    class="flex items-center justify-between px-2 py-2 rounded hover:bg-white">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium truncate">
                                            {{ emp.full_name }} ({{ emp.EmpCode }})
                                        </div>
                                    </div>

                                    <div class="text-xs text-gray-600 truncate">
                                        {{ emp.email }}
                                        <span v-if="emp.Department" class="ml-2 text-gray-500">
                                            • {{ emp.Department }}
                                        </span>
                                    </div>

                                    <button @click="addMember(emp)" v-if="!isMemberAdded(emp.email)"
                                        class="flex-shrink-0 px-3 py-1 ml-2 text-xs text-white bg-green-600 rounded hover:bg-green-700">
                                        เพิ่ม</button>
                                    <span v-else class="flex-shrink-0 ml-2 text-xs text-grey-500"><svg class="w-5 h-5"
                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                            <path fill="#04e000"
                                                d="M384 32c35.3 0 64 28.7 64 64l0 320c0 35.3-28.7 64-64 64L64 480c-35.3 0-64-28.7-64-64L0 96C0 60.7 28.7 32 64 32l320 0zM342 145.7c-10.7-7.8-25.7-5.4-33.5 5.3L189.1 315.2 137 263.1c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l72 72c5 5 11.9 7.5 18.8 7s13.4-4.1 17.5-9.8L347.3 179.2c7.8-10.7 5.4-25.7-5.3-33.5z" />
                                        </svg></span>
                                </div>
                            </div>

                            <!-- สมาชิกปัจจุบัน -->
                            <div class="p-3 overflow-y-auto bg-white border rounded max-h-80">
                                <div class="flex items-center justify-between pb-2 mb-2 bg-white border-b">
                                    <p class="text-sm font-medium">
                                        สมาชิกในกลุ่ม ({{ form.members.length }} คน)
                                    </p>

                                    <button v-if="form.members.length > 0" @click="clearAllMembers">ลบทั้งหมด</button>
                                </div>

                                <div v-if="form.members.length === 0" class="py-8 text-center text-gray-500">
                                    <p>ยังไม่มีสมาชิกในกลุ่ม</p>
                                    <p class="mt-1 text-xs">กรุณาค้นหาและเพิ่มสมาชิก</p>
                                </div>

                                <div v-for="(member, index) in form.members" :key="index"
                                    class="flex items-center justify-between px-2 py-2 border-b rounded last:border-0 hover:bg-gray-50">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium truncate">{{ member.name }}</div>
                                        <div class="text-sm text-gray-600 truncate">{{ member.email }}</div>
                                    </div>
                                    <button @click="removeMember(index)"
                                        class="flex-shrink-0 ml-2 text-red-600 hover:text-red-800">
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                                            <path fill="#cc0000"
                                                d="M136 128a120 120 0 1 1 240 0 120 120 0 1 1 -240 0zM48 482.3C48 383.8 127.8 304 226.3 304l59.4 0c98.5 0 178.3 79.8 178.3 178.3 0 16.4-13.3 29.7-29.7 29.7L77.7 512C61.3 512 48 498.7 48 482.3zM472 168l144 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-144 0c-13.3 0-24-10.7-24-24s10.7-24 24-24z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex justify-end gap-2 p-6 border-t bg-gray-50">
                    <button @click="closeModal" class="px-6 py-2 bg-gray-200 rounded hover:bg-gray-300">ยกเลิก</button>

                    <button @click="saveGroup" :disabled="!form.name || form.members.length === 0" :class="[
                        'px-6 py-2 rounded',
                        form.name && form.members.length > 0
                            ? 'bg-blue-600 text-white hover:bg-blue-700'
                            : 'bg-gray-300 text-gray-500 cursor-now-allowed'
                    ]">บันทึก</button>
                </div>
            </div>
        </div>
    </Teleport>

    <Teleport to="body">
        <div v-if="showMembersModal" @click="showMembersModal = false"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
            <div @click.stop
                class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[80vh] overflow-hidden flex flex-col">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-medium">สมาชิกในกลุ่ม: {{ viewingGroup?.name }}</h3>

                    <button @click="showMembersModal = false" class="text-2xl text-gray-500 hover:text-gray-700">
                        ✕
                    </button>
                </div>

                <div class="p-6 overflow-y-auto flex-1">
                    <div v-if="viewingMembers.length === 0" class="text-center py-8 text-gray-500">
                        ไม่มีสมาชิกในกลุ่มนี้
                    </div>
                    <div v-for="(member, index) in viewingMembers" :key="index"
                        class="flex items-center py-3 border-b last:border-0">
                        <div class="flex-1">
                            <div class="font-medium">{{ member.name }}</div>
                            <div class="font-sm text-gray-600">{{ member.email }}</div>
                        </div>
                    </div>
                </div>
                <div class="p-6 border-t bg-gray-50">
                    <button @click="showMembersModal = false"
                        class="w-full px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                        ปิด
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import http from '../../services/BackendService';
import Swal from 'sweetalert2';

const groups = ref([]);
const showModal = ref(false);
const isEdit = ref(false);
const memberTab = ref('search'); // 'search' | 'department' | 'all'
const searchQuery = ref('');
const searchResults = ref([]);
const departments = ref([]);
const selectedDepartment = ref('');
const showMembersModal = ref(false);
const viewingGroup = ref(null);
const viewingMembers = ref([]);

const form = ref({
    id: null,
    name: '',
    type: 'custom',
    members: [] // {{ email, name }}
});

function getTypeLabel(type) {
    const labels = {
        department: 'แผนก',
        level: 'ระดับ',
        custom: 'กำหนดเอง'
    };
    return labels[type] || type;
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('th-TH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

async function loadGroups() {
    try {
        const response = await http.get('/admin/recipient-groups');
        groups.value = response.data;
    } catch (error) {
        console.error('Error loading groups:', error);
        Swal.fire('ข้อผิดพลาด', 'ไม่สามารถโหลดข้อมูลกลุ่มได้', 'error');
    }
}

async function loadDepartments() {
    try {
        const response = await http.get('/admin/employees/departments');
        departments.value = response.data.data;
    } catch (error) {
        console.error('Error loading departments:', error);
        Swal.fire('ข้อผิดพลาด', 'ไม่สามารถโหลดข้อมูลแผนกได้', 'error');
    }
}

function openCreateModal() {
    form.value = {
        id: null,
        name: '',
        description: '',
        type: 'custom',
        members: []
    };
    isEdit.value = false;
    memberTab.value = 'search';
    searchQuery.value = '';
    searchResults.value = [];
    selectedDepartment.value = '';
    showModal.value = true;
}

async function editGroup(group) {
    try {
        const response = await http.get(`/admin/recipient-groups/${group.id}`);
        const data = response.data;

        form.value = {
            id: data.id,
            name: data.name,
            type: data.type,
            members: data.members.map(m => ({
                email: m.email,
                name: m.name
            }))
        };
        isEdit.value = true;
        memberTab.value = 'search';
        searchQuery.value = '';
        searchResults.value = [];
        selectedDepartment.value = '';
        showModal.value = true;
    } catch (e) {
        Swal.fire('ข้อผิดพลาด', 'ไม่สามารถโหลดข้อมูลกลุ่มได้', 'error');
    }
}

async function searchEmployees() {
    if (!searchQuery.value.trim()) {
        searchResults.value = [];
        return;
    }

    try {
        const response = await http.get('/admin/employees/search', {
            params: { q: searchQuery.value }
        });
        searchResults.value = response.data;
    } catch (e) {
        console.error('Search failed', e)
        Swal.fire('ข้อผิดพลาด', 'ไม่สามารถค้นหาได้', 'error');
    }
}

async function loadByDepartment() {
    if (!selectedDepartment.value) {
        searchResults.value = [];
        return;
    }

    try {
        Swal.fire({
            title: 'กำลังโหลด...',
            didOpen: () => Swal.showLoading(),
            allowOutsideClick: false,
        });

        const response = await http.get('/admin/employees/by-department', {
            params: { department: selectedDepartment.value }
        });
        searchResults.value = response.data;
        Swal.close();
    } catch (e) {
        Swal.close();
        console.error('Failed to load by department', e);
        Swal.fire('ข้อผิดพลาด', 'ไม่สามารถโหลดข้อมูลได้', 'error');
    }
}

async function loadAllEmployees() {
    const confirm = await Swal.fire({
        title: 'โหลดรายชื่อพนักงานทั้งหมด?',
        text: 'อาจใช้เวลาสักครู่',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'โหลด',
        cancelButtonText: 'ยกเลิก',
    });

    if (!confirm.isConfirmed) {
        return;
    }

    try {
        memberTab.value = 'all';

        Swal.fire({
            title: 'กำลังโหลด..',
            didOpen: () => Swal.showLoading(),
            allowOutsideClick: false,
        });

        const response = await http.get('/admin/employees');
        searchResults.value = response.data.data;

        Swal.close();
        Swal.fire({
            icon: 'success',
            title: `โหลดรายชื่อพนักงานทั้งหมด ${searchResults.value.length} คนสำเร็จ`,
            timer: 1500,
            showConfirmButton: false
        });
    } catch (e) {
        Swal.close();
        console.error('Failed to load all employees', e);
        Swal.fire('ข้อผิดพลาด', 'ไม่สามารถโหลดข้อมูลได้', 'error');
    }
}

function addMember(emp) {
    if (isMemberAdded(emp.email)) return;

    form.value.members.push({
        email: emp.email,
        name: emp.full_name
    });
}

function addAllSearchResults() {
    let added = 0;
    searchResults.value.forEach(emp => {
        if (!isMemberAdded(emp.email)) {
            form.value.members.push({
                email: emp.email,
                name: emp.full_name
            });
            added++;
        }
    });

    if (added > 0) {
        Swal.fire({
            icon: 'success',
            title: `เพิ่มสมาชิก ${added} คน`,
            timer: 1500,
            showConfirmButton: false
        });
    }
}

function removeMember(index) {
    form.value.members.splice(index, 1);
}

function isMemberAdded(email) {
    return form.value.members.some(m => m.email === email);
}

async function clearAllMembers() {
    const confirm = await Swal.fire({
        title: 'ลบสมาชิกทั้งหมด?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก',
    });

    if (confirm.isConfirmed) {
        form.value.members = [];
    }
}

async function saveGroup() {
    if (!form.value.name.trim()) {
        Swal.fire('กรุณาระบุชื่อกลุ่ม', '', 'warning');
        return;
    }

    if (form.value.members.length === 0) {
        Swal.fire('กรุณาเพิ่มสมาชิกอย่างน้อย 1 คน', '', 'warning');
        return;
    }

    try {
        const payload = {
            name: form.value.name,
            type: form.value.type,
            members: form.value.members
        };

        if (isEdit.value) {
            await http.put(`/admin/recipient-groups/${form.value.id}`, payload);
            Swal.fire('สำเร็จ', 'แก้ไขกลุ่มเรียบร้อย', 'success');
        } else {
            await http.post('/admin/recipient-groups', payload);
            Swal.fire('สำเร็จ', 'สร้างกลุ่มเรียบร้อย', 'success');
        }

        closeModal();
        loadGroups();
    } catch (e) {
        console.error('Failed to save group', e);
        Swal.fire('ข้อผิดพลาด', e.response?.data?.message || 'ไม่สามารถบันทึกได้', 'error');
    }
}

async function deleteGroup(id) {
    const confirm = await Swal.fire({
        title: 'ยืนยันการลบ',
        text: 'การลบกลุ่มนี้จะไม่สามารถกู้คืนได้',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก',
    });

    if (!confirm.isConfirmed) {
        return;
    }

    try {
        await http.delete(`/admin/recipient-groups/${id}`);
        Swal.fire('ลบสำเร็จ', '', 'success');
        loadGroups();
    } catch (e) {
        Swal.fire('ข้อผิดพลาด', 'ไม่สามารถลบกลุ่มได้', 'error');
    }
}

async function viewMembers(group) {
    try {
        const response = await http.get(`/admin/recipient-groups/${group.id}`);
        viewingGroup.value = group;
        viewingMembers.value = response.data.members;
        showMembersModal.value = true;
    } catch (e) {
        Swal.fire('ข้อผิดพลาด', 'ไม่สามารถโหลดข้อมูลสมาชิกได้', 'error');
    }
}


function closeModal() {
    showModal.value = false;
    form.value = {
        id: null,
        name: '',
        description: '',
        type: 'custom',
        members: []
    };
    searchQuery.value = '';
    searchResults.value = [];
    selectedDepartment.value = '';
}

onMounted(async () => {
    await loadGroups();
    await loadDepartments();
});
</script>