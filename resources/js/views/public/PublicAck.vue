<template>
  <div
    class="flex items-center justify-center w-screen min-h-screen px-4 bg-slate-50"
  >
    <!-- ขั้นตอนการยืนยันตัวตน -->
    <section
      v-if="step === 'identity'"
      class="w-full max-w-md bg-white shadow-lg p-6 space-y-5 rounded-xl"
    >
      <div class="flex items-center gap-3">
        <span
          class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600/10 text-blue-700"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1.5"
              d="M12 6.75c1.148 0 2.25-.224 3.258-.632l1.989-.796a1.125 1.125 0 011.503 1.044V12a9.75 9.75 0 01-6.75 9.269A9.75 9.75 0 015.25 12V6.366a1.125 1.125 0 011.503-1.044l1.989.796A8.997 8.997 0 0012 6.75z"
            />
          </svg>
        </span>
        <div>
          <h2 class="text-lg font-semibold">ยืนยันตัวตน</h2>
          <p class="text-sm text-slate-500">
            กรอกเลขบัตรประชาชนและวันเดือนปีเกิดเพื่อเข้าสู่หน้ารับทราบนโยบาย
          </p>
        </div>
      </div>

      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700"
            >รหัสบัตรประชาชน 13 หลัก</label
          >
          <div
            :class="[
              'mt-1 flex items-center gap-2 rounded-lg border px-3 py-2 focus-within:ring-2',
              idTouched && !idValid
                ? 'border-red-400 ring-red-200'
                : 'border-slate-300 ring-blue-200',
            ]"
          >
            <input
              v-model="form.IdentityCard"
              @input="digitsOnly('IdentityCard')"
              @blur="idTouched = true"
              inputmode="numeric"
              pattern="[0-9]*"
              maxlength="13"
              autocomplete="off"
              class="w-full outline-none"
              placeholder="กรอกเลขบัตรประชาชน 13 หลัก"
              type="text"
            />
          </div>
          <p v-if="idTouched && !idValid" class="mt-1 text-xs text-red-600">
            กรุณากรอกเลขบัตรให้ครบ 13 หลัก
          </p>
        </div>

        <!-- วันเดือนปีเกิด -->
        <div>
          <label class="block text-sm font-medium text-slate-700"
            >วันเดือนปีเกิด</label
          >
          <input
            v-model="form.birthDate"
            @blur="birthTouched = true"
            type="date"
            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200"
          />
          <p
            v-if="birthTouched && !birthValid"
            class="mt-1 text-xs text-red-600"
          >
            กรุณาเลือกวันเดือนปีเกิด
          </p>
        </div>
        <button
          @click="checkIdentity"
          :disabled="!canVerify || loading"
          class="px-4 py-2 text-white bg-blue-600"
        >
          <span
            v-if="loading"
            class="h-4 w-4 border-white/60 border-t-white rounded-full animate-spin"
          ></span>
          เข้าสู่ระบบ
        </button>

        <p v-if="error" class="text-red-600">{{ error }}</p>

        <p class="text-xs text-slate-400 text-center">
          ข้อมูลนี้ใช้เฉพาะเพื่อยืนยันตัวตนสำหรับการรับทราบนโยบายเท่านั้น
        </p>
      </div>
    </section>

    <!-- อ่านนโยบายและเซ็นรับทราบ -->
    <section
      v-else
      class="w-full max-w-2xl bg-white rounded-xl shadow-lg p-6 space-y-4"
    >
      <h2 class="text-lg font-semibold">อ่านและยืนยันรับทราบ</h2>

      <div
        ref="content"
        class="h-64 p-3 overflow-y-auto border rounded-md"
        @scroll="onScroll"
      >
        <p v-for="i in 80" :key="i">เนื้อหานโยบายบรรทัดที่ {{ i }}</p>
      </div>

      <label class="flex items-center gap-2">
        <input type="checkbox" v-model="accepted" class="h-4 w-4" />
        <span>ฉันได้อ่านจนถึงท้ายเอกสารและรับทราบ</span>
      </label>

      <div class="space-y-2">
        <h3 class="font-medium">เซ็นชื่อรับทราบ</h3>
        <VueSignaturePad ref="pad" class="h-48 border rounded-md" :options="{ backgroundColor: '#ffffff' }"/>
        <input
          v-model="signer_name"
          placeholder="ชื่อ-นามสกุลผู้ลงชื่อ"
          class="p-2 border"
        />
      </div>

      <button
        :disabled="!canSubmit || loading"
        @click="acknowledge"
        class="px-4 py-2 text-white bg-green-600 disabled:opacity-50"
      >
        ยืนยันรับทราบนโยบาย
      </button>

      <p v-if="error" class="text-red-600">{{ error }}</p>
    </section>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import http from "../../services/BackendService";

const route = useRoute();
const windowId = route.params.window;

const step = ref("identity");
const loading = ref(false);
const error = ref("");
const form = ref({ IdentityCard: "", birthDate: "" });
const employee = ref(null);

// identity validation
const idTouched = ref(false);
const birthTouched = ref(false);

const signer_name = ref("");
const pad = ref(null);
const content = ref(null);
const scrolledToBottom = ref(false);
const accepted = ref(false);

// ตรวจสอบความถูกต้องของรหัสบัตรประชาชนและวันเกิด
const idValid = computed(() => /^\d{13}$/.test(form.value.IdentityCard));
const birthValid = computed(() => !!form.value.birthDate);

const canVerify = computed(() => idValid.value && birthValid.value);

const canSubmit = computed(
  () =>
    scrolledToBottom.value &&
    accepted.value &&
    signer_name.value.trim().length > 0 &&
    pad.value?.signaturePad?.isEmpty?.() === false
);

function digitsOnly(field) {
  form.value[field] = (form.value[field] || "").replace(/\D+/g, "");
}

function onScroll() {
  const el = content.value;
  if (!el) return;
  scrolledToBottom.value = el.scrollTop + el.clientHeight >= el.scrollHeight - 4;
}

async function checkIdentity() {
  idTouched.value = true;
  birthTouched.value = true;
  if (!canVerify.value) {
    error.value = "กรุณากรอกข้อมูลให้ครบถ้วน";
    return;
  }

  error.value = "";
  loading.value = true;
  try {
    const { data } = await http.post(
      `policy-windows/${windowId}/identity-check`,
      form.value
    );
    employee.value = data.employee;
    step.value = "sign";
    // reset sign state
    scrolledToBottom.value = false;
    accepted.value = false;
    signer_name.value = "";
    pad.value?.signaturePad?.clear();
  } catch (e) {
    error.value = e?.response?.data?.message || "ตรวจสอบไม่สำเร็จ";
  } finally {
    loading.value = false;
  }
}

async function acknowledge() {
  if (!canSubmit.value) return;
  error.value = "";
  loading.value = true;
  try {
    const strokes = pad.value?.signaturePad?.toData() || [];
    await http.post(`policy-windows/${windowId}/acknowledge`, {
      employee_code: employee.value?.EmpCode,
      signer_name: signer_name.value,
      signature_strokes: strokes,
      accepted: accepted.value,
      scrolled_to_bottom: scrolledToBottom.value,
    });
    alert("บันทึกการรับทราบเรียบร้อยแล้ว");
    location.href = "/";
  } catch (e) {
    error.value = e?.response?.data?.message || "บันทึกการรับทราบไม่สำเร็จ";
  } finally {
    loading.value = false;
  }
} 
</script>