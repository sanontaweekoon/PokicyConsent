<template>
    <div class="flex items-center space-y-6 my-5">
        <!-- ฟอร์ม -->
        <form class="container mx-auto">
            <!-- ซ้าย -->
            <div class="space-y-2">
                <div class="grid grid-cols-1">
                    <div class=" flex ">
                        <label class="font-medium mr-3">ผู้รับนโยบาย: </label>

                        <div class="flex flex-col">
                            <div class="flex items-center gap-2">
                                <input type="radio" id="option1" name="recipient"
                                    class="accent-red-700 w-4 h-4 text-red-600 focus:ring-red-500" />
                                <label for="option1" class="text-gray-700">ทุกคน</label>
                            </div>
                            <div class="flex items-center gap-2 mt-2">
                                <input type="radio" id="option2" name="recipient"
                                    class="accent-red-700 w-4 h-4 text-red-600 focus:ring-red-500" />
                                <label for="option2" class="text-gray-700">กำหนดเป้าหมาย</label>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="grid grid-rows-1">
                    <div class="flex my-5">
                        <label class="font-medium mr-3">เวลาประกาศ: </label>

                        <div class="flex flex-col">
                            <div class="flex items-center gap-2">
                                <input type="radio" id="option3" name="announce"
                                    class="accent-red-700 w-4 h-4 text-red-600 focus:ring-red-500" />
                                <label for="option3" class="text-gray-700">ประกาศตอนนี้</label>
                            </div>

                            <div class="grid grid-cols-2">
                                <div class="flex items-center gap-2 mt-2">
                                    <input type="radio" id="option4" name="announce"
                                        class="accent-red-700 w-4 h-4 text-red-600 focus:ring-red-500" />
                                    <label for="option4" class="text-gray-700">
                                        <div class="flex flex-row">
                                            <div class="flex flex-col">
                                                <label class="my-1 text-gray-500 text-xs" for="">วันที่เผยแพร่</label>
                                                <input type="date" v-model="form.publish_date"
                                                    class="rounded border px-3 py-2 mr-5" />
                                            </div>
                                            <div class="flex flex-col">
                                                <label class="my-1 text-gray-500 text-xs" for="">เวลาเผยแพร่</label>
                                                <input type="time" v-model="form.publish_time"
                                                    class="rounded border px-3 py-2" />
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="block mb-1 font-medium">หัวข้อ *</label>
                        <input type="text" v-model="form.title" class="w-full rounded border px-3 py-2"
                            placeholder="ระบุหัวข้อนโยบาย" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">ประเภทนโยบาย *</label>
                        <select v-model="form.category_id" class="w-full rounded border px-3 py-2 bg-white">
                            <option :value="null">— เลือกประเภท —</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id"> {{ c.name }}</option>
                        </select>
                        <small v-if="catLoading" class="text-grey 500">กำลังโหลด...</small>
                    </div>
                </div>
            </div>


            <div class="space-y-3 md:col-span-2 mt-5">
                <label class="block mb-1 font-medium">ข้อมูลนโยบาย/มาตรการองค์กร *</label>
                <div>
                    <label class="block mb-1 font-medium"></label>
                    <Editor v-model="content" :init="tinymceInit" @init="onEditorInit" :tinymce-script-src="'/tinymce/tinymce.min.js'" />
                </div>
            </div>


            <div class="flex items-center justify-center w-full my-5 space-x-5">
                <button class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">
                    ยกเลิก
                </button>
                <button class="px-4 py-2 rounded-lg bg-zinc-500 text-white hover:bg-zinc-700">
                    บันทึกฉบับร่าง
                </button>
                <button class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700">
                    ประกาศ
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted } from 'vue'
import Editor from '@tinymce/tinymce-vue'
import DOMPurify from 'dompurify'
import http from "../../services/BackendService.js";

const content = ref('') // เก็บ text จาก TinyMCE

function onEditorInit(_evt, editor) {
    editorRef.value = editor
}

const appFont = typeof window !== 'undefined' ? getComputedStyle(document.body).fontFamily || 'Kanit, sans-serif' : 'Kanit, sans-serif'

const editorRef = ref(null)

/** form & lists */
const form = ref({
    title: '',
    code: '',
    category_id: null,
    publish_at: '',
    publish_date: '',
    publish_time: '',
})

const categories = ref([])
const catLoading = ref(false)
const categoriesById = ref({})


onMounted(async () => {
    await loadCategories()
    await nextTick()
})

const tinymceInit = {
    base_url: '/tinymce',
    suffix: '.min',
    height: 600,
    menubar: 'file edit view insert format table tools help',
    plugins: 'table lists link autolink code preview fullscreen',
    toolbar: [
        'undo redo | blocks fontfamily fontsize | bold italic underline |',
        'alignleft aligncenter alignright alignjustify | numlist bullist outdent indent |',
        'link | table | removeformat | preview fullscreen | code'
    ].join(' '),
    table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol | tablesmergecells tablesplitcells',
    branding: false,
    content_style: `
    body { font-family: ${appFont}; line-height: 1.6; }
    table { border-collapse: collapse; width: 100%; }
    table, th, td { border: 1px solid #ccc; }
    th, td { padding: 6px; }
  `,
}


/** load categories */
async function loadCategories() {
    catLoading.value = true
    try {
        const res = await http.get('/admin/policy-categories', { params: { per: 0 } });
        categories.value = Array.isArray(res.data) ? res.data : [];
        categoriesById.value = {}
        categories.value.forEach(c => { categoriesById.value[c.id] = c.name })
    } finally {
        catLoading.value = false
    }
}

</script>
