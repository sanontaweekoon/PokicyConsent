<template>
  <div class="flex items-center justify-center w-screen min-h-screen px-4 bg-slate-50">
    <!-- ขั้นตอนการยืนยันตัวตน -->
    <section v-if="step === 'identity'" class="w-full max-w-md bg-white shadow-lg p-6 space-y-5 rounded-xl">
      <div class="flex items-center gap-3">
        <div class="flex flex-col items-center gap-2 w-full">
          <img src="../../assets/img/logo-red.png" class="w-20" />
          <p class="text-sm text-slate-500">
            กรอกเลขบัตรประชาชนและวันเดือนปีเกิดเพื่อเข้าสู่หน้ารับทราบนโยบาย
          </p>
        </div>
      </div>

      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700">รหัสบัตรประชาชน 13 หลัก</label>
          <div :class="[
            'mt-1 flex items-center gap-2 rounded-lg border px-3 py-2 focus-within:ring-2',
            idTouched && !idValid
              ? 'border-red-400 ring-red-200'
              : 'border-slate-300 ring-blue-200',
          ]">
            <input v-model="form.IdentityCard" @input="digitsOnly('IdentityCard')" @blur="idTouched = true"
              inputmode="numeric" pattern="[0-9]*" maxlength="13" autocomplete="off" class="w-full outline-none"
              placeholder="กรอกเลขบัตรประชาชน 13 หลัก" type="text" />
          </div>
          <p v-if="idTouched && !idValid" class="mt-1 text-xs text-red-600">
            กรุณากรอกเลขบัตรให้ครบ 13 หลัก
          </p>
        </div>

        <!-- วันเดือนปีเกิด -->
        <div>
          <label class="block text-sm font-medium text-slate-700">วันเดือนปีเกิด</label>
          <input v-model="form.birthDate" @blur="birthTouched = true" type="date"
            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200" />
          <p v-if="birthTouched && !birthValid" class="mt-1 text-xs text-red-600">
            กรุณาเลือกวันเดือนปีเกิด
          </p>
          <p class="text-xs text-slate-500 mt-1"> เดือน วัน ปี ค.ศ. เช่น 08/30/2021</p>
        </div>
        <button @click="checkIdentity" :disabled="!canVerify || loading"
          class="w-full rounded-md px-4 py-2 text-white bg-red-700">
          <span v-if="loading"
            class="h-4 w-4 shadow-md border-white/60 border-t-white rounded-full animate-spin"></span>
          เข้าสู่ระบบ
        </button>

        <p v-if="error" class="text-red-600">{{ error }}</p>

        <p class="text-xs text-slate-400 text-center">
          ข้อมูลนี้ใช้เฉพาะเพื่อยืนยันตัวตนสำหรับการรับทราบนโยบายเท่านั้น
        </p>
      </div>
    </section>

    <!-- อ่านนโยบายและเซ็นรับทราบ -->
    <section v-else class="w-full max-w-3xl my-4 bg-white rounded-xl shadow-lg p-6 space-y-4">
      <h2 class="text-lg font-medium">{{ policyTitle }}</h2>

      <div ref="content" class="h-auto p-3 overflow-y-auto border rounded-md bg-white">
        <div v-if="policyHtml" v-html="policyHtml"></div>
        <p v-else>กำลังโหลดเนื้อหานโยบาย...</p>
      </div>

      <div class="flex justify-center">
        <label class="flex items-center gap-2">
          <input type="checkbox" v-model="accepted" class="h-6 w-6" />
          <span>ฉันได้อ่านจนถึงท้ายเอกสารและรับทราบ</span>
        </label>
      </div>

      <h3 class="font-medium">เซ็นชื่อรับทราบ</h3>
      <VueSignaturePad ref="pad" class="h-48 border rounded-md" :options="padOptions" />
      <div class="flex justify-end">
        <button type="button" @click="clearSignature" :disabled="!canClear"
          class="px-4 py-2 flex border rounded-full disabled:opacity-50">

          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" height="20" width="20">
            <path
              d="M178.5 416l123 0 65.3-65.3-173.5-173.5-126.7 126.7 112 112zM224 480l-45.5 0c-17 0-33.3-6.7-45.3-18.7L17 345C6.1 334.1 0 319.4 0 304s6.1-30.1 17-41L263 17C273.9 6.1 288.6 0 304 0s30.1 6.1 41 17L527 199c10.9 10.9 17 25.6 17 41s-6.1 30.1-17 41l-135 135 120 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-288 0z" />
          </svg>
          <p class="px-1">ลบลายเซ็น</p>
        </button>
      </div>
      <div class="space-y-2 flex flex-col items-center">
        <input v-model="signer_name" class="p-2 border text-center bg-slate-50" readonly />
        <div class="flex gap-3">
          <button :disabled="!canSubmit || loading" @click="acknowledge"
            class="px-4 py-2 text-white bg-red-700 rounded-full disabled:opacity-50">
            ยืนยันรับทราบนโยบาย
          </button>
        </div>
      </div>
      <div class="flex justify-center">
        <p v-if="error" class="text-red-600">{{ error }}</p>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from "vue";
import { useRoute } from "vue-router";
import http from "../../services/BackendService";
import Swal from 'sweetalert2';

const route = useRoute();
const windowId = route.params.window;

const step = ref("identity");
const loading = ref(false);
const error = ref("");
const form = ref({ IdentityCard: "", birthDate: "" });
const employee = ref(null);
const acknowledgement = ref(null);

// policy
const policyHtml = ref("");
const policyTitle = ref("")

// identity validation
const idTouched = ref(false);
const birthTouched = ref(false);

const signer_name = ref("");
const pad = ref(null);
const hasSignature = ref(false);
const content = ref(null);
const accepted = ref(false);

// ตรวจสอบความถูกต้องของรหัสบัตรประชาชนและวันเกิด
const idValid = computed(() => /^\d{13}$/.test(form.value.IdentityCard));
const birthValid = computed(() => !!form.value.birthDate);

const canVerify = computed(() => idValid.value && birthValid.value);

function digitsOnly(field) {
  form.value[field] = (form.value[field] || "").replace(/\D+/g, "");
}

function showErrorPopup(msg) {
  error.value = msg || "เกิดข้อผิดพลาด";
}

const padOptions = computed(() => ({
  backgroundColor: '#ffffff',
  onBegin: () => {
    // เริ่มเซ็น
  },
  onEnd: () => {
    // อัปเดตสถานะเมื่อเซ็นจบ
    hasSignature.value = pad.value?.signaturePad?.isEmpty?.() === false;
  }
}));

const canClear = computed(() => hasSignature.value);

function clearSignature() {
  pad.value?.signaturePad?.clear();
  hasSignature.value = false;
}

const canSubmit = computed(
  () =>
    accepted.value &&
    hasSignature.value
);

async function checkIdentity() {
  idTouched.value = true;
  birthTouched.value = true;
  if (!canVerify.value) {
    showErrorPopup("กรุณากรอกเลขบัตร 13 หลัก และวันเดือนปีเกิดให้ครบถ้วน");
    return;
  }

  error.value = "";
  loading.value = true;
  try {
    const { data } = await http.post(
      `policy-windows/${windowId}/identity-check`,
      form.value, {
      headers: { Accept: "application/json" },
    }
    );

    // console.log('Identity check response:', data);

    employee.value = data.employee;
    signer_name.value = data.employee?.Name ?? data.employee?.full_name ?? "";
    policyTitle.value = data.policy?.title ?? "";
    policyHtml.value = data.policy?.content ?? "";
    acknowledgement.value = data.acknowledgement_id;

    if (data.has_signed) {
      // console.log('Already signed');
      await Swal.fire({
        icon: 'info',
        title: 'คุณได้รับทราบนโยบายนี้ไปแล้ว',
        confirmButtonText: 'ตกลง',
        allowOutsideClick: false
      });
      window.close();
      setTimeout(() => {
        if (!window.closed) {
          location.href = "about:blank";
        }
      }, 100)
      return;
    }

    step.value = "sign";
    accepted.value = false;
    pad.value?.signaturePad?.clear();
    hasSignature.value = false;
    await nextTick();
  } catch (e) {
    const msg =
      e?.response?.data?.message ||
      (e?.response?.status === 422 ? "เลขบัตรหรือวันเดือนปีเกิดไม่ถูกต้อง" :
        e?.response?.status === 403 ? "ไม่ได้รับอนุญาตเข้าถึงนโยบายนี้" :
          "ตรวจสอบไม่สำเร็จ");
    showErrorPopup(msg);
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
      signature_strokes: strokes,
      accepted: accepted.value,
      acknowledgement_id: acknowledgement.value,
    });
    alert("บันทึกการรับทราบเรียบร้อยแล้ว");
    pad.value?.signaturePad?.clear();
    hasSignature.value = false;
    window.close();

    setTimeout(() => {
      if (!window.closed) {
        location.href = "about:blank";
      }
    }, 100);
  } catch (e) {
    error.value = e?.response?.data?.message || "บันทึกการรับทราบไม่สำเร็จ";
  } finally {
    loading.value = false;
  }
} 
</script>