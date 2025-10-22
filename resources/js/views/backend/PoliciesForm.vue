<template>
  <div class="flex items-center my-5 space-y-6">
    <!-- ฟอร์ม -->
    <form class="container mx-auto">
      <!-- ซ้าย -->
      <div class="space-y-2">
        <div class="grid grid-cols-1">
          <div class="flex">
            <label class="mr-3 font-medium">ผู้รับนโยบาย: </label>

            <div class="flex flex-col">
              <div class="flex items-center gap-2">
                <input
                  type="radio"
                  id="option1"
                  name="recipient"
                  value="all"
                  class="w-4 h-4 text-red-600 accent-red-700 focus:ring-red-500"
                />
                <label for="option1" class="text-gray-700">ทุกคน</label>
              </div>
              <div class="flex items-center gap-2 mt-2">
                <input
                  type="radio"
                  id="option2"
                  name="recipient"
                  value="group"
                  class="w-4 h-4 text-red-600 accent-red-700 focus:ring-red-500"
                />
                <label for="option2" class="text-gray-700">กำหนดเป้าหมาย</label>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-rows-1">
          <div class="flex my-5">
            <label class="mr-3 font-medium">เวลาประกาศ: </label>

            <div class="flex flex-col">
              <div class="flex items-center gap-2">
                <input
                  type="radio"
                  id="option3"
                  name="announce"
                  value="now"
                  v-model="announce"
                  class="w-4 h-4 text-red-600 accent-red-700 focus:ring-red-500"
                />
                <label for="option3" class="text-gray-700">ประกาศตอนนี้</label>
              </div>

              <div class="grid grid-cols-2">
                <div class="flex items-center gap-2 mt-2">
                  <input
                    type="radio"
                    id="option4"
                    name="announce"
                    value="scheduled"
                    v-model="announce"
                    class="w-4 h-4 text-red-600 accent-red-700 focus:ring-red-500"
                  />
                  <label for="option4" class="text-gray-700">
                    <div class="flex flex-row">
                      <div class="flex flex-col">
                        <label class="my-1 text-xs text-gray-500" for=""
                          >วันที่เผยแพร่</label
                        >
                        <input
                          type="date"
                          v-model="form.publish_date"
                          :disabled="announce !== 'scheduled'"
                          class="px-3 py-2 mr-5 border rounded"
                        />
                        <div
                          v-if="publishErrors.publish_date"
                          class="mt-2 text-sm text-red-500"
                        >
                          {{ publishErrors.publish_date }}
                        </div>
                      </div>
                      <div class="flex flex-col">
                        <label class="my-1 text-xs text-gray-500" for=""
                          >เวลาเผยแพร่</label
                        >
                        <input
                          type="time"
                          v-model="form.publish_time"
                          :disabled="announce !== 'scheduled'"
                          class="px-3 py-2 border rounded"
                        />
                        <div
                          v-if="publishErrors.publish_time"
                          class="mt-2 text-sm text-red-500"
                        >
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
            <input
              type="text"
              v-model="form.title"
              class="w-full px-3 py-2 border rounded"
              placeholder="ระบุหัวข้อนโยบาย"
            />
            <div v-if="publishErrors.title" class="mt-2 text-sm text-red-500">
              {{ publishErrors.title }}
            </div>
          </div>

          <div>
            <label class="block mb-1 font-medium">ประเภทนโยบาย *</label>
            <select
              v-model="form.category_id"
              class="w-full px-3 py-2 bg-white border rounded"
            >
              <option :value="null">— เลือกประเภท —</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">
                {{ c.name }}
              </option>
            </select>
            <div
              v-if="publishErrors.category_id"
              class="mt-2 text-sm text-red-500"
            >
              {{ publishErrors.category_id }}
            </div>
            <small v-if="catLoading" class="text-grey 500">กำลังโหลด...</small>
          </div>
        </div>
      </div>

      <div class="mt-5 space-y-3 md:col-span-2">
        <div class="flex">
          <label class="flex mb-1 font-medium"
            >ข้อมูลนโยบาย/มาตรการองค์กร *</label
          >
          <div
            v-if="publishErrors.description"
            class="mx-3 my-auto text-sm text-red-500"
          >
            {{ publishErrors.description }}
          </div>
        </div>
        <div>
          <Editor
            v-model="content"
            :init="tinymceInit"
            @init="onEditorInit"
            :tinymce-script-src="'/tinymce/tinymce.min.js'"
          />
        </div>
      </div>

      <div class="flex items-center justify-center w-full my-5 space-x-5">
        <button
          type="button"
          @click="cancel"
          class="px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700"
        >
          ยกเลิก
        </button>
        <button
          type="button"
          @click="saveDraft"
          class="px-4 py-2 text-white rounded-lg bg-zinc-500 hover:bg-zinc-700"
        >
          บันทึกฉบับร่าง
        </button>
        <button
          type="button"
          @click="publishPolicy"
          class="px-4 py-2 text-white bg-green-600 rounded-lg hover:bg-green-700"
        >
          ประกาศ
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, watch } from "vue";
import { useRouter, useRoute } from "vue-router";
import Editor from "@tinymce/tinymce-vue";
import DOMPurify from "dompurify";
import http from "../../services/BackendService.js";
import useVuelidate from "@vuelidate/core";
import { required, helpers, requiredIf } from "@vuelidate/validators";
import Swal from "sweetalert2";

const content = ref(""); // เก็บ text จาก TinyMCE
const editorRef = ref(null);
const announce = ref(""); // now | scheduled

const route = useRoute();
const router = useRouter();
const saving = ref(false);

const publishErrors = ref({});

function clearPublishErrors() {
  publishErrors.value = {};
}

function collectValidationErrors() {
  clearPublishErrors();
  const v = v$.value;
  alert("กรุณากรอกข้อมูลให้ครบถ้วนก่อนประกาศนโยบาย");
  for (const key of Object.keys(v)) {
    if (v[key] && v[key].$errors && v[key].$errors.length) {
      publishErrors.value[key] =
        v[key].$errors[0].$message || "ข้อมูลไม่ถูกต้อง";
    }
  }
  if (Object.keys(publishErrors.value).length === 0) {
    publishErrors.value._general = "กรุณากรอกข้อมูลให้ครบถ้วน";
  }
  publishErrors.value = { ...publishErrors.value };
}

// Query categories
const categories = ref([]);
const catLoading = ref(false);
const categoriesById = ref({});

const isEdit = computed(() => {
  return !!form.value.id;
});

function onEditorInit(_evt, editor) {
  editorRef.value = editor;
}

/** form & lists */
const form = ref({
  title: "",
  description: "",
  category_id: null,
  publish_at: "",
  publish_date: "",
  publish_time: "",
});

const rules = {
  title: { required: helpers.withMessage("กรุณาระบุหัวข้อ", required) },
  category_id: {
    required: helpers.withMessage("กรุณาเลือกประเภทนโยบาย", required),
  },
  description: {
    required: helpers.withMessage("กรุณาใส่เนื้อหานโยบาย", required),
  },
  publish_date: {
    required: helpers.withMessage(
      "กรุณาเลือกวันที่เผยแพร่",
      requiredIf(() => announce.value === "scheduled")
    ),
  },
  publish_time: {
    required: helpers.withMessage(
      "กรุณาระบุเวลาเผยแพร่",
      requiredIf(() => announce.value === "scheduled")
    ),
  },
};

const v$ = useVuelidate(rules, form);

const tinymceInit = {
  base_url: "/tinymce",
  suffix: ".min",
  height: 500,
  promotion: false,
  menubar: "file edit view insert format table tools help",
  plugins: "table lists link autolink code preview fullscreen",
  toolbar: [
    "undo redo | blocks fontfamily fontsize | bold italic underline |",
    "alignleft aligncenter alignright alignjustify | numlist bullist outdent indent |",
    "link | table | removeformat | preview fullscreen | code",
  ].join(" "),
  table_toolbar:
    "tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol | tablesmergecells tablesplitcells",
  branding: false,
  content_style: `
    @import url('https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap');
    body { font-family: 'Kanit', sans-serif; line-height: 1.6; }
    table { border-collapse: collapse; width: 100%; }
    table, th, td { border: 1px solid #ccc; }
    th, td { padding: 6px; }
  `,
};

/** load categories */
async function loadCategories() {
  catLoading.value = true;
  try {
    const res = await http.get("/admin/policy-categories", {
      params: { per: 0 },
    });
    categories.value = Array.isArray(res.data) ? res.data : [];
    categoriesById.value = {};
    categories.value.forEach((c) => {
      categoriesById.value[c.id] = c.name;
    });
  } finally {
    catLoading.value = false;
  }
}

function getEditorHtml() {
  try {
    const raw =
      editorRef.value.getContent?.({ format: "html" }) ?? content.value ?? "";
    return DOMPurify.sanitize(raw, {
      USE_PROFILES: { html: true },
      ADD_TAGS: [
        "table",
        "thead",
        "tbody",
        "tfoot",
        "tr",
        "td",
        "th",
        "col",
        "colgroup",
      ],
      ADD_ATTR: ["class", "style", "colspan", "rowspan", "width", "data-*"],
    });
  } catch (e) {
    return "<p>เกิดข้อผิดพลาดในการดึงข้อมูล</p>";
  }
}

async function saveDraft() {
  saving.value = true;
  try {
    form.value.description = getEditorHtml();
    v$.value.$touch();
    if (v$.value.$invalid) {
      alert("กรุณากรอกข้อมูลให้ครบถ้วนก่อนบันทึกฉบับร่าง");
      return;
    }

    // ถ้าเลือก scheduled ให้ validate วันที่และเวลา
    if (announce.value === "scheduled") {
      if (v$.value.$invalid) {
        alert("กรุณากรอกข้อมูลให้ครบถ้วน (วันที่ / เวลา) ก่อนบันทึกฉบับร่าง");
        return;
      }
    } else {
      // ถ้าเลิอก now ให้ล validat แค่ title, category , description
      if (
        !form.value.title ||
        !form.value.category_id ||
        !form.value.description
      ) {
        alert("กรุณากรอกข้อมูลให้ครบถ้วนก่อนบันทึกฉบับร่าง");
        return;
      }
    }

    const payload = {
      title: form.value.title,
      category_id: form.value.category_id,
      description: getEditorHtml(),
      status: "draft",
    };

    //  ตรวจสอบว่าจะตั้งเวลาประกาศหรือไม่
    if (
      announce.value === "scheduled" &&
      form.value.publish_date &&
      form.value.publish_time
    ) {
      // draft พร้อมตั้งเวลาประกาศ
      payload.publish_date = form.value.publish_date;
      payload.publish_time = form.value.publish_time;
      payload.publish_at = `${payload.publish_date} ${payload.publish_time}`;
    } else {
      // draft ไม่ตั้งเวลาประกาศ
      payload.publish_at = null;
      payload.publish_date = null;
      payload.publish_time = null;
    }

    let response;
    if (isEdit.value) {
      response = await http.put(`/admin/policies/${form.value.id}`, payload);
    } else {
      response = await http.post("/admin/policies", payload);
    }

    const Toast = Swal.mixin({
      toast: true,
      position: "top-end",
      timer: 1500,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.addEventListener("mouseenter", Swal.stopTimer);
        toast.addEventListener("mouseleave", Swal.resumeTimer);
      },
    });

    const message = announce.value === 'scheduled' ? 'บันทึกฉบับร่างพร้อมเวลาประกาศเรียบร้อยแล่ว' : 'บันทึกฉบับร่างเรียบร้อยแล้ว';
    Toast.fire({
      icon: "success",
      title: message,
    }).then(() => {
      router.push({ name: "Policies" });
    });
  } catch (e) {
    console.error("saveDraft failed:", e);
    alert("เกิดข้อผิดพลาดในการบันทึกฉบับร่าง");
  } finally {
    saving.value = false;
  }
}

async function publishPolicy() {
  saving.value = true;
  try {
    form.value.description = getEditorHtml();
    clearPublishErrors();
    v$.value.$touch();
    if (v$.value.$invalid) {
      collectValidationErrors();
      return;
    }
    const payload = {
      title: form.value.title,
      category_id: form.value.category_id,
      description: getEditorHtml(),
      publish_at: null,
      publish_date: form.value.publish_date,
      publish_time: form.value.publish_time,
      status: "active",
    };

    if (announce.value === "now") {
      payload.status = "active";
      const now = new Date();
      const dd = String(now.getDate()).padStart(2, "0");
      const mm = String(now.getMonth() + 1).padStart(2, "0");
      const yyyy = String(now.getFullYear());
      const HH = String(now.getHours()).padStart(2, "0");
      const Min = String(now.getMinutes()).padStart(2, "0");
      const Sec = String(now.getSeconds()).padStart(2, "0");
      payload.publish_at = `${yyyy}-${mm}-${dd} ${HH}:${Min}:${Sec}`;
      payload.publish_date = null;
      payload.publish_time = null;
    } else if (announce.value === "scheduled") {
      payload.status = "scheduled";
      payload.publish_date = form.value.publish_date;
      payload.publish_time = form.value.publish_time;

      if (payload.publish_date && payload.publish_time) {
        payload.publish_at = `${payload.publish_date} ${payload.publish_time}`;
      }
    }

    if (form.value.id) {
      await http.put(`/admin/policies/${form.value.id}`, payload);
    } else {
      await http.post("/admin/policies", payload);
    }

    // console.log('publishPolicy payload', payload)
    // console.log('publishPolicy response', response && response.data)

    const Toast = Swal.mixin({
      toast: true,
      position: "top-end",
      timer: 1500,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.addEventListener("mouseenter", Swal.stopTimer);
        toast.addEventListener("mouseleave", Swal.resumeTimer);
      },
    });
    Toast.fire({
      icon: "success",
      title: "ประกาศนโยบายเรียบร้อยแล้ว",
    }).then(() => {
      router.push({ name: "Policies" });
    });
  } catch (e) {
    console.error("publishPolicy failed:", e);
    alert("เกิดข้อผิดพลาดในการประกาศนโยบาย");
  } finally {
    saving.value = false;
  }
}

function normalizeDate(value) {
  if (!value) {
    return "";
  }
  const s = String(value).trim();
  const m = s.match(/^(\d{2})-(\d{2})-(\d{4})/);
  if (m) {
    return m[1];
  }
  const d = new Date(s);

  if (!isNaN(d)) {
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
  }
  return "";
}

function normalizeTime(value) {
  if (!value) {
    return "";
  }
  const s = String(value).trim();
  const m = s.match(/(\d{2}:\d{2}(?::\d{2})?)/);
  if (m) {
    const t = m[1];
    return t.split(":").slice(0, 2).join(":");
  }
  const d = new Date(s);

  if (!isNaN(d)) {
    const hh = String(d.getHours()).padStart(2, "0");
    const mm = String(d.getMinutes()).padStart(2, "0");
    return `${hh}:${mm}`;
  }
  return "";
}

async function loadPolicy(id) {
  const response = await http.get(`/admin/policies/${id}`);
  const payload = response.data;
  if (!payload) return;

  form.value.id = payload.id;
  form.value.title = payload.title || "";
  form.value.category_id = payload.category_id ?? null;
  form.value.description = payload.description || "";

  // แยกวันที่จาก publish_date , publish_time หรือ publish_at
  const fromPublishAtDate = payload.publish_at
    ? normalizeDate(payload.publish_at)
    : "";
  const fromPublishAtTime = payload.publish_at
    ? normalizeTime(payload.publish_at)
    : "";

  form.value.publish_date = payload.publish_date || fromPublishAtDate || "";
  form.value.publish_time = payload.publish_time || fromPublishAtTime || "";

  content.value = payload.description || "";

  const status = String(payload.status || "").toLowerCase();

  // ตรวจสอบว่ามีการตั้งเวลาประกาศหรือไม่
  const hasDateTime = !!(form.value.publish_date || form.value.publish_time);

  // กำหนดประกาศ ตามสถานะและวันเวลา
  if (status === "draft") {
    // draft ที่มีวันเวลาประกาศ = scheduled , ไม่มีวันเวลาประกาศ = now
    announce.value = hasDateTime ? "scheduled" : "now";
  } else if (status === "scheduled") {
    // scheduled ต้องมีวันเวลาประกาศ
    announce.value = "scheduled";
  } else if (status === "active") {
    // active = ประกาศตอนนี้
    announce.value = "now";
    form.value.publish_date = "";
    form.value.publish_time = "";
  } else {
    // กรณีอื่นๆ ตั้งค่าเป็น now
    announce.value = hasDateTime ? "scheduled" : "now";
  }

  await nextTick();

  try {
    editorRef.value?.setContent?.(content.value || "");
  } catch (e) {
    console.error("setContent failed:", e);
  }
}

function cancel() {
  router.back();
}

onMounted(async () => {
  await loadCategories();
  await nextTick();
  const id = route.params.id || null;
  if (id) await loadPolicy(id);
});
</script>
