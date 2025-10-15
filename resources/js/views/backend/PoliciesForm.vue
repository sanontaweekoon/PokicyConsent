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

                <div class="grid grid-cols-1">
                    <div class="flex my-5">
                        <label class="font-medium mr-3">เวลาประกาศ: </label>

                        <div class="flex flex-col">
                            <div class="flex items-center gap-2">
                                <input type="radio" id="option3" name="announce"
                                    class="accent-red-700 w-4 h-4 text-red-600 focus:ring-red-500" />
                                <label for="option3" class="text-gray-700">ประกาศตอนนี้</label>
                            </div>

                            <div class="flex items-center gap-2 mt-2">
                                <input type="radio" id="option4" name="announce"
                                    class="accent-red-700 w-4 h-4 text-red-600 focus:ring-red-500" />
                                <label for="option4" class="text-gray-700">
                                    <input type="date" class="rounded border px-3 py-2 mr-5" />
                                    <input type="time" class="rounded border px-3 py-2" />
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="block mb-1 font-medium">หัวข้อ *</label>
                        <input type="text" class="w-full rounded border px-3 py-2" placeholder="ระบุหัวข้อนโยบาย" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">ประเภทนโยบาย</label>
                        <select class="w-full rounded border px-3 py-2 bg-white">
                            <option :value="null">— เลือกประเภท —</option>
                            <option>

                            </option>
                        </select>
                    </div>
                </div>
            </div>


            <div class="space-y-3 md:col-span-2 mt-5">
                <label class="block mb-1 font-medium">ข้อมูลนโยบาย/มาตรการองค์กร *</label>
                <div>
                    <label class="block mb-1 font-medium"></label>
                    <div ref="editorEl"></div>
                </div>
            </div>

            <div class="space-y-3 md:col-span-2 mt-5">
                <!-- Live Preview -->
                <div class="border rounded p-4 bg-gray-50">
                    <div class="text-sm text-gray-500 mb-2">ตัวอย่างเอกสาร (Preview)</div>
                    <h3 class="text-lg font-semibold mb-2"></h3>
                    <div class="prose whitespace-pre-wrap">

                    </div>
                    <div class="mt-3 text-xs text-gray-500">
                        ประเภท:
                        สถานะ:
                    </div>
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
import { ref, onMounted, onBeforeUnmount, markRaw } from 'vue'

const editorEl = ref(null)
const content = ref('')

let editorInstance = null

const editorConfig = {
    placeholder: 'พิมพ์เนื้อหานโยบายที่นี่...',
    toolbar: {
        items: [
            'undo', 'redo', '|', 'heading', '|',
            'fontFamily', 'fontSize', 'fontColor', 'fontBackgroundColor', '|',
            'bold', 'italic', 'strikethrough', 'subscript', 'superscript', 'code', '|',
            'alignment', '|',
            'bulletedList', 'numberedList', 'todoList', 'outdent', 'indent', '|',
            'link', 'blockQuote', 'codeBlock'
        ],
        shouldNotGroupWhenFull: true
    },
    removePlugins: [
        'Image', 'ImageToolbar', 'ImageCaption', 'ImageStyle', 'AutoImage', 'ImageUpload', 'ImageInsert', 'PictureEditing',
        'CloudServices', 'CKBox', 'CKBoxUI', 'CKBoxUtils', 'CKBoxImageEdit', 'CKFinder', 'CKFinderUploadAdapter', 'EasyImage',
        'RealTimeCollaborativeComments', 'RealTimeCollaborativeTrackChanges', 'RealTimeCollaborativeRevisionHistory',
        'PresenceList', 'Comments', 'TrackChanges', 'TrackChangesData', 'RevisionHistory', 'WProofreader',
        'AIAssistant', 'ExportPdf', 'ExportWord', 'Pagination', 'Template', 'SlashCommand', 'DocumentOutline',
        'FormatPainter', 'MathType', 'MultiLevelList', 'TableOfContents', 'PasteFromOfficeEnhanced', 'CaseChange'
    ]
}

onMounted(async () => {
    const ClassicEditor = window.CKEDITOR?.ClassicEditor
    if (!ClassicEditor || !editorEl.value) return

    try {
        const inst = await ClassicEditor.create(editorEl.value, editorConfig)
        editorInstance = markRaw(inst)

        if (content.value) {
            editorInstance.setData(content.value)
        }

        editorInstance.model.document.on('change:data', () => {
            content.value = editorInstance.getData()
        })
    } catch (e) {
        console.error('CKEditor init failed:', e)
    }
})

onBeforeUnmount(() => {
    editorInstance?.destroy().catch(() => { })
    editorInstance = null
})
</script>

<style>
.ck-editor__editable_inline {
    min-height: 240px;
    max-height: 80vh;
    overflow: auto;
    resize: vertical;
}

:deep(.ck-editor__editable_inline) {
    min-height: 240px;
    max-height: 80vh;
    overflow: auto;
    resize: vertical;
}
</style>