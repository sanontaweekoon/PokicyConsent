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
                                <input type="radio" id="option1" name="recipient" value="all"
                                    class="accent-red-700 w-4 h-4 text-red-600 focus:ring-red-500" />
                                <label for="option1" class="text-gray-700">ทุกคน</label>
                            </div>
                            <div class="flex items-center gap-2 mt-2">
                                <input type="radio" id="option2" name="recipient" value="group"
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
                                <input type="radio" id="option3" name="announce" value="now" v-model="announce"
                                    class="accent-red-700 w-4 h-4 text-red-600 focus:ring-red-500" />
                                <label for="option3" class="text-gray-700">ประกาศตอนนี้</label>
                            </div>

                            <div class="grid grid-cols-2">
                                <div class="flex items-center gap-2 mt-2">
                                    <input type="radio" id="option4" name="announce" value="scheduled"
                                        v-model="announce"
                                        class="accent-red-700 w-4 h-4 text-red-600 focus:ring-red-500" />
                                    <label for="option4" class="text-gray-700">
                                        <div class="flex flex-row">
                                            <div class="flex flex-col">
                                                <label class="my-1 text-gray-500 text-xs" for="">วันที่เผยแพร่</label>
                                                <input type="date" v-model="form.publish_date"
                                                    class="rounded border px-3 py-2 mr-5" />
                                                <div v-if="publishErrors.publish_date"
                                                    class="mt-2 text-sm text-red-500">
                                                    {{ publishErrors.publish_date }}
                                                </div>
                                            </div>
                                            <div class="flex flex-col">
                                                <label class="my-1 text-gray-500 text-xs" for="">เวลาเผยแพร่</label>
                                                <input type="time" v-model="form.publish_time"
                                                    class="rounded border px-3 py-2" />
                                                <div v-if="publishErrors.publish_time"
                                                    class="mt-2 text-sm text-red-500">
                                                    {{ publishErrors.publish_time }}
                                                </div>
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
                        <div v-if="publishErrors.title" class="mt-2 text-sm text-red-500">
                            {{ publishErrors.title }}
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">ประเภทนโยบาย *</label>
                        <select v-model="form.category_id" class="w-full rounded border px-3 py-2 bg-white">
                            <option :value="null">— เลือกประเภท —</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id"> {{ c.name }}</option>
                        </select>
                        <div v-if="publishErrors.category_id" class="mt-2 text-sm text-red-500">
                            {{ publishErrors.category_id }}
                        </div>
                        <small v-if="catLoading" class="text-grey 500">กำลังโหลด...</small>
                    </div>
                </div>
            </div>


            <div class="space-y-3 md:col-span-2 mt-5">
                <div class="flex">
                    <label class=" mb-1 font-medium flex">ข้อมูลนโยบาย/มาตรการองค์กร *</label>
                    <div v-if="publishErrors.description" class="my-auto text-sm text-red-500 mx-3">
                        {{ publishErrors.description }}
                    </div>
                </div>
                <div>
                    <Editor v-model="content" :init="tinymceInit" @init="onEditorInit"
                        :tinymce-script-src="'/tinymce/tinymce.min.js'" />

                </div>
            </div>





            <div class="flex items-center justify-center w-full my-5 space-x-5">
                <button type="button" @click="cancel"
                    class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">
                    ยกเลิก
                </button>
                <button type="button" @click="saveDraft"
                    class="px-4 py-2 rounded-lg bg-zinc-500 text-white hover:bg-zinc-700">
                    บันทึกฉบับร่าง
                </button>
                <button type="button" @click="publishPolicy"
                    class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700">
                    ประกาศ
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import Editor from '@tinymce/tinymce-vue'
import DOMPurify from 'dompurify'
import http from "../../services/BackendService.js";
import useVuelidate from '@vuelidate/core';
import { required, helpers, requiredIf } from '@vuelidate/validators';

const content = ref('') // เก็บ text จาก TinyMCE
const editorRef = ref(null)
const announce = ref('') // now | scheduled

const route = useRoute()
const saving = ref(false)

const publishErrors = ref({})

function clearPublishErrors() {
    publishErrors.value = {}
}

function collectValidationErrors() {
    clearPublishErrors()
    const v = v$.value
    alert('กรุณากรอกข้อมูลให้ครบถ้วนก่อนประกาศนโยบาย')
    for (const key of Object.keys(v)) {
        if (v[key] && v[key].$errors && v[key].$errors.length) {
            publishErrors.value[key] = v[key].$errors[0].$message || 'ข้อมูลไม่ถูกต้อง'
        }
    }
    if (Object.keys(publishErrors.value).length === 0) {
        publishErrors.value._general = 'กรุณากรอกข้อมูลให้ครบถ้วน'
    }
    publishErrors.value = { ...publishErrors.value }
}

// Query categories
const categories = ref([])
const catLoading = ref(false)
const categoriesById = ref({})

const isEdit = computed(() => {
    return !!form.value.id
})

function onEditorInit(_evt, editor) {
    editorRef.value = editor
}

/** form & lists */
const form = ref({
    title: '',
    description: '',
    category_id: null,
    publish_at: '',
    publish_date: '',
    publish_time: '',
})

const rules = {
    title: { required: helpers.withMessage('กรุณาระบุหัวข้อ', required) },
    category_id: { required: helpers.withMessage('กรุณาเลือกประเภทนโยบาย', required) },
    description: { required: helpers.withMessage('กรุณาใส่เนื้อหานโยบาย', required) },
    publish_date: {
        required: helpers.withMessage('กรุณาเลือกวันที่เผยแพร่', requiredIf(() => announce.value === 'scheduled'))
    },
    publish_time: {
        required: helpers.withMessage('กรุณาระบุเวลาเผยแพร่', requiredIf(() => announce.value === 'scheduled'))
    }
}

const v$ = useVuelidate(rules, form)

const tinymceInit = {
    base_url: '/tinymce',
    suffix: '.min',
    height: 500,
    promotion: false,
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
    @import url('https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap');
    body { font-family: 'Kanit', sans-serif; line-height: 1.6; }
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

function getEditorHtml() {
    try {
        const raw = editorRef.value.getContent?.({ format: 'html' }) ?? content.value ?? ''
        return DOMPurify.sanitize(raw, {
            USE_PROFILES: { html: true },
            ADD_TAGS: ['table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th', 'col', 'colgroup'],
            ADD_ATTR: ['class', 'style', 'colspan', 'rowspan', 'width', 'data-*']
        })
    } catch (e) {
        return '<p>เกิดข้อผิดพลาดในการดึงข้อมูล</p>'
    }
}

async function saveDraft() {
    saving.value = true
    try {
        form.value.description = getEditorHtml()
        v$.value.$touch()
        if (v$.value.$invalid) {
            alert('กรุณากรอกข้อมูลให้ครบถ้วนก่อนบันทึกฉบับร่าง')
            return
        }
        const payload = {
            title: form.value.title,
            category_id: form.value.category_id,
            description: getEditorHtml(),
            publish_at: form.value.publish_date && form.value.publish_time ? `${form.value.publish_date} ${form.value.publish_time}` : null,
            publish_date: form.value.publish_date,
            publish_time: form.value.publish_time,
            status: 'draft'
        }
        let response
        if (isEdit.value) {
            response = await http.put(`/admin/policies/${form.value.id}`, payload)
        } else {
            response = await http.post('/admin/policies', payload)
        }
        route.push({ path: '/backend/policies' })
    } catch (e) {
        console.error('saveDraft failed:', e)
        alert('เกิดข้อผิดพลาดในการบันทึกฉบับร่าง')
    } finally {
        saving.value = false
    }
}

async function publishPolicy() {
    saving.value = true
    try {
        form.value.description = getEditorHtml()
        clearPublishErrors()
        v$.value.$touch()
        if (v$.value.$invalid) {
            collectValidationErrors()
            return
        }
        const payload = {
            title: form.value.title,
            category_id: form.value.category_id,
            description: getEditorHtml(),
            publish_at: form.value.publish_date && form.value.publish_time ? `${form.value.publish_date} ${form.value.publish_time}` : null,
            publish_date: form.value.publish_date,
            publish_time: form.value.publish_time,
            status: 'active'
        }
        console.log('publishPolicy payload', payload)

        const response = await http.post('/admin/policies', payload)
        console.log('publishPolicy response', response && response.data)
        alert('ประกาศสำเร็จ')
    } catch (e) {
        console.error('publishPolicy failed:', e)
        alert('เกิดข้อผิดพลาดในการประกาศนโยบาย')
    } finally {
        saving.value = false
    }
}

function cancel() {
    route.back()
}

onMounted(async () => {
    await loadCategories()
    await nextTick()
    const id = route.params.id || null
    if (id) await loadPolicy(id)
})

</script>
