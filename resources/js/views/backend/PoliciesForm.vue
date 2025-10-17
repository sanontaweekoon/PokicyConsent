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
                    <QuillEditor ref="quillRef" v-model="content" :options="quillOptions"
                        class="min-h-[240px] bg-white" />
                </div>
            </div>


            <div class="space-y-3 md:col-span-2 mt-5">
                <!-- Live Preview -->
                <div class="border rounded p-4 bg-gray-50">
                    <div class="text-xl text-gray-500 mb-2">ตัวอย่างเอกสาร (Preview)</div>
                    <button type="button" @click="printPreview" class="px-3 py-1 text-xs bg-zinc-200 rounded flex">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5" height="12" width="12"
                            viewBox="0 0 512 512">
                            <path
                                d="M64 64C64 28.7 92.7 0 128 0L341.5 0c17 0 33.3 6.7 45.3 18.7l42.5 42.5c12 12 18.7 28.3 18.7 45.3l0 37.5-384 0 0-80zM0 256c0-35.3 28.7-64 64-64l384 0c35.3 0 64 28.7 64 64l0 96c0 17.7-14.3 32-32 32l-32 0 0 64c0 35.3-28.7 64-64 64l-256 0c-35.3 0-64-28.7-64-64l0-64-32 0c-17.7 0-32-14.3-32-32l0-96zM128 416l0 32 256 0 0-96-256 0 0 64zM456 272a24 24 0 1 0 -48 0 24 24 0 1 0 48 0z" />
                        </svg>
                        <p class="pl-1">พิมพ์ตัวอย่าง</p>
                    </button>
                    <h3 class="text-lg font-semibold mb-2 my-3 text-zinc-700"> {{ form.title || '' }} </h3>

                    <div class="prose whitespace-pre-wrap" v-html="sanitizedHtml"></div>

                    <div class="mt-3 text-xs text-gray-500">
                        ประเภทนโยบาย: {{ categoryName || '-' }}
                        สถานะการประกาศ: {{ previewPublishAt || '-' }}
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

<!--CKEditor-->
<script setup>
import Quill from 'quill'
import { ref, onMounted, onBeforeUnmount, markRaw, computed, nextTick } from 'vue'
import http from "../../services/BackendService.js";
import DOMPurify from 'dompurify'

import { QuillEditor } from '@vueup/vue-quill'
import 'quill/dist/quill.snow.css'

/* computed */
const quillRef = ref(null) // Reference to Quill editor component
const content = ref('') // เก็บ text จาก Quill editor
const appFont = typeof window !== 'undefined' ? getComputedStyle(document.body).fontFamily || 'Kanit, sans-serif' : 'Kanit, sans-serif'

let _editable = null
let _quillKeydownHandler = null
let _quillChangeHandler = null
let _quillEditorChangeHandler = null
let _quillMutObserver = null

// หา Quill instance และ root element
function resolveQuill() {
    try {
        if (!quillRef.value) return { qu: null, root: document.querySelector('.ql-editor') }
       
        if (typeof quillRef.value.getEditor === 'function') {
            const maybe = quillRef.value.getEditor()
            if (maybe) return { qu: maybe, root: maybe.root || document.querySelector('.ql-editor') }
        }
        if (quillRef.value.editor) return { qu: quillRef.value.editor, root: quillRef.value.editor.root }
        if (quillRef.value.quill) return { qu: quillRef.value.quill, root: quillRef.value.quill.root }
    } catch (e) { e }
    // fallback เป็น DOM ของ .ql-editor
    return { qu: null, root: document.querySelector('.ql-editor') }
}

// ล้างค่าเมื่อ component ถูกลบ
onBeforeUnmount(() => {
    try {
        if (_editable && _quillKeydownHandler) {
            _editable.removeEventListener('keydown', _quillKeydownHandler, true)
        }
        try {
            const { qu } = resolveQuill()
            if (qu) {
                if (qu && typeof qu.off === 'function') {
                    if (_quillChangeHandler) {
                        qu.off('text-change', _quillChangeHandler)
                    }
                    if (_quillEditorChangeHandler) {
                        qu.off('editor-change', _quillEditorChangeHandler)
                    }
                }
            }
        } catch (e) { /* ignore */ }

        if (_quillMutObserver) {
            _quillMutObserver.disconnect()
            _quillMutObserver = null
        }
    } catch (e) {
        console.warn('Could not remove keydown listener for Quill editor', e)
    }
})

/**Quill editor ผูก event และ observer เพื่อให้ preview update realtime */
onMounted(async () => {
    await loadCategories();
    await nextTick()

    const resolved = resolveQuill()

    const instance = resolved.qu || (quillRef.value?.getEditor?.() ?? quillRef.value?.editor ?? quillRef.value?.quill) || null
    const root = resolved.root || document.querySelector('.ql-editor')

    const updateContentFromInstance = () => {
        try {
            const html = instance.root ? instance.root.innerHTML : (root ? root.innerHTML : '')
            // ถ้าไม่มีเนื้อหา ให้เก็บเป็นค่าว่างแทน <p><br></p>
            content.value = (html && html.trim() === '<p><br></p>') ? '' : (html || '')
        } catch (e) {
            /* ignore */
        }
    }

    if (instance && typeof instance.on === 'function') {
        _quillChangeHandler = () => updateContentFromInstance()
        instance.on('text-change', _quillChangeHandler)

        _quillEditorChangeHandler = () => updateContentFromInstance()
        instance.on('editor-change', _quillEditorChangeHandler)

        updateContentFromInstance()

    }

    // ใช้ MutationObserver เพื่อจับ class/attribute ของ Quill
    if (root) {
        try {
            // ถ้ายังไม่มี content ให้อ่านจาก DOM
            if (!content.value || content.value === '') {
                const inner = root.innerHTML || ''
                content.value = (inner && inner.trim() === '<p><br></p>') ? '' : inner
            }

            // จับการเปลี่ยนแปลงของ DOM
            _quillMutObserver = new MutationObserver(() => {
                try {
                    const inner = root.innerHTML || ''
                    content.value = (inner && inner.trim() === '<p><br></p>') ? '' : inner
                } catch (e) {
                    /* ignore */
                }
            })
            _quillMutObserver.observe(root, {
                childList: true,
                subtree: true,
                characterData: true,
                attributes: true,
                attributeFilter: ['class', 'style']
            })
        } catch (e) {
            console.warn('Could not attach MutationObserver to quill root', e)
        }
    }

    // ปรับฟอนต์ให้ตรงกับ Quill editor 
    if (root) {
        try {
            root.style.fontFamily = appFont
        } catch (e) { }
    }

    // ให้สามารถกด Tab ได้ใน Editor
    if (root) {
        _quillKeydownHandler = (evt) => {
            if (evt.key !== 'Tab') return
            // ป้องกัน browser กดแท็บแล้วเปลี่ยนตำแหน่ง
            evt.preventDefault && evt.preventDefault()

            try {
                const qu = instance || (quillRef.value?.getEditor?.() ?? quillRef.value?.editor ?? quillRef.value?.quill) || null
                if (qu && typeof qu.getSelection === 'function') {
                    const sel = qu.getSelection(true)
                    if (!sel) {
                        return
                    }

                    const formats = qu.getFormat(sel.index, sel.lenght || 0) || {}
                    // หาก format ที่ใช้อยู่ใน list setting ของ Quill ให้ใช้
                    if (formats.list) {
                        const currentIndent = formats.indent || 0
                        if (evt.shiftKey) {
                            const newIndent = Math.max(0, currentIndent - 1)
                            qu.format('indent', newIndent || false)
                        } else {
                            qu.format('indent', currentIndent + 1)
                        }
                    } else {
                        // ถ้าไม่ใช่ list ให้แทรก tab character แทน
                        if (evt.shiftKey) {
                            const [line, offsetInLine] = qu.getLine(sel.index)
                            const lineStart = sel.index - offsetInLine

                            const lineText = qu.getText(lineStart, offsetInLine)
                            if (!lineText) {
                                return
                            }

                            const match = /^\t| {1,4}/.exec(lineText)

                            if (match) {
                                const removeLen = match[0].length
                                qu.deleteText(lineStart, removeLen, 'user')
                                qu.setSelection(Math.max(lineStart, sel.index - removeLen), 0, 'user')
                            }
                        } else {
                            qu.insertText(sel.index, '\t', 'user')
                            qu.setSelection(sel.index + 1, 0, 'user')
                        }
                    }
                    return
                }
            } catch (e) {
                console.warn('Could not handle Tab key in Quill editor', e)
            }
        }
    }
})


try {
    const Font = Quill.import('formats/font')
    Font.whitelist = ['Kanit', 'sans-serif']
    Quill.register(Font, true)
} catch (e) {
    console.warn('Could not register custom font for Quill editor', e)
}

const quillOptions = {
    theme: 'snow',
    placeholder: 'พิมพ์เนื้อหานโยบายที่นี่...',
    modules: {
        toolbar: [
            ['bold', 'italic', 'underline', 'strike'],        // ปุ่มตัวหนา/เอียง/ขีดเส้น
            ['blockquote', 'code-block'],
            ['link', 'image', 'video', 'formula'],

            [{ 'header': 1 }, { 'header': 2 }],               // ขนาดหัวเรื่อง
            [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'list': 'check' }],
            [{ 'script': 'sub' }, { 'script': 'super' }],      // superscript/subscript
            [{ 'indent': '-1' }, { 'indent': '+1' }],          // ปุ่มย่อ/ขยาย indent
            [{ 'direction': 'rtl' }],                         // ปุ่มจัดแนวขวา-ซ้าย

            [{ 'size': ['small', false, 'large', 'huge'] }],  // ขนาดข้อความ
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

            [{ 'color': [] }, { 'background': [] }],         // สีตัวอักษร/พื้นหลัง
            [{ 'font': [] }],                               // ฟอนต์
            [{ 'align': [] }],                              // ปุ่มจัดแนวข้อความ

            ['clean']
        ],
        keyboard: {
            bindings: {
                tab: {
                    key: 9,
                    handler: function (range, context) {
                        const evt = arguments[2] // keydown event
                        if (evt && evt.shiftKey) {
                            // try outdent (decrease indent)
                            this.quill.format('indent', (context.format && context.format.indent) ? (context.format.indent - 1) : false)
                        } else {
                            // if in a list, increase indent; otherwise insert tab char
                            if (context.format && context.format['list']) {
                                this.quill.format('indent', (context.format.indent || 0) + 1)
                            } else {
                                this.quill.insertText(range.index, '\t', 'user')
                                this.quill.setSelection(range.index + 1, 0, 'user')
                            }
                        }
                        return false
                    }
                }
            }
        }
    },
    formats: ['header', 'font', 'size', 'bold', 'italic', 'underline', 'strike', 'list', 'bullet', 'indent', 'link', 'blockquote', 'code-block', 'script', 'align', 'color', 'background', 'table'],
}

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
const userById = ref({})

/* computed */
const categoryName = computed(() => categoriesById.value[form.value.category_id] ?? (categories.value.find(c => c.id === form.value.category_id)?.name || ''))
const previewPublishAt = computed(() => {
    if (!form.value.publish_date && !form.value.publish_time && !form.value.publish_at) return '-'
    if (form.value.publish_at) {
        try {
            const d = new Date(form.value.publish_at.replace(' ', 'T'))
            return d.toLocaleString()
        } catch {
            return form.value.publish_at
        }
    }

    try {
        const dt = `${form.value.publish_date || ''}T${form.value.publish_time || ''}`
        const d = new Date(dt)
        return isNaN(d.getTime()) ? '-' : d.toLocaleString()
    } catch {
        return '-'
    }
})

/**
* แปลง class/attribute ของ Quill เป็น inline style
* เพื่อให้แสดงผลใน preview/print ได้ถูกต้อง
*/

function transformQuillClassesToInline(html) {
    try {
        const parser = new DOMParser()
        const doc = parser.parseFromString(String(html || ''), 'text/html')
        if (!doc || !doc.body) return html

        // try read Delta attributes per-line from Quill instance
        const qu = quillRef.value?.getEditor?.() ?? quillRef.value?.editor ?? quillRef.value?.quill ?? null
        const lineAttrs = []
        if (qu && typeof qu.getContents === 'function') {
            try {
                const delta = qu.getContents()
                let lineIndex = 0
                ;(delta.ops || []).forEach(op => {
                    if (typeof op.insert === 'string') {
                        for (let i = 0; i < op.insert.length; i++) {
                            if (op.insert[i] === '\n') {
                                lineAttrs[lineIndex] = Object.assign({}, lineAttrs[lineIndex] || {}, op.attributes || {})
                                lineIndex++
                            }
                        }
                    }
                })
            } catch (e) {
                // ignore
            }
        }

        // นำ class ของ Quill มาแปลงเป็น inline styles ของต่อละบรรทัด
        const blocks = Array.from(doc.body.children)
        blocks.forEach((el, idx) => {
            const attrs = lineAttrs[idx] || {}
            if (attrs.align) {
                el.style.textAlign = attrs.align
            }
            if (typeof attrs.indent !== 'undefined') {
                const indentLevel = Number(attrs.indent) || 0
                el.style.marginLeft = `${indentLevel * 2}rem`
            }
        })

        // ถ้ายังมี class ql-align-* หรือ ql-indent-* ที่เหลืออยู่ ให้แปลงเป็น inline styles
        doc.querySelectorAll('.ql-align-center').forEach(el => el.style.textAlign = 'center')
        doc.querySelectorAll('.ql-align-right').forEach(el => el.style.textAlign = 'right')
        doc.querySelectorAll('.ql-align-justify').forEach(el => el.style.textAlign = 'justify')
        doc.querySelectorAll('.ql-align-left').forEach(el => el.style.textAlign = 'left')
        for (let i = 1; i <= 6; i++) {
            doc.querySelectorAll(`.ql-indent-${i}`).forEach(el => {
                el.style.marginLeft = `${i * 2}rem`
            })
        }

        return doc.body.innerHTML || html
    } catch (e) {
        console.warn('transformQuillClassesToInline failed', e)
        return html
    }
}

/* computed : sanitizedHtml ใช้สำหรับ v-html preview และ print */
const sanitizedHtml = computed(() => {
    let raw = ''

    // ถ้ามี content เป็น v-model ให้ใช้ค่านั้นก่อน
    if (typeof content.value === 'string' && content.value.trim() !== '') {
        raw = content.value
    } else {
        // ถ้าไม่มี ให้ลองอ่านจาก Quill editor instance
        try {
            const q = quillRef.value?.getEditor?.() ?? quillRef.value?.editor ?? quillRef.value?.quill ?? null
            if (q && q.root && q.root.innerHTML) {
                raw = q.root.innerHTML
            } else {
                const el = document.querySelector('.ql-editor')
                raw = el ? el.innerHTML : ''
            }
        } catch (e) {
            raw = ''
        }
    }

    // แปลง class/attribute ของ Quill เป็น inline style
    raw = transformQuillClassesToInline(raw)

    return DOMPurify.sanitize(raw, {
        USE_PROFILES: { html: true },
        ADD_ATTR: ['class', 'style']
    })
})

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


/** print preview */
function printPreview() {
    let rawHtml = ''
    try {
        // อ่าน raw HTML จาก Quill editor
        const q = quillRef.value?.getEditor?.() ?? quillRef.value?.editor ?? quillRef.value?.quill ?? null
        if (q && q.root && q.root.innerHTML) {
            rawHtml = q.root.innerHTML
        } else {
            rawHtml = sanitizedHtml.value || ''
        }
    } catch (e) {
        rawHtml = sanititzedHtml.value || ''
    }

    console.log('DEBUG rawHtml from quill:', rawHtml)

    // แปลง class/attribute ของ Quill เป็น inline style ก่อนพิมพ์
    const transformQuillClassesToInline = (html) => {
        try {
            const parser = new DOMParser()
            const doc = parser.parseFromString(html, 'text/html')
            if (!doc || !doc.body) {
                return html
            }

            // align
            doc.querySelectorAll('.ql-align-center').forEach(el => el.style.textAlign = 'center')
            doc.querySelectorAll('.ql-align-right').forEach(el => el.style.textAlign = 'right')
            doc.querySelectorAll('.ql-align-justify').forEach(el => el.style.textAlign = 'justify')
            doc.querySelectorAll('.ql-align-left').forEach(el => el.style.textAlign = 'left')

            for (let i = 1; i <= 6; i++) {
                doc.querySelectorAll(`.ql-indent-${i}`).forEach(el => {
                    el.style.marginLeft = `${i * 2}rem`
                })
            }

            return doc.body.innerHTML || html
        } catch (e) {
            console.warn('Could not transform Quill classes to inline styles', e)
            return html
        }
    }

    let printableHtml = transformQuillClassesToInline(rawHtml)

    // sanitize ก่อนนำไปแสดงในหน้าพิมพ์
    printableHtml = DOMPurify.sanitize(printableHtml, {
        USE_PROFILES: { html: true },
        ADD_ATTR: ['class', 'style']
    })

    const title = form.value.title || 'ไม่มีหัวข้อ'
    const type = categoryName.value || '-'
    const status = previewPublishAt.value || '-'

    // แปลง tab เป็น non-breaking spaces เพื่อรักษาการเยื้องเมื่อพิมพ์
    printableHtml = printableHtml.replace(/\t/g, '&nbsp;&nbsp;&nbsp;&nbsp;')

    const printWindow = window.open('', '_blank', 'width=800,height=600')
    if (!printWindow) {
        alert('ไม่สามารถเปิดหน้าต่างพรีวิวได้')
        return
    }

    // สร้างเอกสารสำหรับพิมพ์ — ใส่ CSS ที่จำเป็นให้แสดง align/indent
    printWindow.document.open()
    printWindow.document.write(`
    <html>
      <head>
        <meta charset="utf-8"/>
        <title>Preview - ${title}</title>
       <style>
            body{font-family:${appFont};padding:24px;color:#222}
            .content{line-height:1.6; white-space: pre-wrap; word-wrap: break-word;}
            
            .content .ql-align-center{ text-align:center !important; }
            .content .ql-align-right{ text-align:right !important; }
            .content .ql-align-justify{ text-align:justify !important; }
          </style>
      </head>
      <body>
        <h3>${title}</h3>
        <div class="meta">ประเภท: ${type} &nbsp;&nbsp; เผยแพร่: ${status}</div>
        <div class="content">${printableHtml}</div>
      </body>
    </html>
  `)

    // copy font จาก head มาด้วย
    try {
        const headNodes = Array.from(document.head.querySelectorAll('link[rel="stylesheet"], style'))
        headNodes.forEach(node => {
            printWindow.document.head.appendChild(node.cloneNode(true))
        })
    } catch (e) {
        console.warn('Could not copy styles to print window', e)
    }

    printWindow.document.close()
    printWindow.focus()
    printWindow.print()
}
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